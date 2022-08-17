import config from "./index.js";

export default {
  client: "postgresql",
  connection: config.dbUri,
  pool: {
    min: 2,
    max: 10,
  },
};
