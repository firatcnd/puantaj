import axios from 'axios'

const TOKEN_KEY = 'puantaj_token'

export const getToken = () => localStorage.getItem(TOKEN_KEY)
export const setToken = (token) => localStorage.setItem(TOKEN_KEY, token)
export const clearToken = () => localStorage.removeItem(TOKEN_KEY)

const api = axios.create({
  baseURL: '/api',
  headers: { Accept: 'application/json' },
})

api.interceptors.request.use((config) => {
  const token = getToken()
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Token geçersizse (401) oturumu temizleyip login'e döndür
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401 && getToken()) {
      clearToken()
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

/** Korumalı bir endpoint'ten dosya indirir (token'lı istek + tarayıcı kaydetme). */
export async function downloadFile(url, params, filename) {
  const response = await api.get(url, { params, responseType: 'blob' })
  const objectUrl = URL.createObjectURL(response.data)
  const link = document.createElement('a')
  link.href = objectUrl
  link.download = filename
  link.click()
  URL.revokeObjectURL(objectUrl)
}

/**
 * Laravel validasyon hatasını (422) tek bir okunabilir mesaja indirger.
 * Diğer hatalar için genel bir mesaj döner.
 */
export function extractErrorMessage(error) {
  const data = error.response?.data
  if (data?.errors) {
    return Object.values(data.errors).flat().join('\n')
  }
  return data?.message ?? 'Beklenmeyen bir hata oluştu. Lütfen tekrar deneyin.'
}

export default api
