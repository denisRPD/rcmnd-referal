export class InvalidConfigError extends Error {

  constructor (message) {
    super(`Invalid config error: ${message}`);
  }
}
