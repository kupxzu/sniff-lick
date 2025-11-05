import api, { API_ENDPOINTS } from './api'

export interface LoginCredentials {
  login: string
  password: string
}

export interface RegisterData {
  name: string
  username: string
  email: string
  password: string
  password_confirmation: string
  role?: 'client' | 'admin'
}

export interface AuthResponse {
  success: boolean
  message: string
  user?: any
  token?: string
  errors?: any
}

export const authService = {
  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    const response = await api.post(API_ENDPOINTS.AUTH.LOGIN, credentials)
    if (response.data.success && response.data.token) {
      localStorage.setItem('auth_token', response.data.token)
      localStorage.setItem('user', JSON.stringify(response.data.user))
    }
    return response.data
  },

  async register(data: RegisterData): Promise<AuthResponse> {
    const response = await api.post(API_ENDPOINTS.AUTH.REGISTER, data)
    if (response.data.success && response.data.token) {
      localStorage.setItem('auth_token', response.data.token)
      localStorage.setItem('user', JSON.stringify(response.data.user))
    }
    return response.data
  },

  async logout(): Promise<void> {
    try {
      await api.post(API_ENDPOINTS.AUTH.LOGOUT)
    } finally {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
    }
  },

  async getUser(): Promise<any> {
    const response = await api.get(API_ENDPOINTS.AUTH.USER)
    return response.data.user
  },

  isAuthenticated(): boolean {
    return !!localStorage.getItem('auth_token')
  },

  getCurrentUser(): any {
    const user = localStorage.getItem('user')
    return user ? JSON.parse(user) : null
  }
}
