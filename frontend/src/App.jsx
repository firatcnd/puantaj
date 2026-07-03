import { Navigate, Route, Routes } from 'react-router-dom'
import Layout from './components/Layout.jsx'
import Dashboard from './pages/Dashboard.jsx'
import PersonnelPage from './pages/PersonnelPage.jsx'
import TripsPage from './pages/TripsPage.jsx'
import TimesheetsPage from './pages/TimesheetsPage.jsx'
import TimesheetFormPage from './pages/TimesheetFormPage.jsx'

export default function App() {
  return (
    <Layout>
      <Routes>
        <Route path="/" element={<Dashboard />} />
        <Route path="/personel" element={<PersonnelPage />} />
        <Route path="/seferler" element={<TripsPage />} />
        <Route path="/puantajlar" element={<TimesheetsPage />} />
        <Route path="/puantajlar/yeni" element={<TimesheetFormPage />} />
        <Route path="/puantajlar/:id/duzenle" element={<TimesheetFormPage />} />
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </Layout>
  )
}
