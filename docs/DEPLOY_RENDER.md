# Deploying to Render

This project now runs as a Node.js application. The deployment flow below covers the current stack: React frontend, Express backend, and Turso or SQLite storage.

## Overview

- Frontend is built with Vite and served from the React app.
- Backend runs through the Node server in the node-app directory.
- Database configuration uses Turso in production or SQLite locally.
- Paystack is used for ticket checkout callbacks.

## Render Setup

1. Create a new Web Service on Render.
2. Connect the repository and choose the root directory.
3. Set the build and start commands:
   - Build: npm install && npm run build
   - Start: npm run node:dev
4. Add the required environment variables in Render.

## Required Environment Variables

- TURSO_DATABASE_URL
- TURSO_AUTH_TOKEN
- PAYSTACK_PUBLIC_KEY
- PAYSTACK_SECRET_KEY
- PAYSTACK_CALLBACK_URL
- NODE_ENV=production
- PORT=3001

## Notes

- If you are using local SQLite instead of Turso, make sure the database file is available in the deployed environment.
- For production, set PAYSTACK_CALLBACK_URL to your live domain.
- If you need the frontend served separately, configure the hosting service accordingly.

## Verification Checklist

- [ ] Build completes successfully
- [ ] Backend starts on the expected port
- [ ] Database connection is working
- [ ] Paystack callback URL is configured correctly
