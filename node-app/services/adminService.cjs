const {
  getAdminStats,
  getChartData,
  getUpcomingEvents,
  toggleEventPublish,
  getOrdersList,
  getOrderById,
  getHostTokens,
  createHostToken,
} = require('../models/adminModel.cjs');

async function getDashboardData() {
  const stats = await getAdminStats();
  const chart = await getChartData(14);
  const upcoming = await getUpcomingEvents(6);

  return {
    stats,
    chart,
    upcoming,
  };
}

async function toggleEvent(eventId) {
  const event = await toggleEventPublish(eventId);
  if (!event) {
    throw new Error('Event not found');
  }
  return event;
}

async function getOrders(page = {}) {
  return await getOrdersList(page);
}

async function getOrderDetails(orderId) {
  return await getOrderById(orderId);
}

async function getEventHostTokens(eventId) {
  return await getHostTokens(eventId);
}

async function generateHostToken(eventId, label = null) {
  return await createHostToken(eventId, label);
}

module.exports = {
  getDashboardData,
  toggleEvent,
  getOrders,
  getOrderDetails,
  getEventHostTokens,
  generateHostToken,
};
