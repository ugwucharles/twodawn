const express = require('express');
const { listPublishedEventsFiltered } = require('../models/eventModel.cjs');
const { getPublicUrl, getImageUrl } = require('../services/eventPublicService.cjs');

function createApiRouter() {
  const router = express.Router();

  // GET /api/v1/events - read-only public API
  router.get('/events', async (req, res) => {
    try {
      const filters = {
        mood: req.query.mood,
        state: req.query.state,
        price: req.query.price,
        date: req.query.date,
        q: req.query.q,
      };
      
      const limit = Math.min(parseInt(req.query.limit || '50', 10), 100);
      const offset = 0;

      const events = await listPublishedEventsFiltered(filters, { limit, offset });

      const items = events.map((event) => ({
        id: event.id,
        title: event.title,
        venue: event.venue,
        starts_at: event.starts_at,
        ends_at: event.ends_at,
        price: event.price ? Number(event.price) : 0,
        url: getPublicUrl(event),
        image_url: getImageUrl(event),
        image_path: event.image_path,
        description: event.description,
        capacity: event.capacity,
      }));

      return res.json({ ok: true, events: items });
    } catch (error) {
      console.error('API events error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to fetch events' });
    }
  });

  // GET /api/v1/events/recent - read-only public API for recent events
  router.get('/events/recent', async (req, res) => {
    try {
      const { getRecentEvents } = require('../services/eventPublicService.cjs');
      const page = {
        limit: req.query.limit ? parseInt(req.query.limit, 10) : 12,
        offset: 0,
      };
      const events = await getRecentEvents(page);
      return res.json({ ok: true, events });
    } catch (error) {
      console.error('API recent events error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to fetch recent events' });
    }
  });

  // GET /api/v1/events/:id - read-only public API for a single event
  router.get('/events/:id', async (req, res) => {
    try {
      const eventId = parseInt(req.params.id, 10);
      if (isNaN(eventId)) {
        return res.status(400).json({ ok: false, error: 'Invalid event ID' });
      }

      // Check if we have a findEventById function exported
      const { findEventById } = require('../models/eventModel.cjs');
      const event = await findEventById(eventId);

      if (!event || !event.is_published) {
        return res.status(404).json({ ok: false, error: 'Event not found' });
      }

      const item = {
        id: event.id,
        title: event.title,
        venue: event.venue,
        state: event.state,
        starts_at: event.starts_at,
        ends_at: event.ends_at,
        price: event.price ? Number(event.price) : 0,
        url: getPublicUrl(event),
        image_url: getImageUrl(event),
        image_path: event.image_path,
        description: event.description,
        capacity: event.capacity,
        must_know: event.must_know,
        ticket_types: event.ticket_types,
      };

      return res.json({ ok: true, event: item });
    } catch (error) {
      console.error('API event error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to fetch event' });
    }
  });

  return router;
}

module.exports = {
  createApiRouter,
};
