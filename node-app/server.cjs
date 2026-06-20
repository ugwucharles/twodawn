const app = require('./app.cjs');

const port = Number(process.env.PORT || 3001);

app.listen(port, () => {
  console.log(`[node-app] Listening on http://localhost:${port}`);
});
