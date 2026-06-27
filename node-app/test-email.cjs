require('./config/envLoader.cjs');
const { sendTicketEmail } = require('./services/emailService.cjs');

async function testEmail() {
  console.log('🧪 Testing email configuration...\n');
  console.log('SMTP Config:');
  console.log('  Host:', process.env.MAIL_HOST);
  console.log('  Port:', process.env.MAIL_PORT);
  console.log('  Username:', process.env.MAIL_USERNAME);
  console.log('  Encryption:', process.env.MAIL_ENCRYPTION);
  console.log('  From:', process.env.MAIL_FROM_ADDRESS);
  console.log('');

  const testOrder = {
    buyer_email: process.env.MAIL_USERNAME, // Send to the same email
    buyer_name: 'Test User',
    paystack_reference: 'TEST_' + Date.now(),
    quantity: 2,
    amount: 5000, // ₦50.00
  };

  const testEvent = {
    title: 'Test Event - Email Verification',
    starts_at: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString(),
    venue: 'Test Venue, Lagos',
  };

  console.log('📧 Sending test email to:', testOrder.buyer_email);
  console.log('🎫 Event:', testEvent.title);
  console.log('🔖 Reference:', testOrder.paystack_reference);
  console.log('');

  try {
    const result = await sendTicketEmail(testOrder, testEvent);
    
    if (result.success) {
      console.log('✅ Email sent successfully!');
      console.log('📬 Message ID:', result.messageId);
      console.log('');
      console.log('📥 Please check your inbox at', testOrder.buyer_email);
      console.log('   If you don\'t see it, check your spam folder.');
    } else {
      console.log('❌ Failed to send email');
      console.log('Error:', result.error);
    }
  } catch (error) {
    console.log('❌ Test failed with error:', error.message);
    console.log('');
    console.log('Troubleshooting tips:');
    console.log('1. Verify your email password is correct in .env');
    console.log('2. Check if your cPanel allows SMTP connections');
    console.log('3. Ensure port 465 is not blocked');
    console.log('4. Verify the email account exists in cPanel');
  }

  process.exit(0);
}

testEmail();
