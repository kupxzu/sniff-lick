# Quick Start Guide

## 🚀 Running the App

### **On Your Development PC:**
Just double-click: `SniffLick.exe`

### **On Another PC:**
1. Extract the ZIP file completely
2. Double-click `SniffLick.exe`
3. Wait 3-5 seconds for backend to start
4. Login with:
   - Email: `admin@snifflick.com`
   - Password: `password`

## ⚠️ Troubleshooting "Network Error"

### **If you see network error:**

1. **Check if port 8000 is available:**
   - Close any apps using port 8000
   - Or restart the computer

2. **Run as Administrator (if needed):**
   - Right-click `SniffLick.exe`
   - Select "Run as administrator"

3. **Check Windows Firewall:**
   - Allow SniffLick.exe through firewall if prompted

4. **Wait a bit longer:**
   - First startup takes 5-10 seconds
   - Backend needs time to initialize

### **To verify backend is running:**

1. Open the app
2. Press `F12` to open Developer Tools
3. Look at the Console tab
4. You should see: "Starting Laravel server..."

## 📝 Technical Details

- **Backend Port:** 8000 (changed from 80 to avoid admin requirement)
- **API URL:** http://127.0.0.1:8000/api
- **Database:** SQLite (embedded in resources/backend/database/)
- **PHP:** Embedded 8.3.1

## 🔧 If Still Not Working

The app needs to:
1. Find PHP at: `resources/backend/php/php.exe`
2. Find Laravel at: `resources/backend/artisan`
3. Start server on port 8000
4. Wait 3 seconds

Make sure you extracted the ENTIRE folder, not just the .exe!

---

**Latest Build:** Port changed to 8000 for better compatibility
