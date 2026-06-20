const { query } = require('../db/client.cjs');

function asPositiveInt(value) {
  const parsed = Number(value);
  if (!Number.isInteger(parsed) || parsed <= 0) return null;
  return parsed;
}

async function getAdminStats() {
  const rows = await query(`
    SELECT 
      (SELECT COUNT(*) FROM events) as events_total,
      (SELECT COUNT(*) FROM events WHERE is_published = 1) as events_published,
      (SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()) as orders_today,
      (SELECT COALESCE(SUM(quantity), 0) FROM orders WHERE status = 'paid' AND DATE(created_at) = CURDATE()) as tickets_today,
      (SELECT COALESCE(SUM(amount), 0) FROM orders WHERE status = 'paid' AND DATE(created_at) = CURDATE()) as revenue_today
  `);

  return {
    events_total: Number(rows[0]?.events_total || 0),
    events_published: Number(rows[0]?.events_published || 0),
    orders_today: Number(rows[0]?.orders_today || 0),
    tickets_today: Number(rows[0]?.tickets_today || 0),
    revenue_today: Number(rows[0]?.revenue_today || 0),
  };
}

async function getChartData(days = 14) {
  const labels = [];
  const ticketsSeries = [];
  const revenueSeries = [];

  for (let i = days - 1; i >= 0; i--) {
    const date = new Date();
    date.setDate(date.getDate() - i);
    const dateStr = date.toISOString().split('T')[0];
    labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));

    const rows = await query(`
      SELECT 
        COALESCE(SUM(quantity), 0) as tickets,
        COALESCE(SUM(amount), 0) as revenue
      FROM orders 
      WHERE status = 'paid' AND DATE(created_at) = ?
    `, [dateStr]);

    ticketsSeries.push(Number(rows[0]?.tickets || 0));
    revenueSeries.push(Number(rows[0]?.revenue || 0));
  }

  return { labels, tickets: ticketsSeries, revenue: revenueSeries };
}

async function getUpcomingEvents(limit = 6) {
  const rows = await query(`
    SELECT id, title, starts_at, venue, is_published
    FROM events
    WHERE is_published = 1
      AND (ends_at IS NULL OR ends_at >= datetime('now'))
    ORDER BY starts_at ASC
    LIMIT ?
  `, [limit]);

  return rows;
}

async function toggleEventPublish(eventId) {
  const id = asPositiveInt(eventId);
  if (!id) return null;

  const rows = await query(`
    UPDATE events 
    SET is_published = NOT is_published 
    WHERE id = ?
  `, [id]);

  const event = await query(`SELECT id, is_published FROM events WHERE id = ? LIMIT 1`, [id]);
  return event[0] || null;
}

async function getOrdersList(page = {}) {
  const limit = Math.min(Math.max(Number(page.limit || 20), 1), 100);
  const offset = Math.max(Number(page.offset || 0), 0);

  const rows = await query(`
    SELECT o.*, e.title as event_title
    FROM orders o
    LEFT JOIN events e ON o.event_id = e.id
    ORDER BY o.created_at DESC
    LIMIT ? OFFSET ?
  `, [limit, offset]);

  return rows;
}

async function getOrderById(orderId) {
  const id = asPositiveInt(orderId);
  if (!id) return null;

  const rows = await query(`
    SELECT o.*, e.title as event_title
    FROM orders o
    LEFT JOIN events e ON o.event_id = e.id
    WHERE o.id = ?
    LIMIT 1
  `, [id]);

  return rows[0] || null;
}

async function getHostTokens(eventId) {
  const id = asPositiveInt(eventId);
  if (!id) return [];

  const rows = await query(`
    SELECT * FROM host_tokens
    WHERE event_id = ?
    ORDER BY created_at DESC
  `, [id]);

  return rows;
}

async function createHostToken(eventId, label = null) {
  const id = asPositiveInt(eventId);
  if (!id) return null;

  const crypto = require('crypto');
  const token = 'H_' + crypto.randomBytes(24).toString('hex');

  const rows = await query(`
    INSERT INTO host_tokens (event_id, token, label, active, expires_at)
    VALUES (?, ?, ?, 1, DATE_ADD((SELECT ends_at FROM events WHERE id = ?), INTERVAL 1 DAY))
  `, [id, token, label || null, id]);

  return { id: rows.insertId, token, label, active: true };
}

module.exports = {
  getAdminStats,
  getChartData,
  getUpcomingEvents,
  toggleEventPublish,
  getOrdersList,
  getOrderById,
  getHostTokens,
  createHostToken,
};
