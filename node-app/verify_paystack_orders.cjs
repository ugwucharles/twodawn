'use strict';
process.chdir(__dirname);
require('dotenv').config({ path: './.env' });

const https = require('https');
const nodemailer = require('nodemailer');
const { createClient } = require('@libsql/client/web');

async function paystackVerify(reference) {
  return new Promise((resolve, reject) => {
    const options = {
      hostname: 'api.paystack.co',
      path: `/transaction/verify/${encodeURIComponent(reference)}`,
      method: 'GET',
      headers: {
        Authorization: `Bearer ${process.env.PAYSTACK_SECRET_KEY}`,
      },
    };
    const req = https.request(options, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => {
        try { resolve(JSON.parse(data)); }
        catch (e) { reject(e); }
      });
    });
    req.on('error', reject);
    req.end();
  });
}

function escapeHtml(v) {
  return String(v ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

async function main() {
  const db = createClient({
    url: process.env.TURSO_DATABASE_URL,
    authToken: process.env.TURSO_AUTH_TOKEN,
  });

  // Get all pending orders
  const result = await db.execute({
    sql: `SELECT o.id, o.buyer_name, o.buyer_email, o.amount, o.status, o.quantity, o.paystack_reference, o.event_id, o.created_at,
                 e.title AS event_title, e.starts_at, e.venue
          FROM orders o
          LEFT JOIN events e ON e.id = o.event_id
          WHERE o.status = 'pending' AND o.amount > 0
          ORDER BY o.id ASC`,
    args: [],
  });

  const pendingOrders = result.rows.map(r => ({ ...r }));
  console.log(`\nFound ${pendingOrders.length} pending orders to verify.\n`);

  if (pendingOrders.length === 0) {
    console.log('No pending orders to verify.');
    await db.close();
    return;
  }

  const FRONTEND = (process.env.FRONTEND_URL || 'https://twodawn.com.ng').replace(/\/$/, '');
  const FROM_NAME = process.env.MAIL_FROM_NAME || '2DAWN';
  const FROM_ADDR = process.env.MAIL_FROM_ADDRESS || 'hello@twodawn.com.ng';

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

  let verifiedCount = 0;

  for (const o of pendingOrders) {
    const amountNaira = (Number(o.amount) / 100).toFixed(2);
    console.log(`Verifying ID:${o.id} | ${o.buyer_name} <${o.buyer_email}> | Reference: ${o.paystack_reference}`);

    try {
      const ps = await paystackVerify(o.paystack_reference);
      const psStatus = ps.data?.status;
      console.log(`  -> Paystack status: ${psStatus}`);

      if (ps.status === true && psStatus === 'success') {
        console.log(`  🎉 Success verified! Updating DB and sending email...`);

        // 1. Update DB to status = 'paid' and ensure it points to event_id = 13 (or stays same if it's correct)
        // Since event 12 was deleted, we should point it to event 13.
        const targetEventId = o.event_id === 12 ? 13 : o.event_id;
        
        await db.execute({
          sql: `UPDATE orders SET status = 'paid', event_id = ? WHERE id = ?`,
          args: [targetEventId, o.id],
        });
        console.log(`  Updated order ID ${o.id} status to 'paid' and event_id to ${targetEventId}`);

        // Fetch refreshed event details just in case event_id changed to 13
        const evResult = await db.execute({
          sql: `SELECT title, starts_at, venue FROM events WHERE id = ?`,
          args: [targetEventId],
        });
        const eventInfo = evResult.rows[0] || {};
        const eventTitle = eventInfo.title || o.event_title;
        const venue = eventInfo.venue || o.venue;
        const startsAt = eventInfo.starts_at || o.starts_at;

        const startDate = startsAt
          ? new Date(startsAt).toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' })
          : 'TBD';
        const startTime = startsAt
          ? new Date(startsAt).toLocaleTimeString('en-US', { hour:'2-digit', minute:'2-digit' })
          : 'TBD';

        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(o.paystack_reference)}`;
        const publicUrl = `${FRONTEND}/find-tickets?ref=${o.paystack_reference}`;
        const logoUrl = `${FRONTEND}/logo.svg`;

        const html = `
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f9fafb; padding: 20px;">
  <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <div style="text-align: center; margin-bottom: 30px;">
      <img src="${logoUrl}" alt="2DAWN" style="display: block; width: 150px; max-width: 60%; height: auto; margin: 0 auto 18px;" />
      <h1 style="color: #8b5cf6; margin: 0; font-size: 28px;">🎫 Your Ticket</h1>
      <p style="color: #6b7280; margin: 10px 0 0;">Payment confirmed! See you at the event.</p>
    </div>
    <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border-radius: 8px; padding: 20px; margin-bottom: 25px;">
      <h2 style="color: white; margin: 0 0 10px; font-size: 22px;">${escapeHtml(eventTitle)}</h2>
      <p style="color: rgba(255,255,255,0.9); margin: 0; font-size: 14px;">${startDate}</p>
    </div>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
      <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
        <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Date &amp; Time</p>
        <p style="color: #1f2937; margin: 0; font-size: 14px;">${startDate}<br>${startTime}</p>
      </div>
      <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
        <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Venue</p>
        <p style="color: #1f2937; margin: 0; font-size: 14px;">${escapeHtml(venue)}</p>
      </div>
    </div>
    <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
      <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Reference</p>
      <p style="color: #1f2937; margin: 0; font-size: 18px; font-weight: bold; letter-spacing: 1px;">${escapeHtml(o.paystack_reference)}</p>
    </div>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
      <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
        <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Ticket</p>
        <p style="color: #1f2937; margin: 0; font-size: 18px; font-weight: bold;">${escapeHtml(o.ticket_type || 'General Admission')} x${o.quantity}</p>
      </div>
      <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
        <p style="color: #6b7280; margin: 0 0 5px; font-size: 12px; text-transform: uppercase; font-weight: bold;">Amount Paid</p>
        <p style="color: #1f2937; margin: 0; font-size: 18px; font-weight: bold;">₦${amountNaira}</p>
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

        await transporter.sendMail({
          from: `"${FROM_NAME}" <${FROM_ADDR}>`,
          to: o.buyer_email,
          subject: `Your Ticket for ${eventTitle} – ${o.paystack_reference}`,
          html,
        });
        console.log(`  Sent email successfully to ${o.buyer_email}`);
        verifiedCount++;
      } else {
        console.log(`  Not successful. Msg: ${ps.message || 'None'}`);
      }
    } catch (e) {
      console.log(`  ❌ Check failed for ref ${o.paystack_reference}: ${e.message}`);
    }
  }

  console.log(`\nFinished verifying pending orders. Automatically processed and approved: ${verifiedCount}`);
  await db.close();
}

main().catch(console.error);
