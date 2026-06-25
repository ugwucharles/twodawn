const nodemailer = require('nodemailer');

let transporter = null;

function getTransporter() {
  if (!transporter) {
    transporter = nodemailer.createTransport({
      host: process.env.MAIL_HOST || 'smtp.mailtrap.io',
      port: parseInt(process.env.MAIL_PORT || '2525', 10),
      secure: process.env.MAIL_ENCRYPTION === 'ssl',
      auth: {
        user: process.env.MAIL_USERNAME,
        pass: process.env.MAIL_PASSWORD,
      },
    });
  }
  return transporter;
}

async function sendTicketEmail(order, event) {
  const transporter = getTransporter();

  const qrRemote = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(order.paystack_reference)}`;
  const publicUrl = `${process.env.FRONTEND_URL || 'https://twodawn.com.ng'}/find-tickets?ref=${order.paystack_reference}`;

  const startDate = event.starts_at ? new Date(event.starts_at).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) : 'TBD';
  const startTime = event.starts_at ? new Date(event.starts_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : 'TBD';
  const venue = event.venue || 'TBD';

  const mailOptions = {
    from: process.env.MAIL_FROM_ADDRESS || 'noreply@twodawn.com.ng',
    to: order.buyer_email,
    subject: `Your Ticket for ${event.title} - ${order.paystack_reference}`,
    html: `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f9fafb; padding: 20px;">
        <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
          <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #8b5cf6; margin: 0; font-size: 28px;">🎫 Your Ticket</h1>
            <p style="color: #6b7280; margin: 10px 0 0;">Thank you for your purchase!</p>
          </div>

          <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border-radius: 8px; padding: 20px; margin-bottom: 25px;">
            <h2 style="color: white; margin: 0 0 10px; font-size: 22px;">${event.title}</h2>
            <p style="color: rgba(255,255,255,0.9); margin: 0; font-size: 14px;">${startDate}</p>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
            <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
              <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Date & Time</p>
              <p style="color: #1f2937; margin: 0; font-size: 14px;">${startDate}<br>${startTime}</p>
            </div>
            <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
              <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Venue</p>
              <p style="color: #1f2937; margin: 0; font-size: 14px;">${venue}</p>
            </div>
          </div>

          <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
            <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Reference</p>
            <p style="color: #1f2937; margin: 0; font-size: 18px; font-weight: bold; letter-spacing: 1px;">${order.paystack_reference}</p>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
            <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
              <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Quantity</p>
              <p style="color: #1f2937; margin: 0; font-size: 18px; font-weight: bold;">${order.quantity}</p>
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
    const info = await transporter.sendMail(mailOptions);
    console.log('Ticket email sent:', info.messageId);
    return { success: true, messageId: info.messageId };
  } catch (error) {
    console.error('Failed to send ticket email:', error);
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
