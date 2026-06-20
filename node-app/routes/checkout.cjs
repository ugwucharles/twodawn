const express = require('express');
const { findEventById } = require('../models/eventModel.cjs');
const { createOrder, findOrderByReference } = require('../models/orderModel.cjs');
const { countRecentFreeOrders, sumPaidQuantityForEvent } = require('../models/orderModel.cjs');
const {
  generateReference,
  calculateQuote,
  initializePaystackPayment,
  finalizePayment,
  finalizeZeroCostOrder,
} = require('../services/checkoutService.cjs');
const { proxyRequest } = require('../services/proxyRequest.cjs');
const { isJsonRequest } = require('../lib/authHttp.cjs');

function createCheckoutRouter() {
  const router = express.Router();

  // GET /events/:id/quote - pricing quote
  router.get('/events/:id/quote', async (req, res) => {
    try {
      const eventId = parseInt(req.params.id, 10);
      const event = await findEventById(eventId);

      if (!event || !event.is_published) {
        return res.status(404).json({ ok: false, message: 'Event not found' });
      }

      const data = req.query;
      const quantity = parseInt(data.quantity || '1', 10);
      const couponCode = data.coupon || null;
      const selectedTicketType = data.ticket_type || null;

      if (quantity < 1) {
        return res.status(400).json({ ok: false, message: 'Invalid quantity' });
      }

      const quote = calculateQuote(event, quantity, couponCode, selectedTicketType);
      return res.json({ ok: true, ...quote });
    } catch (error) {
      console.error('Quote error:', error);
      return res.status(500).json({ ok: false, message: 'Failed to compute quote' });
    }
  });

  // GET /events/:id/buy - buy page (proxied to Laravel for HTML)
  router.get('/events/:id/buy', async (req, res) => {
    try {
      const eventId = parseInt(req.params.id, 10);
      const event = await findEventById(eventId);

      if (!event || !event.is_published) {
        return proxyRequest(req, res);
      }

      // Check if event is past
      const now = new Date();
      const endsAt = event.ends_at ? new Date(event.ends_at) : null;
      const startsAt = event.starts_at ? new Date(event.starts_at) : null;
      const isPast = (endsAt && endsAt < now) || (!endsAt && startsAt && startsAt < now);

      if (isPast) {
        if (isJsonRequest(req)) {
          return res.status(410).json({ ok: false, message: 'Ticket sales closed for this event' });
        }
        return proxyRequest(req, res);
      }

      // Proxy to Laravel for HTML
      return proxyRequest(req, res);
    } catch (error) {
      console.error('Buy page error:', error);
      return proxyRequest(req, res);
    }
  });

  // POST /events/:id/orders - create order
  router.post('/events/:id/orders', async (req, res) => {
    try {
      const eventId = parseInt(req.params.id, 10);
      const event = await findEventById(eventId);

      if (!event || !event.is_published) {
        if (isJsonRequest(req)) {
          return res.status(404).json({ ok: false, message: 'Event not found' });
        }
        return proxyRequest(req, res);
      }

      // Check if event is past
      const now = new Date();
      const endsAt = event.ends_at ? new Date(event.ends_at) : null;
      const startsAt = event.starts_at ? new Date(event.starts_at) : null;
      const isPast = (endsAt && endsAt < now) || (!endsAt && startsAt && startsAt < now);

      if (isPast) {
        if (isJsonRequest(req)) {
          return res.status(410).json({ ok: false, message: 'Ticket sales closed for this event' });
        }
        return proxyRequest(req, res);
      }

      // Validate request
      const { buyer_name, buyer_email, buyer_phone, quantity, coupon, ticket_type } = req.body;
      
      if (!buyer_name || !buyer_email || !quantity) {
        if (isJsonRequest(req)) {
          return res.status(400).json({ ok: false, message: 'Missing required fields' });
        }
        return proxyRequest(req, res);
      }

      const orderQuantity = parseInt(quantity, 10);
      if (orderQuantity < 1) {
        if (isJsonRequest(req)) {
          return res.status(400).json({ ok: false, message: 'Invalid quantity' });
        }
        return proxyRequest(req, res);
      }

      // Prevent oversell
      if (event.capacity !== null && orderQuantity > event.capacity) {
        if (isJsonRequest(req)) {
          return res.status(400).json({ ok: false, message: `Only ${event.capacity} ticket(s) remaining` });
        }
        return proxyRequest(req, res);
      }

      // Calculate pricing
      const quote = calculateQuote(event, orderQuantity, coupon, ticket_type);
      
      // Check free ticket promo
      let isFreeTicketPromo = false;
      if (event.free_tickets_count > 0) {
        const ticketsSold = await sumPaidQuantityForEvent(eventId);
        if ((ticketsSold + orderQuantity) <= event.free_tickets_count) {
          isFreeTicketPromo = true;
        }
      }

      const finalAmount = isFreeTicketPromo ? 0 : quote.total_kobo;

      // For zero-cost orders, check rate limiting
      if (finalAmount <= 0) {
        const ip = req.ip || req.connection.remoteAddress;
        const recentFree = await countRecentFreeOrders(eventId, ip, 1);
        if (recentFree > 0) {
          if (isJsonRequest(req)) {
            return res.status(429).json({ ok: false, message: 'You recently claimed a free ticket. Please try again later.' });
          }
          return proxyRequest(req, res);
        }
      }

      // Create order
      const reference = generateReference();
      const order = await createOrder({
        event_id: eventId,
        ticket_type: ticket_type || null,
        buyer_name,
        buyer_email,
        buyer_phone: buyer_phone || null,
        coupon_code: coupon || null,
        quantity: orderQuantity,
        amount: finalAmount,
        paystack_reference: reference,
        created_ip: req.ip || req.connection.remoteAddress,
      });

      // If zero cost, finalize immediately
      if (finalAmount <= 0) {
        const result = await finalizeZeroCostOrder(order);
        if (!result.success) {
          if (isJsonRequest(req)) {
            return res.status(500).json({ ok: false, message: 'Failed to process free order' });
          }
          return proxyRequest(req, res);
        }
        
        if (isJsonRequest(req)) {
          return res.json({ ok: true, reference, status: 'paid' });
        }
        return res.redirect(`/orders/${reference}`);
      }

      // Initialize Paystack payment
      const callbackUrl = `${process.env.APP_URL || 'https://twodawn.com.ng'}/paystack/callback`;
      const authUrl = await initializePaystackPayment(order, callbackUrl);

      if (isJsonRequest(req)) {
        return res.json({ ok: true, reference, authorization_url: authUrl });
      }
      
      return res.redirect(authUrl);
    } catch (error) {
      console.error('Order creation error:', error);
      if (isJsonRequest(req)) {
        return res.status(500).json({ ok: false, message: 'Failed to create order' });
      }
      return proxyRequest(req, res);
    }
  });

  // GET /paystack/callback - payment callback
  router.get('/paystack/callback', async (req, res) => {
    try {
      const reference = req.query.reference;
      if (!reference) {
        return proxyRequest(req, res);
      }

      const result = await finalizePayment(reference);
      
      if (!result.success) {
        if (isJsonRequest(req)) {
          return res.status(400).json({ ok: false, message: result.error });
        }
        return proxyRequest(req, res);
      }

      // Redirect to order success page
      return res.redirect(`/orders/${reference}`);
    } catch (error) {
      console.error('Callback error:', error);
      return proxyRequest(req, res);
    }
  });

  // POST /paystack/webhook - Paystack webhook
  router.post('/paystack/webhook', async (req, res) => {
    try {
      const { event, data } = req.body;
      
      if (event === 'charge.success') {
        const reference = data.reference;
        await finalizePayment(reference);
      }
      
      return res.status(200).json({ ok: true });
    } catch (error) {
      console.error('Webhook error:', error);
      return res.status(500).json({ ok: false });
    }
  });

  // GET /orders/:reference - order lookup
  router.get('/orders/:reference', async (req, res) => {
    try {
      const reference = req.params.reference;
      const order = await findOrderByReference(reference);

      if (!order) {
        if (isJsonRequest(req)) {
          return res.status(404).json({ ok: false, message: 'Order not found' });
        }
        return proxyRequest(req, res);
      }

      if (isJsonRequest(req)) {
        return res.json({ ok: true, order });
      }

      // Proxy to Laravel for HTML
      return proxyRequest(req, res);
    } catch (error) {
      console.error('Order lookup error:', error);
      return proxyRequest(req, res);
    }
  });

  // GET /orders/:reference/download - PDF download (proxied to Laravel for now)
  router.get('/orders/:reference/download', async (req, res) => {
    // PDF generation requires dompdf and other dependencies
    // For now, proxy to Laravel to maintain functionality
    return proxyRequest(req, res);
  });

  return router;
}

module.exports = {
  createCheckoutRouter,
};
