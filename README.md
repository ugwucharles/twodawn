# 2DAWN

2DAWN is a Node.js and React event platform for discovering events, buying tickets, and managing organizer workflows.

## Stack

- Frontend: React + Vite
- Backend: Node.js + Express
- Database: Turso/libsql or local SQLite
- Payments: Paystack
- Media: Cloudinary

## Project Structure

- frontend/ - React application
- node-app/ - Express API and server logic
- public/ - static assets
- database.sqlite - local SQLite database (development)

## Quick Start

1. Install dependencies
   ```bash
   npm install
   ```

2. Configure environment variables
   ```bash
   cp .env.example .env
   ```

3. Start the app
   ```bash
   npm run dev
   ```

4. Open the app
   - Frontend: http://localhost:5173
   - Backend: http://localhost:3001

## Scripts

- npm run dev - start frontend and backend together
- npm run node:dev - start backend only
- npm run frontend:dev - start frontend only
- npm run build - build the frontend

## Deployment

The app is deployed as a Node-based application. Configure your hosting environment with the same environment variables used locally.

## License

This project is licensed under the terms of the repository owner.
