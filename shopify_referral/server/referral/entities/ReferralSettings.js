export class ReferralSettings {
  constructor(shop, apiToken, testUrl, active) {
    this.shop = shop;
    this.apiToken = apiToken;
    this.testUrl = testUrl;
    this.active = active;
  }

  getShop() {
    return this.shop;
  }

  getApiToken() {
    return this.apiToken;
  }

  getTestUrl() {
    return this.testUrl;
  }

  isActive() {
    return this.active;
  }
}
