const {
  findEventById,
  findEventBySlug,
  listPublishedEvents,
  listRecentEvents,
  listPublishedEventsFiltered,
  listTopSellingEvents,
  getEventCapacity,
} = require('../models/eventModel.cjs');

function getPublicUrl(event) {
  if (!event) return null;
  const baseUrl = process.env.APP_URL || 'https://twodawn.com.ng';
  if (event.use_custom_slug && event.slug) {
    return `${baseUrl}/event/${event.slug}`;
  }
  return `${baseUrl}/events/${event.id}`;
}

function getImageUrl(event) {
  if (!event || !event.image_path) return null;
  if (event.image_path.startsWith('http://') || event.image_path.startsWith('https://')) {
    return event.image_path;
  }
  let cleanPath = event.image_path;
  if (cleanPath.startsWith('/')) {
    cleanPath = cleanPath.slice(1);
  }
  if (cleanPath.startsWith('storage/')) {
    cleanPath = cleanPath.slice(8);
  }
  const appUrl = process.env.APP_URL || 'http://localhost:5173';
  return `${appUrl.replace(/\/$/, '')}/storage/${cleanPath}`;
}

function formatEventForApi(event) {
  if (!event) return null;
  return {
    id: event.id,
    title: event.title,
    venue: event.venue,
    starts_at: event.starts_at,
    ends_at: event.ends_at,
    price: event.price ? Number(event.price) : 0,
    url: getPublicUrl(event),
    image_url: getImageUrl(event),
    slug: event.slug,
    use_custom_slug: event.use_custom_slug,
    mood: event.mood,
    state: event.state,
    image_path: event.image_path,
    description: event.description,
    must_know: event.must_know,
    capacity: event.capacity,
    ticket_types: event.ticket_types,
    organizer_username: event.organizer_username || null,
    organizer_name: event.organizer_name || null,
    organizer_profile_picture: event.organizer_avatar || null,
  };
}

async function getEventsIndex(filters = {}, page = {}) {
  const events = await listPublishedEventsFiltered(filters, page);
  return events.map(formatEventForApi);
}

async function getRecentEvents(page = {}) {
  const events = await listRecentEvents(page);
  return events.map(formatEventForApi);
}

async function getEventById(eventId) {
  const event = await findEventById(eventId);
  if (!event || !event.is_published) {
    return null;
  }
  return formatEventForApi(event);
}

async function getEventBySlug(slug) {
  const event = await findEventBySlug(slug);
  if (!event || !event.is_published) {
    return null;
  }
  return formatEventForApi(event);
}

async function getEventRemaining(eventId) {
  const event = await findEventById(eventId);
  if (!event || !event.is_published) {
    return null;
  }
  return getEventCapacity(eventId);
}

async function getTopSellingEvents({ limit = 6, filters = {} } = {}) {
  const events = await listTopSellingEvents({ limit, filters });
  return events.map(formatEventForApi);
}

function generateIcsContent(event) {
  if (!event) return null;

  const title = (event.title || 'Event').replace(/[,;\\]/g, '\\$&');
  const desc = (event.description || '').replace(/[,;\\]/g, '\\$&');
  const loc = (event.venue || '').replace(/[,;\\]/g, '\\$&');
  
  const start = event.starts_at ? new Date(event.starts_at) : new Date();
  const end = event.ends_at ? new Date(event.ends_at) : new Date(start.getTime() + 2 * 60 * 60 * 1000);
  
  const dtStart = start.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
  const dtEnd = end.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
  
  const baseUrl = process.env.APP_URL || 'https://twodawn.com.ng';
  const url = getPublicUrl(event);
  const host = new URL(baseUrl).hostname;
  const uid = `event-${event.id}@${host}`;
  
  const alarmMin = 60;
  const alarmLines = alarmMin > 0 ? [
    'BEGIN:VALARM',
    'ACTION:DISPLAY',
    'DESCRIPTION:Reminder',
    `TRIGGER:-PT${alarmMin}M`,
    'END:VALARM',
  ] : [];
  
  const lines = [
    'BEGIN:VCALENDAR',
    'VERSION:2.0',
    'PRODID:-//2DAWN//Event//EN',
    'CALSCALE:GREGORIAN',
    'METHOD:PUBLISH',
    'BEGIN:VEVENT',
    `UID:${uid}`,
    `DTSTAMP:${new Date().toISOString().replace(/[-:]/g, '').split('.')[0]}Z`,
    `DTSTART:${dtStart}`,
    `DTEND:${dtEnd}`,
    `SUMMARY:${title}`,
    desc ? `DESCRIPTION:${desc}` : null,
    loc ? `LOCATION:${loc}` : null,
    `URL:${url}`,
    ...alarmLines,
    'END:VEVENT',
    'END:VCALENDAR',
  ];
  
  return lines.filter(l => l !== null).join('\r\n') + '\r\n';
}

module.exports = {
  getEventsIndex,
  getRecentEvents,
  getTopSellingEvents,
  getEventById,
  getEventBySlug,
  getEventRemaining,
  generateIcsContent,
  getPublicUrl,
  getImageUrl,
};
