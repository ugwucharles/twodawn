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
const { sendTicketEmail } = require('../services/emailService.cjs');
const { isJsonRequest } = require('../lib/authHttp.cjs');
const { ensureOrdersSchema } = require('../db/ensureOrdersSchema.cjs');
const { resolvePaystackCallbackUrl: resolvePaystackCallbackUrlFromRequest } = require('../lib/filtering.cjs');

function resolvePaystackCallbackUrl(req) {
  return resolvePaystackCallbackUrlFromRequest(req);
}

function resolveFrontendUrl() {
  return String(process.env.FRONTEND_URL || 'https://twodawn-frontend-real.vercel.app').trim();
}

function resolveOrderConfirmationUrl(reference) {
  return `${String(resolveFrontendUrl()).replace(/\/$/, '').trim()}/payment-success?reference=${encodeURIComponent(reference)}`;
}

function createCheckoutRouter() {
  const router = express.Router();

  router.use(express.json({ limit: '1mb' }));
  router.use(express.urlencoded({ extended: false }));

  // GET /events/:id/quote - pricing quote
  router.get('/events/:id/quote', async (req, res) => {
    try {
      await ensureOrdersSchema();

      const eventId = parseInt(req.params.id, 10);
      const event = await findEventById(eventId);

      if (!event || !event.is_published) {
        return res.status(404).json({ ok: false, message: 'Event not found' });
      }

      const data = req.query;
      const quantity = parseInt(data.quantity || '1', 10);
      const selectedTicketType = data.ticket_type || null;

      if (quantity < 1) {
        return res.status(400).json({ ok: false, message: 'Invalid quantity' });
      }

      const quote = calculateQuote(event, quantity, selectedTicketType);

      // Resolve ticket type display name + unit price
      let ticketTypeName = null;
      let unitPrice = 0;
      if (selectedTicketType && Array.isArray(event.ticket_types)) {
        const matched = event.ticket_types.find(
          (t) => t.name && String(t.name).toLowerCase() === String(selectedTicketType).toLowerCase()
        );
        if (matched) {
          ticketTypeName = matched.name;
          unitPrice = Number(matched.price) || 0;
        }
      } else {
        unitPrice = event.price ? Number(event.price) : 0;
      }

      return res.json({
        ok: true,
        subtotal: quote.subtotal_kobo / 100,
        fee: quote.fees_kobo / 100,
        discount: quote.discount_kobo / 100,
        total: quote.total_kobo / 100,
        unit_price: unitPrice,
        quantity,
        ticket_type: ticketTypeName,
      });
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
      await ensureOrdersSchema();

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
      const body = req.body || {};
      const buyer_name = body.buyer_name;
      const buyer_email = body.buyer_email;
      const buyer_phone = body.buyer_phone;
      const quantity = body.quantity;
      const ticket_type = body.ticket_type;
      const referral_source = body.referral_source;
      
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
      const quote = calculateQuote(event, orderQuantity, ticket_type);
      
      // Check free ticket promo
      let isFreeTicketPromo = false;
      if (event.free_tickets_count > 0) {
        const ticketsSold = await sumPaidQuantityForEvent(eventId);
        if ((ticketsSold + orderQuantity) <= event.free_tickets_count) {
          isFreeTicketPromo = true;
        }
      }

      const finalAmount = isFreeTicketPromo ? 0 : quote.total_kobo;

      // For zero-cost orders, check rate limiting (TEMPORARILY DISABLED FOR TESTING)
      // if (finalAmount <= 0) {
      //   const ip = req.ip || req.connection.remoteAddress;
      //   const recentFree = await countRecentFreeOrders(eventId, ip, 1);
      //   if (recentFree > 0) {
      //     if (isJsonRequest(req)) {
      //       return res.status(429).json({ ok: false, message: 'You recently claimed a free ticket. Please try again later.' });
      //     }
      //     return proxyRequest(req, res);
      //   }
      // }

      // Create order
      const reference = generateReference();
      const order = await createOrder({
        event_id: eventId,
        ticket_type: ticket_type || null,
        buyer_name,
        buyer_email,
        buyer_phone: buyer_phone || null,
        coupon_code: null,
        quantity: orderQuantity,
        amount: finalAmount,
        paystack_reference: reference,
        created_ip: req.ip || req.connection.remoteAddress,
        referral_source: referral_source || null,
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
      const callbackUrl = resolvePaystackCallbackUrl(req);
      const authUrl = await initializePaystackPayment(order, callbackUrl);

      if (isJsonRequest(req)) {
        return res.json({ ok: true, reference, authorization_url: authUrl });
      }
      
      return res.redirect(authUrl);
    } catch (error) {
      console.error('Order creation error:', error);
      if (isJsonRequest(req)) {
        return res.status(500).json({
          ok: false,
          message: error.message || 'Failed to create order',
        });
      }
      return proxyRequest(req, res);
    }
  });

  // GET /paystack/callback - payment callback
  router.get('/paystack/callback', async (req, res) => {
    try {
      const reference = req.query.reference;
      console.log('🔔 Paystack callback received:', reference);
      
      if (!reference) {
        console.log('❌ No reference in callback');
        return res.redirect(`${resolveFrontendUrl()}/events`);
      }

      const result = await finalizePayment(reference);
      console.log('💰 Payment finalization result:', result.success ? 'SUCCESS' : 'FAILED', result.error || '');
      
      if (!result.success) {
        if (isJsonRequest(req)) {
          return res.status(400).json({ ok: false, message: result.error });
        }
        console.log('🔴 Redirecting to events with payment=failed');
        return res.redirect(`${resolveFrontendUrl()}/events?payment=failed`);
      }

      const redirectUrl = resolveOrderConfirmationUrl(reference);
      console.log('✅ Redirecting to:', redirectUrl);
      return res.redirect(redirectUrl);
    } catch (error) {
      console.error('❌ Callback error:', error);
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

  // POST /test-email - Send test email (for testing email configuration)
  router.post('/test-email', async (req, res) => {
    try {
      const { email } = req.body;
      
      if (!email) {
        return res.status(400).json({ ok: false, message: 'Email address is required' });
      }

      const testOrder = {
        buyer_email: email,
        buyer_name: 'Test User',
        paystack_reference: 'TEST_' + Date.now(),
        quantity: 2,
        amount: 5000,
      };

      const testEvent = {
        title: 'Test Event - Email Verification',
        starts_at: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString(),
        venue: 'Test Venue, Lagos',
      };

      const result = await sendTicketEmail(testOrder, testEvent);
      
      if (result.success) {
        return res.json({ ok: true, message: 'Test email sent successfully', messageId: result.messageId });
      } else {
        return res.status(500).json({ ok: false, message: 'Failed to send test email', error: result.error });
      }
    } catch (error) {
      console.error('Test email error:', error);
      return res.status(500).json({ ok: false, message: 'Test email failed', error: error.message });
    }
  });

  return router;
}

module.exports = {
  createCheckoutRouter,
};
