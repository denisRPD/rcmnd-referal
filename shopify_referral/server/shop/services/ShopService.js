import db from "../../db/index.js";
import { Shop } from "../entities/Shop.js";

export class ShopService {
  static async registerShop(shop, shopMeta) {
    const existingShop = await ShopService.findByShop(shop);
    if (existingShop) {
      return existingShop;
    }

    const entity = await db("shops").insert({
      shop,
      shop_meta: shopMeta,
    });

    return ShopService.mapToDomain(entity);
  }

  static async findByShop(shop) {
    const row = await db("shops").where({ shop }).first();
    if (!row) {
      return null;
    }
    return this.mapToDomain(row);
  }

  static async findById(id) {
    const row = await db("shops").where({ id }).first();
    if (!row) {
      return null;
    }
    return this.mapToDomain(row);
  }

  static mapToDomain(row) {
    return new Shop(row["id"], row["shop"], row["shop_meta"]);
  }
}
