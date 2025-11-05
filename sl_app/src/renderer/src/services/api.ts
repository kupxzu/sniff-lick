import axios from 'axios'

// Detect environment and set API URL
const getBaseURL = () => {
  // Check if running in packaged Electron app
  const isPackaged = window.api?.isPackaged || false
  
  if (isPackaged) {
    console.log('🚀 PRODUCTION MODE: Using embedded Laravel server at http://127.0.0.1:8000/api')
    return 'http://127.0.0.1:8000/api'
  } else {
    console.log('🔧 DEVELOPMENT MODE: Using external server at http://snifflick.api/api')
    return 'http://snifflick.api/api'
  }
}

const baseURL = getBaseURL()

const api = axios.create({
  baseURL: baseURL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  timeout: 10000 // 10 second timeout
})

// Add token to requests if available
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Handle response errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token expired or invalid
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
    }
    return Promise.reject(error)
  }
)

// Centralized API endpoints
export const API_ENDPOINTS = {
  // Auth endpoints
  AUTH: {
    LOGIN: '/auth/login',
    REGISTER: '/auth/register',
    LOGOUT: '/auth/logout',
    USER: '/auth/user',
  },
  // User endpoints
  USER: {
    PROFILE: '/user/profile',
    UPDATE_PROFILE: '/user/profile',
    UPDATE_EMAIL: '/user/email',
    UPDATE_USERNAME: '/user/username',
    UPDATE_PASSWORD: '/user/password',
  },
  // Admin endpoints
  ADMIN: {
    DASHBOARD: '/admin/dashboard',
    CLIENTS: '/admin/clients',
    CLIENT: (clientId: number) => `/admin/clients/${clientId}`,
    CLIENT_PETS: (clientId: number) => `/admin/clients/${clientId}/pets`,
    CLIENT_PET: (clientId: number, petId: number) => `/admin/clients/${clientId}/pets/${petId}`,
    CLIENT_PET_CONSULTATIONS: (clientId: number, petId: number) => 
      `/admin/clients/${clientId}/pets/${petId}/consultations`,
    CLIENT_PET_CONSULTATION: (clientId: number, petId: number, consultationId: number) => 
      `/admin/clients/${clientId}/pets/${petId}/consultations/${consultationId}`,
    CLIENT_PET_CONSULTATION_TREATMENTS: (clientId: number, petId: number, consultationId: number) => 
      `/admin/clients/${clientId}/pets/${petId}/consultations/${consultationId}/treatments`,
    CLIENT_PET_CONSULTATION_TREATMENT: (clientId: number, petId: number, consultationId: number, treatmentId: number) => 
      `/admin/clients/${clientId}/pets/${petId}/consultations/${consultationId}/treatments/${treatmentId}`,
    CLIENT_PET_CONSULTATION_PRESCRIPTIONS: (clientId: number, petId: number, consultationId: number) => 
      `/admin/clients/${clientId}/pets/${petId}/consultations/${consultationId}/prescriptions`,
    CLIENT_PET_CONSULTATION_PRESCRIPTION: (clientId: number, petId: number, consultationId: number, prescriptionId: number) => 
      `/admin/clients/${clientId}/pets/${petId}/consultations/${consultationId}/prescriptions/${prescriptionId}`,
    CLIENT_PET_CONSULTATION_LABTESTS: (clientId: number, petId: number, consultationId: number) => 
      `/admin/clients/${clientId}/pets/${petId}/consultations/${consultationId}/labtests`,
    CLIENT_PET_CONSULTATION_LABTEST: (clientId: number, petId: number, consultationId: number, labtestId: number) => 
      `/admin/clients/${clientId}/pets/${petId}/consultations/${consultationId}/labtests/${labtestId}`,
  },
  // Health check
  HEALTH: '/health',
}

export default api
