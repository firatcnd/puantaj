import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  headers: { Accept: 'application/json' },
})

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
