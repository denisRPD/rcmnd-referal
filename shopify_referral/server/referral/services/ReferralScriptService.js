import { ScriptTag } from "@shopify/shopify-api/dist/rest-resources/2022-04/index.js";

const RMD_REFERRAL_SRC = "https://klip-kod.com/rmd/recommend-referral.js";
const RMD_REFERRAL_TY_SRC =
  "https://klip-kod.com/rmd/recommend-referral-thank-you.js";

export class ReferralScriptService {
  async registerReferral(session) {
    await this.registerReferralScript(session);
    await this.registerCookieCleaner(session);
  }

  async deleteScriptTags(session) {
    await this.deleteScriptTagBySrc(session, RMD_REFERRAL_SRC);
    await this.deleteScriptTagBySrc(session, RMD_REFERRAL_TY_SRC);
  }

  async deleteScriptTagBySrc(session, src) {
    console.log("deleting script tag", src);
    const tags = await ScriptTag.all({
      session,
      src,
    });

    for (const tag of tags) {
      await ScriptTag.delete({
        session,
        id: tag.id,
      });
    }
  }

  async registerReferralScript(session) {
    console.log("Register referral script tag");
    const scriptTag = new ScriptTag({ session });
    scriptTag.event = "onload";
    scriptTag.display_scope = "online_store";
    scriptTag.src = RMD_REFERRAL_SRC;
    await scriptTag.save({});
  }

  async registerCookieCleaner(session) {
    console.log("Register referral ty script tag");
    const scriptTag = new ScriptTag({ session });
    scriptTag.event = "onload";
    scriptTag.display_scope = "order_status";
    scriptTag.src = RMD_REFERRAL_TY_SRC;
    await scriptTag.save({});
  }
}
