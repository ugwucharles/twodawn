const express = require('express');
const {
  getDashboardData,
  toggleEvent,
  getOrders,
  getOrderDetails,
  getEventHostTokens,
  generateHostToken,
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

  // All other admin routes proxy to Laravel for now
  router.all('*', (req, res) => {
    return proxyRequest(req, res);
  });

  return router;
}

module.exports = {
  createAdminRouter,
};
