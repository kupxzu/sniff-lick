import { app, shell, BrowserWindow, ipcMain } from 'electron'
import { join } from 'path'
import { electronApp, optimizer, is } from '@electron-toolkit/utils'
import icon from '../../resources/icon.png?asset'
import { spawn, ChildProcess } from 'child_process'
import { existsSync } from 'fs'

let phpServerProcess: ChildProcess | null = null

// IPC handler to get isPackaged state
ipcMain.handle('get-is-packaged', () => {
  const isPackaged = app.isPackaged
  console.log('📦 App.isPackaged:', isPackaged)
  return isPackaged
})

function startLaravelServer(): Promise<void> {
  return new Promise((resolve) => {
    const isDev = is.dev
    
    if (isDev) {
      // In development, assume Laravel is running separately
      console.log('Development mode: Expecting Laravel server at http://snifflick.api')
      resolve()
      return
    }

    // In production, start embedded PHP server
    const phpPath = join(process.resourcesPath, 'backend', 'php', 'php.exe')
    const artisanPath = join(process.resourcesPath, 'backend', 'artisan')
    const backendPath = join(process.resourcesPath, 'backend')
    
    console.log('=== Starting Laravel Server ===')
    console.log('Resources Path:', process.resourcesPath)
    console.log('Backend Path:', backendPath)
    console.log('PHP Path:', phpPath)
    console.log('Artisan Path:', artisanPath)
    console.log('PHP exists:', existsSync(phpPath))
    console.log('Artisan exists:', existsSync(artisanPath))
    console.log('Backend exists:', existsSync(backendPath))
    
    if (!existsSync(phpPath)) {
      console.error('ERROR: PHP not found!')
      resolve()
      return
    }
    
    if (!existsSync(artisanPath)) {
      console.error('ERROR: Artisan not found!')
      resolve()
      return
    }
    
    phpServerProcess = spawn(phpPath, [artisanPath, 'serve', '--host=127.0.0.1', '--port=8000'], {
      cwd: backendPath,
      stdio: 'pipe'
    })

    phpServerProcess.stdout?.on('data', (data) => {
      console.log(`[Laravel STDOUT]: ${data}`)
      // Check if server is ready
      if (data.toString().includes('started')) {
        console.log('✅ Laravel server started successfully!')
        setTimeout(() => resolve(), 1000)
      }
    })

    phpServerProcess.stderr?.on('data', (data) => {
      console.error(`[Laravel STDERR]: ${data}`)
    })

    phpServerProcess.on('error', (error) => {
      console.error(`[Laravel ERROR]:`, error)
    })

    phpServerProcess.on('close', (code) => {
      console.log(`[Laravel] Server exited with code ${code}`)
    })
    
    // Fallback: resolve after 5 seconds
    setTimeout(() => {
      console.log('⏰ Timeout reached, proceeding anyway...')
      resolve()
    }, 5000)
  })
}

function stopLaravelServer(): void {
  if (phpServerProcess) {
    console.log('Stopping Laravel server...')
    phpServerProcess.kill()
    phpServerProcess = null
  }
}


function createWindow(): void {
  // Create the browser window.
  const mainWindow = new BrowserWindow({
    width: 1400,
    height: 900,
    minWidth: 1200,
    minHeight: 800,
    show: false,
    autoHideMenuBar: true,
    ...(process.platform === 'linux' ? { icon } : {}),
    webPreferences: {
      preload: join(__dirname, '../preload/index.js'),
      sandbox: false,
      webSecurity: false, // Disable web security for local API calls
      contextIsolation: true,
      nodeIntegration: false
    }
  })

  mainWindow.on('ready-to-show', () => {
    mainWindow.show()
  })

  mainWindow.webContents.setWindowOpenHandler((details) => {
    shell.openExternal(details.url)
    return { action: 'deny' }
  })

  // Enable DevTools with F12 or Ctrl+Shift+I in production
  mainWindow.webContents.on('before-input-event', (event, input) => {
    if (input.key === 'F12' || 
        (input.control && input.shift && input.key.toLowerCase() === 'i')) {
      mainWindow.webContents.toggleDevTools()
      event.preventDefault()
    }
  })

  // Also open DevTools automatically in production for debugging
  if (!is.dev) {
    console.log('🔧 Opening DevTools for production debugging')
    mainWindow.webContents.openDevTools()
  }

  // HMR for renderer base on electron-vite cli.
  // Load the remote URL for development or the local html file for production.
  if (is.dev && process.env['ELECTRON_RENDERER_URL']) {
    mainWindow.loadURL(process.env['ELECTRON_RENDERER_URL'])
  } else {
    mainWindow.loadFile(join(__dirname, '../renderer/index.html'))
  }
}

// This method will be called when Electron has finished
// initialization and is ready to create browser windows.
// Some APIs can only be used after this event occurs.
app.whenReady().then(async () => {
  // Start Laravel server first and wait for it to be ready
  await startLaravelServer()
  
  // Set app user model id for windows
  electronApp.setAppUserModelId('com.electron')

  // Default open or close DevTools by F12 in development
  // and ignore CommandOrControl + R in production.
  // see https://github.com/alex8088/electron-toolkit/tree/master/packages/utils
  app.on('browser-window-created', (_, window) => {
    optimizer.watchWindowShortcuts(window)
  })

  // IPC test
  ipcMain.on('ping', () => console.log('pong'))

  createWindow()

  app.on('activate', function () {
    // On macOS it's common to re-create a window in the app when the
    // dock icon is clicked and there are no other windows open.
    if (BrowserWindow.getAllWindows().length === 0) createWindow()
  })
})

// Quit when all windows are closed, except on macOS. There, it's common
// for applications and their menu bar to stay active until the user quits
// explicitly with Cmd + Q.
app.on('window-all-closed', () => {
  stopLaravelServer()
  if (process.platform !== 'darwin') {
    app.quit()
  }
})

// In this file you can include the rest of your app's specific main process
// code. You can also put them in separate files and require them here.
