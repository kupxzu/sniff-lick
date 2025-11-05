# How the Environment Detection Works

## 🔍 The Problem We Solved

**Development (`npm run dev`)** works fine:
- Frontend connects to `http://snifflick.api/api`
- Laravel runs separately on Apache

**Production (`npm run build:unpack`)** didn't work:
- Wrong environment detection
- Frontend tried to connect to wrong URL
- Backend wasn't being reached

## ✅ The Solution

### **1. Preload Script** (`src/preload/index.ts`)
Exposes environment information to the renderer:

```typescript
const api = {
  isPackaged: process.env.NODE_ENV === 'production'
}
```

### **2. API Service** (`src/renderer/src/services/api.ts`)
Uses this information to choose the correct URL:

```typescript
const getBaseURL = () => {
  const isPackaged = window.api?.isPackaged || false
  
  if (isPackaged) {
    return 'http://127.0.0.1:8000/api'  // Production: embedded server
  } else {
    return 'http://snifflick.api/api'   // Development: external server
  }
}
```

### **3. Main Process** (`src/main/index.ts`)
Starts Laravel server automatically in production:

```typescript
// Only in production builds
phpServerProcess = spawn(
  phpPath, 
  [artisanPath, 'serve', '--host=127.0.0.1', '--port=8000'],
  { cwd: backendPath }
)
```

## 🎯 How It Works Now

### **Development Mode** (`npm run dev`):
1. ✅ `isPackaged = false`
2. ✅ Frontend → `http://snifflick.api/api`
3. ✅ Backend runs separately (Apache/MAMP)
4. ✅ Hot reload enabled

### **Production Mode** (`npm run build:unpack`):
1. ✅ `isPackaged = true`
2. ✅ Frontend → `http://127.0.0.1:8000/api`
3. ✅ Backend auto-starts (embedded PHP)
4. ✅ Single executable

## 📝 Console Messages

Open DevTools (F12) to see:

**Development:**
```
🔧 DEVELOPMENT MODE: Using external server at http://snifflick.api/api
```

**Production:**
```
🚀 PRODUCTION MODE: Using embedded Laravel server at http://127.0.0.1:8000/api
=== Starting Laravel Server ===
Resources Path: D:\...\resources
Backend Path: D:\...\resources\backend
PHP Path: D:\...\resources\backend\php\php.exe
✅ Laravel server started successfully!
```

## 🔧 Files Modified

1. **src/preload/index.ts** - Added `isPackaged` flag
2. **src/preload/index.d.ts** - TypeScript definitions
3. **src/renderer/src/services/api.ts** - Smart URL detection
4. **src/main/index.ts** - Enhanced logging + error handling
5. **.env** - Set to production mode

## 🚀 Commands

```bash
# Development (uses http://snifflick.api)
npm run dev

# Build production (uses http://127.0.0.1:8000)
npm run build:unpack

# Run the built app
cd dist/win-unpacked
./SniffLick.exe
```

## ✨ Key Features

- ✅ **Auto-detection** of environment
- ✅ **No manual configuration** needed
- ✅ **Detailed logging** for debugging
- ✅ **Graceful fallbacks** if server fails
- ✅ **10-second timeout** for API requests
- ✅ **Auto-start/stop** of backend in production

---

**Now both development AND production work perfectly!** 🎉
