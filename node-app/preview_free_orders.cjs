// Step 1: Fetch all free orders + their event details, then preview before sending
process.chdir(__dirname);

const { createClient } = require('@libsql/client/web');

const TURSO_URL = 'libsql://twodawn-ugwucharles.aws-us-west-2.turso.io';
const TURSO_TOKEN = 'eyJhbGciOiJFZERTQSIsInR5cCI6IkpXVCJ9.eyJhIjoicnciLCJpYXQiOjE3ODIwNzQ4MzksImlkIjoiMDE5ZWVhYmQtYWYwMS03OWU4LTk3ZTgtZjE1NmMwMTRhZWQ2IiwicmlkIjoiZWJmNDA1NTktMTBlYS00YWVkLTg1N2EtZmZiMjYxZDg0NGQyIn0.lzIxv7SszXjLK4oR124Bs0fblN-H92vdaHmH18uZFFU2qb6PEhILR12hQUpn61PX0gxxe895abDs_eJxjgweDA';

async function main() {
  const db = createClient({ url: TURSO_URL, authToken: TURSO_TOKEN });

  const ordersResult = await db.execute({
    sql: `SELECT o.id, o.buyer_name, o.buyer_email, o.buyer_phone, o.amount, o.status,
                 o.paystack_reference, o.ticket_code, o.ticket_type, o.quantity, o.event_id, o.created_at,
                 e.title AS event_title, e.starts_at, e.venue
          FROM orders o
          LEFT JOIN events e ON e.id = o.event_id
          WHERE o.amount = 0 AND o.status IN ('paid', 'confirmed')
          ORDER BY o.created_at`,
    args: [],
  });

  const orders = ordersResult.rows.map(r => ({ ...r }));
  console.log(JSON.stringify(orders, null, 2));

  await db.close();
}

main().catch(console.error);
