import { Navigate, Route, Routes } from 'react-router-dom'
import Layout from './components/Layout.jsx'
import RequireAuth from './components/RequireAuth.jsx'
import Dashboard from './pages/Dashboard.jsx'
import LoginPage from './pages/LoginPage.jsx'
import ManagementPage from './pages/ManagementPage.jsx'
import PersonnelPage from './pages/PersonnelPage.jsx'
import TripsPage from './pages/TripsPage.jsx'
import TimesheetsPage from './pages/TimesheetsPage.jsx'
import TimesheetFormPage from './pages/TimesheetFormPage.jsx'

const guard = (page, element) => (
  <RequireAuth page={page}>
    <Layout>{element}</Layout>
  </RequireAuth>
)

export default function App() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route path="/" element={guard('dashboard', <Dashboard />)} />
      <Route path="/personel" element={guard('personel', <PersonnelPage />)} />
      <Route path="/seferler" element={guard('seferler', <TripsPage />)} />
      <Route path="/puantajlar" element={guard('puantajlar', <TimesheetsPage />)} />
      <Route path="/puantajlar/yeni" element={guard('puantajlar', <TimesheetFormPage />)} />
      <Route path="/puantajlar/:id/duzenle" element={guard('puantajlar', <TimesheetFormPage />)} />
      <Route
        path="/yonetim"
        element={
          <RequireAuth adminOnly>
            <Layout>
              <ManagementPage />
            </Layout>
          </RequireAuth>
        }
      />
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  )
}
