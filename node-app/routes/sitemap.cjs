const express = require('express');
const { listPublishedEvents } = require('../models/eventModel.cjs');
const { getPublicUrl } = require('../services/eventPublicService.cjs');

function createSitemapRouter() {
  const router = express.Router();

  // GET /sitemap.xml
  router.get('/sitemap.xml', async (req, res) => {
    try {
      const baseUrl = process.env.APP_URL || `https://${req.get('host')}`;
      const base = baseUrl.replace(/\/$/, '');
      const urls = [];

      // Static pages
      urls.push({
        loc: `${base}/`,
        lastmod: new Date().toISOString(),
        changefreq: 'daily',
        priority: '1.0',
      });
      urls.push({
        loc: `${base}/events`,
        lastmod: new Date().toISOString(),
        changefreq: 'daily',
        priority: '0.8',
      });
      urls.push({
        loc: `${base}/events/recent`,
        lastmod: new Date().toISOString(),
        changefreq: 'daily',
        priority: '0.6',
      });

      // Dynamic event pages (best-effort, limit to 1000)
      try {
        const events = await listPublishedEvents({ limit: 1000, offset: 0 });
        events.forEach((event) => {
          urls.push({
            loc: getPublicUrl(event),
            lastmod: event.updated_at || new Date().toISOString(),
            changefreq: 'weekly',
            priority: '0.8',
          });
        });
      } catch (error) {
        console.error('Error fetching events for sitemap:', error);
      }

      // Generate XML
      const xml = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${urls
  .map(
    (url) => `  <url>
    <loc>${url.loc}</loc>
    <lastmod>${url.lastmod}</lastmod>
    <changefreq>${url.changefreq}</changefreq>
    <priority>${url.priority}</priority>
  </url>`
  )
  .join('\n')}
</urlset>`;

      res.set('Content-Type', 'application/xml; charset=UTF-8');
      return res.send(xml);
    } catch (error) {
      console.error('Sitemap generation error:', error);
      
      // Fallback minimal sitemap (never 500)
      const baseUrl = process.env.APP_URL || `https://${req.get('host')}`;
      const base = baseUrl.replace(/\/$/, '');
      const fallback = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>${base}/</loc>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>
</urlset>`;
      
      res.set('Content-Type', 'application/xml; charset=UTF-8');
      return res.send(fallback);
    }
  });

  return router;
}

module.exports = {
  createSitemapRouter,
};
