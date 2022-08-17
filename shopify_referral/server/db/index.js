import knex from "knex";
import dbConfig from "../config/db-config.js";

const db = knex(dbConfig);

export default db;
