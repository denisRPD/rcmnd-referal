export class Shop {
  constructor(id, shop, meta) {
    this.shop = shop;
    this.id = id;
    this.shopMeta = meta;
  }

  getShop() {
    return this.shop;
  }

  getId() {
    return this.id;
  }

  getMeta() {
    return this.shopMeta;
  }
}
