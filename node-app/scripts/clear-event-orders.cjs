const { query } = require('../db/client.cjs');

async function clearEventOrders() {
  try {
    console.log('🔍 Checking orders for event 11...');
    
    // First, show what will be deleted
    const orders = await query(`
      SELECT id, paystack_reference, buyer_name, buyer_email, status, created_at 
      FROM orders 
      WHERE event_id = 11
    `);
    
    console.log(`📦 Found ${orders.length} orders for event 11:`);
    orders.forEach(order => {
      console.log(`  - ID: ${order.id}, Ref: ${order.paystack_reference}, Buyer: ${order.buyer_name}, Status: ${order.status}`);
    });
    
    if (orders.length === 0) {
      console.log('✅ No orders to delete');
      process.exit(0);
    }
    
    // Delete the orders
    console.log('🗑️ Deleting orders...');
    await query(`DELETE FROM orders WHERE event_id = 11`);
    
    console.log(`✅ Successfully deleted ${orders.length} orders for event 11`);
    
    // Verify deletion
    const remaining = await query(`SELECT COUNT(*) as count FROM orders WHERE event_id = 11`);
    console.log(`📊 Remaining orders: ${remaining[0].count}`);
    
    process.exit(0);
  } catch (error) {
    console.error('❌ Error:', error.message);
    process.exit(1);
  }
}

clearEventOrders();
