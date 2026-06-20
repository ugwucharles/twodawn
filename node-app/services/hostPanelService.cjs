const {
  validateHostToken,
  getHostStats,
  getHostCheckins,
  verifyTicket,
} = require('../models/hostPanelModel.cjs');

async function getHostPanelData(token) {
  const host = await validateHostToken(token);
  if (!host) {
    throw new Error('Expired or invalid link');
  }

  const stats = await getHostStats(host.event_id);

  return {
    host,
    event: {
      id: host.event_id,
      title: host.event_title,
    },
    stats,
  };
}

async function getHostPeopleData(token, page = {}) {
  const host = await validateHostToken(token);
  if (!host) {
    throw new Error('Expired or invalid link');
  }

  const stats = await getHostStats(host.event_id);
  const checkins = await getHostCheckins(host.event_id, page);

  return {
    host,
    event: {
      id: host.event_id,
      title: host.event_title,
    },
    stats,
    checkins,
  };
}

async function verifyTicketForHost(token, reference) {
  return await verifyTicket(token, reference);
}

module.exports = {
  getHostPanelData,
  getHostPeopleData,
  verifyTicketForHost,
};
