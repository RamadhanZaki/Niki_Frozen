import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true,
  timeout: 5000, // 5 detik timeout — kalau server mati langsung gagal
})

// Sertakan token Bearer di setiap request
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// JANGAN redirect di sini — biarkan router guard yang handle
// Cukup reject error agar catch di verifyToken() bisa menangkap
api.interceptors.response.use(
  (response) => response,
  (error) => Promise.reject(error)
)

export default api