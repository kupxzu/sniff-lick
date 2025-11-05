# Download and setup portable PHP for bundling with Electron
# Run this script in PowerShell as Administrator

$phpVersion = "8.2.26"
$phpUrl = "https://windows.php.net/downloads/releases/php-$phpVersion-Win32-vs16-x64.zip"
$downloadPath = "d:\carl supan file\sniff-lick\sl_app\php-download.zip"
$extractPath = "d:\carl supan file\sniff-lick\sl_app\php-windows"

Write-Host "📥 Downloading PHP $phpVersion..." -ForegroundColor Cyan

try {
    # Download PHP
    Invoke-WebRequest -Uri $phpUrl -OutFile $downloadPath -UseBasicParsing
    Write-Host "✅ Downloaded successfully!" -ForegroundColor Green
    
    # Extract
    Write-Host "📦 Extracting PHP..." -ForegroundColor Cyan
    Expand-Archive -Path $downloadPath -DestinationPath $extractPath -Force
    Write-Host "✅ Extracted successfully!" -ForegroundColor Green
    
    # Copy php.ini-production to php.ini
    $phpIniProd = Join-Path $extractPath "php.ini-production"
    $phpIni = Join-Path $extractPath "php.ini"
    Copy-Item $phpIniProd -Destination $phpIni
    Write-Host "✅ Created php.ini" -ForegroundColor Green
    
    # Enable required extensions
    Write-Host "🔧 Configuring PHP extensions..." -ForegroundColor Cyan
    $iniContent = Get-Content $phpIni
    $iniContent = $iniContent -replace ';extension=pdo_sqlite', 'extension=pdo_sqlite'
    $iniContent = $iniContent -replace ';extension=sqlite3', 'extension=sqlite3'
    $iniContent = $iniContent -replace ';extension=mbstring', 'extension=mbstring'
    $iniContent = $iniContent -replace ';extension=openssl', 'extension=openssl'
    $iniContent = $iniContent -replace ';extension=curl', 'extension=curl'
    $iniContent = $iniContent -replace ';extension=fileinfo', 'extension=fileinfo'
    $iniContent | Set-Content $phpIni
    Write-Host "✅ Extensions enabled!" -ForegroundColor Green
    
    # Cleanup
    Remove-Item $downloadPath
    Write-Host "Cleaned up download file" -ForegroundColor Green
    
    Write-Host ""
    Write-Host "PHP setup complete! You can now build the app with:" -ForegroundColor Green
    Write-Host "   cd 'd:\carl supan file\sniff-lick\sl_app'" -ForegroundColor Yellow
    Write-Host "   npm run build:win" -ForegroundColor Yellow
    
} catch {
    Write-Host "Error: $_" -ForegroundColor Red
    Write-Host ""
    Write-Host "Alternative: Copy PHP from Laragon installation" -ForegroundColor Yellow
}
