const { query } = require('../db/client.cjs');

function asPositiveInt(value) {
  const parsed = Number(value);
  if (!Number.isInteger(parsed) || parsed <= 0) return null;
  return parsed;
}

async function getHostTokenByToken(token) {
  const normalizedToken = String(token || '').trim();
  if (!normalizedToken) return null;

  const rows = await query(`
    SELECT ht.*, e.title as event_title, e.id as event_id, e.ends_at
    FROM host_tokens ht
    LEFT JOIN events e ON ht.event_id = e.id
    WHERE ht.token = ?
    LIMIT 1
  `, [normalizedToken]);

  return rows[0] || null;
}

async function validateHostToken(token) {
  const host = await getHostTokenByToken(token);
  if (!host) return null;

  const now = new Date();
  const expiresAt = host.expires_at ? new Date(host.expires_at) : null;

  if (!host.active || (expiresAt && expiresAt < now)) {
    return null;
  }

  return host;
}

async function getHostStats(eventId) {
  const id = asPositiveInt(eventId);
  if (!id) return null;

  const rows = await query(`
    SELECT 
      (SELECT COALESCE(SUM(quantity), 0) FROM orders WHERE event_id = ? AND status = 'paid') as sold,
      (SELECT COALESCE(SUM(count), 0) FROM order_checkins oc 
       JOIN orders o ON oc.order_id = o.id 
       WHERE o.event_id = ? AND o.status = 'paid') as checked
  `, [id, id]);

  const sold = Number(rows[0]?.sold || 0);
  const checked = Number(rows[0]?.checked || 0);
  const remaining = Math.max(0, sold - checked);

  return { sold, checked, remaining };
}

async function getHostCheckins(eventId, page = {}) {
  const id = asPositiveInt(eventId);
  if (!id) return [];

  const limit = Math.min(Math.max(Number(page.limit || 25), 1), 100);
  const offset = Math.max(Number(page.offset || 0), 0);

  const rows = await query(`
    SELECT oc.*, o.buyer_name, o.buyer_email, o.paystack_reference
    FROM order_checkins oc
    JOIN orders o ON oc.order_id = o.id
    WHERE o.event_id = ? AND o.status = 'paid'
    ORDER BY oc.created_at DESC
    LIMIT ? OFFSET ?
  `, [id, limit, offset]);

  return rows;
}

async function verifyTicket(token, reference) {
  const host = await validateHostToken(token);
  if (!host) {
    return { ok: false, message: 'Expired or invalid link' };
  }

  const ref = String(reference || '').trim();
  const rows = await query(`
    SELECT o.*, e.title as event_title
    FROM orders o
    LEFT JOIN events e ON o.event_id = e.id
    WHERE o.paystack_reference = ?
    LIMIT 1
  `, [ref]);

  const order = rows[0] || null;

  if (!order || order.event_id !== host.event_id || order.status !== 'paid') {
    return { ok: true, valid: false, message: 'Invalid ticket' };
  }

  // Check how many already used
  const checkinRows = await query(`
    SELECT COALESCE(SUM(count), 0) as used
    FROM order_checkins
    WHERE order_id = ?
  `, [order.id]);

  const used = Number(checkinRows[0]?.used || 0);
  const allowed = Math.max(0, Number(order.quantity) - used);

  if (allowed <= 0) {
    const lastRows = await query(`
      SELECT created_at FROM order_checkins
      WHERE order_id = ?
      ORDER BY created_at DESC
      LIMIT 1
    `, [order.id]);

    const last = lastRows[0]?.created_at || null;

    return {
      ok: true,
      valid: false,
      already: true,
      buyer: { name: order.buyer_name, email: order.buyer_email },
      event: { title: order.event_title },
      remaining: 0,
      last_checkin_at: last,
    };
  }

  // Record check-in
  const now = new Date();
  await query(`
    INSERT INTO order_checkins (order_id, host_token_id, count, source, created_at)
    VALUES (?, ?, 1, 'camera', ?)
  `, [order.id, host.id, now]);

  return {
    ok: true,
    valid: true,
    buyer: { name: order.buyer_name, email: order.buyer_email },
    event: { title: order.event_title },
    remaining: allowed - 1,
    last_checkin_at: now.toISOString(),
  };
}

module.exports = {
  getHostTokenByToken,
  validateHostToken,
  getHostStats,
  getHostCheckins,
  verifyTicket,
};
