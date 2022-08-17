import { Shopify } from "@shopify/shopify-api";

export class WebhookService {
  async registerAppUnInstall(session) {
    const topic = "APP_UNINSTALLED";
    const response = await Shopify.Webhooks.Registry.register({
      shop: session.shop,
      accessToken: session.accessToken,
      topic,
      path: "/webhooks",
    });

    if (!response[topic].success) {
      console.error(`Failed to register webhook: ${topic}`);
      // TODO: log response
      return false;
    }

    return true;
  }

  async registerOrdersCreate(session) {
    const topic = "ORDERS_CREATE";
    const response = await Shopify.Webhooks.Registry.register({
      path: "/webhooks",
      topic,
      accessToken: session.accessToken,
      shop: session.shop,
    });

    if (!response[topic].success) {
      console.error(`Failed to register webhook: ${topic}`);
      // TODO: log response
      return false;
    }

    return true;
  }
}
