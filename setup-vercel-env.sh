#!/bin/bash

# Script to automatically add environment variables from .env to Vercel
# Usage: ./setup-vercel-env.sh

echo "🚀 Setting up Vercel environment variables..."

# Check if .env file exists
if [ ! -f .env ]; then
    echo "❌ Error: .env file not found!"
    exit 1
fi

# Check if Vercel CLI is installed
if ! command -v vercel &> /dev/null; then
    echo "❌ Error: Vercel CLI is not installed!"
    echo "Please install it first: npm i -g vercel"
    exit 1
fi

# Read .env file and set each variable
while IFS='=' read -r key value; do
    # Skip comments and empty lines
    [[ $key =~ ^#.*$ ]] && continue
    [[ -z $key ]] && continue
    
    # Remove quotes from value if present
    value=$(echo "$value" | sed 's/^["'\'']//' | sed 's/["'\'']$//')
    
    # Set environment variable in Vercel
    echo "📝 Setting $key..."
    vercel env add "$key" "$value" --yes
    
    if [ $? -eq 0 ]; then
        echo "✅ Successfully set $key"
    else
        echo "❌ Failed to set $key"
    fi
done < .env

echo "🎉 Environment variables setup complete!"
echo "Run 'vercel env ls' to verify all variables."
