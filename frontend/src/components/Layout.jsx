import { NavLink } from 'react-router-dom'

const MENU = [
  { to: '/', label: 'Dashboard', icon: 'bi-speedometer2', exact: true },
  { to: '/personel', label: 'Personel', icon: 'bi-people' },
  { to: '/seferler', label: 'Seferler', icon: 'bi-signpost-split' },
  { to: '/puantajlar', label: 'Puantajlar', icon: 'bi-calendar3' },
]

export default function Layout({ children }) {
  return (
    <div className="d-flex app-shell">
      <aside className="app-sidebar p-3">
        <div className="brand fs-5 mb-4 d-flex align-items-center gap-2">
          <i className="bi bi-bus-front" />
          Puantaj Sistemi
        </div>
        <nav className="nav flex-column gap-1">
          {MENU.map((item) => (
            <NavLink
              key={item.to}
              to={item.to}
              end={item.exact}
              className="nav-link px-3 py-2"
            >
              <i className={`bi ${item.icon}`} />
              {item.label}
            </NavLink>
          ))}
        </nav>
      </aside>
      <main className="flex-grow-1 p-4" style={{ minWidth: 0 }}>
        {children}
      </main>
    </div>
  )
}
