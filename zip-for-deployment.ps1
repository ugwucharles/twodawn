# PowerShell script to zip necessary Laravel files for cPanel deployment
# Run this in your local project directory

Write-Host "Creating deployment zip file..." -ForegroundColor Cyan

# Files/folders to include
$include = @(
    "app",
    "bootstrap", 
    "config",
    "database",
    "public",
    "resources",
    "routes",
    "storage",
    "vendor",
    ".env.example",
    "artisan",
    "composer.json",
    "composer.lock",
    "package.json",
    "package-lock.json"
)

# Create temp directory with only necessary files
$tempDir = "temp-deploy"
if (Test-Path $tempDir) { Remove-Item $tempDir -Recurse -Force }
New-Item -ItemType Directory -Path $tempDir | Out-Null

foreach ($item in $include) {
    if (Test-Path $item) {
        Copy-Item -Path $item -Destination "$tempDir\" -Recurse -Force
        Write-Host "Copied $item" -ForegroundColor Green
    }
}

# Create zip
Compress-Archive -Path "$tempDir\*" -DestinationPath "2dawn-deployment.zip" -Force

# Cleanup
Remove-Item $tempDir -Recurse -Force

Write-Host "Created 2dawn-deployment.zip" -ForegroundColor Green
Write-Host "Upload this file to cPanel File Manager" -ForegroundColor Cyan
