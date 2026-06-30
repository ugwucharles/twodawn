import axios from 'axios'
import { API_BASE_URL } from './httpConfig'

const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 15000,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor to handle errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status
    const isAdminRoute = window.location.pathname.startsWith('/ucc')
    if (status === 401 || (isAdminRoute && status === 403)) {
      // Clear token and redirect to login
      localStorage.removeItem('token')
      window.location.href = isAdminRoute ? '/ucc/login' : '/login'
    }
    return Promise.reject(error)
  }
)

export default api
