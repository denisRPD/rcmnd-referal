/**
 * @param { import("knex").Knex } knex
 * @returns { Promise<void> }
 */
export const up = (knex) => {
  console.info(
    "[CREATE TABLE] table: referral_settings, migration: 20220511234147_create_referral_settings"
  );
  return knex.schema.createTable("referral_settings", (table) => {
    table.increments("id").primary();
    table.string("shop").unique().notNullable();
    table.string("api_token").notNullable();
    table.string("test_url");
    table.boolean("is_active").defaultTo(false);
    table.dateTime("updated_at").defaultTo(knex.fn.now());
    table.dateTime("created_at").default(knex.fn.now());
  });
};

/**
 * @param { import("knex").Knex } knex
 * @returns { Promise<void> }
 */
export const down = (knex) => {
  console.info(
    "[DROP TABLE] table: referral_settings, migration: 20220511234147_create_referral_settings"
  );
  return knex.schema.dropTable("referral_settings");
};
