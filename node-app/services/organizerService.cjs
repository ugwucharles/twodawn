const {
  getOrganizerStats,
  getOrganizerEvents,
  getOrganizerOrders,
} = require('../models/organizerModel.cjs');

async function getDashboardData(userId) {
  const stats = await getOrganizerStats(userId);
  const events = await getOrganizerEvents(userId);
  const recentOrders = await getOrganizerOrders(userId, { limit: 5, offset: 0 });

  // Map image_path to image_url for Cloudinary URLs
  const eventsWithImages = events.map(event => ({
    ...event,
    image_url: event.image_path || null
  }));

  return {
    stats,
    events: eventsWithImages,
    recent_orders: recentOrders,
  };
}

async function getEvents(userId) {
  const events = await getOrganizerEvents(userId);
  // Map image_path to image_url for Cloudinary URLs
  return events.map(event => ({
    ...event,
    image_url: event.image_path || null
  }));
}

async function getOrders(userId, page = {}) {
  return await getOrganizerOrders(userId, page);
}

module.exports = {
  getDashboardData,
  getEvents,
  getOrders,
};
