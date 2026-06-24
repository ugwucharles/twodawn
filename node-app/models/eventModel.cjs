const { query } = require('../db/client.cjs');

const EVENT_SELECT = `
  id,
  user_id,
  title,
  description,
  must_know,
  venue,
  state,
  starts_at,
  ends_at,
  price,
  early_bird_price,
  early_bird_ends_at,
  capacity,
  free_tickets_count,
  is_published,
  pass_fees_to_buyer,
  image_path,
  gallery,
  mood,
  use_custom_slug,
  slug,
  whatsapp_group_url,
  ticket_types,
  created_at,
  updated_at
`;

function parseJsonColumn(value) {
  if (value === null || value === undefined) return null;
  if (typeof value === 'object') return value;

  try {
    return JSON.parse(value);
  } catch (_error) {
    return null;
  }
}

function mapEvent(row) {
  if (!row) return null;

  return {
    ...row,
    is_published: Boolean(row.is_published),
    pass_fees_to_buyer: Boolean(row.pass_fees_to_buyer),
    use_custom_slug: Boolean(row.use_custom_slug),
    gallery: parseJsonColumn(row.gallery),
    ticket_types: parseJsonColumn(row.ticket_types),
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

async function findEventById(eventId) {
  const id = asPositiveInt(eventId);
  if (!id) return null;

  const rows = await query(
    `SELECT e.*, u.username as organizer_username, u.name as organizer_name, u.profile_picture as organizer_avatar
     FROM events e
     LEFT JOIN users u ON e.user_id = u.id
     WHERE e.id = ?`,
    [id]
  );
  return mapEvent(rows[0]);
}

async function findEventBySlug(slug) {
  const normalizedSlug = String(slug || '').trim();
  if (!normalizedSlug) return null;

  const rows = await query(
    `SELECT e.*, u.username as organizer_username, u.name as organizer_name, u.profile_picture as organizer_avatar
     FROM events e
     LEFT JOIN users u ON e.user_id = u.id
     WHERE e.slug = ?
     LIMIT 1`,
    [normalizedSlug]
  );

  return mapEvent(rows[0]);
}

async function listPublishedEvents(page = {}) {
  const { limit, offset } = normalizePage(page);

  const rows = await query(
    `SELECT ${EVENT_SELECT}
     FROM events
     WHERE is_published = 1
     ORDER BY starts_at ASC, id ASC
     LIMIT ? OFFSET ?`,
    [limit, offset]
  );

  return rows.map(mapEvent);
}

async function getRemainingFreeTickets(eventId) {
  const id = asPositiveInt(eventId);
  if (!id) return null;

  const events = await query(
    `SELECT id, free_tickets_count
     FROM events
     WHERE id = ?
     LIMIT 1`,
    [id]
  );

  const event = events[0];
  if (!event) return null;

  const paidRows = await query(
    `SELECT COALESCE(SUM(quantity), 0) AS sold_quantity
     FROM orders
     WHERE event_id = ? AND status = 'paid'`,
    [id]
  );

  const soldQuantity = Number(paidRows[0]?.sold_quantity || 0);
  const freeTicketsCount = Number(event.free_tickets_count || 0);

  return {
    event_id: id,
    free_tickets_count: freeTicketsCount,
    sold_quantity: soldQuantity,
    remaining: Math.max(0, freeTicketsCount - soldQuantity),
  };
}

async function listRecentEvents(page = {}) {
  const { limit, offset } = normalizePage(page);
  const oneMonthAgo = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000);

  const rows = await query(
    `SELECT e.*, u.username as organizer_username, u.name as organizer_name, u.profile_picture as organizer_avatar
     FROM events e
     LEFT JOIN users u ON e.user_id = u.id
     WHERE e.is_published = 1
       AND (
         (e.ends_at IS NOT NULL AND e.ends_at >= ?)
         OR (e.ends_at IS NULL AND e.starts_at >= ?)
       )
     ORDER BY e.starts_at DESC, e.id DESC
     LIMIT ? OFFSET ?`,
    [oneMonthAgo.toISOString(), oneMonthAgo.toISOString(), limit, offset]
  );

  return rows.map(mapEvent);
}

async function listPublishedEventsFiltered(filters = {}, page = {}) {
  const { limit, offset } = normalizePage(page);
  const { mood, state, price, date, q } = filters;

  console.log('🔍 listPublishedEventsFiltered called with filters:', filters);

  const conditions = ['is_published = 1'];
  const params = [];

  // Base condition: upcoming events (use JavaScript to get current time)
  const now = new Date().toISOString();
  conditions.push(`(
    (ends_at IS NULL AND starts_at >= ?)
    OR (ends_at IS NOT NULL AND ends_at >= ?)
  )`);
  params.push(now, now);

  if (mood) {
    conditions.push('mood = ?');
    params.push(mood);
    console.log('🎭 Mood filter applied:', mood);
  }

  if (state) {
    conditions.push('LOWER(state) = LOWER(?)');
    params.push(state);
    console.log('📍 State filter applied:', state);
  }

  if (price === 'free' || price === 'paid') {
    console.log(`💰 Price filter: ${price}`);
  }

  if (date === 'today') {
    // Calculate today's date range using local time
    const now = new Date();
    const offset = now.getTimezoneOffset() * 60000; // offset in milliseconds
    const todayStart = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0, 0);
    const todayEnd = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59, 999);
    
    conditions.push('starts_at >= ? AND starts_at <= ?');
    params.push(new Date(todayStart.getTime() - offset).toISOString(), new Date(todayEnd.getTime() - offset).toISOString());
    console.log('📅 Date filter: today (local)', new Date(todayStart.getTime() - offset).toISOString(), new Date(todayEnd.getTime() - offset).toISOString());
  } else if (date === 'weekend') {
    // Calculate Friday and Sunday of current week using local time
    const now = new Date();
    const offset = now.getTimezoneOffset() * 60000;
    const dayOfWeek = now.getDay();
    const daysUntilFriday = (5 - dayOfWeek + 7) % 7;
    const friday = new Date(now.getFullYear(), now.getMonth(), now.getDate() + (dayOfWeek <= 5 ? daysUntilFriday : daysUntilFriday + 7), 0, 0, 0, 0);
    const sunday = new Date(friday.getFullYear(), friday.getMonth(), friday.getDate() + 2, 23, 59, 59, 999);
    
    conditions.push('starts_at >= ? AND starts_at <= ?');
    params.push(new Date(friday.getTime() - offset).toISOString(), new Date(sunday.getTime() - offset).toISOString());
    console.log('📅 Date filter: weekend (Friday-Sunday local)', new Date(friday.getTime() - offset).toISOString(), new Date(sunday.getTime() - offset).toISOString());
  } else if (date === 'next-week') {
    // Calculate next Monday and Sunday using local time
    const now = new Date();
    const offset = now.getTimezoneOffset() * 60000;
    const dayOfWeek = now.getDay();
    const daysUntilMonday = (1 - dayOfWeek + 7) % 7;
    const nextMonday = new Date(now.getFullYear(), now.getMonth(), now.getDate() + daysUntilMonday + 7, 0, 0, 0, 0);
    const nextSunday = new Date(nextMonday.getFullYear(), nextMonday.getMonth(), nextMonday.getDate() + 6, 23, 59, 59, 999);
    
    conditions.push('starts_at >= ? AND starts_at <= ?');
    params.push(new Date(nextMonday.getTime() - offset).toISOString(), new Date(nextSunday.getTime() - offset).toISOString());
    console.log('📅 Date filter: next-week (Monday-Sunday local)', new Date(nextMonday.getTime() - offset).toISOString(), new Date(nextSunday.getTime() - offset).toISOString());
  }

  if (q) {
    const searchTerm = `%${q}%`;
    conditions.push('(title LIKE ? OR venue LIKE ? OR description LIKE ? OR mood LIKE ?)');
    params.push(searchTerm, searchTerm, searchTerm, searchTerm);
    console.log('🔎 Search filter applied:', q);
  }

  const whereClause = conditions.join(' AND ');
  console.log('🔍 Final WHERE clause:', whereClause);
  console.log('🔍 Params:', params);

  const rows = await query(
    `SELECT e.*, u.username as organizer_username, u.name as organizer_name, u.profile_picture as organizer_avatar
     FROM events e
     LEFT JOIN users u ON e.user_id = u.id
     WHERE ${whereClause}
     ORDER BY starts_at ASC, e.id ASC
     LIMIT ? OFFSET ?`,
    [...params, limit, offset]
  );

  const mappedRows = rows.map(mapEvent);

  console.log('📊 Events returned from DB:', mappedRows.length);
  return mappedRows;
}

async function listTopSellingEvents({ limit = 6, filters = {} } = {}) {
  const normalizedLimit = Math.min(Math.max(Number(limit) || 6, 1), 20);
  const { mood, state, price, date, q } = filters;
  const now = new Date().toISOString();

  console.log('🏆 listTopSellingEvents called with limit:', normalizedLimit, 'filters:', filters);

  const conditions = ['e.is_published = 1'];
  const params = [];

  // Base condition: upcoming events
  conditions.push(`(
    (e.ends_at IS NULL AND e.starts_at >= ?)
    OR (e.ends_at IS NOT NULL AND e.ends_at >= ?)
  )`);
  params.push(now, now);

  if (mood) {
    conditions.push('e.mood = ?');
    params.push(mood);
    console.log('🎭 Mood filter applied:', mood);
  }

  if (state) {
    conditions.push('LOWER(e.state) = LOWER(?)');
    params.push(state);
    console.log('📍 State filter applied:', state);
  }

  if (price === 'free' || price === 'paid') {
    console.log(`💰 Price filter: ${price}`);
  }

  if (date === 'today') {
    // Calculate today's date range using local time
    const now = new Date();
    const offset = now.getTimezoneOffset() * 60000; // offset in milliseconds
    const todayStart = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0, 0);
    const todayEnd = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59, 999);
    
    conditions.push('e.starts_at >= ? AND e.starts_at <= ?');
    params.push(new Date(todayStart.getTime() - offset).toISOString(), new Date(todayEnd.getTime() - offset).toISOString());
    console.log('📅 Date filter: today (local)', new Date(todayStart.getTime() - offset).toISOString(), new Date(todayEnd.getTime() - offset).toISOString());
  } else if (date === 'weekend') {
    // Calculate Friday and Sunday of current week using local time
    const now = new Date();
    const offset = now.getTimezoneOffset() * 60000;
    const dayOfWeek = now.getDay();
    const daysUntilFriday = (5 - dayOfWeek + 7) % 7;
    const friday = new Date(now.getFullYear(), now.getMonth(), now.getDate() + (dayOfWeek <= 5 ? daysUntilFriday : daysUntilFriday + 7), 0, 0, 0, 0);
    const sunday = new Date(friday.getFullYear(), friday.getMonth(), friday.getDate() + 2, 23, 59, 59, 999);
    
    conditions.push('e.starts_at >= ? AND e.starts_at <= ?');
    params.push(new Date(friday.getTime() - offset).toISOString(), new Date(sunday.getTime() - offset).toISOString());
    console.log('📅 Date filter: weekend (Friday-Sunday local)', new Date(friday.getTime() - offset).toISOString(), new Date(sunday.getTime() - offset).toISOString());
  } else if (date === 'next-week') {
    // Calculate next Monday and Sunday using local time
    const now = new Date();
    const offset = now.getTimezoneOffset() * 60000;
    const dayOfWeek = now.getDay();
    const daysUntilMonday = (1 - dayOfWeek + 7) % 7;
    const nextMonday = new Date(now.getFullYear(), now.getMonth(), now.getDate() + daysUntilMonday + 7, 0, 0, 0, 0);
    const nextSunday = new Date(nextMonday.getFullYear(), nextMonday.getMonth(), nextMonday.getDate() + 6, 23, 59, 59, 999);
    
    conditions.push('e.starts_at >= ? AND e.starts_at <= ?');
    params.push(new Date(nextMonday.getTime() - offset).toISOString(), new Date(nextSunday.getTime() - offset).toISOString());
    console.log('📅 Date filter: next-week (Monday-Sunday local)', new Date(nextMonday.getTime() - offset).toISOString(), new Date(nextSunday.getTime() - offset).toISOString());
  }

  if (q) {
    const searchTerm = `%${q}%`;
    conditions.push('(e.title LIKE ? OR e.venue LIKE ? OR e.description LIKE ? OR e.mood LIKE ?)');
    params.push(searchTerm, searchTerm, searchTerm, searchTerm);
    console.log('🔎 Search filter applied:', q);
  }

  const whereClause = conditions.join(' AND ');
  console.log('🔍 Final WHERE clause for top-selling:', whereClause);

  const rows = await query(
    `SELECT e.*, u.username as organizer_username, u.name as organizer_name, u.profile_picture as organizer_avatar,
            COALESCE(o.sold_quantity, 0) as sold_quantity
     FROM events e
     LEFT JOIN users u ON e.user_id = u.id
     LEFT JOIN (
       SELECT event_id, SUM(quantity) as sold_quantity
       FROM orders
       WHERE status = 'paid'
       GROUP BY event_id
     ) o ON o.event_id = e.id
     WHERE ${whereClause}
     ORDER BY sold_quantity DESC, e.starts_at ASC
     LIMIT ?`,
    [...params, normalizedLimit]
  );

  const mappedRows = rows.map(mapEvent);

  console.log('🏆 Top selling events returned from DB:', mappedRows.length);
  mappedRows.forEach((row, i) => {
    console.log(`  ${i + 1}. ${row.title} - Sold: ${row.sold_quantity}`);
  });
  return mappedRows;
}

async function getEventCapacity(eventId) {
  const id = asPositiveInt(eventId);
  if (!id) return null;

  const rows = await query(
    `SELECT capacity, starts_at, ends_at
     FROM events
     WHERE id = ?
     LIMIT 1`,
    [id]
  );

  if (!rows[0]) return null;

  const event = rows[0];
  const now = new Date();
  const startsAt = event.starts_at ? new Date(event.starts_at) : null;
  const endsAt = event.ends_at ? new Date(event.ends_at) : null;

  const isPast = (endsAt && endsAt < now) || (!endsAt && startsAt && startsAt < now);
  const remaining = event.capacity !== null ? Math.max(0, Number(event.capacity)) : null;

  return {
    id: id,
    remaining: remaining,
    status: isPast ? 'past' : 'upcoming',
  };
}

async function createEvent(userId, data) {
  const title = data.title;
  const description = data.description || null;
  const must_know = data.must_know || null;
  const venue = data.venue;
  const state = data.state;
  const starts_at = data.starts_at;
  const ends_at = data.ends_at || null;
  const price = data.price ? Number(data.price) : 0;
  const capacity = data.capacity ? Number(data.capacity) : null;
  const is_published = 1; // Auto publish for organizers
  const pass_fees_to_buyer = data.pass_fees_to_buyer ? 1 : 0;
  const image_path = data.image_path || null;
  
  // Generate slug — use organizer's custom slug if provided, else auto-generate from title
  let slug;
  if (data.custom_slug) {
    // Check if custom slug is taken
    const existing = await query(`SELECT id FROM events WHERE slug = ? LIMIT 1`, [data.custom_slug]);
    if (existing.length > 0) {
      // Append random suffix to avoid collision
      const rand = Math.random().toString(36).substring(2, 6);
      slug = `${data.custom_slug}-${rand}`;
    } else {
      slug = data.custom_slug;
    }
  } else {
    const baseSlug = String(title || '')
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/(^-|-$)/g, '');
    const rand = Math.random().toString(36).substring(2, 8);
    slug = `${baseSlug}-${rand}`;
  }


  const ticket_types = data.ticket_types ? JSON.stringify(data.ticket_types) : null;
  
  const nowStr = new Date().toISOString().replace('T', ' ').substring(0, 19);

  const sql = `
    INSERT INTO events (
      user_id, title, description, must_know, venue, state,
      starts_at, ends_at, price, capacity, is_published,
      pass_fees_to_buyer, image_path, slug, ticket_types,
      created_at, updated_at
    ) VALUES (
      ?, ?, ?, ?, ?, ?,
      ?, ?, ?, ?, ?,
      ?, ?, ?, ?,
      ?, ?
    )
  `;

  const params = [
    userId, title, description, must_know, venue, state,
    starts_at, ends_at, price, capacity, is_published,
    pass_fees_to_buyer, image_path, slug, ticket_types,
    nowStr, nowStr
  ];

  const result = await query(sql, params);
  return result.insertId;
}

module.exports = {
  findEventById,
  findEventBySlug,
  listPublishedEvents,
  listRecentEvents,
  listPublishedEventsFiltered,
  listTopSellingEvents,
  getRemainingFreeTickets,
  getEventCapacity,
  createEvent,
};
