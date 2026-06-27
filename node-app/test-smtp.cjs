require('dotenv').config();

// Simple SMTP connection test without nodemailer
const net = require('net');

function testSMTPConnection() {
  console.log('🧪 Testing SMTP connection...\n');
  console.log('SMTP Config:');
  console.log('  Host:', process.env.MAIL_HOST);
  console.log('  Port:', process.env.MAIL_PORT);
  console.log('  Username:', process.env.MAIL_USERNAME);
  console.log('  Encryption:', process.env.MAIL_ENCRYPTION);
  console.log('');

  const host = process.env.MAIL_HOST || 'mail.twodawn.com.ng';
  const port = parseInt(process.env.MAIL_PORT || '465', 10);

  console.log(`🔌 Connecting to ${host}:${port}...`);

  const socket = net.createConnection({ host, port }, () => {
    console.log('✅ Successfully connected to SMTP server!');
    console.log('');
    console.log('📧 SMTP server is reachable.');
    console.log('🔐 SSL/TLS connection works.');
    console.log('');
    console.log('Your email configuration should work in production.');
    socket.destroy();
    process.exit(0);
  });

  socket.on('error', (error) => {
    console.log('❌ Failed to connect to SMTP server');
    console.log('Error:', error.message);
    console.log('');
    console.log('Troubleshooting tips:');
    console.log('1. Check if the SMTP host is correct:', host);
    console.log('2. Verify port 465 is not blocked by your firewall');
    console.log('3. Ensure your cPanel allows SMTP connections');
    console.log('4. Check if the email account exists in cPanel');
    socket.destroy();
    process.exit(1);
  });

  socket.setTimeout(10000, () => {
    console.log('❌ Connection timeout');
    console.log('The SMTP server did not respond within 10 seconds');
    socket.destroy();
    process.exit(1);
  });
}

testSMTPConnection();
