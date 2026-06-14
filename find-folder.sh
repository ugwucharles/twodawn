#!/bin/bash

# Script to find your Laravel folder on cPanel
echo "🔍 Searching for your Laravel folder..."
echo ""

echo "📂 Listing home directory:"
ls -la ~/
echo ""

echo "📂 Listing public_html:"
ls -la ~/public_html/
echo ""

echo "🔍 Searching for folders with 'two' or 'dawn' in name:"
find ~/public_html -type d -iname "*two*" -o -iname "*dawn*" 2>/dev/null
echo ""

echo "📂 Current directory contents:"
ls -la
echo ""

echo "✅ Check the output above to find your folder path"
