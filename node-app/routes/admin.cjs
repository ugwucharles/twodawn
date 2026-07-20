const express = require('express');
const {
  getDashboardData,
  toggleEvent,
  getOrders,
  getOrderDetails,
  getEventHostTokens,
  generateHostToken,
  getEvents,
  getEventDetails,
  updateEventDetails,
  removeEvent,
  getOrganizers,
  getOrganizerDetails,
  updateOrganizerStatus,
  getUsers,
  getUserDetails,
  getTransactions,
  getActivityFeed,
  getHealthStatus,
} = require('../services/adminService.cjs');
const { proxyRequest } = require('../services/proxyRequest.cjs');
const { isJsonRequest } = require('../lib/authHttp.cjs');
const { attachSessionUser, requireAdmin } = require('../services/sessionAuth.cjs');
const { sendTicketEmail } = require('../services/emailService.cjs');
const { findOrderByReference } = require('../models/orderModel.cjs');
const { findEventById } = require('../models/eventModel.cjs');
const { query } = require('../db/client.cjs');

function createAdminRouter() {
  const router = express.Router();

  router.use(attachSessionUser);
  router.use(requireAdmin);

  // GET /admin/dashboard - admin dashboard
  router.get('/dashboard', async (req, res) => {
    try {
      const data = await getDashboardData();
      return res.json({ ok: true, ...data });
    } catch (error) {
      console.error('Admin dashboard error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to fetch dashboard data' });
    }
  });

  // PATCH /admin/events/:id/toggle - toggle event publish status
  router.patch('/events/:id/toggle', async (req, res) => {
    try {
      const eventId = parseInt(req.params.id, 10);
      const event = await toggleEvent(eventId);
      
      if (!event) {
        return res.status(404).json({ ok: false, error: 'Event not found' });
      }
      
      return res.json({ ok: true, id: event.id, is_published: Boolean(event.is_published) });
    } catch (error) {
      console.error('Toggle event error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to toggle event' });
    }
  });

  // PATCH /admin/events/:id/toggle-json - JSON toggle endpoint
  router.patch('/events/:id/toggle-json', async (req, res) => {
    try {
      const eventId = parseInt(req.params.id, 10);
      const event = await toggleEvent(eventId);
      
      if (!event) {
        return res.status(404).json({ ok: false, error: 'Event not found' });
      }
      
      return res.json({ id: event.id, is_published: Boolean(event.is_published) });
    } catch (error) {
      console.error('Toggle event JSON error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to toggle event' });
    }
  });

  // GET /admin/orders - orders list
  router.get('/orders', async (req, res) => {
    try {
      if (isJsonRequest(req)) {
        const page = {
          limit: req.query.limit ? parseInt(req.query.limit, 10) : 20,
          offset: req.query.page ? (parseInt(req.query.page, 10) - 1) * 20 : 0,
        };
        const orders = await getOrders(page);
        return res.json({ ok: true, orders });
      }
      // Proxy to Laravel for HTML
      return proxyRequest(req, res);
    } catch (error) {
      console.error('Admin orders error:', error);
      if (isJsonRequest(req)) {
        return res.status(500).json({ ok: false, error: 'Failed to fetch orders' });
      }
      return proxyRequest(req, res);
    }
  });

  // GET /admin/orders/:id - order details
  router.get('/orders/:id', async (req, res) => {
    try {
      const orderId = parseInt(req.params.id, 10);
      const order = await getOrderDetails(orderId);
      
      if (!order) {
        if (isJsonRequest(req)) {
          return res.status(404).json({ ok: false, error: 'Order not found' });
        }
        return proxyRequest(req, res);
      }
      
      if (isJsonRequest(req)) {
        return res.json({ ok: true, order });
      }
      // Proxy to Laravel for HTML
      return proxyRequest(req, res);
    } catch (error) {
      console.error('Admin order details error:', error);
      if (isJsonRequest(req)) {
        return res.status(500).json({ ok: false, error: 'Failed to fetch order' });
      }
      return proxyRequest(req, res);
    }
  });

  // POST /admin/events/:id/host-tokens - create host token
  router.post('/events/:id/host-tokens', async (req, res) => {
    try {
      const eventId = parseInt(req.params.id, 10);
      const label = req.body.label || null;
      const token = await generateHostToken(eventId, label);

      if (!token) {
        return res.status(404).json({ ok: false, error: 'Event not found' });
      }

      return res.json({ ok: true, token });
    } catch (error) {
      console.error('Create host token error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to create host token' });
    }
  });

  // GET /admin/events/list - list all events
  router.get('/events/list', async (req, res) => {
    try {
      const limit = parseInt(req.query.limit || '50', 10);
      const offset = parseInt(req.query.offset || '0', 10);
      const status = req.query.status || null;
      const events = await getEvents({ limit, offset, status });
      return res.json({ ok: true, events });
    } catch (error) {
      console.error('Get events error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to fetch events' });
    }
  });

  // GET /admin/events/:id/details - event details
  router.get('/events/:id/details', async (req, res) => {
    try {
      const eventId = parseInt(req.params.id, 10);
      const event = await getEventDetails(eventId);
      if (!event) {
        return res.status(404).json({ ok: false, error: 'Event not found' });
      }
      return res.json({ ok: true, event });
    } catch (error) {
      console.error('Get event details error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to fetch event details' });
    }
  });

  // PATCH /admin/events/:id - update event
  router.patch('/events/:id', async (req, res) => {
    try {
      const eventId = parseInt(req.params.id, 10);
      const event = await updateEventDetails(eventId, req.body);
      if (!event) {
        return res.status(404).json({ ok: false, error: 'Event not found' });
      }
      return res.json({ ok: true, event });
    } catch (error) {
      console.error('Update event error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to update event' });
    }
  });

  // DELETE /admin/events/:id - delete event
  router.delete('/events/:id', async (req, res) => {
    try {
      const eventId = parseInt(req.params.id, 10);
      const success = await removeEvent(eventId);
      if (!success) {
        return res.status(404).json({ ok: false, error: 'Event not found' });
      }
      return res.json({ ok: true, message: 'Event deleted' });
    } catch (error) {
      console.error('Delete event error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to delete event' });
    }
  });

  // GET /admin/organizers - list organizers
  router.get('/organizers', async (req, res) => {
    try {
      const limit = parseInt(req.query.limit || '50', 10);
      const offset = parseInt(req.query.offset || '0', 10);
      const organizers = await getOrganizers({ limit, offset });
      return res.json({ ok: true, organizers });
    } catch (error) {
      console.error('Get organizers error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to fetch organizers' });
    }
  });

  // GET /admin/organizers/:id - organizer details
  router.get('/organizers/:id', async (req, res) => {
    try {
      const organizerId = parseInt(req.params.id, 10);
      const organizer = await getOrganizerDetails(organizerId);
      if (!organizer) {
        return res.status(404).json({ ok: false, error: 'Organizer not found' });
      }
      return res.json({ ok: true, organizer });
    } catch (error) {
      console.error('Get organizer details error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to fetch organizer details' });
    }
  });

  // PATCH /admin/organizers/:id - update organizer
  router.patch('/organizers/:id', async (req, res) => {
    try {
      const organizerId = parseInt(req.params.id, 10);
      const organizer = await updateOrganizerStatus(organizerId, req.body);
      if (!organizer) {
        return res.status(404).json({ ok: false, error: 'Organizer not found' });
      }
      return res.json({ ok: true, organizer });
    } catch (error) {
      console.error('Update organizer error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to update organizer' });
    }
  });

  // POST /admin/organizers - create organizer
  router.post('/organizers', async (req, res) => {
    try {
      const { name, email, username, password } = req.body;
      if (!name || !email || !username || !password) {
        return res.status(400).json({ ok: false, error: 'All fields are required' });
      }

      const bcrypt = require('bcryptjs');
      const hashedPassword = await bcrypt.hash(password, 10);

      const { query } = require('../db/client.cjs');
      const result = await query(`
        INSERT INTO users (name, email, username, password, is_organizer, created_at, updated_at)
        VALUES (?, ?, ?, ?, 1, datetime('now'), datetime('now'))
      `, [name, email, username, hashedPassword]);

      const newId = Number(result.lastInsertRowid);
      await logActivity('create_organizer', 'user', newId);

      return res.json({
        ok: true,
        organizer: {
          id: newId,
          name,
          email,
          username,
          is_organizer: 1,
          events_count: 0,
          total_revenue: 0
        }
      });
    } catch (error) {
      console.error('Create organizer error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to create organizer' });
    }
  });

  // DELETE /admin/organizers/:id - delete organizer
  router.delete('/organizers/:id', async (req, res) => {
    try {
      const organizerId = parseInt(req.params.id, 10);
      const { deleteAuthUserById } = require('../models/authUserModel.cjs');
      const success = await deleteAuthUserById(organizerId);
      if (!success) {
        return res.status(404).json({ ok: false, error: 'Organizer not found' });
      }
      await logActivity('delete_organizer', 'user', organizerId);
      return res.json({ ok: true, message: 'Organizer deleted successfully' });
    } catch (error) {
      console.error('Delete organizer error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to delete organizer' });
    }
  });

  // POST /admin/events - create event (as admin)
  router.post('/events', async (req, res) => {
    try {
      const { title, slug, venue, starts_at, price, capacity, is_published } = req.body;
      if (!title || !starts_at || !venue) {
        return res.status(400).json({ ok: false, error: 'Title, date and venue are required' });
      }
      const { query } = require('../db/client.cjs');

      // Default user_id to 1 (Charles/Admin)
      const user_id = req.body.user_id || 1;

      const eventSlug = slug || title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');

      const result = await query(`
        INSERT INTO events (user_id, title, slug, venue, starts_at, price, capacity, is_published, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))
      `, [user_id, title, eventSlug, venue, starts_at, price || 0, capacity || 100, is_published ? 1 : 0]);

      const newId = Number(result.lastInsertRowid);
      await logActivity('create_event', 'event', newId);

      return res.json({
        ok: true,
        event: {
          id: newId,
          user_id,
          title,
          slug: eventSlug,
          venue,
          starts_at,
          price: price || 0,
          capacity: capacity || 100,
          is_published: is_published ? 1 : 0
        }
      });
    } catch (error) {
      console.error('Create event error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to create event' });
    }
  });

  // GET /admin/users - list users
  router.get('/users', async (req, res) => {
    try {
      const limit = parseInt(req.query.limit || '50', 10);
      const offset = parseInt(req.query.offset || '0', 10);
      const search = req.query.search || null;
      const users = await getUsers({ limit, offset, search });
      return res.json({ ok: true, users });
    } catch (error) {
      console.error('Get users error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to fetch users' });
    }
  });

  // GET /admin/users/:id - user details
  router.get('/users/:id', async (req, res) => {
    try {
      const userId = parseInt(req.params.id, 10);
      const user = await getUserDetails(userId);
      if (!user) {
        return res.status(404).json({ ok: false, error: 'User not found' });
      }
      return res.json({ ok: true, user });
    } catch (error) {
      console.error('Get user details error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to fetch user details' });
    }
  });

  // GET /admin/transactions - list transactions
  router.get('/transactions', async (req, res) => {
    try {
      const limit = parseInt(req.query.limit || '50', 10);
      const offset = parseInt(req.query.offset || '0', 10);
      const status = req.query.status || null;
      const transactions = await getTransactions({ limit, offset, status });
      return res.json({ ok: true, transactions });
    } catch (error) {
      console.error('Get transactions error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to fetch transactions' });
    }
  });

  // GET /admin/activity - activity feed
  router.get('/activity', async (req, res) => {
    try {
      const limit = parseInt(req.query.limit || '50', 10);
      const offset = parseInt(req.query.offset || '0', 10);
      const activity = await getActivityFeed({ limit, offset });
      return res.json({ ok: true, activity });
    } catch (error) {
      console.error('Get activity error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to fetch activity' });
    }
  });

  // GET /admin/health - system health
  router.get('/health', async (req, res) => {
    try {
      const health = await getHealthStatus();
      return res.json({ ok: true, health });
    } catch (error) {
      console.error('Get health error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to fetch health status' });
    }
  });

  // GET /admin/withdrawals - list all withdrawal requests
  router.get('/withdrawals', async (req, res) => {
    try {
      const { query } = require('../db/client.cjs');
      const withdrawals = await query(`
        SELECT w.*, u.name as organizer_name, u.email as organizer_email
        FROM withdrawals w
        LEFT JOIN users u ON w.user_id = u.id
        ORDER BY w.created_at DESC
      `);
      return res.json({ ok: true, withdrawals });
    } catch (error) {
      console.error('Get withdrawals error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to fetch withdrawals' });
    }
  });

function getBankCodeFromName(bankName) {
  const name = String(bankName || '').toLowerCase();
  if (name.includes('opay') || name.includes('paycom')) return '999992';
  if (name.includes('guaranty') || name.includes('gtb') || name.includes('gtbank')) return '058';
  if (name.includes('access')) return '044';
  if (name.includes('zenith')) return '057';
  if (name.includes('united bank') || name.includes('uba')) return '033';
  if (name.includes('sterling')) return '050';
  if (name.includes('first bank') || name.includes('firstbank')) return '011';
  if (name.includes('wema')) return '094';
  if (name.includes('kuda')) return '50211';
  if (name.includes('moniepoint')) return '50515';
  if (name.includes('palmpay')) return '999991';
  return null;
}

  // PATCH /admin/withdrawals/:id/approve - approve withdrawal
  router.patch('/withdrawals/:id/approve', async (req, res) => {
    try {
      const withdrawalId = parseInt(req.params.id, 10);
      const { query } = require('../db/client.cjs');
      
      const withdrawals = await query(`SELECT * FROM withdrawals WHERE id = ? LIMIT 1`, [withdrawalId]);
      const w = withdrawals[0];
      if (!w) {
        return res.status(404).json({ ok: false, error: 'Withdrawal request not found' });
      }

      if (w.status === 'approved') {
        return res.status(400).json({ ok: false, error: 'Withdrawal already approved' });
      }

      let bankCode = w.bank_code;
      if (!bankCode) {
        bankCode = getBankCodeFromName(w.bank_name);
      }
      if (!bankCode) {
        return res.status(400).json({ ok: false, error: 'Could not resolve bank code for bank: ' + w.bank_name });
      }

      const secret = process.env.PAYSTACK_SECRET_KEY;
      if (!secret) {
        return res.status(500).json({ ok: false, error: 'Paystack secret key is not configured' });
      }

      const recipientResponse = await fetch('https://api.paystack.co/transferrecipient', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${secret}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          type: 'nuban',
          name: w.account_name,
          account_number: w.account_number,
          bank_code: bankCode,
          currency: 'NGN'
        })
      });
      const recipientData = await recipientResponse.json();

      if (!recipientData.status) {
        console.error('Paystack recipient creation failed:', recipientData);
        return res.status(400).json({ ok: false, error: `Paystack Recipient failed: ${recipientData.message}` });
      }

      const recipientCode = recipientData.data.recipient_code;

      const transferResponse = await fetch('https://api.paystack.co/transfer', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${secret}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          source: 'balance',
          amount: Math.round(w.amount * 100),
          recipient: recipientCode,
          reason: `2DAWN Organizer Payout (#${w.id})`
        })
      });
      const transferData = await transferResponse.json();

      if (!transferData.status) {
        console.error('Paystack Transfer failed:', transferData);
        return res.status(400).json({ ok: false, error: `Paystack Transfer failed: ${transferData.message}` });
      }

      const now = new Date().toISOString();
      await query(`
        UPDATE withdrawals
        SET status = 'approved', updated_at = ?
        WHERE id = ?
      `, [now, withdrawalId]);

      return res.json({ ok: true, message: 'Withdrawal approved and paid out via Paystack successfully!', data: transferData.data });
    } catch (error) {
      console.error('Approve withdrawal error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to approve withdrawal' });
    }
  });

  // PATCH /admin/withdrawals/:id/reject - reject withdrawal
  router.patch('/withdrawals/:id/reject', async (req, res) => {
    try {
      const withdrawalId = parseInt(req.params.id, 10);
      const { query } = require('../db/client.cjs');
      const now = new Date().toISOString();
      await query(`
        UPDATE withdrawals
        SET status = 'rejected', updated_at = ?
        WHERE id = ?
      `, [now, withdrawalId]);
      return res.json({ ok: true, message: 'Withdrawal rejected successfully' });
    } catch (error) {
      console.error('Reject withdrawal error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to reject withdrawal' });
    }
  });

  // POST /admin/orders/:reference/resend-email - resend ticket email for a specific order
  router.post('/orders/:reference/resend-email', async (req, res) => {
    try {
      const order = await findOrderByReference(req.params.reference);
      if (!order) return res.status(404).json({ ok: false, error: 'Order not found' });
      if (!order.buyer_email) return res.status(400).json({ ok: false, error: 'Order has no buyer email' });
      if (order.status !== 'paid' && order.status !== 'confirmed' && order.status !== 'used') {
        return res.status(400).json({ ok: false, error: `Cannot resend for order with status: ${order.status}` });
      }

      const event = await findEventById(order.event_id);
      if (!event) return res.status(404).json({ ok: false, error: 'Event not found' });

      const result = await sendTicketEmail(order, event);
      if (result.success) {
        console.log(`✉️ Admin resent ticket email to ${order.buyer_email} for order ${order.paystack_reference}`);
        return res.json({ ok: true, message: `Ticket email sent to ${order.buyer_email}`, messageId: result.messageId });
      } else {
        return res.status(500).json({ ok: false, error: result.error || 'Failed to send email' });
      }
    } catch (error) {
      console.error('Resend email error:', error);
      return res.status(500).json({ ok: false, error: error.message });
    }
  });

  // POST /admin/events/:id/resend-all-emails - resend ticket emails for ALL paid orders for an event
  router.post('/events/:id/resend-all-emails', async (req, res) => {
    try {
      const eventId = parseInt(req.params.id, 10);
      const event = await findEventById(eventId);
      if (!event) return res.status(404).json({ ok: false, error: 'Event not found' });

      const orders = await query(
        `SELECT * FROM orders WHERE event_id = ? AND status IN ('paid','confirmed','used') AND buyer_email IS NOT NULL`,
        [eventId]
      );

      if (!orders || orders.length === 0) {
        return res.json({ ok: true, message: 'No paid orders found for this event', sent: 0, failed: 0 });
      }

      let sent = 0;
      let failed = 0;
      const errors = [];

      for (const order of orders) {
        try {
          const result = await sendTicketEmail(order, event);
          if (result.success) {
            sent++;
            console.log(`✉️ Resent to ${order.buyer_email}`);
          } else {
            failed++;
            errors.push({ email: order.buyer_email, error: result.error });
          }
        } catch (e) {
          failed++;
          errors.push({ email: order.buyer_email, error: e.message });
        }
      }

      return res.json({ ok: true, message: `Sent ${sent} emails, ${failed} failed`, sent, failed, errors });
    } catch (error) {
      console.error('Resend all emails error:', error);
      return res.status(500).json({ ok: false, error: error.message });
    }
  });

  return router;
}

module.exports = {
  createAdminRouter,
};
