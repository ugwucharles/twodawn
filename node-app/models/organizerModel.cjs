const { query } = require('../db/client.cjs');

function asPositiveInt(value) {
  const parsed = Number(value);
  if (!Number.isInteger(parsed) || parsed <= 0) return null;
  return parsed;
}

async function getOrganizerStats(userId) {
  const id = asPositiveInt(userId);
  if (!id) return null;

  const rows = await query(`
    SELECT 
      (SELECT COUNT(*) FROM events WHERE user_id = ? AND deleted_at IS NULL) as total_events,
      (SELECT COUNT(*) FROM events WHERE user_id = ? AND starts_at > datetime('now') AND deleted_at IS NULL) as upcoming_events
  `, [id, id]);

  const totalEvents = Number(rows[0]?.total_events || 0);
  const upcomingEvents = Number(rows[0]?.upcoming_events || 0);

  let totalTicketsSold = 0;
  let totalRevenue = 0;

  if (totalEvents > 0) {
    const orderRows = await query(`
      SELECT 
        COALESCE(SUM(quantity), 0) as tickets_sold,
        COALESCE(SUM(amount), 0) as revenue
      FROM orders 
      WHERE event_id IN (SELECT id FROM events WHERE user_id = ? AND deleted_at IS NULL) AND status = 'paid'
    `, [id]);

    totalTicketsSold = Number(orderRows[0]?.tickets_sold || 0);
    totalRevenue = Number(orderRows[0]?.revenue || 0);
  }

  // Calculate wallet balance (total revenue minus 2DAWN fee)
  // Assuming 2DAWN takes a 5% fee + ₦50 per transaction
  let twoDawnFee = 0;
  if (totalEvents > 0) {
    const orderRows = await query(`
      SELECT amount FROM orders 
      WHERE event_id IN (SELECT id FROM events WHERE user_id = ? AND deleted_at IS NULL) AND status = 'paid'
    `, [id]);

    for (const row of orderRows) {
      const fee = (Number(row.amount) * 0.05) + 5000; // 5% + ₦50 (in kobo)
      twoDawnFee += fee;
    }
  }

  const walletBalance = Math.max(0, (totalRevenue - twoDawnFee) / 100); // Convert back to naira

  // Revenue Statistics (Monthly for the current year)
  const revenueStats = [];
  for (let i = 1; i <= 12; i++) {
    let val = 0;
    if (totalEvents > 0) {
      const monthStr = i.toString().padStart(2, '0');
      const monthRows = await query(`
        SELECT COALESCE(SUM(amount), 0) as revenue
        FROM orders 
        WHERE event_id IN (SELECT id FROM events WHERE user_id = ? AND deleted_at IS NULL) 
          AND status = 'paid'
          AND strftime('%Y', created_at) = strftime('%Y', 'now')
          AND strftime('%m', created_at) = ?
      `, [id, monthStr]);
      val = Number(monthRows[0]?.revenue || 0);
    }
    revenueStats.push(val / 100);
  }

  // Sales Statistics
  const capacityRows = await query(`
    SELECT COALESCE(SUM(capacity), 0) as total_capacity
    FROM events 
    WHERE user_id = ?
  `, [id]);

  const totalCapacity = Number(capacityRows[0]?.total_capacity || 0);
  const leftTickets = Math.max(0, totalCapacity - totalTicketsSold);

  return {
    total_events: totalEvents,
    upcoming_events: upcomingEvents,
    total_tickets_sold: totalTicketsSold,
    total_revenue: totalRevenue,
    wallet_balance: walletBalance,
    revenue_stats: revenueStats,
    total_capacity: totalCapacity,
    left_tickets: leftTickets,
  };
}

async function getOrganizerEvents(userId) {
  const id = asPositiveInt(userId);
  if (!id) return [];

  const rows = await query(`
    SELECT e.*,
           COALESCE(o.orders_count, 0) as orders_count
    FROM events e
    LEFT JOIN (
      SELECT event_id, COUNT(*) as orders_count
      FROM orders
      WHERE status = 'paid'
      GROUP BY event_id
    ) o ON o.event_id = e.id
    WHERE e.user_id = ? AND e.deleted_at IS NULL
    ORDER BY e.starts_at DESC
  `, [id]);

  return rows;
}

async function getOrganizerOrders(userId, page = {}) {
  const id = asPositiveInt(userId);
  if (!id) return [];

  const limit = Math.min(Math.max(Number(page.limit || 20), 1), 100);
  const offset = Math.max(Number(page.offset || 0), 0);

  const rows = await query(`
    SELECT o.*, e.title as event_title
    FROM orders o
    LEFT JOIN events e ON o.event_id = e.id
    WHERE e.user_id = ?
    ORDER BY o.created_at DESC
    LIMIT ? OFFSET ?
  `, [id, limit, offset]);

  return rows;
}

module.exports = {
  getOrganizerStats,
  getOrganizerEvents,
  getOrganizerOrders,
};
