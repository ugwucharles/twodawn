const {
  getAdminStats,
  getChartData,
  getUpcomingEvents,
  toggleEventPublish,
  getOrdersList,
  getOrderById,
  getHostTokens,
  createHostToken,
  logActivity,
  getActivityLogs,
  getAllEvents,
  getEventById,
  updateEvent,
  deleteEvent,
  getAllOrganizers,
  getOrganizerById,
  updateOrganizer,
  getAllUsers,
  getUserById,
  getAllTransactions,
  getSystemHealth,
} = require('../models/adminModel.cjs');

async function getDashboardData() {
  const stats = await getAdminStats();
  const chart = await getChartData(14);
  const upcoming = await getUpcomingEvents(6);
  const activity = await getActivityLogs(10);

  return {
    stats,
    chart,
    upcoming,
    activity,
  };
}

async function toggleEvent(eventId) {
  const event = await toggleEventPublish(eventId);
  if (!event) {
    throw new Error('Event not found');
  }
  await logActivity('toggle_event', 'event', eventId, { is_published: event.is_published });
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
  const token = await createHostToken(eventId, label);
  await logActivity('create_host_token', 'event', eventId, { label });
  return token;
}

async function getEvents(params = {}) {
  const { limit = 50, offset = 0, status } = params;
  return await getAllEvents(limit, offset, status);
}

async function getEventDetails(eventId) {
  return await getEventById(eventId);
}

async function updateEventDetails(eventId, updates) {
  const event = await updateEvent(eventId, updates);
  if (event) {
    await logActivity('update_event', 'event', eventId, updates);
  }
  return event;
}

async function removeEvent(eventId) {
  const success = await deleteEvent(eventId);
  if (success) {
    await logActivity('delete_event', 'event', eventId);
  }
  return success;
}

async function getOrganizers(params = {}) {
  const { limit = 50, offset = 0 } = params;
  return await getAllOrganizers(limit, offset);
}

async function getOrganizerDetails(organizerId) {
  return await getOrganizerById(organizerId);
}

async function updateOrganizerStatus(organizerId, updates) {
  const organizer = await updateOrganizer(organizerId, updates);
  if (organizer) {
    await logActivity('update_organizer', 'user', organizerId, updates);
  }
  return organizer;
}

async function getUsers(params = {}) {
  const { limit = 50, offset = 0, search } = params;
  return await getAllUsers(limit, offset, search);
}

async function getUserDetails(userId) {
  return await getUserById(userId);
}

async function getTransactions(params = {}) {
  const { limit = 50, offset = 0, status } = params;
  return await getAllTransactions(limit, offset, status);
}

async function getActivityFeed(params = {}) {
  const { limit = 50, offset = 0 } = params;
  return await getActivityLogs(limit, offset);
}

async function getHealthStatus() {
  return await getSystemHealth();
}

module.exports = {
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
};
