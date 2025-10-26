# Go-live checklist

Use this list the same day you announce real events.

- App config
  - [ ] Set APP_ENV=production, APP_DEBUG=false, APP_URL=https://your-domain
  - [ ] FORCE_HTTPS=true and SSL works end-to-end (redirect http→https)
  - [ ] SESSION_SECURE_COOKIE=true; queue and scheduler enabled if used
  - [ ] Run: php artisan config:cache route:cache view:cache
- Payments
  - [ ] Paystack keys set (pub + secret) and live mode enabled in dashboard
  - [ ] Callback URL: https://YOUR_DOMAIN/paystack/callback
  - [ ] Webhook URL: https://YOUR_DOMAIN/paystack/webhook (events: charge.success)
- Email & deliverability
  - [ ] SMTP verified sender; SPF, DKIM, DMARC on your domain
  - [ ] Test ticket receipt email to a real mailbox
- SEO/analytics
  - [ ] Submit sitemap: https://YOUR_DOMAIN/sitemap.xml in Google & Bing
  - [ ] Ownership verified (meta tags set) and robots.txt accessible
  - [ ] GA4 purchase marked as conversion; test events visible in Realtime
- Security
  - [ ] Admin scanner works; camera allowed only on /admin/scanner
  - [ ] CSP allows required endpoints only; plan to remove 'unsafe-eval' later
  - [ ] Rate limits active on orders and comments; custom 404/500 pages render
- Ops
  - [ ] Uptime monitoring and alerting set (Render, Pingdom, UptimeRobot, etc.)
  - [ ] Daily backups for DB; log rotation (daily) confirmed

When all are checked, you can start posting real events.
