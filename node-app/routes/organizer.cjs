const express = require('express');
const multer = require('multer');
const path = require('path');
const fs = require('fs');
const { query } = require('../db/client.cjs');
const {
  getDashboardData,
  getEvents,
  getOrders,
} = require('../services/organizerService.cjs');
const { createEvent, findEventById } = require('../models/eventModel.cjs');
const { wantsJson } = require('../lib/authHttp.cjs');
const { attachSessionUser } = require('../services/sessionAuth.cjs');

// Configure upload storage for event flyers using Cloudinary
const cloudinary = require('cloudinary').v2;
const { CloudinaryStorage } = require('multer-storage-cloudinary');

cloudinary.config({
  cloud_name: process.env.CLOUDINARY_CLOUD_NAME,
  api_key: process.env.CLOUDINARY_API_KEY,
  api_secret: process.env.CLOUDINARY_API_SECRET,
});

const uploadStorage = new CloudinaryStorage({
  cloudinary: cloudinary,
  params: {
    folder: 'events',
    allowed_formats: ['jpg', 'jpeg', 'png', 'webp'],
  },
});

const upload = multer({ storage: uploadStorage });

function createOrganizerRouter() {
  const router = express.Router();

  router.use(attachSessionUser);

  // GET /organizer/dashboard - organizer dashboard
  router.get('/dashboard', async (req, res) => {
    try {
      if (wantsJson(req)) {
        if (!req.auth || !req.auth.isAuthenticated) {
          return res.status(401).json({ ok: false, error: 'unauthenticated', message: 'Authentication required.' });
        }
        const data = await getDashboardData(req.auth.user.id);
        return res.json({ ok: true, ...data });
      }
      // Proxy to Laravel for HTML
      return res.status(406).json({ ok: false, error: 'Not Acceptable', message: 'API only accepts JSON' });
    } catch (error) {
      console.error('Organizer dashboard error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to fetch dashboard data' });
    }
  });

  // GET /organizer/events - organizer events
  router.get('/events', async (req, res) => {
    try {
      if (wantsJson(req)) {
        if (!req.auth || !req.auth.isAuthenticated) {
          return res.status(401).json({ ok: false, error: 'unauthenticated', message: 'Authentication required.' });
        }
        const events = await getEvents(req.auth.user.id);
        return res.json({ ok: true, events });
      }
      // Proxy to Laravel for HTML
      return res.status(406).json({ ok: false, error: 'Not Acceptable', message: 'API only accepts JSON' });
    } catch (error) {
      console.error('Organizer events error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to fetch events' });
    }
  });

  // GET /organizer/orders - organizer orders
  router.get('/orders', async (req, res) => {
    try {
      if (wantsJson(req)) {
        if (!req.auth || !req.auth.isAuthenticated) {
          return res.status(401).json({ ok: false, error: 'unauthenticated', message: 'Authentication required.' });
        }
        const orders = await getOrders(req.auth.user.id, req.query);
        return res.json({ ok: true, orders });
      }
      // Proxy to Laravel for HTML
      return res.status(406).json({ ok: false, error: 'Not Acceptable', message: 'API only accepts JSON' });
    } catch (error) {
      console.error('Organizer orders error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to fetch orders' });
    }
  });

  // POST /organizer/events - create event
  router.post('/events', upload.single('image'), async (req, res) => {
    try {
      if (!req.auth || !req.auth.isAuthenticated) {
        return res.status(401).json({ ok: false, error: 'unauthenticated', message: 'Authentication required.' });
      }

      const body = req.body;

      if (!body.title || !body.starts_at || !body.state || !body.venue) {
        return res.status(400).json({ ok: false, error: 'validation', message: 'Missing required fields (title, starts_at, state, venue).' });
      }

      // Parse ticket types if present
      let ticketTypes = null;
      if (body.ticket_types) {
        try {
          ticketTypes = typeof body.ticket_types === 'string'
            ? JSON.parse(body.ticket_types)
            : body.ticket_types;
        } catch (e) {
          // ignore or format if it's already an array
        }
      }

      if (Array.isArray(ticketTypes)) {
        ticketTypes = ticketTypes
          .filter(t => t && t.name)
          .map(t => ({
            name: String(t.name).trim(),
            price: t.price ? parseFloat(t.price) : 0
          }));
        if (ticketTypes.length === 0) {
          ticketTypes = null;
        }
      } else {
        ticketTypes = null;
      }

      let image_path = null;
      if (req.file) {
        // Cloudinary returns the full URL in req.file.path
        image_path = req.file.path;
      }

      const eventData = {
        title: body.title,
        description: body.description || null,
        must_know: body.must_know || null,
        venue: body.venue,
        state: body.state,
        starts_at: body.starts_at,
        ends_at: body.ends_at || null,
        price: body.price ? parseFloat(body.price) : 0,
        capacity: body.capacity ? parseInt(body.capacity, 10) : null,
        pass_fees_to_buyer: body.pass_fees_to_buyer === 'true' || body.pass_fees_to_buyer === true || body.pass_fees_to_buyer === '1',
        ticket_types: ticketTypes,
        image_path,
        custom_slug: body.custom_slug ? String(body.custom_slug).trim().toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/(^-|-$)/g, '') : null,
      };

      const eventId = await createEvent(req.auth.user.id, eventData);

      return res.status(201).json({
        ok: true,
        message: 'Event created successfully',
        eventId
      });
    } catch (error) {
      console.error('Create event error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to create event' });
    }
  });

  // GET /organizer/wallet - wallet data
  router.get('/wallet', async (req, res) => {
    try {
      if (!req.auth || !req.auth.isAuthenticated) {
        return res.status(401).json({ ok: false, error: 'unauthenticated', message: 'Authentication required.' });
      }

      // Get wallet balance from organizer stats
      const stats = await getDashboardData(req.auth.user.id);
      
      return res.json({
        ok: true,
        balance: stats.wallet_balance || 0,
        available_for_withdrawal: stats.wallet_balance || 0,
        withdrawals: []
      });
    } catch (error) {
      console.error('Wallet error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to fetch wallet data' });
    }
  });

  // POST /organizer/wallet/withdraw - withdrawal request
  router.post('/wallet/withdraw', async (req, res) => {
    try {
      if (!req.auth || !req.auth.isAuthenticated) {
        return res.status(401).json({ ok: false, error: 'unauthenticated', message: 'Authentication required.' });
      }

      const { amount, bank_name, account_number, account_name } = req.body;

      if (!amount || !bank_name || !account_number || !account_name) {
        return res.status(400).json({ ok: false, error: 'Missing required fields' });
      }

      // For now, just return success (implement actual withdrawal logic later)
      return res.json({
        ok: true,
        message: 'Withdrawal request submitted successfully'
      });
    } catch (error) {
      console.error('Withdrawal error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to process withdrawal' });
    }
  });

  // GET /organizer/settings - settings data
  router.get('/settings', async (req, res) => {
    try {
      if (!req.auth || !req.auth.isAuthenticated) {
        return res.status(401).json({ ok: false, error: 'unauthenticated', message: 'Authentication required.' });
      }

      const { findAuthUserById } = require('../models/authUserModel.cjs');
      const user = await findAuthUserById(req.auth.user.id);

      return res.json({
        ok: true,
        settings: {
          username: user.username || '',
          name: user.name || '',
          instagram_handle: user.instagram_handle || '',
          whatsapp_number: user.whatsapp_number || '',
          twitter_handle: user.twitter_handle || ''
        }
      });
    } catch (error) {
      console.error('Settings error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to fetch settings' });
    }
  });

  // PATCH /organizer/settings - update settings
  router.patch('/settings', async (req, res) => {
    try {
      if (!req.auth || !req.auth.isAuthenticated) {
        return res.status(401).json({ ok: false, error: 'unauthenticated', message: 'Authentication required.' });
      }

      const { instagram_handle, whatsapp_number, twitter_handle, name } = req.body;
      const userId = req.auth.user.id;

      await query(`
        UPDATE users 
        SET name = COALESCE(?, name),
            instagram_handle = ?, whatsapp_number = ?, twitter_handle = ?,
            updated_at = datetime('now')
        WHERE id = ?
      `, [name || null, instagram_handle || null, whatsapp_number || null, twitter_handle || null, userId]);

      const { findAuthUserById } = require('../models/authUserModel.cjs');
      const updated = await findAuthUserById(userId);

      return res.json({
        ok: true,
        message: 'Settings updated successfully',
        settings: {
          username: updated.username || '',
          name: updated.name || '',
          instagram_handle: updated.instagram_handle || '',
          whatsapp_number: updated.whatsapp_number || '',
          twitter_handle: updated.twitter_handle || ''
        }
      });
    } catch (error) {
      console.error('Update settings error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to update settings' });
    }
  });

  // POST /organizer/scanner/verify - verify ticket
  router.post('/scanner/verify', async (req, res) => {
    try {
      if (!req.auth || !req.auth.isAuthenticated) {
        return res.status(401).json({ ok: false, error: 'unauthenticated', message: 'Authentication required.' });
      }

      const { code } = req.body;

      if (!code) {
        return res.status(400).json({ ok: false, error: 'Ticket code is required' });
      }

      // Find order by ticket code
      const orderRows = await query(`
        SELECT o.*, e.title as event_title, e.user_id as organizer_id
        FROM orders o
        LEFT JOIN events e ON o.event_id = e.id
        WHERE o.reference = ? OR o.ticket_code = ?
        LIMIT 1
      `, [code, code]);

      if (!orderRows[0]) {
        return res.json({
          ok: false,
          message: 'Ticket not found'
        });
      }

      const order = orderRows[0];

      // Check if user owns the event
      if (order.organizer_id !== req.auth.user.id) {
        return res.status(403).json({ ok: false, error: 'You do not have permission to verify this ticket' });
      }

      // Check if already used
      if (order.status === 'used') {
        return res.json({
          ok: false,
          message: 'Ticket already used',
          ticket: {
            buyer_name: order.buyer_name,
            event_title: order.event_title,
            quantity: order.quantity,
            used_at: order.updated_at
          }
        });
      }

      // Mark as used
      await query(`
        UPDATE orders 
        SET status = 'used', updated_at = datetime('now')
        WHERE id = ?
      `, [order.id]);

      return res.json({
        ok: true,
        message: 'Ticket verified successfully',
        ticket: {
          buyer_name: order.buyer_name,
          event_title: order.event_title,
          quantity: order.quantity,
          amount: order.amount
        }
      });
    } catch (error) {
      console.error('Scanner verify error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to verify ticket' });
    }
  });

  // GET /organizer/events/:id - get event details
  router.get('/events/:id', async (req, res) => {
    try {
      if (!req.auth || !req.auth.isAuthenticated) {
        return res.status(401).json({ ok: false, error: 'unauthenticated', message: 'Authentication required.' });
      }

      const eventId = parseInt(req.params.id, 10);
      const event = await findEventById(eventId);

      if (!event) {
        return res.status(404).json({ ok: false, error: 'Event not found' });
      }

      // Check if user owns this event
      if (event.user_id !== req.auth.user.id) {
        return res.status(403).json({ ok: false, error: 'You do not have permission to view this event' });
      }

      // Get orders for this event
      const orderRows = await query(`
        SELECT o.*
        FROM orders o
        WHERE o.event_id = ? AND o.status = 'paid'
        ORDER BY o.created_at DESC
      `, [eventId]);

      const orders = orderRows.map(order => ({
        ...order,
        buyer_name: order.buyer_name || 'Unknown',
        buyer_email: order.buyer_email || 'Unknown'
      }));

      // Calculate stats
      const totalSold = orders.reduce((sum, order) => sum + (order.quantity || 0), 0);
      const totalRevenue = orders.reduce((sum, order) => sum + (order.amount || 0), 0);

      return res.json({
        ok: true,
        event: {
          ...event,
          image_url: event.image_path ? `/storage/${event.image_path}` : null
        },
        stats: {
          totalSold,
          totalRevenue
        },
        orders
      });
    } catch (error) {
      console.error('Event details error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to fetch event details' });
    }
  });

  // PATCH /organizer/events/:id - update event
  router.patch('/events/:id', async (req, res) => {
    try {
      if (!req.auth || !req.auth.isAuthenticated) {
        return res.status(401).json({ ok: false, error: 'unauthenticated', message: 'Authentication required.' });
      }

      const eventId = parseInt(req.params.id, 10);
      const event = await findEventById(eventId);

      if (!event) {
        return res.status(404).json({ ok: false, error: 'Event not found' });
      }

      // Check if user owns this event
      if (event.user_id !== req.auth.user.id) {
        return res.status(403).json({ ok: false, error: 'You do not have permission to update this event' });
      }

      const { title, description, must_know, venue, state, starts_at, ends_at, price, capacity, pass_fees_to_buyer } = req.body;

      await query(`
        UPDATE events 
        SET title = ?, description = ?, must_know = ?, venue = ?, state = ?,
            starts_at = ?, ends_at = ?, price = ?, capacity = ?, pass_fees_to_buyer = ?,
            updated_at = datetime('now')
        WHERE id = ?
      `, [
        title || event.title,
        description || event.description,
        must_know || event.must_know,
        venue || event.venue,
        state || event.state,
        starts_at || event.starts_at,
        ends_at || event.ends_at,
        price !== undefined ? price : event.price,
        capacity !== undefined ? capacity : event.capacity,
        pass_fees_to_buyer !== undefined ? (pass_fees_to_buyer ? 1 : 0) : event.pass_fees_to_buyer,
        eventId
      ]);

      return res.json({
        ok: true,
        message: 'Event updated successfully'
      });
    } catch (error) {
      console.error('Update event error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to update event' });
    }
  });

  // PATCH /organizer/events/:id/toggle-publish — organizer can publish or unpublish their own event
  router.patch('/events/:id/toggle-publish', async (req, res) => {
    try {
      if (!req.auth || !req.auth.isAuthenticated) {
        return res.status(401).json({ ok: false, error: 'unauthenticated', message: 'Authentication required.' });
      }

      const eventId = parseInt(req.params.id, 10);
      const event = await findEventById(eventId);

      if (!event) return res.status(404).json({ ok: false, error: 'Event not found' });
      if (event.user_id !== req.auth.user.id) {
        return res.status(403).json({ ok: false, error: 'You do not have permission to modify this event' });
      }

      const newStatus = event.is_published ? 0 : 1;
      await query(
        `UPDATE events SET is_published = ?, updated_at = datetime('now') WHERE id = ?`,
        [newStatus, eventId]
      );

      return res.json({
        ok: true,
        is_published: Boolean(newStatus),
        message: newStatus ? 'Event published.' : 'Event unpublished.'
      });
    } catch (error) {
      console.error('Toggle publish error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to toggle publish status' });
    }
  });

  return router;
}

module.exports = {
  createOrganizerRouter,
};
