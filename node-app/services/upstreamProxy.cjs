const { proxyToLaravel } = require('./proxyRequest.cjs');

function createUpstreamProxy() {
  return function upstreamProxy(req, res) {
    proxyToLaravel(req, res);
  };
}

module.exports = {
  createUpstreamProxy,
};
