import { Navigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext.jsx'

/**
 * Oturum ve (verilmişse) sayfa izni kontrolü.
 * İzin olmayan sayfada kullanıcı, erişebildiği ilk sayfaya yönlendirilir.
 */
export default function RequireAuth({ page, adminOnly = false, children }) {
  const { user, loading, canAccess } = useAuth()

  if (loading) {
    return <div className="text-center py-5 text-muted">Yükleniyor…</div>
  }

  if (!user) {
    return <Navigate to="/login" replace />
  }

  const allowed = adminOnly ? user.is_admin : !page || canAccess(page)

  if (!allowed) {
    const fallback = user.is_admin ? '/' : `/${user.permissions[0] === 'dashboard' ? '' : user.permissions[0]}`
    return <Navigate to={fallback} replace />
  }

  return children
}
