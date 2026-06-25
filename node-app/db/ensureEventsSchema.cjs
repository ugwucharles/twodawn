const { query } = require('./client.cjs');

const EVENTS_TABLE_SQL = `
  CREATE TABLE IF NOT EXISTS events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    title TEXT,
    description TEXT,
    must_know TEXT,
    venue TEXT,
    state TEXT,
    starts_at TEXT,
    ends_at TEXT,
    price REAL,
    early_bird_price REAL,
    early_bird_ends_at TEXT,
    capacity INTEGER,
    free_tickets_count INTEGER,
    is_published INTEGER,
    pass_fees_to_buyer INTEGER,
    image_path TEXT,
    gallery TEXT,
    mood TEXT,
    use_custom_slug INTEGER,
    slug TEXT,
    whatsapp_group_url TEXT,
    ticket_types TEXT,
    created_at TEXT,
    updated_at TEXT,
    deleted_at TEXT
  )
`;

const COLUMN_MIGRATIONS = [
  { column: 'deleted_at', sql: 'ALTER TABLE events ADD COLUMN deleted_at TEXT' },
];

let schemaReady = null;

function normalizeRow(row) {
  if (!row) return row;
  if (typeof row.toJSON === 'function') return row.toJSON();
  return { ...row };
}

async function listEventColumns() {
  const rows = await query('PRAGMA table_info(events)');
  return rows.map((row) => {
    const normalized = normalizeRow(row);
    return String(normalized.name || '').toLowerCase();
  });
}

async function columnExists(columnName, existingColumns = null) {
  const columns = existingColumns || (await listEventColumns());
  return columns.includes(String(columnName).toLowerCase());
}

async function ensureEventsSchema() {
  if (schemaReady) return schemaReady;

  schemaReady = (async () => {
    await query(EVENTS_TABLE_SQL);

    const existingColumns = await listEventColumns();

    for (const migration of COLUMN_MIGRATIONS) {
      if (await columnExists(migration.column, existingColumns)) continue;

      try {
        await query(migration.sql);
        existingColumns.push(migration.column.toLowerCase());
        console.log(`✅ Added events.${migration.column} column`);
      } catch (error) {
        if (!String(error.message).includes('duplicate column')) {
          console.error(`❌ Failed to add events.${migration.column}:`, error.message);
          throw error;
        }
      }
    }
  })();

  return schemaReady;
}

module.exports = { ensureEventsSchema };
