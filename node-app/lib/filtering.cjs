const NIGERIAN_STATES = {
  abia: 'Abia',
  adamawa: 'Adamawa',
  'akwa-ibom': 'Akwa Ibom',
  anambra: 'Anambra',
  bauchi: 'Bauchi',
  bayelsa: 'Bayelsa',
  benue: 'Benue',
  borno: 'Borno',
  'cross-river': 'Cross River',
  delta: 'Delta',
  ebonyi: 'Ebonyi',
  edo: 'Edo',
  ekiti: 'Ekiti',
  enugu: 'Enugu',
  gombe: 'Gombe',
  imo: 'Imo',
  jigawa: 'Jigawa',
  kaduna: 'Kaduna',
  kano: 'Kano',
  katsina: 'Katsina',
  kebbi: 'Kebbi',
  kogi: 'Kogi',
  kwara: 'Kwara',
  lagos: 'Lagos',
  nasarawa: 'Nasarawa',
  niger: 'Niger',
  ogun: 'Ogun',
  ondo: 'Ondo',
  osun: 'Osun',
  oyo: 'Oyo',
  plateau: 'Plateau',
  rivers: 'Rivers',
  sokoto: 'Sokoto',
  taraba: 'Taraba',
  yobe: 'Yobe',
  zamfara: 'Zamfara',
  abuja: 'Abuja (FCT)',
};

function normalizeStateFilterValue(value) {
  if (value === null || value === undefined) return null;

  const raw = String(value).trim().toLowerCase();
  if (!raw) return null;

  const aliases = {
    'abuja (fct)': 'abuja',
    'abuja fct': 'abuja',
    'akwa ibom': 'akwa-ibom',
    'cross river': 'cross-river',
    'fct': 'abuja',
  };

  if (aliases[raw]) return aliases[raw];
  if (Object.prototype.hasOwnProperty.call(NIGERIAN_STATES, raw)) return raw;

  const labelMap = Object.fromEntries(
    Object.entries(NIGERIAN_STATES).map(([code, label]) => [label.toLowerCase(), code])
  );

  return labelMap[raw] || raw;
}

function buildEventFiltersFromQuery(query = {}) {
  const filters = {};

  if (query.mood) filters.mood = query.mood;

  const state = normalizeStateFilterValue(query.state);
  if (state) filters.state = state;

  if (query.price) filters.price = query.price;
  if (query.date) filters.date = query.date;
  if (query.q) filters.q = query.q;

  return filters;
}

function getEventPriceValue(event) {
  if (!event) return 0;

  if (event.price !== null && event.price !== undefined && event.price !== '') {
    const numericPrice = Number(event.price);
    if (!Number.isNaN(numericPrice) && numericPrice > 0) return numericPrice;
  }

  if (Array.isArray(event.ticket_types)) {
    const paidTicketTypes = event.ticket_types.filter((ticket) => {
      const price = Number(ticket?.price);
      return Number.isFinite(price) && price > 0;
    });

    if (paidTicketTypes.length > 0) {
      return Math.min(...paidTicketTypes.map((ticket) => Number(ticket.price)));
    }
  }

  return 0;
}

function matchesPriceFilter(event, priceFilter) {
  if (!priceFilter) return true;

  const priceValue = getEventPriceValue(event);

  if (priceFilter === 'free') return priceValue <= 0;
  if (priceFilter === 'paid') return priceValue > 0;
  return true;
}

function matchesDateFilter(event, dateFilter, now = new Date()) {
  if (!dateFilter || !event?.starts_at) return true;

  const startDate = new Date(event.starts_at);
  if (Number.isNaN(startDate.getTime())) return true;

  const currentDate = now instanceof Date ? now : new Date(now);
  if (Number.isNaN(currentDate.getTime())) return true;

  if (dateFilter === 'today') {
    return startDate.toDateString() === currentDate.toDateString();
  }

  if (dateFilter === 'weekend') {
    const currentDay = currentDate.getDay();
    const dayOffset = currentDay === 0 ? 6 : currentDay;
    const startOfWeek = new Date(currentDate);
    startOfWeek.setDate(currentDate.getDate() - dayOffset);
    startOfWeek.setHours(0, 0, 0, 0);

    const endOfWeekend = new Date(startOfWeek);
    endOfWeekend.setDate(startOfWeek.getDate() + 6);
    endOfWeekend.setHours(23, 59, 59, 999);

    return startDate >= startOfWeek && startDate <= endOfWeekend;
  }

  if (dateFilter === 'next-week') {
    const nextWeekStart = new Date(currentDate);
    nextWeekStart.setDate(currentDate.getDate() + 7);
    nextWeekStart.setHours(0, 0, 0, 0);

    const nextWeekEnd = new Date(nextWeekStart);
    nextWeekEnd.setDate(nextWeekStart.getDate() + 6);
    nextWeekEnd.setHours(23, 59, 59, 999);

    return startDate >= nextWeekStart && startDate <= nextWeekEnd;
  }

  return true;
}

function resolvePaystackCallbackUrl(req = {}, env = process.env) {
  const configuredCallbackUrl = String(env.PAYSTACK_CALLBACK_URL || '').trim();
  const requestHost = req?.get?.('host') || req?.headers?.host;
  const forwardedProto = req?.headers?.['x-forwarded-proto'];
  const requestProtocol = req?.protocol || (typeof forwardedProto === 'string' ? forwardedProto : Array.isArray(forwardedProto) ? forwardedProto[0] : null) || 'https';
  const requestCallbackUrl = requestHost ? `${requestProtocol}://${requestHost}/paystack/callback` : null;
  const defaultBackendUrl = env.BACKEND_URL || env.APP_URL || 'https://twodawn-frontend.vercel.app';

  const usesFrontendHost = configuredCallbackUrl && /twodawn-frontend(?:-real)?\.vercel\.app/i.test(configuredCallbackUrl);

  if (requestCallbackUrl && (!configuredCallbackUrl || usesFrontendHost)) {
    return requestCallbackUrl;
  }

  if (configuredCallbackUrl) {
    return configuredCallbackUrl;
  }

  return requestCallbackUrl || `${String(defaultBackendUrl).replace(/\/$/, '')}/paystack/callback`;
}

module.exports = {
  NIGERIAN_STATES,
  normalizeStateFilterValue,
  buildEventFiltersFromQuery,
  matchesPriceFilter,
  matchesDateFilter,
  resolvePaystackCallbackUrl,
};
