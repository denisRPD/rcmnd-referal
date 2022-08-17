import axios from "axios";

export class RecommendReferralService {
  constructor(baseUrl, apiKey) {
    this.apiClient = axios.create({
      baseURL: baseUrl,
      timeout: 4000,
    });
    this.apiKey = apiKey;
  }

  async registerReferralCode(code) {
    const { data } = await this.apiClient.post("", {
      code,
      apiToken: this.apiKey,
    });
    return data;
  }
}
