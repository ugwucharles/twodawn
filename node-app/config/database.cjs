const DEFAULT_DB_PORT = 3306;
const DEFAULT_POOL_LIMIT = 10;
const DEFAULT_CONNECT_TIMEOUT_MS = 10000;

function firstNonEmpty(...values) {
  for (const value of values) {
    if (value === undefined || value === null) continue;
    const text = String(value).trim();
    if (text.length > 0) return text;
  }
  return null;
}

function firstDefined(...values) {
  for (const value of values) {
    if (value !== undefined && value !== null) return value;
  }
  return null;
}

function asPositiveInteger(value, fallback) {
  const parsed = Number(value);
  if (!Number.isFinite(parsed) || parsed <= 0) return fallback;
  return Math.floor(parsed);
}

function asBoolean(value, fallback = false) {
  if (value === undefined || value === null || String(value).trim() === '') return fallback;

  const raw = String(value).trim().toLowerCase();
  if (['1', 'true', 'yes', 'on'].includes(raw)) return true;
  if (['0', 'false', 'no', 'off'].includes(raw)) return false;

  return fallback;
}

function resolveSslConfig(env) {
  const sslEnabled = asBoolean(firstNonEmpty(env.DB_SSL, env.MYSQL_SSL), false);
  if (!sslEnabled) return undefined;

  return {
    rejectUnauthorized: asBoolean(env.DB_SSL_REJECT_UNAUTHORIZED, true),
  };
}

function readDatabaseConfig(env = process.env) {
  const host = firstNonEmpty(env.DB_HOST, env.MYSQL_HOST);
  const port = asPositiveInteger(firstNonEmpty(env.DB_PORT, env.MYSQL_PORT), DEFAULT_DB_PORT);
  const user = firstNonEmpty(env.DB_USERNAME, env.DB_USER, env.MYSQL_USER);
  const password = firstDefined(env.DB_PASSWORD, env.DB_PASS, env.MYSQL_PASSWORD, '');
  const database = firstNonEmpty(env.DB_DATABASE, env.MYSQL_DATABASE);
  const connectionLimit = asPositiveInteger(env.DB_POOL_LIMIT, DEFAULT_POOL_LIMIT);
  const connectTimeout = asPositiveInteger(env.DB_CONNECT_TIMEOUT_MS, DEFAULT_CONNECT_TIMEOUT_MS);
  const ssl = resolveSslConfig(env);

  return {
    host,
    port,
    user,
    password,
    database,
    connectionLimit,
    connectTimeout,
    ssl,
  };
}

function isDatabaseConfigured(config) {
  return Boolean(config.host && config.user && config.database);
}

function toPublicDatabaseConfig(config) {
  return {
    host: config.host || null,
    port: config.port || null,
    database: config.database || null,
    ssl: Boolean(config.ssl),
  };
}

module.exports = {
  readDatabaseConfig,
  isDatabaseConfigured,
  toPublicDatabaseConfig,
};
