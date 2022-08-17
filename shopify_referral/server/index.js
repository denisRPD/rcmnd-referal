// @ts-check
import { resolve } from "path";
import express from "express";
import cookieParser from "cookie-parser";
import bodyParser from "body-parser";
import { ApiVersion, Shopify } from "@shopify/shopify-api";
import { ScriptTag } from "@shopify/shopify-api/dist/rest-resources/2022-04/index.js";

import appConfig from "./config/index.js";

import applyAuthMiddleware from "./middleware/auth.js";
import verifyRequest from "./middleware/verify-request.js";
import {
  ACTIVE_SHOPIFY_SHOPS_KEY,
  TOP_LEVEL_OAUTH_COOKIE,
  TOP_LEVEL_OAUTH_COOKIE_KEY,
  USE_ONLINE_TOKENS_KEY,
} from "./config/constants.js";
import { ReferralSettingsService } from "./referral/ReferralSettingsService.js";
import { ReferralScriptService } from "./referral/services/ReferralScriptService.js";
import { RecommendReferralService } from "./referral/services/RecommendReferralService.js";

const isTest = process.env.NODE_ENV === "test" || !!process.env.VITE_TEST_BUILD;

Shopify.Context.initialize({
  API_KEY: appConfig.shopify.apiKey,
  API_SECRET_KEY: appConfig.shopify.apiSecret,
  SCOPES: appConfig.shopify.scopes.split(","),
  HOST_NAME: appConfig.shopify.host.replace(/https:\/\//, ""),
  API_VERSION: ApiVersion.April22,
  IS_EMBEDDED_APP: true,
  // This should be replaced with your preferred storage strategy
  SESSION_STORAGE: new Shopify.Session.MemorySessionStorage(),
});

// Storing the currently active shops in memory will force them to re-login when your server restarts. You should
// persist this object in your app.
const ACTIVE_SHOPIFY_SHOPS = {};
Shopify.Webhooks.Registry.addHandler("APP_UNINSTALLED", {
  path: "/webhooks",
  webhookHandler: async (topic, shop, body) => {
    console.log("")
    delete ACTIVE_SHOPIFY_SHOPS[shop];
    await ReferralSettingsService.delete(shop);
  },
});

Shopify.Webhooks.Registry.addHandler("ORDERS_CREATE", {
  path: "/webhooks",
  webhookHandler: async (topic, shop, body) => {
    console.log("orders/create handler");
    const order = JSON.parse(body);
    const { note_attributes: noteAttributes } = order;
    if (!noteAttributes || !noteAttributes.length) {
      console.log("Note attributes not present");
      return;
    }
    const referralNote = noteAttributes.find((na) => na.name === "rcmndref");
    if (!referralNote) {
      console.log("Referral note not present");
      return;
    }

    const referral = referralNote.value;
    if (!referral) {
      console.log("Referral note is present but empty");
      return;
    }

    const settings = await ReferralSettingsService.findByShopId(shop);
    if (!settings) {
      console.log("Missing referral settings for shop", shop);
      return;
    }

    if (!settings.isActive()) {
      console.log("Referral is inactive for shop", shop);
      return;
    }

    try {
      const rmdClient = new RecommendReferralService(
        settings.testUrl,
        settings.apiToken
      );
      const resp = await rmdClient.registerReferralCode(referral);
      console.log("sent to rcmnd", resp);
    } catch (e) {
      console.error(e);
    }
  },
});

// export for test use only
export async function createServer(
  root = process.cwd(),
  isProd = process.env.NODE_ENV === "production"
) {
  const app = express();
  app.set(TOP_LEVEL_OAUTH_COOKIE_KEY, TOP_LEVEL_OAUTH_COOKIE);
  app.set(ACTIVE_SHOPIFY_SHOPS_KEY, ACTIVE_SHOPIFY_SHOPS);
  app.set(USE_ONLINE_TOKENS_KEY, true);

  app.use(cookieParser(Shopify.Context.API_SECRET_KEY));

  applyAuthMiddleware(app);

  app.post("/webhooks", async (req, res) => {
    console.log("webhook endpoint hit");
    // console.log(req);
    try {
      await Shopify.Webhooks.Registry.process(req, res);
      console.log(`Webhook processed, returned status code 200`);
    } catch (error) {
      console.log(`Failed to process webhook: ${error}`);
      if (!res.headersSent) {
        res.status(500).send(error.message);
      }
    }
  });

  app.use(bodyParser.json());
  app.use(bodyParser.urlencoded({ extended: true }));

  /**
   * Fetch referral settings for shop. Returns
   */
  app.get("/referral-settings", verifyRequest(app), async (req, res) => {
    const session = await Shopify.Utils.loadCurrentSession(req, res, true);
    const settings = await ReferralSettingsService.findByShopId(session.shop);

    if (!settings) {
      return {
        shop: session.shop,
        apiToken: "",
        testUrl: "",
        isActive: false,
      };
    }

    return res.status(200).json({
      shop: settings.getShop(),
      apiToken: settings.getApiToken(),
      testUrl: settings.getTestUrl(),
    });
  });

  /**
   * Create or update referral settings
   */
  app.put("/referral-settings", verifyRequest(app), async (req, res) => {
    const session = await Shopify.Utils.loadCurrentSession(req, res, true);
    const shop = session.shop;
    const { apiToken, testUrl, isActive } = req.body;
    if (!apiToken || !testUrl || isActive === undefined) {
      // TODO: add validator here
      console.log("Bad request - missing apiToken, testUrl or isActive");
      return res.status(400).json({});
    }
    console.log("Searching settings for shop");
    let settings = await ReferralSettingsService.findByShopId(shop);
    if (!settings) {
      console.log("Creating settings for shop", shop);
      settings = await ReferralSettingsService.create(
        shop,
        apiToken,
        testUrl,
        isActive
      );
    } else {
      console.log("Updating settings");
      settings = await ReferralSettingsService.update(
        settings,
        apiToken,
        testUrl,
        isActive
      );
    }

    const rs = new ReferralScriptService();
    if (settings.isActive()) {
      await rs.deleteScriptTags(session);
      await rs.registerReferral(session);
    } else {
      await rs.deleteScriptTags(session);
    }

    return res.status(200).json({
      shop: settings.getShop(),
      apiToken: settings.getApiToken(),
      testUrl: settings.getTestUrl(),
      isActive: settings.isActive(),
    });
  });

  app.post("/graphql", verifyRequest(app), async (req, res) => {
    try {
      const response = await Shopify.Utils.graphqlProxy(req, res);
      res.status(200).send(response.body);
    } catch (error) {
      res.status(500).send(error.message);
    }
  });

  app.use(express.json());

  app.use((req, res, next) => {
    const shop = req.query.shop;
    if (Shopify.Context.IS_EMBEDDED_APP && shop) {
      res.setHeader(
        "Content-Security-Policy",
        `frame-ancestors https://${shop} https://admin.shopify.com;`
      );
    } else {
      res.setHeader("Content-Security-Policy", `frame-ancestors 'none';`);
    }
    next();
  });

  app.use("/*", (req, res, next) => {
    const { shop } = req.query;

    // Detect whether we need to reinstall the app, any request from Shopify will
    // include a shop in the query parameters.
    if (app.get("active-shopify-shops")[shop] === undefined && shop) {
      res.redirect(`/auth?${new URLSearchParams(req.query).toString()}`);
    } else {
      next();
    }
  });

  /**
   * @type {import("vite").ViteDevServer}
   */
  let vite;
  if (!isProd) {
    vite = await import("vite").then(({ createServer }) =>
      createServer({
        root,
        logLevel: isTest ? "error" : "info",
        server: {
          port: appConfig.port,
          hmr: {
            protocol: "ws",
            host: "localhost",
            port: 64999,
            clientPort: 64999,
          },
          middlewareMode: "html",
        },
      })
    );
    app.use(vite.middlewares);
  } else {
    const compression = await import("compression").then(
      ({ default: fn }) => fn
    );
    const serveStatic = await import("serve-static").then(
      ({ default: fn }) => fn
    );
    const fs = await import("fs");
    app.use(compression());
    app.use(serveStatic(resolve("dist/client")));
    app.use("/*", (req, res, next) => {
      // Client-side routing will pick up on the correct route to render, so we always render the index here
      res
        .status(200)
        .set("Content-Type", "text/html")
        .send(fs.readFileSync(`${process.cwd()}/dist/client/index.html`));
    });
  }

  return { app, vite };
}

if (!isTest) {
  createServer().then(({ app }) => app.listen(appConfig.port));
}
