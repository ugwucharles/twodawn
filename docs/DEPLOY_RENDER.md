# Deploying to Render (Docker)

This document captures the setup we added today to deploy your Laravel app on Render, plus storage changes so uploads and QR codes persist without Render Disks.

Overview
- Containerized the app with Nginx + PHP-FPM using a multi‑stage Dockerfile (Composer deps + Vite build).
- Enabled PostgreSQL (pdo_pgsql) for Render Postgres.
- Switched uploads/QR codes from local disk to S3‑compatible storage (since Render Free has no Disks).
- Provided a step‑by‑step checklist and environment variables.

Files added/changed
- Dockerfile (root): multi‑stage build, Nginx + PHP‑FPM, pdo_mysql + pdo_pgsql.
- docker/nginx/default.conf.template: Nginx vhost bound to $PORT.
- docker/start.sh: renders Nginx config, caches config/routes/views, runs storage:link, migrations, then starts PHP‑FPM + Nginx.
- .dockerignore: smaller build context.
- config/filesystems.php:
  - default disk now ‘public’ (overridable via FILESYSTEM_DISK)
  - s3 disk visibility set to ‘public’ for public URLs
- composer.json: added league/flysystem-aws-s3-v3
- Controllers + Views:
  - Event flyers upload via storePublicly('events') on default disk
  - QR codes written with Storage::put(..., 'public')
  - Reading files uses Storage::exists/get/mimeType
  - Displaying images uses Storage::url(...)

Render setup (high level)
1) Create PostgreSQL in Render
   - New → PostgreSQL → pick region → wait for “Available”
   - Keep Internal Connection details (host, port 5432, db, user, password)

2) Option A — Build on Render (uses build minutes)
   - New → Web Service → select GitHub repo (branch: main)
   - Environment: Docker, Root Directory: /
   - Region: same as PostgreSQL
   - Add environment variables (below)

2) Option B — Deploy prebuilt image from GHCR (saves build minutes)
   - First run the GitHub Actions workflow (added at .github/workflows/docker-ghcr-render.yml) to publish ghcr.io/<owner>/2dawn:latest
   - In Render: New → Web Service → “Deploy an existing image”
     - Image URL: ghcr.io/<owner>/2dawn:latest
     - If private: add registry credentials (GitHub username + PAT with read:packages)
     - Enable Auto‑Deploy and (optional) create a Deploy Hook URL; add it as repo secret RENDER_DEPLOY_HOOK so the workflow triggers deploys automatically

Environment variables
Core
- APP_NAME=2DAWN
- APP_ENV=production
- APP_DEBUG=false
- APP_URL=https://YOUR-SERVICE.onrender.com
- LOG_CHANNEL=errorlog

Laravel runtime
- FILESYSTEM_DISK=s3  (for object storage; set to ‘public’ if testing locally)
- SESSION_DRIVER=file
- QUEUE_CONNECTION=sync

Database (PostgreSQL)
- DB_CONNECTION=pgsql
- DB_HOST=your_render_pg_internal_host
- DB_PORT=5432
- DB_DATABASE=your_db_name
- DB_USERNAME=your_db_user
- DB_PASSWORD=your_db_password

Paystack
- PAYSTACK_PUBLIC_KEY=your_pub_key
- PAYSTACK_SECRET_KEY=your_secret_key

App key
- APP_KEY=base64:XXXXXXXXXXXXXXXX
  - Generate locally:  & 'C:\\xampp\\php\\php.exe' artisan key:generate --show

Object storage options (S3 compatible)
Pick ONE provider and set the variables accordingly.

A) AWS S3
- FILESYSTEM_DISK=s3
- AWS_ACCESS_KEY_ID=...
- AWS_SECRET_ACCESS_KEY=...
- AWS_DEFAULT_REGION=us-east-1 (your region)
- AWS_BUCKET=your-bucket
- AWS_URL=https://your-bucket.s3.amazonaws.com   (or your CDN URL)
- AWS_ENDPOINT=  (leave empty)
- AWS_USE_PATH_STYLE_ENDPOINT=false
Bucket policy (public reads): allow s3:GetObject on arn:aws:s3:::your-bucket/*

B) Cloudflare R2
- FILESYSTEM_DISK=s3
- AWS_ACCESS_KEY_ID=...
- AWS_SECRET_ACCESS_KEY=...
- AWS_DEFAULT_REGION=auto
- AWS_BUCKET=your-bucket
- AWS_ENDPOINT=https://<account_id>.r2.cloudflarestorage.com
- AWS_URL=https://your-public-bucket-domain  (if configured)
- AWS_USE_PATH_STYLE_ENDPOINT=true

C) DigitalOcean Spaces
- FILESYSTEM_DISK=s3
- AWS_ACCESS_KEY_ID=...
- AWS_SECRET_ACCESS_KEY=...
- AWS_DEFAULT_REGION=nyc3 (or your region)
- AWS_BUCKET=your-bucket
- AWS_ENDPOINT=https://nyc3.digitaloceanspaces.com
- AWS_URL=https://your-bucket.nyc3.digitaloceanspaces.com
- AWS_USE_PATH_STYLE_ENDPOINT=false

Why S3 now?
- Render Free plan doesn’t include Disks. Using S3/R2/Spaces ensures flyers and QR codes persist across deploys and restarts.
- We changed code to use Storage::url() for image links and default disk APIs for reads/writes.

Paystack callback
- Set in your Paystack dashboard to:
  https://YOUR-SERVICE.onrender.com/paystack/callback
- Use test keys while verifying. The flow creates tickets, reduces capacity, and shows success page with tickets.

Admin
- Admin login route: /admin/login
- If you need a fresh admin in production, we can create a quick one-time route/command or seed safely.

Deploy flow summary
- Push to GitHub → Render builds Docker image → start.sh runs migrations → app serves on $PORT via Nginx
- With S3 configured, uploads and QR codes persist outside the container.

Troubleshooting
- 500 APP_KEY missing: set APP_KEY in Render env and redeploy
- 502/Bad Gateway: ensure Nginx listens on $PORT (template handles this) and app booted; check logs
- DB connection errors: verify DB_* from Render Postgres Internal Connection
- Missing CSS/JS: Docker builds Vite assets → verify public/build exists and APP_URL is correct
- Images not loading: check S3 env vars, bucket policy (public read), and that Storage::url(...) resolves to a public URL

Checklist for tomorrow
- [ ] Confirm Web Service env includes DB_* and APP_KEY
- [ ] Choose an object store (S3/R2/Spaces) and set FILESYSTEM_DISK=s3 + provider envs
- [ ] If using GHCR: set Render Image URL to ghcr.io/<owner>/2dawn:latest and (if private) add registry credentials
- [ ] Upload a flyer on an event → confirm it loads
- [ ] Do a test checkout with Paystack test card → confirm tickets + QR
- [ ] Set APP_URL to your final domain (Render or custom)
- [ ] Update Paystack callback to your final domain
- [ ] (Optional) Add custom domain on Render; queues/cron if needed

Notes
- start.sh still runs storage:link; harmless with S3 and helpful if you run local/public disk elsewhere.
- You can switch back to local/public disk for local dev by setting FILESYSTEM_DISK=public and keeping APP_URL=http://localhost.
