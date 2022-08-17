export const up = (knex) => {
  console.info(
    "[CREATE TABLE] table: shops, migration: 20220526174220_create_shops"
  );
  return knex.schema.createTable("shops", (table) => {
    table.increments("id").primary();
    table.string("shop").notNullable();
    table.text("shop_meta").defaultTo("");
    table.string("referral_tag_id").nullable().defaultTo(null);
    table.string("referral_ty_tag_id").nullable().defaultTo(null);
    table.dateTime("created_at").default(knex.fn.now());
  });
};

export const down = (knex) => {
  console.info(
    "[DROP TABLE] table:: shops, migration: 20220526174220_create_shops"
  );
  return knex.schema.dropTable("shops");
};
