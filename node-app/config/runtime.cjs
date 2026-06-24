const DEFAULT_NODE_ORIGIN = process.env.NODE_PUBLIC_URL || 'http://localhost:3000';

function normalizeOrigin(value) {
  const raw = String(value || '').trim();

  if (!raw) return DEFAULT_NODE_ORIGIN;
  if (raw.startsWith('http://') || raw.startsWith('https://')) return raw;

  return `https://${raw}`;
}

function resolveLaravelOrigin() {
  return normalizeOrigin(process.env.LARAVEL_ORIGIN || process.env.NODE_PUBLIC_URL || DEFAULT_NODE_ORIGIN);
}

module.exports = {
  DEFAULT_LARAVEL_ORIGIN: DEFAULT_NODE_ORIGIN,
  DEFAULT_NODE_ORIGIN,
  normalizeOrigin,
  resolveLaravelOrigin,
};
