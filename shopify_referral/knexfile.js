import "dotenv/config";

export default {
  client: "postgresql",
  connection: process.env.DB_URI,
  migrations: {
    directory: "./migrations",
    tableName: "migrations",
  },
};
