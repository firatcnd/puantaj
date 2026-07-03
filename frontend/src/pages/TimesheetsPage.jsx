import { useCallback, useEffect, useState } from 'react'
import { Button, Form } from 'react-bootstrap'
import { Link } from 'react-router-dom'
import api, { extractErrorMessage } from '../api.js'
import ConfirmModal from '../components/ConfirmModal.jsx'
import Pagination from '../components/Pagination.jsx'
import { useToast } from '../context/ToastContext.jsx'
import { formatCurrency, MONTHS, monthName } from '../utils/format.js'

const EMPTY_FILTERS = {
  search: '',
  personnel_id: '',
  department_id: '',
  position_id: '',
  year: '',
  month: '',
}

const YEARS = Array.from({ length: 6 }, (_, i) => new Date().getFullYear() - i)

export default function TimesheetsPage() {
  const notify = useToast()

  const [rows, setRows] = useState([])
  const [meta, setMeta] = useState(null)
  const [page, setPage] = useState(1)
  const [filters, setFilters] = useState(EMPTY_FILTERS)

  const [personnel, setPersonnel] = useState([])
  const [departments, setDepartments] = useState([])
  const [positions, setPositions] = useState([])
  const [deleting, setDeleting] = useState(null)

  const load = useCallback(() => {
    const params = { page }
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== '') params[key] = value
    })

    api
      .get('/timesheets', { params })
      .then((response) => {
        setRows(response.data.data)
        setMeta(response.data.meta)
      })
      .catch((error) => notify(extractErrorMessage(error), 'danger'))
  }, [page, filters, notify])

  useEffect(() => {
    load()
  }, [load])

  useEffect(() => {
    Promise.all([
      api.get('/personnel', { params: { per_page: 500 } }),
      api.get('/departments'),
      api.get('/positions'),
    ])
      .then(([people, deps, poss]) => {
        setPersonnel(people.data.data)
        setDepartments(deps.data)
        setPositions(poss.data)
      })
      .catch((error) => notify(extractErrorMessage(error), 'danger'))
  }, [notify])

  const setFilter = (field) => (event) => {
    setFilters((current) => ({ ...current, [field]: event.target.value }))
    setPage(1)
  }

  const handleDelete = () => {
    api
      .delete(`/timesheets/${deleting.id}`)
      .then(() => {
        notify('Puantaj silindi.')
        setDeleting(null)
        load()
      })
      .catch((error) => notify(extractErrorMessage(error), 'danger'))
  }

  return (
    <>
      <div className="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h4 className="fw-bold mb-0">Puantaj Yönetimi</h4>
        <Button as={Link} to="/puantajlar/yeni">
          <i className="bi bi-plus-lg me-1" /> Yeni Puantaj
        </Button>
      </div>

      <div className="card">
        <div className="card-body">
          <div className="row g-2 mb-3">
            <div className="col-12 col-md-4 col-xl-3">
              <Form.Control
                placeholder="Personel adı veya sicil no ile ara…"
                value={filters.search}
                onChange={setFilter('search')}
              />
            </div>
            <div className="col-6 col-md-4 col-xl-2">
              <Form.Select value={filters.personnel_id} onChange={setFilter('personnel_id')}>
                <option value="">Tüm Personeller</option>
                {personnel.map((person) => (
                  <option key={person.id} value={person.id}>{person.full_name}</option>
                ))}
              </Form.Select>
            </div>
            <div className="col-6 col-md-4 col-xl-2">
              <Form.Select value={filters.department_id} onChange={setFilter('department_id')}>
                <option value="">Tüm Departmanlar</option>
                {departments.map((dep) => (
                  <option key={dep.id} value={dep.id}>{dep.name}</option>
                ))}
              </Form.Select>
            </div>
            <div className="col-6 col-md-4 col-xl-2">
              <Form.Select value={filters.position_id} onChange={setFilter('position_id')}>
                <option value="">Tüm Pozisyonlar</option>
                {positions.map((pos) => (
                  <option key={pos.id} value={pos.id}>{pos.name}</option>
                ))}
              </Form.Select>
            </div>
            <div className="col-3 col-md-4 col-xl-1">
              <Form.Select value={filters.month} onChange={setFilter('month')}>
                <option value="">Ay</option>
                {MONTHS.map((name, index) => (
                  <option key={name} value={index + 1}>{name}</option>
                ))}
              </Form.Select>
            </div>
            <div className="col-3 col-md-4 col-xl-1">
              <Form.Select value={filters.year} onChange={setFilter('year')}>
                <option value="">Yıl</option>
                {YEARS.map((year) => (
                  <option key={year} value={year}>{year}</option>
                ))}
              </Form.Select>
            </div>
            <div className="col-6 col-md-4 col-xl-1 d-grid">
              <Button
                variant="outline-secondary"
                onClick={() => {
                  setFilters(EMPTY_FILTERS)
                  setPage(1)
                }}
              >
                Temizle
              </Button>
            </div>
          </div>

          <div className="table-responsive">
            <table className="table table-hover align-middle">
              <thead>
                <tr>
                  <th>Personel</th>
                  <th>Pozisyon</th>
                  <th>Dönem</th>
                  <th className="text-center">Çalışma Günü</th>
                  <th className="text-center">Toplam Sefer</th>
                  <th className="text-end">Toplam Mesai Tutarı</th>
                  <th>Oluşturulma</th>
                  <th className="text-end">İşlemler</th>
                </tr>
              </thead>
              <tbody>
                {rows.length === 0 && (
                  <tr>
                    <td colSpan={8} className="text-center text-muted py-4">
                      Kayıt bulunamadı.
                    </td>
                  </tr>
                )}
                {rows.map((row) => (
                  <tr key={row.id}>
                    <td>
                      <div className="fw-semibold">{row.personnel?.full_name}</div>
                      <small className="text-muted">{row.personnel?.registration_no}</small>
                    </td>
                    <td>
                      <span className="badge text-bg-primary">{row.position}</span>
                    </td>
                    <td>{monthName(row.month)} {row.year}</td>
                    <td className="text-center">{row.work_days}</td>
                    <td className="text-center">{row.entries_sum_trip_count ?? 0}</td>
                    <td className="text-end fw-bold">{formatCurrency(row.total_amount)}</td>
                    <td>
                      <small className="text-muted">{row.created_at}</small>
                    </td>
                    <td className="text-end">
                      <Button
                        as={Link}
                        to={`/puantajlar/${row.id}/duzenle`}
                        size="sm"
                        variant="outline-primary"
                        className="me-1"
                      >
                        <i className="bi bi-pencil" />
                      </Button>
                      <Button size="sm" variant="outline-danger" onClick={() => setDeleting(row)}>
                        <i className="bi bi-trash" />
                      </Button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <Pagination meta={meta} onChange={setPage} />
        </div>
      </div>

      <ConfirmModal
        show={Boolean(deleting)}
        title="Puantajı Sil"
        message={`"${deleting?.personnel?.full_name}" personelinin ${deleting ? monthName(deleting.month) : ''} ${deleting?.year} puantajını silmek istediğinize emin misiniz?`}
        onConfirm={handleDelete}
        onCancel={() => setDeleting(null)}
      />
    </>
  )
}
