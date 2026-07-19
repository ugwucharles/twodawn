const { query } = require('../db/client.cjs');

function asPositiveInt(value) {
  const parsed = Number(value);
  if (!Number.isInteger(parsed) || parsed <= 0) return null;
  return parsed;
}

// Activity logging
async function logActivity(action, entityType, entityId, details = null, userId = null) {
  try {
    await query(`
      INSERT INTO activity_logs (action, entity_type, entity_id, details, user_id, created_at)
      VALUES (?, ?, ?, ?, ?, datetime('now'))
    `, [action, entityType, entityId, JSON.stringify(details), userId]);
  } catch (error) {
    console.error('Failed to log activity:', error);
  }
}

async function getActivityLogs(limit = 50, offset = 0) {
  const rows = await query(`
    SELECT * FROM activity_logs
    ORDER BY created_at DESC
    LIMIT ? OFFSET ?
  `, [limit, offset]);
  return rows;
}

async function getAdminStats() {
  const rows = await query(`
    SELECT
      (SELECT COUNT(*) FROM events WHERE (deleted_at IS NULL OR deleted_at = '')) as events_total,
      (SELECT COUNT(*) FROM events WHERE is_published = 1 AND (deleted_at IS NULL OR deleted_at = '')) as events_published,
      (SELECT COUNT(*) FROM events WHERE is_published = 1 AND (deleted_at IS NULL OR deleted_at = '')
        AND (
          (ends_at IS NOT NULL AND ends_at != '' AND datetime(ends_at) >= datetime('now', '+1 hours'))
          OR ((ends_at IS NULL OR ends_at = '') AND starts_at IS NOT NULL AND datetime(starts_at) >= datetime('now', '+1 hours'))
        )
      ) as events_active,
      (SELECT COUNT(*) FROM users) as users_total,
      (SELECT COUNT(*) FROM users WHERE is_organizer = 1) as organizers_total,
      (SELECT COUNT(*) FROM orders WHERE status IN ('paid', 'used')) as orders_total,
      (SELECT COUNT(*) FROM orders WHERE status IN ('paid', 'used') AND date(created_at) = date('now')) as orders_today,
      (SELECT COALESCE(SUM(quantity), 0) FROM orders WHERE status IN ('paid', 'used')) as tickets_total,
      (SELECT COALESCE(SUM(quantity), 0) FROM orders WHERE status IN ('paid', 'used') AND date(created_at) = date('now')) as tickets_today,
      (SELECT COALESCE(SUM(amount), 0) FROM orders WHERE status IN ('paid', 'used')) as revenue_total,
      (SELECT COALESCE(SUM(amount), 0) FROM orders WHERE status IN ('paid', 'used') AND date(created_at) = date('now')) as revenue_today,
      (SELECT COALESCE(SUM(CASE WHEN amount > 0 THEN (amount * 0.10) + (10000 * quantity) ELSE 0 END), 0) FROM orders WHERE status IN ('paid', 'used')) as twodawn_earnings_total,
      (SELECT COALESCE(SUM(CASE WHEN amount > 0 THEN (amount * 0.10) + (10000 * quantity) ELSE 0 END), 0) FROM orders WHERE status IN ('paid', 'used') AND date(created_at) = date('now')) as twodawn_earnings_today,
      (SELECT COUNT(*) FROM orders WHERE status = 'failed') as payments_failed
  `);

  return {
    events_total: Number(rows[0]?.events_total || 0),
    events_published: Number(rows[0]?.events_published || 0),
    events_active: Number(rows[0]?.events_active || 0),
    users_total: Number(rows[0]?.users_total || 0),
    organizers_total: Number(rows[0]?.organizers_total || 0),
    orders_total: Number(rows[0]?.orders_total || 0),
    orders_today: Number(rows[0]?.orders_today || 0),
    tickets_total: Number(rows[0]?.tickets_total || 0),
    tickets_today: Number(rows[0]?.tickets_today || 0),
    revenue_total: Number(rows[0]?.revenue_total || 0),
    revenue_today: Number(rows[0]?.revenue_today || 0),
    twodawn_earnings_total: Number(rows[0]?.twodawn_earnings_total || 0),
    twodawn_earnings_today: Number(rows[0]?.twodawn_earnings_today || 0),
    payments_failed: Number(rows[0]?.payments_failed || 0),
  };
}

async function getChartData(days = 14) {
  const labels = [];
  const ticketsSeries = [];
  const revenueSeries = [];
  const dateStrings = [];

  for (let i = days - 1; i >= 0; i--) {
    const date = new Date();
    date.setDate(date.getDate() - i);
    const dateStr = date.toISOString().split('T')[0];
    dateStrings.push(dateStr);
    labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
  }

  // Fetch all daily summaries in a single query
  const rows = await query(`
    SELECT 
      DATE(created_at) as date_str,
      COALESCE(SUM(quantity), 0) as tickets,
      COALESCE(SUM(amount), 0) as revenue
    FROM orders 
    WHERE status IN ('paid', 'used') AND DATE(created_at) >= DATE('now', '-' || ? || ' day')
    GROUP BY DATE(created_at)
  `, [String(days)]);

  const dataMap = new Map();
  rows.forEach(row => {
    const dStr = String(row.date_str || '').trim();
    dataMap.set(dStr, {
      tickets: Number(row.tickets || 0),
      revenue: Number(row.revenue || 0)
    });
  });

  dateStrings.forEach(dateStr => {
    const data = dataMap.get(dateStr) || { tickets: 0, revenue: 0 };
    ticketsSeries.push(data.tickets);
    revenueSeries.push(data.revenue);
  });

  return { labels, tickets: ticketsSeries, revenue: revenueSeries };
}

async function getUpcomingEvents(limit = 6) {
  const rows = await query(`
    SELECT id, title, starts_at, venue, is_published
    FROM events
    WHERE is_published = 1
      AND (deleted_at IS NULL OR deleted_at = '')
      AND (
        (ends_at IS NOT NULL AND ends_at != '' AND datetime(ends_at) >= datetime('now', '+1 hours'))
        OR ((ends_at IS NULL OR ends_at = '') AND starts_at IS NOT NULL AND datetime(starts_at) >= datetime('now', '+1 hours'))
      )
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
    VALUES (?, ?, ?, 1, date((SELECT ends_at FROM events WHERE id = ?), '+1 day'))
  `, [id, token, label || null, id]);

  return { id: rows.insertId, token, label, active: true };
}

// Event management
async function getAllEvents(limit = 50, offset = 0, status = null) {
  let whereConditions = ["(e.deleted_at IS NULL OR e.deleted_at = '')"];
  let params = [];

  if (status === 'published') {
    whereConditions.push('e.is_published = 1');
  } else if (status === 'draft') {
    whereConditions.push('e.is_published = 0');
  } else if (status === 'active') {
    whereConditions.push('e.is_published = 1');
    whereConditions.push(`(
      (e.ends_at IS NOT NULL AND e.ends_at != '' AND datetime(e.ends_at) >= datetime('now', '+1 hours'))
      OR ((e.ends_at IS NULL OR e.ends_at = '') AND e.starts_at IS NOT NULL AND datetime(e.starts_at) >= datetime('now', '+1 hours'))
    )`);
  }

  const whereClause = 'WHERE ' + whereConditions.join(' AND ');

  const rows = await query(`
    SELECT e.*, u.name as organizer_name, u.email as organizer_email,
           (SELECT COALESCE(SUM(quantity), 0) FROM orders WHERE event_id = e.id AND status IN ('paid', 'used')) as tickets_sold,
           (SELECT COALESCE(SUM(amount), 0) FROM orders WHERE event_id = e.id AND status IN ('paid', 'used')) as revenue
    FROM events e
    LEFT JOIN users u ON e.user_id = u.id
    ${whereClause}
    ORDER BY e.created_at DESC
    LIMIT ? OFFSET ?
  `, [...params, limit, offset]);

  return rows;
}

async function getEventById(eventId) {
  const id = asPositiveInt(eventId);
  if (!id) return null;

  const rows = await query(`
    SELECT e.*, u.name as organizer_name, u.email as organizer_email
    FROM events e
    LEFT JOIN users u ON e.user_id = u.id
    WHERE e.id = ?
    LIMIT 1
  `, [id]);

  return rows[0] || null;
}

async function updateEvent(eventId, updates) {
  const id = asPositiveInt(eventId);
  if (!id) return null;

  const fields = [];
  const values = [];

  if (updates.is_published !== undefined) {
    fields.push('is_published = ?');
    values.push(updates.is_published ? 1 : 0);
  }
  if (updates.is_featured !== undefined) {
    fields.push('is_featured = ?');
    values.push(updates.is_featured ? 1 : 0);
  }
  if (updates.title !== undefined) {
    fields.push('title = ?');
    values.push(updates.title);
  }
  if (updates.venue !== undefined) {
    fields.push('venue = ?');
    values.push(updates.venue);
  }

  if (fields.length === 0) return null;

  values.push(id);
  await query(`UPDATE events SET ${fields.join(', ')} WHERE id = ?`, values);

  return await getEventById(id);
}

async function deleteEvent(eventId) {
  const id = asPositiveInt(eventId);
  if (!id) return false;

  await query(`UPDATE events SET deleted_at = datetime('now') WHERE id = ?`, [id]);
  return true;
}

// Organizer management
async function getAllOrganizers(limit = 50, offset = 0) {
  const rows = await query(`
    SELECT u.*,
           (SELECT COUNT(*) FROM events WHERE user_id = u.id) as events_count,
           (SELECT COALESCE(SUM(o.amount), 0) FROM orders o
            JOIN events e ON o.event_id = e.id
            WHERE e.user_id = u.id AND o.status = 'paid') as total_revenue
    FROM users u
    WHERE is_organizer = 1
    ORDER BY u.created_at DESC
    LIMIT ? OFFSET ?
  `, [limit, offset]);

  return rows;
}

async function getOrganizerById(organizerId) {
  const id = asPositiveInt(organizerId);
  if (!id) return null;

  const rows = await query(`
    SELECT u.*,
           (SELECT COUNT(*) FROM events WHERE user_id = u.id) as events_count,
           (SELECT COALESCE(SUM(o.amount), 0) FROM orders o
            JOIN events e ON o.event_id = e.id
            WHERE e.user_id = u.id AND o.status = 'paid') as total_revenue
    FROM users u
    WHERE id = ? AND is_organizer = 1
    LIMIT 1
  `, [id]);

  return rows[0] || null;
}

async function updateOrganizer(organizerId, updates) {
  const id = asPositiveInt(organizerId);
  if (!id) return null;

  const fields = [];
  const values = [];

  if (updates.is_suspended !== undefined) {
    fields.push('is_suspended = ?');
    values.push(updates.is_suspended ? 1 : 0);
  }

  if (fields.length === 0) return null;

  values.push(id);
  await query(`UPDATE users SET ${fields.join(', ')} WHERE id = ?`, values);

  return await getOrganizerById(id);
}

// User management
async function getAllUsers(limit = 50, offset = 0, search = null) {
  let whereClause = '';
  let params = [];

  if (search) {
    whereClause = 'WHERE name LIKE ? OR email LIKE ?';
    params = [`%${search}%`, `%${search}%`];
  }

  const rows = await query(`
    SELECT u.*,
           (SELECT COUNT(*) FROM orders WHERE buyer_email = u.email) as orders_count
    FROM users u
    ${whereClause}
    ORDER BY u.created_at DESC
    LIMIT ? OFFSET ?
  `, [...params, limit, offset]);

  return rows;
}

async function getUserById(userId) {
  const id = asPositiveInt(userId);
  if (!id) return null;

  const rows = await query(`
    SELECT u.*,
           (SELECT COUNT(*) FROM orders WHERE buyer_email = u.email) as orders_count
    FROM users
    WHERE id = ?
    LIMIT 1
  `, [id]);

  return rows[0] || null;
}

// Transaction management
async function getAllTransactions(limit = 50, offset = 0, status = null) {
  let whereClause = '';
  let params = [];

  if (status) {
    whereClause = 'WHERE o.status = ?';
    params.push(status);
  }

  const rows = await query(`
    SELECT o.*, e.title as event_title, u.name as organizer_name
    FROM orders o
    LEFT JOIN events e ON o.event_id = e.id
    LEFT JOIN users u ON e.user_id = u.id
    ${whereClause}
    ORDER BY o.created_at DESC
    LIMIT ? OFFSET ?
  `, [...params, limit, offset]);

  return rows;
}

// System health
async function getSystemHealth() {
  const results = {};

  // 1. Database – run a real SELECT to confirm connectivity
  try {
    await query('SELECT COUNT(*) as n FROM users');
    results.database = 'healthy';
  } catch (e) {
    results.database = 'unhealthy';
  }

  // 2. API – if we reached here the process is alive
  results.api = 'healthy';

  // 3. Payment Gateway – hit Paystack's public status / ping endpoint
  try {
    const https = require('https');
    await new Promise((resolve, reject) => {
      const req = https.get(
        'https://api.paystack.co/bank',
        { headers: { Authorization: `Bearer ${process.env.PAYSTACK_SECRET_KEY || ''}`, 'Content-Type': 'application/json' } },
        (res) => {
          if (res.statusCode >= 200 && res.statusCode < 500) resolve();
          else reject(new Error(`HTTP ${res.statusCode}`));
          res.resume();
        }
      );
      req.on('error', reject);
      req.setTimeout(5000, () => { req.destroy(); reject(new Error('timeout')); });
    });
    results.payment_gateway = 'healthy';
  } catch (e) {
    results.payment_gateway = 'unhealthy';
  }

  // 4. Email Service – verify required SMTP env vars are set
  const mailConfigured =
    process.env.MAIL_HOST &&
    process.env.MAIL_USERNAME &&
    process.env.MAIL_PASSWORD;
  results.email_service = mailConfigured ? 'healthy' : 'unhealthy';

  // 5. Recent errors
  let recentErrors = 0;
  try {
    const errorRows = await query(`
      SELECT COUNT(*) as count FROM error_logs
      WHERE created_at >= datetime('now', '-1 hour')
    `);
    recentErrors = Number(errorRows[0]?.count || 0);
  } catch (e) {
    // error_logs table might not exist
  }

  return {
    database: results.database,
    api: results.api,
    payment_gateway: results.payment_gateway,
    email_service: results.email_service,
    recent_errors: recentErrors,
  };
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
  logActivity,
  getActivityLogs,
  getAllEvents,
  getEventById,
  updateEvent,
  deleteEvent,
  getAllOrganizers,
  getOrganizerById,
  updateOrganizer,
  getAllUsers,
  getUserById,
  getAllTransactions,
  getSystemHealth,
};
