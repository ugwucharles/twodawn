require('dotenv').config();
const { generateReference, calculateQuote, initializePaystackPayment, verifyPaystackTransaction } = require('../services/checkoutService.cjs');
const { findEventById, listPublishedEvents } = require('../models/eventModel.cjs');
const { createOrder, findOrderByReference, updateOrderStatusByReference } = require('../models/orderModel.cjs');

async function testPayment() {
  console.log('🧪 Starting Payment Test...\n');

  try {
    // Step 1: Find a test event
    console.log('📋 Step 1: Finding a test event...');
    const events = await listPublishedEvents({ limit: 1, offset: 0 });
    
    if (!events || events.length === 0) {
      console.error('❌ No published events found in the database.');
      return;
    }
    
    const event = events[0];
    
    console.log(`✅ Found event: ${event.title}`);
    console.log(`   Event ID: ${event.id}`);
    console.log(`   Price field: ${event.price}`);
    console.log(`   Ticket types: ${JSON.stringify(event.ticket_types || [])}`);
    console.log(`   Early bird price: ${event.early_bird_price}`);
    console.log(`   Early bird ends at: ${event.early_bird_ends_at}\n`);

    // Step 2: Calculate quote
    console.log('💰 Step 2: Calculating quote...');
    const quote = calculateQuote(event, 1, null);
    console.log(`   Subtotal: ₦${quote.subtotal_kobo / 100}`);
    console.log(`   Fees: ₦${quote.fees_kobo / 100}`);
    console.log(`   Total: ₦${quote.total_kobo / 100}\n`);

    // Step 3: Create order
    console.log('📝 Step 3: Creating order...');
    const reference = generateReference();
    console.log(`   Reference: ${reference}`);
    
    const order = await createOrder({
      event_id: event.id,
      ticket_type: null,
      buyer_name: 'Test User',
      buyer_email: 'test@example.com',
      buyer_phone: '08012345678',
      coupon_code: null,
      quantity: 1,
      amount: quote.total_kobo,
      paystack_reference: reference,
      created_ip: '127.0.0.1',
    });
    
    console.log(`✅ Order created with ID: ${order.id}\n`);

    // Step 4: Handle payment (skip Paystack for free events)
    if (quote.total_kobo <= 0) {
      console.log('💳 Step 4: Free event detected - skipping Paystack...');
      console.log('   Finalizing order directly...\n');
      
      const { finalizeZeroCostOrder } = require('../services/checkoutService.cjs');
      const result = await finalizeZeroCostOrder(order);
      
      if (result.success) {
        console.log(`✅ Order finalized successfully!`);
        console.log(`   Status: ${result.order.status}`);
        console.log(`   Reference: ${reference}\n`);
        console.log(`🎉 Test completed! You can view the order at:`);
        console.log(`   https://twodawn-frontend-real.vercel.app/payment-success?reference=${reference}`);
      } else {
        console.error(`❌ Failed to finalize order: ${result.error}`);
      }
      return;
    }

    // Step 4: Initialize Paystack payment
    console.log('💳 Step 4: Initializing Paystack payment...');
    const callbackUrl = 'https://twodawn-frontend.vercel.app/paystack/callback';
    const authUrl = await initializePaystackPayment(order, callbackUrl);
    console.log(`✅ Payment initialized`);
    console.log(`   Authorization URL: ${authUrl}\n`);

    // Step 5: Instructions for manual testing
    console.log('📋 Manual Testing Instructions:');
    console.log('================================');
    console.log('1. Open the authorization URL above in your browser');
    console.log('2. Complete the payment using Paystack test mode');
    console.log('3. Use test card: 4084 0840 4084 0840');
    console.log('4. Expiry: Any future date, CVV: Any 3 digits');
    console.log('5. After payment, you should be redirected to:');
    console.log(`   https://twodawn-frontend-real.vercel.app/payment-success?reference=${reference}\n`);

    // Step 6: Verify payment (optional - if you want to test verification)
    console.log('🔍 Step 6: Waiting for payment verification...');
    console.log('   (This will check if payment was successful after 30 seconds)');
    
    setTimeout(async () => {
      try {
        const transaction = await verifyPaystackTransaction(reference);
        console.log(`✅ Payment status: ${transaction.status}`);
        console.log(`   Amount: ₦${transaction.amount / 100}`);
        
        if (transaction.status === 'success') {
          console.log('\n🎉 Payment successful! Checking order status...');
          const updatedOrder = await updateOrderStatusByReference(reference, 'paid');
          console.log(`✅ Order status updated to: ${updatedOrder.status}`);
        }
      } catch (error) {
        console.log(`⚠️  Payment verification failed: ${error.message}`);
        console.log('   This is normal if payment hasn\'t been completed yet.');
      }
    }, 30000);

  } catch (error) {
    console.error('❌ Test failed:', error.message);
    console.error(error.stack);
  }
}

// Run the test
testPayment();
