import { NavLink, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext.jsx'
import { useTheme } from '../context/ThemeContext.jsx'

const MENU = [
  { to: '/', label: 'Dashboard', icon: 'bi-speedometer2', page: 'dashboard', exact: true },
  { to: '/personel', label: 'Personel', icon: 'bi-people', page: 'personel' },
  { to: '/seferler', label: 'Seferler', icon: 'bi-signpost-split', page: 'seferler' },
  { to: '/puantajlar', label: 'Puantajlar', icon: 'bi-calendar3', page: 'puantajlar' },
]

export default function Layout({ children }) {
  const { user, canAccess, logout } = useAuth()
  const { theme, toggleTheme } = useTheme()
  const navigate = useNavigate()

  const handleLogout = async () => {
    await logout()
    navigate('/login', { replace: true })
  }

  const items = MENU.filter((item) => canAccess(item.page))

  return (
    <div className="d-flex app-shell">
      <aside className="app-sidebar p-3 d-flex flex-column">
        <div className="brand fs-5 mb-4 d-flex align-items-center gap-2">
          <i className="bi bi-bus-front" />
          Puantaj Sistemi
        </div>
        <nav className="nav flex-column gap-1">
          {items.map((item) => (
            <NavLink key={item.to} to={item.to} end={item.exact} className="nav-link px-3 py-2">
              <i className={`bi ${item.icon}`} />
              {item.label}
            </NavLink>
          ))}
          {user?.is_admin && (
            <NavLink to="/yonetim" className="nav-link px-3 py-2">
              <i className="bi bi-shield-lock" />
              Yönetim
            </NavLink>
          )}
        </nav>
        <div className="mt-auto pt-3">
          <button
            className="btn btn-sm btn-outline-light border-0 w-100 mb-2 d-flex align-items-center justify-content-center gap-2"
            onClick={toggleTheme}
            title="Temayı değiştir"
          >
            <i className={`bi ${theme === 'dark' ? 'bi-sun' : 'bi-moon-stars'}`} />
            {theme === 'dark' ? 'Açık Tema' : 'Koyu Tema'}
          </button>
          <div className="user-box d-flex align-items-center justify-content-between gap-2">
            <div style={{ minWidth: 0 }}>
              <div className="text-white text-truncate small fw-semibold">{user?.name}</div>
              <div className="text-truncate" style={{ fontSize: '0.75rem', color: 'var(--stone)' }}>
                {user?.role}
              </div>
            </div>
            <button
              className="btn btn-sm btn-outline-light border-0"
              onClick={handleLogout}
              title="Çıkış Yap"
            >
              <i className="bi bi-box-arrow-right" />
            </button>
          </div>
        </div>
      </aside>
      <main className="flex-grow-1 p-4" style={{ minWidth: 0 }}>
        {children}
      </main>
    </div>
  )
}
