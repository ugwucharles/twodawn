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
      WHERE event_id IN (SELECT id FROM events WHERE user_id = ? AND deleted_at IS NULL) AND status IN ('paid', 'used')
    `, [id]);

    totalTicketsSold = Number(orderRows[0]?.tickets_sold || 0);
    totalRevenue = Number(orderRows[0]?.revenue || 0);
  }

  // Calculate wallet balance (total revenue minus 2DAWN fee)
  // Assuming 2DAWN takes a 10% fee + NGN 100 per transaction
  let twoDawnFee = 0;
  if (totalEvents > 0) {
    const orderRows = await query(`
      SELECT amount FROM orders 
      WHERE event_id IN (SELECT id FROM events WHERE user_id = ? AND deleted_at IS NULL) AND status IN ('paid', 'used')
    `, [id]);

    for (const row of orderRows) {
      const amt = Number(row.amount);
      if (amt > 0) {
        const fee = (amt * 0.10) + 10000; // 10% + NGN 100 (in kobo)
        twoDawnFee += fee;
      }
    }
  }

  // Get withdrawals sum
  let approvedWithdrawals = 0;
  let pendingWithdrawals = 0;
  try {
    const wRows = await query(`
      SELECT 
        COALESCE(SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END), 0) as approved,
        COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) as pending
      FROM withdrawals
      WHERE user_id = ?
    `, [id]);
    approvedWithdrawals = Number(wRows[0]?.approved || 0);
    pendingWithdrawals = Number(wRows[0]?.pending || 0);
  } catch (err) {
    console.error('Failed to fetch withdrawals sum:', err);
  }

  const walletBalance = Math.max(0, ((totalRevenue - twoDawnFee) / 100) - approvedWithdrawals - pendingWithdrawals);
  const availableForWithdrawal = walletBalance;

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
          AND status IN ('paid', 'used')
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
    available_for_withdrawal: availableForWithdrawal,
    pending_withdrawals: pendingWithdrawals,
    revenue_stats: revenueStats,
    total_capacity: totalCapacity,
    left_tickets: leftTickets,
  };
}

async function getOrganizerEvents(userId) {
  const id = asPositiveInt(userId);
  if (!id) return [];

  try {
    const rows = await query(`
      SELECT e.*,
             COALESCE(o.orders_count, 0) as orders_count
      FROM events e
      LEFT JOIN (
        SELECT event_id, COALESCE(SUM(quantity), 0) as orders_count
        FROM orders
        WHERE status IN ('paid', 'used')
        GROUP BY event_id
      ) o ON o.event_id = e.id
      WHERE e.user_id = ? AND (e.deleted_at IS NULL OR e.deleted_at = '')
      ORDER BY e.starts_at DESC
    `, [id]);

    return rows;
  } catch (error) {
    console.error('Error in getOrganizerEvents:', error);
    // Fallback query without deleted_at column
    try {
      const rows = await query(`
        SELECT e.*,
               COALESCE(o.orders_count, 0) as orders_count
        FROM events e
        LEFT JOIN (
          SELECT event_id, COALESCE(SUM(quantity), 0) as orders_count
          FROM orders
          WHERE status IN ('paid', 'used')
          GROUP BY event_id
        ) o ON o.event_id = e.id
        WHERE e.user_id = ?
        ORDER BY e.starts_at DESC
      `, [id]);
      return rows;
    } catch (fallbackError) {
      console.error('Fallback query also failed:', fallbackError);
      return [];
    }
  }
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

  return rows.map(row => ({
    ...row,
    referral_source: row.referral_source || null
  }));
}

module.exports = {
  getOrganizerStats,
  getOrganizerEvents,
  getOrganizerOrders,
};
