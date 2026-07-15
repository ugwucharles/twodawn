const nodemailer = require('nodemailer');
const fs = require('fs');
const path = require('path');

function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

let transporter = null;

function getTransporter() {
  // In serverless environments, always create a fresh transporter
  // Cached connections go stale between invocations
  const encryption = String(process.env.MAIL_ENCRYPTION || '').toLowerCase();
  const port = parseInt(process.env.MAIL_PORT || '465', 10);
  const isSSL = encryption === 'ssl' || port === 465;
  const t = nodemailer.createTransport({
    host: process.env.MAIL_HOST || 'smtp.mailtrap.io',
    port,
    secure: isSSL,
    requireTLS: encryption === 'tls',
    connectionTimeout: 15000,
    greetingTimeout: 15000,
    socketTimeout: 20000,
    auth: {
      user: process.env.MAIL_USERNAME,
      pass: process.env.MAIL_PASSWORD,
    },
    tls: {
      rejectUnauthorized: false, // Some hosting providers use self-signed certs
    },
  });
  return t;
}

function logEmailToFile({ to, subject, html }) {
  const logDir = path.join(__dirname, '../../storage/logs');
  if (!fs.existsSync(logDir)) {
    fs.mkdirSync(logDir, { recursive: true });
  }
  const logPath = path.join(logDir, 'mail.log');
  const logEntry = `
======================================================================
[${new Date().toISOString()}] EMAIL SENT
To: ${to}
Subject: ${subject}
----------------------------------------------------------------------
HTML Body:
${html}
======================================================================
\n`;
  fs.appendFileSync(logPath, logEntry, 'utf8');
  console.log(`✉️ [Mail Log] Email written to storage/logs/mail.log (To: ${to})`);
  return { success: true, messageId: `log_${Date.now()}` };
}

async function sendMailViaDriver({ to, subject, html }) {
  const driver = (process.env.MAIL_MAILER || 'smtp').toLowerCase();
  const fromAddress = process.env.MAIL_FROM_ADDRESS || 'noreply@twodawn.com.ng';
  const fromName = process.env.MAIL_FROM_NAME || '2DAWN';
  const formattedFrom = `"${fromName}" <${fromAddress}>`;

  if (driver === 'log') {
    return logEmailToFile({ to, subject, html });
  }

  if (driver === 'resend') {
    const apiKey = process.env.RESEND_API_KEY;
    if (!apiKey) {
      throw new Error('RESEND_API_KEY is not set in environment variables');
    }
    console.log('✉️ Sending email via Resend API to:', to);
    const response = await fetch('https://api.resend.com/emails', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${apiKey}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        from: formattedFrom,
        to: [to],
        subject,
        html,
      }),
    });
    const data = await response.json();
    if (!response.ok || data.error) {
      throw new Error(data.error?.message || response.statusText || 'Failed to send via Resend API');
    }
    return { success: true, messageId: data.id || `resend_${Date.now()}` };
  }

  if (driver === 'sendgrid') {
    const apiKey = process.env.SENDGRID_API_KEY;
    if (!apiKey) {
      throw new Error('SENDGRID_API_KEY is not set in environment variables');
    }
    console.log('✉️ Sending email via SendGrid API to:', to);
    const response = await fetch('https://api.sendgrid.com/v3/mail/send', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${apiKey}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        personalizations: [{ to: [{ email: to }] }],
        from: { email: fromAddress, name: fromName },
        subject,
        content: [{ type: 'text/html', value: html }],
      }),
    });
    if (!response.ok) {
      const errorText = await response.text();
      throw new Error(`SendGrid API Error (status ${response.status}): ${errorText || response.statusText}`);
    }
    return { success: true, messageId: response.headers.get('x-message-id') || `sendgrid_${Date.now()}` };
  }

  // Default to SMTP
  console.log('✉️ Sending email via SMTP to:', to);
  const transporter = getTransporter();
  const info = await transporter.sendMail({
    from: formattedFrom,
    to,
    subject,
    html,
  });
  return { success: true, messageId: info.messageId };
}

async function sendTicketEmail(order, event) {

  const frontendUrl = String(process.env.FRONTEND_URL || 'https://twodawn.com.ng').replace(/\/$/, '');
  const qrRemote = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(order.paystack_reference)}`;
  const publicUrl = `${frontendUrl}/find-tickets?ref=${order.paystack_reference}`;
  const logoUrl = `${frontendUrl}/logo.svg`;

  const startDate = event.starts_at ? new Date(event.starts_at).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) : 'TBD';
  const startTime = event.starts_at ? new Date(event.starts_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : 'TBD';
  const venue = event.venue || 'TBD';
  const buyerName = escapeHtml(order.buyer_name || 'Guest');
  const buyerEmail = escapeHtml(order.buyer_email || 'Not provided');
  const buyerPhone = escapeHtml(order.buyer_phone || 'Not provided');
  const ticketType = escapeHtml(order.ticket_type || 'General Admission');
  const safeReference = escapeHtml(order.paystack_reference);
  const safeEventTitle = escapeHtml(event.title || '2DAWN Event');
  const safeVenue = escapeHtml(venue);

  const mailOptions = {
    from: process.env.MAIL_FROM_ADDRESS || 'noreply@twodawn.com.ng',
    to: order.buyer_email,
    subject: `Your Ticket for ${event.title} - ${order.paystack_reference}`,
    html: `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f9fafb; padding: 20px;">
        <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
          <div style="text-align: center; margin-bottom: 30px;">
            <img src="${logoUrl}" alt="2DAWN" style="display: block; width: 150px; max-width: 60%; height: auto; margin: 0 auto 18px;" />
            <h1 style="color: #8b5cf6; margin: 0; font-size: 28px;">🎫 Your Ticket</h1>
            <p style="color: #6b7280; margin: 10px 0 0;">Thank you for your purchase!</p>
          </div>

          <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border-radius: 8px; padding: 20px; margin-bottom: 25px;">
            <h2 style="color: white; margin: 0 0 10px; font-size: 22px;">${safeEventTitle}</h2>
            <p style="color: rgba(255,255,255,0.9); margin: 0; font-size: 14px;">${startDate}</p>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
            <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
              <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Date & Time</p>
              <p style="color: #1f2937; margin: 0; font-size: 14px;">${startDate}<br>${startTime}</p>
            </div>
            <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
              <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Venue</p>
              <p style="color: #1f2937; margin: 0; font-size: 14px;">${safeVenue}</p>
            </div>
          </div>

          <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
            <p style="color: #6b7280; margin: 0 0 10px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Buyer Details</p>
            <p style="color: #1f2937; margin: 0 0 6px; font-size: 14px;"><strong>Name:</strong> ${buyerName}</p>
            <p style="color: #1f2937; margin: 0 0 6px; font-size: 14px;"><strong>Email:</strong> ${buyerEmail}</p>
            <p style="color: #1f2937; margin: 0; font-size: 14px;"><strong>Phone:</strong> ${buyerPhone}</p>
          </div>

          <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
            <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Reference</p>
            <p style="color: #1f2937; margin: 0; font-size: 18px; font-weight: bold; letter-spacing: 1px;">${safeReference}</p>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
            <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
              <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Ticket</p>
              <p style="color: #1f2937; margin: 0; font-size: 18px; font-weight: bold;">${ticketType} x ${order.quantity}</p>
            </div>
            <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
              <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Amount Paid</p>
              <p style="color: #1f2937; margin: 0; font-size: 18px; font-weight: bold;">₦${(order.amount / 100).toFixed(2)}</p>
            </div>
          </div>

          <div style="text-align: center; margin-bottom: 25px;">
            <p style="color: #6b7280; margin: 0 0 15px; font-size: 14px;">Scan this QR code at the event entrance</p>
            <div style="display: inline-block; padding: 10px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
              <img src="${qrRemote}" alt="QR Code" style="width: 200px; height: 200px;" />
            </div>
          </div>

          <div style="text-align: center; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <a href="${publicUrl}" style="display: inline-block; background: #8b5cf6; color: white; text-decoration: none; padding: 12px 30px; border-radius: 8px; font-weight: bold; margin-bottom: 10px;">View Your Ticket Online</a>
            <p style="color: #6b7280; margin: 10px 0 0; font-size: 12px;">Need help? Contact us at info@twodawn.com.ng</p>
          </div>
        </div>
        <p style="text-align: center; color: #9ca3af; margin-top: 20px; font-size: 12px;">© ${new Date().getFullYear()} 2DAWN. All rights reserved.</p>
      </div>
    `,
  };

  try {
    return await sendMailViaDriver({
      to: order.buyer_email,
      subject: mailOptions.subject,
      html: mailOptions.html,
    });
  } catch (error) {
    console.error(`Failed to send email via ${process.env.MAIL_MAILER || 'smtp'} driver:`, error.message);
    
    // In local development / non-production, fall back to log so flow doesn't break
    if (process.env.APP_ENV !== 'production' && process.env.NODE_ENV !== 'production') {
      console.log('⚠️ Non-production environment detected. Falling back to log driver to prevent flow block.');
      try {
        return logEmailToFile({
          to: order.buyer_email,
          subject: mailOptions.subject,
          html: mailOptions.html,
        });
      } catch (logError) {
        console.error('Failed to write fallback log email:', logError.message);
      }
    }
    
    return { success: false, error: error.message };
  }
}

// Simple in-memory queue for background jobs
const emailQueue = [];
let isProcessing = false;

async function queueTicketEmail(order, event) {
  emailQueue.push({ order, event, timestamp: Date.now() });
  processQueue();
}

async function processQueue() {
  if (isProcessing || emailQueue.length === 0) return;
  
  isProcessing = true;
  
  while (emailQueue.length > 0) {
    const job = emailQueue.shift();
    try {
      await sendTicketEmail(job.order, job.event);
    } catch (error) {
      console.error('Email job failed:', error);
    }
  }
  
  isProcessing = false;
}

module.exports = {
  sendTicketEmail,
  queueTicketEmail,
  processQueue,
};
