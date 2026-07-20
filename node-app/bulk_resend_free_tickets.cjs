/**
 * Bulk Resend: Free Ticket Emails
 * Sends ticket emails to all paid free-ticket holders for AFTER DARK - THE HOUSE PARTY
 * Run from: node-app/
 */
'use strict';
process.chdir(__dirname);

require('dotenv').config({ path: './.env' });

const nodemailer = require('nodemailer');
const { createClient } = require('@libsql/client/web');

// ── Config ────────────────────────────────────────────────────────────────────
const TURSO_URL   = process.env.TURSO_DATABASE_URL;
const TURSO_TOKEN = process.env.TURSO_AUTH_TOKEN;

const SMTP_HOST = process.env.MAIL_HOST       || 'mail.twodawn.com.ng';
const SMTP_PORT = parseInt(process.env.MAIL_PORT || '465', 10);
const SMTP_USER = process.env.MAIL_USERNAME;
const SMTP_PASS = process.env.MAIL_PASSWORD;
const FROM_ADDR = process.env.MAIL_FROM_ADDRESS || 'hello@twodawn.com.ng';
const FROM_NAME = process.env.MAIL_FROM_NAME    || '2DAWN';
const FRONTEND  = (process.env.FRONTEND_URL || 'https://twodawn.com.ng').replace(/\/$/, '');

// Skip orders with obviously bad emails
const BAD_EMAIL_PATTERNS = [/@gmail$/, /@gmsil\.com$/];

// Delay between emails (ms) — avoids SMTP throttling
const DELAY_MS = 1500;

// ── Helpers ───────────────────────────────────────────────────────────────────
function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }

function escapeHtml(v) {
  return String(v ?? '')
    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function buildTransporter() {
  return nodemailer.createTransport({
    host: SMTP_HOST,
    port: SMTP_PORT,
    secure: true,           // SSL on 465
    auth: { user: SMTP_USER, pass: SMTP_PASS },
    connectionTimeout: 15000,
    greetingTimeout:   15000,
    socketTimeout:     20000,
    tls: { rejectUnauthorized: false },
  });
}

function buildHtml(order, event) {
  const startDate  = event.starts_at
    ? new Date(event.starts_at).toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' })
    : 'TBD';
  const startTime  = event.starts_at
    ? new Date(event.starts_at).toLocaleTimeString('en-US', { hour:'2-digit', minute:'2-digit' })
    : 'TBD';

  const safeTitle   = escapeHtml(event.title || '2DAWN Event');
  const safeVenue   = escapeHtml(event.venue || 'TBD');
  const safeName    = escapeHtml(order.buyer_name  || 'Guest');
  const safeEmail   = escapeHtml(order.buyer_email || '');
  const safePhone   = escapeHtml(order.buyer_phone || 'Not provided');
  const safeRef     = escapeHtml(order.paystack_reference);
  const ticketType  = escapeHtml(order.ticket_type || 'General Admission');
  const amountPaid  = `₦${((order.amount || 0) / 100).toFixed(2)}`;
  const publicUrl   = `${FRONTEND}/find-tickets?ref=${order.paystack_reference}`;
  const qrUrl       = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(order.paystack_reference)}`;
  const logoUrl     = `${FRONTEND}/logo.svg`;

  return `
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f9fafb; padding: 20px;">
  <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <div style="text-align: center; margin-bottom: 30px;">
      <img src="${logoUrl}" alt="2DAWN" style="display: block; width: 150px; max-width: 60%; height: auto; margin: 0 auto 18px;" />
      <h1 style="color: #8b5cf6; margin: 0; font-size: 28px;">🎫 Your Ticket</h1>
      <p style="color: #6b7280; margin: 10px 0 0;">You're all set! See you at the event.</p>
    </div>

    <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border-radius: 8px; padding: 20px; margin-bottom: 25px;">
      <h2 style="color: white; margin: 0 0 10px; font-size: 22px;">${safeTitle}</h2>
      <p style="color: rgba(255,255,255,0.9); margin: 0; font-size: 14px;">${startDate}</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
      <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
        <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Date &amp; Time</p>
        <p style="color: #1f2937; margin: 0; font-size: 14px;">${startDate}<br>${startTime}</p>
      </div>
      <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
        <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Venue</p>
        <p style="color: #1f2937; margin: 0; font-size: 14px;">${safeVenue}</p>
      </div>
    </div>

    <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
      <p style="color: #6b7280; margin: 0 0 10px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Buyer Details</p>
      <p style="color: #1f2937; margin: 0 0 6px; font-size: 14px;"><strong>Name:</strong> ${safeName}</p>
      <p style="color: #1f2937; margin: 0 0 6px; font-size: 14px;"><strong>Email:</strong> ${safeEmail}</p>
      <p style="color: #1f2937; margin: 0; font-size: 14px;"><strong>Phone:</strong> ${safePhone}</p>
    </div>

    <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
      <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Reference</p>
      <p style="color: #1f2937; margin: 0; font-size: 18px; font-weight: bold; letter-spacing: 1px;">${safeRef}</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
      <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
        <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Ticket</p>
        <p style="color: #1f2937; margin: 0; font-size: 18px; font-weight: bold;">${ticketType} x ${order.quantity}</p>
      </div>
      <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
        <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Amount Paid</p>
        <p style="color: #1f2937; margin: 0; font-size: 18px; font-weight: bold;">${amountPaid}</p>
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
}

// ── Main ──────────────────────────────────────────────────────────────────────
async function main() {
  if (!TURSO_URL || !TURSO_TOKEN) {
    console.error('❌ TURSO_DATABASE_URL / TURSO_AUTH_TOKEN not set'); process.exit(1);
  }
  if (!SMTP_USER || !SMTP_PASS) {
    console.error('❌ MAIL_USERNAME / MAIL_PASSWORD not set'); process.exit(1);
  }

  console.log('🔌 Connecting to Turso…');
  const db = createClient({ url: TURSO_URL, authToken: TURSO_TOKEN });

  const result = await db.execute({
    sql: `SELECT o.id, o.buyer_name, o.buyer_email, o.buyer_phone, o.amount, o.status,
                 o.paystack_reference, o.ticket_code, o.ticket_type, o.quantity, o.event_id,
                 e.title AS event_title, e.starts_at, e.venue
          FROM orders o
          LEFT JOIN events e ON e.id = o.event_id
          WHERE o.amount = 0 AND o.status IN ('paid', 'confirmed')
          ORDER BY o.id`,
    args: [],
  });

  const orders = result.rows.map(r => ({ ...r }));
  console.log(`📋 Found ${orders.length} free ticket orders to process.\n`);

  const transporter = buildTransporter();

  // Verify SMTP connection once before blasting
  try {
    await transporter.verify();
    console.log('✅ SMTP connection verified.\n');
  } catch (err) {
    console.error('❌ SMTP connection failed:', err.message);
    process.exit(1);
  }

  const results = { sent: [], skipped: [], failed: [] };

  for (let i = 0; i < orders.length; i++) {
    const order = orders[i];
    const prefix = `[${i + 1}/${orders.length}] ${order.buyer_name} <${order.buyer_email}>`;

    // Skip bad/invalid emails
    const isBadEmail = BAD_EMAIL_PATTERNS.some(p => p.test(order.buyer_email || ''));
    if (isBadEmail) {
      console.log(`⚠️  SKIP ${prefix} — invalid email address`);
      results.skipped.push({ id: order.id, email: order.buyer_email, reason: 'invalid email' });
      continue;
    }

    const event = {
      title:    order.event_title,
      starts_at: order.starts_at,
      venue:    order.venue,
    };

    const html    = buildHtml(order, event);
    const subject = `Your Ticket for ${event.title} – ${order.paystack_reference}`;

    try {
      const info = await transporter.sendMail({
        from:    `"${FROM_NAME}" <${FROM_ADDR}>`,
        to:      order.buyer_email,
        subject,
        html,
      });
      console.log(`✅ SENT  ${prefix} (msgId: ${info.messageId})`);
      results.sent.push({ id: order.id, email: order.buyer_email, msgId: info.messageId });
    } catch (err) {
      console.error(`❌ FAIL  ${prefix} — ${err.message}`);
      results.failed.push({ id: order.id, email: order.buyer_email, error: err.message });
    }

    // Polite delay between sends
    if (i < orders.length - 1) await sleep(DELAY_MS);
  }

  await db.close();

  // ── Summary ────────────────────────────────────────────────────────────────
  console.log('\n══════════════════════════════════════════');
  console.log('           BULK RESEND SUMMARY');
  console.log('══════════════════════════════════════════');
  console.log(`✅ Sent:    ${results.sent.length}`);
  console.log(`⚠️  Skipped: ${results.skipped.length}`);
  console.log(`❌ Failed:  ${results.failed.length}`);

  if (results.skipped.length > 0) {
    console.log('\nSkipped (bad email):');
    results.skipped.forEach(s => console.log(`  - [ID ${s.id}] ${s.email} — ${s.reason}`));
  }
  if (results.failed.length > 0) {
    console.log('\nFailed (SMTP error):');
    results.failed.forEach(f => console.log(`  - [ID ${f.id}] ${f.email} — ${f.error}`));
  }
  if (results.sent.length > 0) {
    console.log('\nSent to:');
    results.sent.forEach(s => console.log(`  ✓ [ID ${s.id}] ${s.email}`));
  }
}

main().catch(err => { console.error('Fatal error:', err); process.exit(1); });
