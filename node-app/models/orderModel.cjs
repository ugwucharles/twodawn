const crypto = require('crypto');
const { query } = require('../db/client.cjs');
const { ensureOrdersSchema } = require('../db/ensureOrdersSchema.cjs');

const ORDER_SELECT = `
  id,
  event_id,
  ticket_type,
  buyer_name,
  buyer_email,
  buyer_phone,
  coupon_code,
  quantity,
  amount,
  paystack_reference,
  ticket_code,
  status,
  created_ip,
  referral_source,
  created_at,
  updated_at,
  last_checkin_at
`;

function mapOrder(row) {
  if (!row) return null;

  return {
    ...row,
    quantity: Number(row.quantity),
  };
}

function asPositiveInt(value) {
  const parsed = Number(value);
  if (!Number.isInteger(parsed) || parsed <= 0) return null;
  return parsed;
}

function normalizePage({ limit = 20, offset = 0 } = {}) {
  const normalizedLimit = Math.min(Math.max(Number(limit) || 20, 1), 100);
  const normalizedOffset = Math.max(Number(offset) || 0, 0);
  return {
    limit: normalizedLimit,
    offset: normalizedOffset,
  };
}

async function findOrderById(orderId) {
  const id = asPositiveInt(orderId);
  if (!id) return null;

  const rows = await query(`SELECT ${ORDER_SELECT} FROM orders WHERE id = ? LIMIT 1`, [id]);
  return mapOrder(rows[0]);
}

async function findOrderByReference(reference) {
  const normalizedReference = String(reference || '').trim();
  if (!normalizedReference) return null;

  const rows = await query(
    `SELECT ${ORDER_SELECT}
     FROM orders
     WHERE paystack_reference = ?
     LIMIT 1`,
    [normalizedReference]
  );

  return mapOrder(rows[0]);
}

async function listOrdersByEvent(eventId, page = {}) {
  const id = asPositiveInt(eventId);
  if (!id) return [];

  const { limit, offset } = normalizePage(page);
  const rows = await query(
    `SELECT ${ORDER_SELECT}
     FROM orders
     WHERE event_id = ?
     ORDER BY id DESC
     LIMIT ? OFFSET ?`,
    [id, limit, offset]
  );

  return rows.map(mapOrder);
}

async function sumPaidQuantityForEvent(eventId) {
  const id = asPositiveInt(eventId);
  if (!id) return 0;

  const rows = await query(
    `SELECT COALESCE(SUM(quantity), 0) AS paid_quantity
     FROM orders
     WHERE event_id = ? AND status = 'paid'`,
    [id]
  );

  return Number(rows[0]?.paid_quantity || 0);
}

async function createOrder(orderData) {
  await ensureOrdersSchema();

  const {
    event_id,
    ticket_type,
    buyer_name,
    buyer_email,
    buyer_phone,
    coupon_code,
    quantity,
    amount,
    paystack_reference,
    created_ip,
    referral_source,
  } = orderData;

  const ticket_code = orderData.ticket_code || ('TC-' + crypto.randomBytes(3).toString('hex').toUpperCase());

  const rows = await query(
    `INSERT INTO orders (
      event_id, ticket_type, buyer_name, buyer_email, buyer_phone,
      coupon_code, quantity, amount, paystack_reference, ticket_code, status, created_ip, referral_source,
      created_at, updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, datetime('now'), datetime('now'))`,
    [
      event_id,
      ticket_type || null,
      buyer_name,
      buyer_email,
      buyer_phone || null,
      coupon_code || null,
      quantity,
      amount,
      paystack_reference,
      ticket_code,
      created_ip || null,
      referral_source || null,
    ]
  );

  return findOrderById(rows.insertId);
}

async function updateOrderStatus(orderId, status) {
  const id = asPositiveInt(orderId);
  if (!id) return null;

  await query(`UPDATE orders SET status = ? WHERE id = ?`, [status, id]);
  return findOrderById(id);
}

async function updateOrderStatusByReference(reference, status) {
  const normalizedReference = String(reference || '').trim();
  if (!normalizedReference) return null;

  await query(`UPDATE orders SET status = ? WHERE paystack_reference = ?`, [status, normalizedReference]);
  return findOrderByReference(normalizedReference);
}

async function decrementEventCapacity(eventId, quantity) {
  const id = asPositiveInt(eventId);
  if (!id) return null;

  await query(
    `UPDATE events SET capacity = CASE WHEN capacity - ? < 0 THEN 0 ELSE capacity - ? END WHERE id = ? AND capacity IS NOT NULL`,
    [quantity, quantity, id]
  );

  const eventRows = await query(`SELECT capacity FROM events WHERE id = ? LIMIT 1`, [id]);
  return eventRows[0] ? Number(eventRows[0].capacity) : null;
}

async function incrementCouponUses(couponCode) {
  const code = String(couponCode || '').trim();
  if (!code) return null;

  await query(`UPDATE coupons SET uses = uses + 1 WHERE code = ?`, [code]);
}

async function countRecentFreeOrders(eventId, ip, hours = 1) {
  const id = asPositiveInt(eventId);
  if (!id) return 0;

  const since = new Date(Date.now() - hours * 60 * 60 * 1000);

  const rows = await query(
    `SELECT COUNT(*) as count FROM orders 
     WHERE event_id = ? AND created_ip = ? AND amount <= 0 AND status = 'paid' AND created_at >= ?`,
    [id, ip, since]
  );

  return Number(rows[0]?.count || 0);
}

module.exports = {
  findOrderById,
  findOrderByReference,
  listOrdersByEvent,
  sumPaidQuantityForEvent,
  createOrder,
  updateOrderStatus,
  updateOrderStatusByReference,
  decrementEventCapacity,
  incrementCouponUses,
  countRecentFreeOrders,
};
