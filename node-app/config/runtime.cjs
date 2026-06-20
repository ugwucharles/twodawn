const DEFAULT_LARAVEL_ORIGIN = 'https://twodawn.com.ng';

function normalizeOrigin(value) {
  const raw = String(value || '').trim();

  if (!raw) return DEFAULT_LARAVEL_ORIGIN;
  if (raw.startsWith('http://') || raw.startsWith('https://')) return raw;

  return `https://${raw}`;
}

function resolveLaravelOrigin() {
  return normalizeOrigin(process.env.LARAVEL_ORIGIN);
}

module.exports = {
  DEFAULT_LARAVEL_ORIGIN,
  normalizeOrigin,
  resolveLaravelOrigin,
};
