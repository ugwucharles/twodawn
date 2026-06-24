const test = require('node:test');
const assert = require('node:assert/strict');

const {
  normalizeStateFilterValue,
  buildEventFiltersFromQuery,
  resolvePaystackCallbackUrl,
  matchesPriceFilter,
  matchesDateFilter,
} = require('../lib/filtering.cjs');

test('normalizes UI state labels to backend-friendly values', () => {
  assert.equal(normalizeStateFilterValue('Lagos'), 'lagos');
  assert.equal(normalizeStateFilterValue('Abuja (FCT)'), 'abuja');
  assert.equal(normalizeStateFilterValue('Akwa Ibom'), 'akwa-ibom');
  assert.equal(normalizeStateFilterValue('  '), null);
});

test('builds backend filters from query params and normalizes state', () => {
  const filters = buildEventFiltersFromQuery({
    price: 'free',
    date: 'today',
    state: 'Abuja (FCT)',
  });

  assert.deepEqual(filters, {
    price: 'free',
    date: 'today',
    state: 'abuja',
  });
});

test('uses the incoming request host when no explicit callback URL is configured', () => {
  const callbackUrl = resolvePaystackCallbackUrl(
    {
      protocol: 'https',
      get(name) {
        return name === 'host' ? 'api.twodawn.com.ng' : undefined;
      },
    },
    {}
  );

  assert.equal(callbackUrl, 'https://api.twodawn.com.ng/paystack/callback');
});

test('does not treat events with paid ticket tiers as free', () => {
  const event = {
    price: 0,
    ticket_types: [{ name: 'VIP', price: '5000' }],
  };

  assert.equal(matchesPriceFilter(event, 'free'), false);
  assert.equal(matchesPriceFilter(event, 'paid'), true);
});

test('matches the expected date window for today and weekend filters', () => {
  const now = new Date('2026-06-24T12:00:00.000Z');
  const todayEvent = { starts_at: '2026-06-24T18:00:00.000Z' };
  const weekendEvent = { starts_at: '2026-06-27T18:00:00.000Z' };
  const nextWeekEvent = { starts_at: '2026-07-01T18:00:00.000Z' };

  assert.equal(matchesDateFilter(todayEvent, 'today', now), true);
  assert.equal(matchesDateFilter(weekendEvent, 'weekend', now), true);
  assert.equal(matchesDateFilter(nextWeekEvent, 'next-week', now), true);
  assert.equal(matchesDateFilter(nextWeekEvent, 'today', now), false);
});
