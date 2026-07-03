import { createContext, useContext, useEffect, useState } from 'react'
import api, { clearToken, getToken, setToken } from '../api.js'

const AuthContext = createContext(null)

export function useAuth() {
  return useContext(AuthContext)
}

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(Boolean(getToken()))

  // Sayfa yenilendiğinde token varsa oturumu geri yükle
  useEffect(() => {
    if (!getToken()) return

    api
      .get('/me')
      .then((response) => setUser(response.data.user))
      .catch(() => clearToken())
      .finally(() => setLoading(false))
  }, [])

  const login = async (email, password) => {
    const response = await api.post('/login', { email, password })
    setToken(response.data.token)
    setUser(response.data.user)
  }

  const logout = async () => {
    try {
      await api.post('/logout')
    } finally {
      clearToken()
      setUser(null)
    }
  }

  const canAccess = (page) => Boolean(user?.permissions?.includes(page))

  return (
    <AuthContext.Provider value={{ user, loading, login, logout, canAccess }}>
      {children}
    </AuthContext.Provider>
  )
}
