const { query } = require('./client.cjs');

const ORDERS_TABLE_SQL = `
  CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    event_id INTEGER NOT NULL,
    ticket_type TEXT,
    buyer_name TEXT NOT NULL,
    buyer_email TEXT NOT NULL,
    buyer_phone TEXT,
    coupon_code TEXT,
    quantity INTEGER NOT NULL,
    amount INTEGER NOT NULL,
    paystack_reference TEXT NOT NULL UNIQUE,
    ticket_code TEXT,
    status TEXT DEFAULT 'pending',
    created_ip TEXT,
    created_at TEXT,
    updated_at TEXT
  )
`;

const COLUMN_MIGRATIONS = [
  { column: 'event_id', sql: 'ALTER TABLE orders ADD COLUMN event_id INTEGER' },
  { column: 'ticket_type', sql: 'ALTER TABLE orders ADD COLUMN ticket_type TEXT' },
  { column: 'buyer_name', sql: 'ALTER TABLE orders ADD COLUMN buyer_name TEXT' },
  { column: 'buyer_email', sql: 'ALTER TABLE orders ADD COLUMN buyer_email TEXT' },
  { column: 'buyer_phone', sql: 'ALTER TABLE orders ADD COLUMN buyer_phone TEXT' },
  { column: 'coupon_code', sql: 'ALTER TABLE orders ADD COLUMN coupon_code TEXT' },
  { column: 'quantity', sql: 'ALTER TABLE orders ADD COLUMN quantity INTEGER' },
  { column: 'amount', sql: 'ALTER TABLE orders ADD COLUMN amount INTEGER' },
  { column: 'paystack_reference', sql: 'ALTER TABLE orders ADD COLUMN paystack_reference TEXT' },
  { column: 'ticket_code', sql: 'ALTER TABLE orders ADD COLUMN ticket_code TEXT' },
  { column: 'status', sql: "ALTER TABLE orders ADD COLUMN status TEXT DEFAULT 'pending'" },
  { column: 'created_ip', sql: 'ALTER TABLE orders ADD COLUMN created_ip TEXT' },
  { column: 'created_at', sql: 'ALTER TABLE orders ADD COLUMN created_at TEXT' },
  { column: 'updated_at', sql: 'ALTER TABLE orders ADD COLUMN updated_at TEXT' },
];

let schemaReady = null;

function normalizeRow(row) {
  if (!row) return row;
  if (typeof row.toJSON === 'function') return row.toJSON();
  return { ...row };
}

async function listOrderColumns() {
  const rows = await query('PRAGMA table_info(orders)');
  return rows.map((row) => {
    const normalized = normalizeRow(row);
    return String(normalized.name || '').toLowerCase();
  });
}

async function columnExists(columnName, existingColumns = null) {
  const columns = existingColumns || (await listOrderColumns());
  return columns.includes(String(columnName).toLowerCase());
}

async function ensureOrdersSchema() {
  if (schemaReady) return schemaReady;

  schemaReady = (async () => {
    await query(ORDERS_TABLE_SQL);

    const existingColumns = await listOrderColumns();

    for (const migration of COLUMN_MIGRATIONS) {
      if (await columnExists(migration.column, existingColumns)) continue;

      try {
        await query(migration.sql);
        existingColumns.push(migration.column.toLowerCase());
        console.log(`✅ Added orders.${migration.column} column`);
      } catch (error) {
        if (!String(error.message).includes('duplicate column')) {
          console.error(`❌ Failed to add orders.${migration.column}:`, error.message);
          throw error;
        }
      }
    }
  })();

  return schemaReady;
}

module.exports = { ensureOrdersSchema };
