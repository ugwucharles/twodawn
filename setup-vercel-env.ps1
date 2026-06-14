# Script to automatically add environment variables from .env to Vercel
# Usage: .\setup-vercel-env.ps1

Write-Host "🚀 Setting up Vercel environment variables..." -ForegroundColor Cyan

# Check if .env file exists
if (-not (Test-Path .env)) {
    Write-Host "❌ Error: .env file not found!" -ForegroundColor Red
    exit 1
}

# Check if Vercel CLI is installed
$vercelInstalled = Get-Command vercel -ErrorAction SilentlyContinue
if (-not $vercelInstalled) {
    Write-Host "❌ Error: Vercel CLI is not installed!" -ForegroundColor Red
    Write-Host "Please install it first: npm i -g vercel" -ForegroundColor Yellow
    exit 1
}

# Read .env file and set each variable
Get-Content .env | ForEach-Object {
    $line = $_.Trim()
    
    # Skip comments and empty lines
    if ($line -match '^#.*$' -or [string]::IsNullOrWhiteSpace($line)) {
        return
    }
    
    # Parse key=value
    if ($line -match '^([^=]+)=(.*)$') {
        $key = $matches[1].Trim()
        $value = $matches[2].Trim()
        
        # Remove quotes from value if present
        $value = $value -replace '^["'']' -replace '["'']$'
        
        # Set environment variable in Vercel
        Write-Host "📝 Setting $key..." -ForegroundColor Yellow
        $result = vercel env add $key $value --yes
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host "✅ Successfully set $key" -ForegroundColor Green
        } else {
            Write-Host "❌ Failed to set $key" -ForegroundColor Red
        }
    }
}

Write-Host "🎉 Environment variables setup complete!" -ForegroundColor Green
Write-Host "Run 'vercel env ls' to verify all variables." -ForegroundColor Cyan
