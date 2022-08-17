export class ReferralFormatter {
  /**
   *
   * @param referralSetting
   * @returns {Promise<{}>}
   */
  async format(referralSetting) {
    return {
      shop: referralSetting.getShop(),
      apiToken: referralSetting.getApiToken(),
      testUrl: referralSetting.getTestUrl(),
      isActive: referralSetting.isActive(),
    };
  }
}
