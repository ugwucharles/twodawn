const { createClient } = require('@libsql/client');

const db = createClient({
  url: 'libsql://twodawn-ugwucharles.aws-us-west-2.turso.io',
  authToken: 'eyJhbGciOiJFZERTQSIsInR5cCI6IkpXVCJ9.eyJhIjoicnciLCJpYXQiOjE3ODIwNzQ4MzksImlkIjoiMDE5ZWVhYmQtYWYwMS03OWU4LTk3ZTgtZjE1NmMwMTRhZWQ2IiwicmlkIjoiZWJmNDA1NTktMTBlYS00YWVkLTg1N2EtZmZiMjYxZDg0NGQyIn0.lzIxv7SszXjLK4oR124Bs0fblN-H92vdaHmH18uZFFU2qb6PEhILR12hQUpn61PX0gxxe895abDs_eJxjgweDA'
});

async function main() {
  console.log('=== EVENTS ===');
  const events = await db.execute('SELECT id, title, starts_at, is_published FROM events ORDER BY starts_at DESC LIMIT 10');
  events.rows.forEach(r => console.log(`id=${r.id} | ${r.title} | starts=${r.starts_at} | published=${r.is_published}`));

  console.log('\n=== PAID ORDERS PER EVENT ===');
  const orders = await db.execute(
    "SELECT event_id, COUNT(*) as total, buyer_email, buyer_name, status, paystack_reference FROM orders WHERE status IN ('paid','confirmed','used') ORDER BY event_id, created_at DESC"
  );
  orders.rows.forEach(r => console.log(`event=${r.event_id} | ${r.status} | ${r.buyer_name} <${r.buyer_email}> | ref=${r.paystack_reference}`));
}

main().catch(console.error);
