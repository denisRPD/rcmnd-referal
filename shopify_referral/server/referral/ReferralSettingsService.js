import db from "../db/index.js";
import { ReferralSettings } from "./entities/ReferralSettings.js";

export class ReferralSettingsService {
  static async findByShopId (shopId) {
    const settings = await db("referral_settings")
      .where({ shop: shopId })
      .first();
    if (!settings) {
      return null;
    }
    return this.mapToDomain(settings);
  }

  static async delete (shop) {
    await db("referral_settings")
      .where({ shop })
      .delete();
  }

  static async create (shop, apiToken, testUrl, isActive) {
    const settings = await db("referral_settings").insert({
      shop,
      api_token: apiToken,
      test_url: testUrl,
      is_active: isActive,
    });

    return this.mapToDomain(settings);
  }

  static async update (referralSettings, apiToken, testUrl, isActive) {
    const settings = await db("referral_settings")
      .update({
        api_token: apiToken,
        test_url: testUrl,
        is_active: isActive,
      })
      .where({
        shop: referralSettings.getShop(),
      });

    return await this.findByShopId(referralSettings.getShop());
  }

  static async setIsActive (referralSettings, isActive) {
    const settings = await db("referral_settings")
      .update({
        is_active: isActive,
      })
      .where({
        shop: referralSettings.getShop(),
      });

    return await this.findByShopId(referralSettings.getShop());
  }

  static mapToDomain (row) {
    return new ReferralSettings(
      row.shop,
      row.api_token,
      row.test_url,
      row.is_active,
    );
  }
}
