const { createMigrationRouter } = require('./migration.cjs');
const { createSystemRouter } = require('./system.cjs');
const { createEventsRouter } = require('./events.cjs');
const { createSitemapRouter } = require('./sitemap.cjs');
const { createApiRouter } = require('./api.cjs');
const { createCheckoutRouter } = require('./checkout.cjs');
const { createAdminRouter } = require('./admin.cjs');
const { createAuthRouter, createPublicAuthRouter } = require('./auth.cjs');
const { createOrganizerRouter } = require('./organizer.cjs');
const { createHostPanelRouter } = require('./hostPanel.cjs');

function registerRoutes(app) {
  app.use(createSystemRouter());
  app.use(createEventsRouter());
  app.use(createSitemapRouter());
  app.use('/api/v1', createApiRouter());
  app.use(createCheckoutRouter());
  app.use('/admin', createAdminRouter());
  app.use(createPublicAuthRouter());
  app.use('/organizer', createOrganizerRouter());
  app.use('/h', createHostPanelRouter());
  app.use(createMigrationRouter());
}

module.exports = {
  registerRoutes,
};
