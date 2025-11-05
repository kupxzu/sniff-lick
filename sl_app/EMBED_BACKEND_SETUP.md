# Embedding Laravel Backend in Electron - Setup Guide

## ✅ What We've Done So Far:

1. **Configured SQLite Database**
   - Updated `.env` to use SQLite
   - Created and migrated database
   - Seeded with admin users

2. **Updated Electron Main Process**
   - Added auto-start for Laravel server
   - Auto-stop on app close
   - Development vs Production mode handling

3. **Updated electron-builder Configuration**
   - Bundle Laravel files in `extraResources`
   - Include PHP binaries

## 📥 Required: Download Portable PHP

You need to download a portable PHP binary for Windows:

### Option 1: PHP 8.2+ (Recommended)

1. Visit: https://windows.php.net/download/
2. Download: **PHP 8.2+ (x64) Thread Safe ZIP**
3. Extract to: `d:\carl supan file\sniff-lick\sl_app\php-windows\`

### Option 2: Use Laragon PHP

If you have Laragon installed:
```powershell
# Copy your existing PHP installation
Copy-Item "C:\laragon\bin\php\php-8.2.12-Win32-vs16-x64" -Destination "d:\carl supan file\sniff-lick\sl_app\php-windows" -Recurse
```

## 📁 Directory Structure After Setup:

```
sniff-lick/
├── sl_app/
│   ├── php-windows/          # ← PHP portable binary goes here
│   │   ├── php.exe
│   │   ├── php.ini
│   │   ├── ext/              # Extensions
│   │   └── ...
│   ├── src/
│   ├── dist/
│   └── electron-builder.yml
├── app/                      # Laravel backend (will be bundled)
├── database/
│   └── database.sqlite      # SQLite database (will be bundled)
└── ...
```

## 🔧 Required PHP Extensions:

Make sure your `php.ini` has these enabled:
```ini
extension=pdo_sqlite
extension=sqlite3
extension=mbstring
extension=openssl
extension=curl
extension=fileinfo
```

## 🚀 Build Command:

Once PHP is in place, build with:
```powershell
cd "d:\carl supan file\sniff-lick\sl_app"
npm run build:win
```

## 📦 What Gets Bundled:

The final executable will include:
- ✅ Electron frontend (Vue + TypeScript)
- ✅ Laravel backend (PHP code)
- ✅ SQLite database (with seeded data)
- ✅ PHP runtime (portable binary)
- ✅ All dependencies

## 🎯 Result:

Single `.exe` file that users can run without any installation:
- No Apache needed
- No MySQL needed
- No PHP installation needed
- Everything runs from one folder

## ⚠️ Important Notes:

1. **Database Location**: `database.sqlite` will be bundled read-only. For production, you may want to copy it to a writable location on first run.

2. **Port Conflicts**: Laravel will try to use port 80. Make sure no other service is using it.

3. **File Size**: The final package will be ~150-200 MB due to PHP and vendor files.

## 🔄 Development vs Production:

- **Development**: Laravel runs separately (current setup at http://snifflick.api)
- **Production**: Electron automatically starts PHP server on port 80

The code automatically detects the environment and acts accordingly.
