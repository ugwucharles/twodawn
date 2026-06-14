#!/bin/bash

# cPanel Environment Diagnostic Script
# Run this in cPanel terminal to show me your environment

echo "🔍 cPanel Environment Diagnostic"
echo "================================"
echo ""

echo "📁 Current Directory:"
pwd
echo ""

echo "📂 Folder Contents:"
ls -la
echo ""

echo "🐧 PHP Version:"
php -v
echo ""

echo "📦 Composer Version:"
composer --version
echo ""

echo "📦 Node/NPM Version:"
node --version
npm --version
echo ""

echo "🔧 Available PHP Commands:"
which php
which composer
which node
which npm
echo ""

echo "🗄️  Database Info:"
echo "MySQL version:"
mysql --version 2>/dev/null || echo "MySQL command not found"
echo ""

echo "📁 Storage Permissions:"
ls -la storage/
echo ""

echo "📁 Bootstrap Permissions:"
ls -la bootstrap/cache/
echo ""

echo "🔧 Environment Variables:"
env | grep -E "(APP_|DB_|CACHE_|SESSION_)" || echo "No Laravel env vars found"
echo ""

echo "📁 .env File:"
if [ -f .env ]; then
    echo ".env file exists"
    head -5 .env
else
    echo ".env file not found"
fi
echo ""

echo "📁 Public Folder:"
ls -la public/
echo ""

echo "✅ Diagnostic complete"
