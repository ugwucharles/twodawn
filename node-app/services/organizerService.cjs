const {
  getOrganizerStats,
  getOrganizerEvents,
  getOrganizerOrders,
} = require('../models/organizerModel.cjs');

async function getDashboardData(userId) {
  const stats = await getOrganizerStats(userId);
  const events = await getOrganizerEvents(userId);
  const recentOrders = await getOrganizerOrders(userId, { limit: 5, offset: 0 });

  return {
    stats,
    events,
    recent_orders: recentOrders,
  };
}

async function getEvents(userId) {
  return await getOrganizerEvents(userId);
}

async function getOrders(userId, page = {}) {
  return await getOrganizerOrders(userId, page);
}

module.exports = {
  getDashboardData,
  getEvents,
  getOrders,
};
