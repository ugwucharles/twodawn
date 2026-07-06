const express = require('express');
const {
  getEventsIndex,
  getRecentEvents,
  getEventById,
  getEventBySlug,
  getEventRemaining,
  getTopSellingEvents,
  generateIcsContent,
} = require('../services/eventPublicService.cjs');
const { proxyRequest } = require('../services/proxyRequest.cjs');
const { wantsJson } = require('../lib/authHttp.cjs');

function createEventsRouter() {
  const router = express.Router();

  // GET /events - events index (proxied to Laravel for HTML, JSON for API)
  router.get('/events', async (req, res) => {
    try {
      const filters = {
        mood: req.query.mood,
        state: req.query.state,
        price: req.query.price,
        date: req.query.date,
        q: req.query.q,
      };
      const page = {
        limit: req.query.limit ? parseInt(req.query.limit, 10) : 12,
        offset: req.query.page ? (parseInt(req.query.page, 10) - 1) * (req.query.limit ? parseInt(req.query.limit, 10) : 12) : 0,
      };

      if (wantsJson(req)) {
        const events = await getEventsIndex(filters, page);
        return res.json({ ok: true, events });
      }

      // Proxy to Laravel for HTML
      return proxyRequest(req, res);
    } catch (error) {
      console.error('Events index error:', error);
      if (wantsJson(req)) {
        return res.status(500).json({ ok: false, error: 'Failed to fetch events' });
      }
      return proxyRequest(req, res);
    }
  });

  // GET /discover - alias for /events
  router.get('/discover', async (req, res) => {
    try {
      const filters = {
        mood: req.query.mood,
        state: req.query.state,
        price: req.query.price,
        date: req.query.date,
        q: req.query.q,
      };
      const page = {
        limit: req.query.limit ? parseInt(req.query.limit, 10) : 12,
        offset: req.query.page ? (parseInt(req.query.page, 10) - 1) * (req.query.limit ? parseInt(req.query.limit, 10) : 12) : 0,
      };

      if (wantsJson(req)) {
        const events = await getEventsIndex(filters, page);
        return res.json({ ok: true, events });
      }

      // Proxy to Laravel for HTML
      return proxyRequest(req, res);
    } catch (error) {
      console.error('Discover error:', error);
      if (wantsJson(req)) {
        return res.status(500).json({ ok: false, error: 'Failed to fetch events' });
      }
      return proxyRequest(req, res);
    }
  });

  // GET /events/recent - recent events
  router.get('/events/recent', async (req, res) => {
    try {
      const page = {
        limit: req.query.limit ? parseInt(req.query.limit, 10) : 12,
        offset: req.query.page ? (parseInt(req.query.page, 10) - 1) * (req.query.limit ? parseInt(req.query.limit, 10) : 12) : 0,
      };

      if (wantsJson(req)) {
        const events = await getRecentEvents(page);
        return res.json({ ok: true, events });
      }

      // Proxy to Laravel for HTML
      return proxyRequest(req, res);
    } catch (error) {
      console.error('Recent events error:', error);
      if (wantsJson(req)) {
        return res.status(500).json({ ok: false, error: 'Failed to fetch recent events' });
      }
      return proxyRequest(req, res);
    }
  });

  // GET /event/:slug - event by slug
  router.get('/event/:slug', async (req, res) => {
    try {
      const event = await getEventBySlug(req.params.slug);

      if (!event) {
        if (wantsJson(req)) {
          return res.status(404).json({ ok: false, error: 'Event not found' });
        }
        return proxyRequest(req, res);
      }

      if (wantsJson(req)) {
        return res.json({ ok: true, event });
      }

      // Proxy to Laravel for HTML
      return proxyRequest(req, res);
    } catch (error) {
      console.error('Event by slug error:', error);
      if (wantsJson(req)) {
        return res.status(500).json({ ok: false, error: 'Failed to fetch event' });
      }
      return proxyRequest(req, res);
    }
  });

  // GET /events/:id - event by ID
  router.get('/events/:id', async (req, res) => {
    try {
      const eventId = parseInt(req.params.id, 10);
      
      // Redirect event 11 to custom slug
      if (eventId === 11) {
        return res.redirect(301, '/event/afterdarkhouseparty');
      }
      
      const event = await getEventById(eventId);

      if (!event) {
        if (wantsJson(req)) {
          return res.status(404).json({ ok: false, error: 'Event not found' });
        }
        return proxyRequest(req, res);
      }

      if (wantsJson(req)) {
        return res.json({ ok: true, event });
      }

      // Proxy to Laravel for HTML
      return proxyRequest(req, res);
    } catch (error) {
      console.error('Event by ID error:', error);
      if (wantsJson(req)) {
        return res.status(500).json({ ok: false, error: 'Failed to fetch event' });
      }
      return proxyRequest(req, res);
    }
  });

  // GET /events/:id/remaining - remaining capacity
  router.get('/events/:id/remaining', async (req, res) => {
    try {
      const eventId = parseInt(req.params.id, 10);
      const remaining = await getEventRemaining(eventId);

      if (!remaining) {
        return res.status(404).json({ ok: false, error: 'Event not found' });
      }

      return res.json(remaining);
    } catch (error) {
      console.error('Event remaining error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to fetch remaining capacity' });
    }
  });

  // GET /events/:id/ics - ICS calendar file
  router.get('/events/:id/ics', async (req, res) => {
    try {
      const eventId = parseInt(req.params.id, 10);
      const event = await getEventById(eventId);

      if (!event) {
        return res.status(404).send('Event not found');
      }

      const icsContent = generateIcsContent(event);
      
      res.set({
        'Content-Type': 'text/calendar; charset=UTF-8',
        'Content-Disposition': `attachment; filename="event_${eventId}.ics"`,
      });
      
      return res.send(icsContent);
    } catch (error) {
      console.error('ICS generation error:', error);
      return res.status(500).send('Failed to generate ICS file');
    }
  });

  return router;
}

module.exports = {
  createEventsRouter,
};
