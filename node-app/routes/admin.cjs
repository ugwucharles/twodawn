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

function createAdminRouter() {
  const router = express.Router();

  // GET /admin/dashboard - admin dashboard
  router.get('/dashboard', async (req, res) => {
    try {
      if (isJsonRequest(req)) {
        const data = await getDashboardData();
        return res.json({ ok: true, ...data });
      }
      // Proxy to Laravel for HTML
      return proxyRequest(req, res);
    } catch (error) {
      console.error('Admin dashboard error:', error);
      if (isJsonRequest(req)) {
        return res.status(500).json({ ok: false, error: 'Failed to fetch dashboard data' });
      }
      return proxyRequest(req, res);
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

  // All other admin routes proxy to Laravel for now
  router.all('*', (req, res) => {
    return proxyRequest(req, res);
  });

  return router;
}

module.exports = {
  createAdminRouter,
};
