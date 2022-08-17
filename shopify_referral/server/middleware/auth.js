import { Shopify } from "@shopify/shopify-api";

import topLevelAuthRedirect from "../helpers/top-level-auth-redirect.js";
import {
  ACTIVE_SHOPIFY_SHOPS_KEY,
  TOP_LEVEL_OAUTH_COOKIE_KEY,
  USE_ONLINE_TOKENS_KEY,
} from "../config/constants.js";
import { WebhookService } from "../core/webhooks/WebhookService.js";
import { ShopService } from "../shop/services/ShopService.js";

export default function applyAuthMiddleware(app) {
  app.get("/auth", async (req, res) => {
    if (!req.signedCookies[app.get(TOP_LEVEL_OAUTH_COOKIE_KEY)]) {
      return res.redirect(
        `/auth/toplevel?${new URLSearchParams(req.query).toString()}`
      );
    }

    const redirectUrl = await Shopify.Auth.beginAuth(
      req,
      res,
      req.query.shop,
      "/auth/callback",
      app.get(USE_ONLINE_TOKENS_KEY)
    );

    res.redirect(redirectUrl);
  });

  app.get("/auth/toplevel", (req, res) => {
    res.cookie(app.get(TOP_LEVEL_OAUTH_COOKIE_KEY), "1", {
      signed: true,
      httpOnly: true,
      sameSite: "strict",
    });

    res.set("Content-Type", "text/html");

    res.send(
      topLevelAuthRedirect({
        apiKey: Shopify.Context.API_KEY,
        hostName: Shopify.Context.HOST_NAME,
        host: req.query.host,
        query: req.query,
      })
    );
  });

  app.get("/auth/callback", async (req, res) => {
    console.log("Auth callback here");
    try {
      const session = await Shopify.Auth.validateAuthCallback(
        req,
        res,
        req.query
      );
      const host = req.query.host;
      app.set(
        ACTIVE_SHOPIFY_SHOPS_KEY,
        Object.assign(app.get(ACTIVE_SHOPIFY_SHOPS_KEY), {
          [session.shop]: session.scope,
        })
      );

      // register shop in the DB
      await ShopService.registerShop(session.shop, session);

      const webHookService = new WebhookService();
      await webHookService.registerAppUnInstall(session);
      await webHookService.registerOrdersCreate(session);

      // Redirect to app with shop parameter upon auth
      res.redirect(`/?shop=${session.shop}&host=${host}`);
    } catch (e) {
      console.error(e);
      switch (true) {
        case e instanceof Shopify.Errors.InvalidOAuthError:
          res.status(400);
          res.send(e.message);
          break;
        case e instanceof Shopify.Errors.CookieNotFound:
        case e instanceof Shopify.Errors.SessionNotFound:
          // This is likely because the OAuth session cookie expired before the merchant approved the request
          res.redirect(`/auth?shop=${req.query.shop}`);
          break;
        default:
          res.status(500);
          res.send(e.message);
          break;
      }
    }
  });
}
