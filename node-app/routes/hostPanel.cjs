const express = require('express');
const {
  getHostPanelData,
  getHostPeopleData,
  verifyTicketForHost,
} = require('../services/hostPanelService.cjs');
const { proxyRequest } = require('../services/proxyRequest.cjs');
const { isJsonRequest } = require('../lib/authHttp.cjs');

function createHostPanelRouter() {
  const router = express.Router();

  // GET /h/:token - host panel dashboard
  router.get('/:token', async (req, res) => {
    try {
      const token = req.params.token;
      
      if (isJsonRequest(req)) {
        const data = await getHostPanelData(token);
        return res.json({ ok: true, ...data });
      }
      // Proxy to Laravel for HTML
      return proxyRequest(req, res);
    } catch (error) {
      console.error('Host panel error:', error);
      if (isJsonRequest(req)) {
        return res.status(410).json({ ok: false, error: 'Expired or invalid link' });
      }
      return proxyRequest(req, res);
    }
  });

  // GET /h/:token/people - host panel people/checkins
  router.get('/:token/people', async (req, res) => {
    try {
      const token = req.params.token;
      const page = {
        limit: req.query.limit ? parseInt(req.query.limit, 10) : 25,
        offset: req.query.page ? (parseInt(req.query.page, 10) - 1) * 25 : 0,
      };
      
      if (isJsonRequest(req)) {
        const data = await getHostPeopleData(token, page);
        return res.json({ ok: true, ...data });
      }
      // Proxy to Laravel for HTML
      return proxyRequest(req, res);
    } catch (error) {
      console.error('Host people error:', error);
      if (isJsonRequest(req)) {
        return res.status(410).json({ ok: false, error: 'Expired or invalid link' });
      }
      return proxyRequest(req, res);
    }
  });

  // GET /h/:token/scan - host panel scan
  router.get('/:token/scan', async (req, res) => {
    try {
      const token = req.params.token;
      
      if (isJsonRequest(req)) {
        const data = await getHostPanelData(token);
        return res.json({ ok: true, ...data });
      }
      // Proxy to Laravel for HTML
      return proxyRequest(req, res);
    } catch (error) {
      console.error('Host scan error:', error);
      if (isJsonRequest(req)) {
        return res.status(410).json({ ok: false, error: 'Expired or invalid link' });
      }
      return proxyRequest(req, res);
    }
  });

  // POST /h/:token/verify - verify ticket
  router.post('/:token/verify', async (req, res) => {
    try {
      const token = req.params.token;
      const reference = req.body.text || req.body.reference;
      
      const result = await verifyTicketForHost(token, reference);
      return res.json(result);
    } catch (error) {
      console.error('Host verify error:', error);
      return res.status(500).json({ ok: false, error: 'Failed to verify ticket' });
    }
  });

  // CSV export routes - proxy to Laravel for now
  router.get('/:token/checkins.csv', (req, res) => {
    return proxyRequest(req, res);
  });

  router.get('/:token/sales.csv', (req, res) => {
    return proxyRequest(req, res);
  });

  router.get('/:token/sales_daily.csv', (req, res) => {
    return proxyRequest(req, res);
  });

  return router;
}

module.exports = {
  createHostPanelRouter,
};
