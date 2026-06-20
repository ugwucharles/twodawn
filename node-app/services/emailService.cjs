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
  const publicUrl = `${process.env.APP_URL || 'https://twodawn.com.ng'}/orders/${order.paystack_reference}`;

  const mailOptions = {
    from: process.env.MAIL_FROM_ADDRESS || 'noreply@twodawn.com.ng',
    to: order.buyer_email,
    subject: `Your ticket - ${order.paystack_reference}`,
    html: `
      <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2>Your Ticket</h2>
        <p>Thank you for your purchase!</p>
        <p><strong>Event:</strong> ${event.title}</p>
        <p><strong>Reference:</strong> ${order.paystack_reference}</p>
        <p><strong>Quantity:</strong> ${order.quantity}</p>
        <p><strong>Amount:</strong> ₦${(order.amount / 100).toFixed(2)}</p>
        <p><img src="${qrRemote}" alt="QR Code" style="width: 220px; height: 220px;" /></p>
        <p><a href="${publicUrl}">View your ticket online</a></p>
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
