import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1'

const api = axios.create({
  baseURL: API_URL,
  timeout: 15000,
})

let unauthorizedHandler = () => {
  localStorage.removeItem('roadlink_token')
  localStorage.removeItem('roadlink_user')
}

export const setUnauthorizedHandler = (handler) => {
  unauthorizedHandler = handler
}

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('roadlink_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  return config
})

api.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      await unauthorizedHandler(error)
    }

    return Promise.reject(error)
  },
)

export default api
