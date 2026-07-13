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

// Configure upload storage for gallery images using Cloudinary
const galleryStorage = new CloudinaryStorage({
  cloudinary: cloudinary,
  params: {
    folder: 'events/gallery',
    allowed_formats: ['jpg', 'jpeg', 'png', 'webp'],
  },
});

const galleryUpload = multer({ storage: galleryStorage });

// Configure upload storage for profile pictures using Cloudinary
const profilePicStorage = new CloudinaryStorage({
  cloudinary: cloudinary,
  params: {
    folder: 'profile-pictures',
    allowed_formats: ['jpg', 'jpeg', 'png', 'webp'],
    transformation: [
      { width: 200, height: 200, crop: 'fill' }
    ]
  },
});

const profilePicUpload = multer({ storage: profilePicStorage });

function createOrganizerRouter() {
  const router = express.Router();

  router.use(attachSessionUser);

  // GET /organizer/dashboard - organizer dashboard
  router.get('/dashboard', async (req, res) => {
    try {
      if (wantsJson(req)) {
        if (!req.auth || !req.auth.isAuthenticated) {
          console.log('Dashboard: Unauthenticated request');
          return res.status(401).json({ ok: false, error: 'unauthenticated', message: 'Authentication required.' });
        }
        console.log('Dashboard: Fetching data for user ID:', req.auth.user.id);
        const data = await getDashboardData(req.auth.user.id);
        console.log('Dashboard: Data returned:', JSON.stringify(data, null, 2));
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

      const userId = req.auth.user.id;
      const { getOrganizerStats } = require('../models/organizerModel.cjs');
      const stats = await getOrganizerStats(userId);
      const withdrawals = await query(`
        SELECT * FROM withdrawals
        WHERE user_id = ?
        ORDER BY created_at DESC
      `, [userId]);
      
      return res.json({
        ok: true,
        wallet: {
          balance: stats.wallet_balance || 0,
          available_for_withdrawal: stats.available_for_withdrawal || 0
        },
        withdrawals: withdrawals || []
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

      const userId = req.auth.user.id;
      let { amount, bank_name, account_number, account_name, bank_details } = req.body;

      if (bank_details && (!bank_name || !account_number || !account_name)) {
        const parts = bank_details.split(',').map(p => p.trim());
        account_name = parts[0] || '';
        account_number = parts[1] || '';
        bank_name = parts[2] || '';
      }

      if (!amount || !bank_name || !account_number || !account_name) {
        return res.status(400).json({ ok: false, error: 'Missing required fields' });
      }

      const amountNumber = parseFloat(amount);
      if (isNaN(amountNumber) || amountNumber < 100) {
        return res.status(400).json({ ok: false, error: 'validation', message: 'Amount must be at least ₦100' });
      }

      const { getOrganizerStats } = require('../models/organizerModel.cjs');
      const stats = await getOrganizerStats(userId);

      if (amountNumber > stats.available_for_withdrawal) {
        return res.status(400).json({ ok: false, error: 'insufficient_funds', message: 'Insufficient funds available for withdrawal' });
      }

      const now = new Date().toISOString();
      await query(`
        INSERT INTO withdrawals (user_id, amount, bank_name, account_number, account_name, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, 'pending', ?, ?)
      `, [userId, amountNumber, bank_name, account_number, account_name, now, now]);

      return res.json({
        ok: true,
        message: 'Withdrawal request submitted successfully'
      });
    } catch (error) {
      console.error('Withdrawal error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to process withdrawal' });
    }
  });

  // GET /organizer/bank/list - fetch NGN banks
  router.get('/bank/list', async (req, res) => {
    try {
      if (!req.auth || !req.auth.isAuthenticated) {
        return res.status(401).json({ ok: false, error: 'unauthenticated', message: 'Authentication required.' });
      }

      const response = await fetch('https://api.paystack.co/bank?currency=NGN', {
        headers: {
          Authorization: `Bearer ${process.env.PAYSTACK_SECRET_KEY || ''}`
        }
      });
      const data = await response.json();
      if (!data.status) {
        return res.status(400).json({ ok: false, message: data.message || 'Failed to fetch banks' });
      }

      // Return simplified list
      const banks = data.data.map(b => ({
        name: b.name,
        code: b.code
      }));
      return res.json({ ok: true, banks });
    } catch (error) {
      console.error('Fetch banks error:', error);
      return res.status(500).json({ ok: false, message: 'Failed to retrieve banks' });
    }
  });

  // GET /organizer/bank/resolve - resolve bank account name
  router.get('/bank/resolve', async (req, res) => {
    try {
      if (!req.auth || !req.auth.isAuthenticated) {
        return res.status(401).json({ ok: false, error: 'unauthenticated', message: 'Authentication required.' });
      }

      const { account_number, bank_code } = req.query;
      if (!account_number || !bank_code) {
        return res.status(400).json({ ok: false, message: 'Account number and bank code are required' });
      }

      const url = `https://api.paystack.co/bank/resolve?account_number=${account_number}&bank_code=${bank_code}`;
      const response = await fetch(url, {
        headers: {
          Authorization: `Bearer ${process.env.PAYSTACK_SECRET_KEY || ''}`
        }
      });
      const data = await response.json();
      if (!data.status) {
        return res.status(400).json({ ok: false, message: data.message || 'Could not resolve account name' });
      }

      return res.json({
        ok: true,
        account_name: data.data.account_name,
        account_number: data.data.account_number
      });
    } catch (error) {
      console.error('Resolve bank error:', error);
      return res.status(500).json({ ok: false, message: 'Failed to resolve account' });
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
          email: user.email || '',
          name: user.name || '',
          instagram_handle: user.instagram_handle || '',
          whatsapp_number: user.whatsapp_number || '',
          twitter_handle: user.twitter_handle || '',
          profile_picture: user.profile_picture || ''
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
          email: updated.email || '',
          name: updated.name || '',
          instagram_handle: updated.instagram_handle || '',
          whatsapp_number: updated.whatsapp_number || '',
          twitter_handle: updated.twitter_handle || '',
          profile_picture: updated.profile_picture || ''
        }
      });
    } catch (error) {
      console.error('Update settings error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to update settings' });
    }
  });

  // POST /organizer/settings/profile-picture - upload profile picture
  router.post('/settings/profile-picture', profilePicUpload.single('profile_picture'), async (req, res) => {
    try {
      if (!req.auth || !req.auth.isAuthenticated) {
        return res.status(401).json({ ok: false, error: 'unauthenticated', message: 'Authentication required.' });
      }

      if (!req.file) {
        return res.status(400).json({ ok: false, error: 'No file uploaded' });
      }

      const userId = req.auth.user.id;
      const profilePictureUrl = req.file.path;

      await query(`
        UPDATE users 
        SET profile_picture = ?, updated_at = datetime('now')
        WHERE id = ?
      `, [profilePictureUrl, userId]);

      return res.json({
        ok: true,
        profile_picture: profilePictureUrl,
        message: 'Profile picture updated successfully'
      });
    } catch (error) {
      console.error('Profile picture upload error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to upload profile picture' });
    }
  });

  // POST /organizer/scanner/verify - verify ticket
  router.post('/scanner/verify', async (req, res) => {
    try {
      console.log('Scanner verify request received');
      if (!req.auth || !req.auth.isAuthenticated) {
        console.log('Scanner verify: Unauthenticated request');
        return res.status(401).json({ ok: false, valid: false, error: 'unauthenticated', message: 'Authentication required.' });
      }

      const { code } = req.body;
      const trimmedCode = (code || '').trim();
      console.log('Scanner verify: Code received:', trimmedCode);

      if (!trimmedCode) {
        console.log('Scanner verify: No code provided');
        return res.status(400).json({ ok: false, valid: false, message: 'Ticket code is required' });
      }

      // Find order by ticket code or paystack reference (case-insensitive)
      console.log('Scanner verify: Querying for order with code:', trimmedCode);
      const orderRows = await query(`
        SELECT o.*, e.title as event_title, e.user_id as organizer_id
        FROM orders o
        LEFT JOIN events e ON o.event_id = e.id
        WHERE lower(o.paystack_reference) = lower(?) OR lower(o.ticket_code) = lower(?)
        LIMIT 1
      `, [trimmedCode, trimmedCode]);
      console.log('Scanner verify: Query result:', orderRows);

      if (!orderRows[0]) {
        console.log('Scanner verify: Order not found');
        return res.json({
          ok: false,
          valid: false,
          message: 'Ticket not found. Please check the code and try again.'
        });
      }

      const order = orderRows[0];
      console.log('Scanner verify: Order found, organizer_id:', order.organizer_id, 'user_id:', req.auth.user.id);

      // Check if user owns the event
      if (order.organizer_id !== req.auth.user.id) {
        console.log('Scanner verify: Permission denied');
        return res.status(403).json({ ok: false, valid: false, message: 'You do not have permission to verify this ticket' });
      }

      // Check if order is confirmed/paid (not pending)
      if (order.status !== 'confirmed' && order.status !== 'used' && order.status !== 'paid') {
        console.log('Scanner verify: Order not confirmed, status:', order.status);
        return res.json({
          ok: false,
          valid: false,
          message: `Ticket payment not confirmed (status: ${order.status}). Cannot check in.`
        });
      }

      // Build buyer + event objects for frontend
      const buyer = {
        name: order.buyer_name,
        email: order.buyer_email,
        phone: order.buyer_phone || null,
      };
      const event = {
        title: order.event_title,
        id: order.event_id,
      };

      // Check if already used
      if (order.status === 'used') {
        console.log('Scanner verify: Ticket already used');
        return res.json({
          ok: false,
          valid: false,
          already: true,
          message: 'Ticket already used',
          buyer,
          event,
          quantity: order.quantity,
          last_checkin_at: order.updated_at
        });
      }

      // Mark as used
      const now = new Date().toISOString();
      await query(`
        UPDATE orders
        SET status = 'used', updated_at = ?, last_checkin_at = ?
        WHERE id = ?
      `, [now, now, order.id]);

      console.log('Scanner verify: Ticket marked as used');
      return res.json({
        ok: true,
        valid: true,
        already: false,
        message: 'Ticket verified successfully',
        buyer,
        event,
        quantity: order.quantity,
        amount: order.amount,
        last_checkin_at: now
      });
    } catch (error) {
      console.error('Scanner verify error:', error);
      return res.status(500).json({ ok: false, valid: false, message: 'Server error while verifying ticket' });
    }
  });

  // DELETE /organizer/events/:id - soft delete event
  router.delete('/events/:id', async (req, res) => {
    try {
      console.log('Delete event: Request received');
      if (!req.auth || !req.auth.isAuthenticated) {
        console.log('Delete event: Unauthenticated request');
        return res.status(401).json({ ok: false, error: 'unauthenticated', message: 'Authentication required.' });
      }

      const eventId = parseInt(req.params.id);
      console.log('Delete event: Event ID:', eventId, 'User ID:', req.auth.user.id);
      if (!eventId) {
        return res.status(400).json({ ok: false, error: 'Invalid event ID' });
      }

      // Check if event belongs to user
      const event = await query('SELECT * FROM events WHERE id = ? AND user_id = ?', [eventId, req.auth.user.id]);
      console.log('Delete event: Event query result:', event);
      if (!event[0]) {
        console.log('Delete event: Event not found for user');
        return res.status(404).json({ ok: false, error: 'Event not found' });
      }

      // Try soft delete by setting deleted_at
      try {
        await query('UPDATE events SET deleted_at = datetime("now") WHERE id = ?', [eventId]);
        console.log('Delete event: Event soft deleted successfully');
      } catch (softDeleteError) {
        console.log('Delete event: Soft delete failed, trying hard delete:', softDeleteError.message);
        // Fallback to hard delete if deleted_at column doesn't exist
        await query('DELETE FROM events WHERE id = ?', [eventId]);
        console.log('Delete event: Event hard deleted successfully');
      }

      return res.json({ ok: true, message: 'Event deleted successfully' });
    } catch (error) {
      console.error('Delete event error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to delete event' });
    }
  });

  // GET /organizer/events/:id - get event details
  router.get('/events/:id', async (req, res) => {
    try {
      if (!req.auth || !req.auth.isAuthenticated) {
        console.log('Event details: Unauthenticated request');
        return res.status(401).json({ ok: false, error: 'unauthenticated', message: 'Authentication required.' });
      }

      const eventId = parseInt(req.params.id, 10);
      console.log('Event details: Fetching event ID:', eventId, 'for user ID:', req.auth.user.id);
      const event = await findEventById(eventId);
      console.log('Event details: Event found:', event ? 'yes' : 'no');

      if (!event) {
        console.log('Event details: Event not found in database');
        return res.status(404).json({ ok: false, error: 'Event not found' });
      }

      console.log('Event details: Event owner ID:', event.user_id, 'Request user ID:', req.auth.user.id);
      // Check if user owns this event
      if (event.user_id !== req.auth.user.id) {
        console.log('Event details: Permission denied - user does not own this event');
        return res.status(403).json({ ok: false, error: 'You do not have permission to view this event' });
      }

      // Get orders for this event (both paid and used)
      const orderRows = await query(`
        SELECT o.*
        FROM orders o
        WHERE o.event_id = ? AND (o.status = 'paid' OR o.status = 'used')
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
      const scannedCount = orders.filter(order => order.status === 'used').reduce((sum, order) => sum + (order.quantity || 0), 0);

      const responseData = {
        ok: true,
        event: {
          ...event,
          orders_count: totalSold,
          scanned_count: scannedCount,
          total_tickets: totalSold,
          image_url: event.image_path ? `/storage/${event.image_path}` : null
        },
        stats: {
          totalSold,
          totalRevenue
        },
        orders
      };
      console.log('Event details: Returning data:', JSON.stringify(responseData, null, 2));
      return res.json(responseData);
    } catch (error) {
      console.error('Event details error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to fetch event details' });
    }
  });

  // PATCH /organizer/events/:id - update event
  router.patch('/events/:id', upload.fields([
    { name: 'image', maxCount: 1 },
    { name: 'gallery', maxCount: 10 }
  ]), async (req, res) => {
    try {
      console.log('PATCH event: Request received for ID:', req.params.id);
      console.log('PATCH event: Auth status:', req.auth ? 'present' : 'missing', 'isAuthenticated:', req.auth?.isAuthenticated);
      
      if (!req.auth || !req.auth.isAuthenticated) {
        console.log('PATCH event: Unauthenticated request');
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

      // Handle uploaded files
      let imagePath = event.image_path;
      if (req.files && req.files.image && req.files.image[0]) {
        imagePath = req.files.image[0].path;
      }

      // Handle gallery uploads
      let galleryImages = [];
      if (event.gallery) {
        try {
          galleryImages = typeof event.gallery === 'string' ? JSON.parse(event.gallery) : event.gallery;
        } catch (e) {
          galleryImages = [];
        }
      }
      
      if (req.files && req.files.gallery && req.files.gallery.length > 0) {
        const newGalleryImages = req.files.gallery.map(file => file.path);
        galleryImages = [...galleryImages, ...newGalleryImages];
      }

      const { title, description, must_know, venue, state, starts_at, ends_at, price, capacity, pass_fees_to_buyer, custom_slug, use_custom_slug } = req.body;
      console.log('PATCH event: Update data received:', { title, custom_slug, use_custom_slug, must_know, gallery: galleryImages });

      await query(`
        UPDATE events 
        SET title = ?, description = ?, must_know = ?, venue = ?, state = ?,
            starts_at = ?, ends_at = ?, price = ?, capacity = ?, pass_fees_to_buyer = ?,
            slug = ?, use_custom_slug = ?, image_path = ?, gallery = ?, updated_at = datetime('now')
        WHERE id = ?
      `, [
        title && title.trim() ? title : event.title,
        description && description.trim() ? description : event.description,
        must_know && must_know.trim() ? must_know : event.must_know,
        venue && venue.trim() ? venue : event.venue,
        state && state.trim() ? state : event.state,
        starts_at && starts_at.trim() ? starts_at : event.starts_at,
        ends_at && ends_at.trim() ? ends_at : event.ends_at,
        price !== undefined && price !== '' ? price : event.price,
        capacity !== undefined && capacity !== '' ? capacity : event.capacity,
        pass_fees_to_buyer !== undefined ? (pass_fees_to_buyer ? 1 : 0) : event.pass_fees_to_buyer,
        custom_slug && custom_slug.trim() ? custom_slug : event.slug,
        use_custom_slug !== undefined ? (use_custom_slug ? 1 : 0) : event.use_custom_slug,
        imagePath,
        JSON.stringify(galleryImages),
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
