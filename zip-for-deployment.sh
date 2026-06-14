#!/bin/bash

# Script to zip necessary Laravel files for cPanel deployment
# Run this in your local project directory

echo "📦 Creating deployment zip file..."

# Create zip excluding unnecessary files
zip -r 2dawn-deployment.zip \
  app/ \
  bootstrap/ \
  config/ \
  database/ \
  public/ \
  resources/ \
  routes/ \
  storage/ \
  vendor/ \
  .env.example \
  artisan \
  composer.json \
  composer.lock \
  package.json \
  package-lock.json \
  -x "*.git*" \
  -x "node_modules/*" \
  -x ".DS_Store" \
  -x "*.log" \
  -x ".env" \
  -x "storage/logs/*" \
  -x "storage/framework/cache/*" \
  -x "storage/framework/sessions/*" \
  -x "storage/framework/views/*"

echo "✅ Created 2dawn-deployment.zip"
echo "📤 Upload this file to cPanel File Manager"
