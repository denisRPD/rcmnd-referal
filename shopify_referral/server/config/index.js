import "dotenv/config";

import { InvalidConfigError } from "../error/InvalidConfigError.js";

function loadShopifyConf() {
  const apiKey = process.env.SHOPIFY_API_KEY;
  const apiSecret = process.env.SHOPIFY_API_SECRET;
  const scopes = process.env.SCOPES;
  const host = process.env.HOST;
  if (!apiKey) {
    throw new InvalidConfigError("Missing required SHOPIFY_API_KEY");
  }
  if (!apiSecret) {
    throw new InvalidConfigError("Missing required SHOPIFY_API_SECRET");
  }
  if (!scopes) {
    throw new InvalidConfigError("Missing required SCOPES");
  }
  if (!host) {
    throw new InvalidConfigError("Missing required HOST");
  }

  return {
    apiKey,
    apiSecret,
    scopes,
    host,
  };
}

function loadFromEnv() {
  const dbUri = process.env.DB_URI;
  if (!dbUri) {
    throw new InvalidConfigError("Missing required DB_URI");
  }

  return {
    port: parseInt(process.env.PORT || "8081", 10),
    dbUri,
    shopify: loadShopifyConf(),
  };
}

export default loadFromEnv();
