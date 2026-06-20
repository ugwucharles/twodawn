/*
  Vercel Node Function entrypoint.
  Delegates to the real Express app structure in /node-app.
*/

const app = require('../node-app/app.cjs');

module.exports = function handler(req, res) {
  return app(req, res);
};
