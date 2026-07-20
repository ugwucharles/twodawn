'use strict';
process.chdir(__dirname);
require('dotenv').config({ path: './.env' });

const nodemailer = require('nodemailer');
const { createClient } = require('@libsql/client/web');

async function main() {
  const db = createClient({
    url: process.env.TURSO_DATABASE_URL,
    authToken: process.env.TURSO_AUTH_TOKEN,
  });

  // Last emailed order was ID 57 (koko, 2026-07-17 15:38:11)
  const result = await db.execute({
    sql: `SELECT o.id, o.buyer_name, o.buyer_email, o.amount, o.status, o.created_at,
                 o.ticket_type, o.quantity, o.paystack_reference,
                 e.title AS event_title, e.starts_at, e.venue
          FROM orders o
          LEFT JOIN events e ON e.id = o.event_id
          WHERE o.id > 57
          ORDER BY o.id ASC`,
    args: [],
  });

  const orders = result.rows.map(r => ({ ...r }));
  console.log(`\nNew orders since last email batch (ID > 57): ${orders.length}\n`);

  if (orders.length === 0) {
    console.log('No new purchases — nobody missed an email.');
    await db.close();
    return;
  }

  orders.forEach((o, i) => {
    const amount = o.amount > 0 ? `₦${(o.amount / 100).toFixed(2)}` : 'FREE';
    console.log(`[${i+1}] ID:${o.id} | ${o.buyer_name} <${o.buyer_email}>`);
    console.log(`     Amount: ${amount} | Status: ${o.status}`);
    console.log(`     Date:   ${o.created_at}\n`);
  });

  // Now send emails to all new ones
  const FRONTEND = (process.env.FRONTEND_URL || 'https://twodawn.com.ng').replace(/\/$/, '');
  const FROM_NAME = process.env.MAIL_FROM_NAME || '2DAWN';
  const FROM_ADDR = process.env.MAIL_FROM_ADDRESS || 'hello@twodawn.com.ng';

  function escapeHtml(v) {
    return String(v ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }

  const transporter = nodemailer.createTransport({
    host: process.env.MAIL_HOST,
    port: parseInt(process.env.MAIL_PORT || '465', 10),
    secure: true,
    auth: { user: process.env.MAIL_USERNAME, pass: process.env.MAIL_PASSWORD },
    connectionTimeout: 15000,
    greetingTimeout: 15000,
    socketTimeout: 20000,
    tls: { rejectUnauthorized: false },
  });

  let sent = 0, failed = 0;
  for (const order of orders) {
    const email = order.buyer_email;
    if (!email || !email.includes('@') || !email.includes('.')) {
      console.log(`⚠️  Skipping ID:${order.id} — invalid email: ${email}`);
      failed++;
      continue;
    }

    const startDate = order.starts_at
      ? new Date(order.starts_at).toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' })
      : 'TBD';
    const startTime = order.starts_at
      ? new Date(order.starts_at).toLocaleTimeString('en-US', { hour:'2-digit', minute:'2-digit' })
      : 'TBD';
    const qrUrl   = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(order.paystack_reference)}`;
    const publicUrl = `${FRONTEND}/find-tickets?ref=${order.paystack_reference}`;
    const logoUrl   = `${FRONTEND}/logo.svg`;
    const amountLabel = order.amount > 0 ? `₦${(order.amount/100).toFixed(2)}` : 'FREE';

    const html = `
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f9fafb; padding: 20px;">
  <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <div style="text-align: center; margin-bottom: 30px;">
      <img src="${logoUrl}" alt="2DAWN" style="display: block; width: 150px; max-width: 60%; height: auto; margin: 0 auto 18px;" />
      <h1 style="color: #8b5cf6; margin: 0; font-size: 28px;">🎫 Your Ticket</h1>
      <p style="color: #6b7280; margin: 10px 0 0;">You're all set! See you at the event.</p>
    </div>
    <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border-radius: 8px; padding: 20px; margin-bottom: 25px;">
      <h2 style="color: white; margin: 0 0 10px; font-size: 22px;">${escapeHtml(order.event_title)}</h2>
      <p style="color: rgba(255,255,255,0.9); margin: 0; font-size: 14px;">${startDate}</p>
    </div>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
      <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
        <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Date &amp; Time</p>
        <p style="color: #1f2937; margin: 0; font-size: 14px;">${startDate}<br>${startTime}</p>
      </div>
      <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
        <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Venue</p>
        <p style="color: #1f2937; margin: 0; font-size: 14px;">${escapeHtml(order.venue)}</p>
      </div>
    </div>
    <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
      <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Reference</p>
      <p style="color: #1f2937; margin: 0; font-size: 18px; font-weight: bold; letter-spacing: 1px;">${escapeHtml(order.paystack_reference)}</p>
    </div>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
      <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
        <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Ticket</p>
        <p style="color: #1f2937; margin: 0; font-size: 18px; font-weight: bold;">${escapeHtml(order.ticket_type || 'General Admission')} x${order.quantity}</p>
      </div>
      <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
        <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Amount Paid</p>
        <p style="color: #1f2937; margin: 0; font-size: 18px; font-weight: bold;">${amountLabel}</p>
      </div>
    </div>
    <div style="text-align: center; margin-bottom: 25px;">
      <p style="color: #6b7280; margin: 0 0 15px; font-size: 14px;">Scan this QR code at the event entrance</p>
      <div style="display: inline-block; padding: 10px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <img src="${qrUrl}" alt="QR Code" style="width: 200px; height: 200px;" />
      </div>
    </div>
    <div style="text-align: center; padding-top: 20px; border-top: 1px solid #e5e7eb;">
      <a href="${publicUrl}" style="display: inline-block; background: #8b5cf6; color: white; text-decoration: none; padding: 12px 30px; border-radius: 8px; font-weight: bold; margin-bottom: 10px;">View Your Ticket Online</a>
      <p style="color: #6b7280; margin: 10px 0 0; font-size: 12px;">Need help? Contact us at info@twodawn.com.ng</p>
    </div>
  </div>
  <p style="text-align: center; color: #9ca3af; margin-top: 20px; font-size: 12px;">© ${new Date().getFullYear()} 2DAWN. All rights reserved.</p>
</div>`;

    try {
      const info = await transporter.sendMail({
        from: `"${FROM_NAME}" <${FROM_ADDR}>`,
        to: email,
        subject: `Your Ticket for ${order.event_title} – ${order.paystack_reference}`,
        html,
      });
      console.log(`✅ ID:${order.id} | Sent to ${email} | msgId: ${info.messageId}`);
      sent++;
    } catch (err) {
      console.log(`❌ ID:${order.id} | Failed to send to ${email}: ${err.message}`);
      failed++;
    }
  }

  console.log(`\nDone — ✅ Sent: ${sent} | ❌ Failed/Skipped: ${failed}`);
  await db.close();
}

main().catch(err => { console.error('❌ Fatal:', err.message); process.exit(1); });
