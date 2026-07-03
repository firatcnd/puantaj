import { useCallback, useEffect, useState } from 'react'
import { Button, Form, Modal } from 'react-bootstrap'
import api, { extractErrorMessage } from '../api.js'
import ConfirmModal from '../components/ConfirmModal.jsx'
import ExportButtons from '../components/ExportButtons.jsx'
import PersonnelImportModal from '../components/PersonnelImportModal.jsx'
import Pagination from '../components/Pagination.jsx'
import { useToast } from '../context/ToastContext.jsx'
import { formatDate } from '../utils/format.js'

const EMPTY_FORM = {
  full_name: '',
  registration_no: '',
  department_id: '',
  position_id: '',
  hire_date: '',
  is_active: true,
}

const EMPTY_FILTERS = { department_id: '', position_id: '', is_active: '' }

export default function PersonnelPage() {
  const notify = useToast()

  const [rows, setRows] = useState([])
  const [meta, setMeta] = useState(null)
  const [page, setPage] = useState(1)
  const [search, setSearch] = useState('')
  const [filters, setFilters] = useState(EMPTY_FILTERS)
  const [departments, setDepartments] = useState([])
  const [positions, setPositions] = useState([])

  const [showForm, setShowForm] = useState(false)
  const [editing, setEditing] = useState(null) // null = yeni kayıt
  const [form, setForm] = useState(EMPTY_FORM)
  const [saving, setSaving] = useState(false)
  const [deleting, setDeleting] = useState(null)
  const [showImport, setShowImport] = useState(false)

  // Export ve listeleme için aktif filtre/arama parametreleri
  const activeParams = {
    search: search || undefined,
    department_id: filters.department_id || undefined,
    position_id: filters.position_id || undefined,
    is_active: filters.is_active !== '' ? filters.is_active : undefined,
  }

  const load = useCallback(() => {
    api
      .get('/personnel', {
        params: {
          page,
          search: search || undefined,
          department_id: filters.department_id || undefined,
          position_id: filters.position_id || undefined,
          is_active: filters.is_active !== '' ? filters.is_active : undefined,
        },
      })
      .then((response) => {
        setRows(response.data.data)
        setMeta(response.data.meta)
      })
      .catch((error) => notify(extractErrorMessage(error), 'danger'))
  }, [page, search, filters, notify])

  useEffect(() => {
    load()
  }, [load])

  useEffect(() => {
    Promise.all([api.get('/departments'), api.get('/positions')])
      .then(([deps, poss]) => {
        setDepartments(deps.data)
        setPositions(poss.data)
      })
      .catch((error) => notify(extractErrorMessage(error), 'danger'))
  }, [notify])

  const openCreate = () => {
    setEditing(null)
    setForm(EMPTY_FORM)
    setShowForm(true)
  }

  const openEdit = (row) => {
    setEditing(row)
    setForm({
      full_name: row.full_name,
      registration_no: row.registration_no,
      department_id: row.department_id,
      position_id: row.position_id,
      hire_date: row.hire_date,
      is_active: row.is_active,
    })
    setShowForm(true)
  }

  const handleSubmit = (event) => {
    event.preventDefault()
    setSaving(true)

    const request = editing
      ? api.put(`/personnel/${editing.id}`, form)
      : api.post('/personnel', form)

    request
      .then(() => {
        notify(editing ? 'Personel güncellendi.' : 'Personel eklendi.')
        setShowForm(false)
        load()
      })
      .catch((error) => notify(extractErrorMessage(error), 'danger'))
      .finally(() => setSaving(false))
  }

  const handleDelete = () => {
    api
      .delete(`/personnel/${deleting.id}`)
      .then(() => {
        notify('Personel silindi.')
        setDeleting(null)
        load()
      })
      .catch((error) => notify(extractErrorMessage(error), 'danger'))
  }

  const setField = (field) => (event) =>
    setForm((current) => ({
      ...current,
      [field]: field === 'is_active' ? event.target.checked : event.target.value,
      // Departman değişince önceki pozisyon geçersizleşir; seçim sıfırlanır
      ...(field === 'department_id' ? { position_id: '' } : {}),
    }))

  // Pozisyonlar seçili departmana göre daraltılır (örn. İK + Muavin engellenir)
  const departmentPositions = positions.filter(
    (pos) => pos.department_id === Number(form.department_id)
  )

  return (
    <>
      <div className="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h4 className="fw-bold mb-0">Personel Yönetimi</h4>
        <div className="d-flex gap-2">
          <Button variant="outline-primary" onClick={() => setShowImport(true)}>
            <i className="bi bi-upload me-1" /> Excel İçe Aktar
          </Button>
          <Button onClick={openCreate}>
            <i className="bi bi-plus-lg me-1" /> Yeni Personel
          </Button>
        </div>
      </div>

      <div className="card">
        <div className="card-body">
          <div className="row g-2 mb-3">
            <div className="col-12 col-md-4 col-xl-3">
              <Form.Control
                placeholder="Ad soyad veya sicil no ile ara…"
                value={search}
                onChange={(event) => {
                  setSearch(event.target.value)
                  setPage(1)
                }}
              />
            </div>
            <div className="col-6 col-md-3 col-xl-2">
              <Form.Select
                value={filters.department_id}
                onChange={(event) => {
                  setFilters((c) => ({ ...c, department_id: event.target.value }))
                  setPage(1)
                }}
              >
                <option value="">Tüm Departmanlar</option>
                {departments.map((dep) => (
                  <option key={dep.id} value={dep.id}>{dep.name}</option>
                ))}
              </Form.Select>
            </div>
            <div className="col-6 col-md-3 col-xl-2">
              <Form.Select
                value={filters.position_id}
                onChange={(event) => {
                  setFilters((c) => ({ ...c, position_id: event.target.value }))
                  setPage(1)
                }}
              >
                <option value="">Tüm Pozisyonlar</option>
                {positions.map((pos) => (
                  <option key={pos.id} value={pos.id}>{pos.name}</option>
                ))}
              </Form.Select>
            </div>
            <div className="col-6 col-md-2 col-xl-2">
              <Form.Select
                value={filters.is_active}
                onChange={(event) => {
                  setFilters((c) => ({ ...c, is_active: event.target.value }))
                  setPage(1)
                }}
              >
                <option value="">Tüm Durumlar</option>
                <option value="1">Aktif</option>
                <option value="0">Pasif</option>
              </Form.Select>
            </div>
            <div className="col-6 col-md-4 col-xl-2 d-flex gap-2">
              <Button
                variant="outline-secondary"
                onClick={() => {
                  setSearch('')
                  setFilters(EMPTY_FILTERS)
                  setPage(1)
                }}
              >
                Temizle
              </Button>
              <ExportButtons resource="personnel" params={activeParams} fileBase="personel-listesi" />
            </div>
          </div>

          <div className="table-responsive">
            <table className="table table-hover align-middle">
              <thead>
                <tr>
                  <th>Ad Soyad</th>
                  <th>Sicil No</th>
                  <th>Departman</th>
                  <th>Pozisyon</th>
                  <th>İşe Giriş</th>
                  <th>Durum</th>
                  <th className="text-end">İşlemler</th>
                </tr>
              </thead>
              <tbody>
                {rows.length === 0 && (
                  <tr>
                    <td colSpan={7} className="text-center text-muted py-4">
                      Kayıt bulunamadı.
                    </td>
                  </tr>
                )}
                {rows.map((row) => (
                  <tr key={row.id}>
                    <td className="fw-semibold">{row.full_name}</td>
                    <td>{row.registration_no}</td>
                    <td>{row.department}</td>
                    <td>
                      <span className="badge text-bg-primary">{row.position}</span>
                    </td>
                    <td>{formatDate(row.hire_date)}</td>
                    <td>
                      <span className={`badge ${row.is_active ? 'text-bg-success' : 'text-bg-secondary'}`}>
                        {row.is_active ? 'Aktif' : 'Pasif'}
                      </span>
                    </td>
                    <td className="text-end">
                      <Button size="sm" variant="outline-primary" className="me-1" onClick={() => openEdit(row)}>
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

      <Modal show={showForm} onHide={() => setShowForm(false)} centered>
        <Form onSubmit={handleSubmit}>
          <Modal.Header closeButton>
            <Modal.Title className="fs-5">
              {editing ? 'Personel Düzenle' : 'Yeni Personel'}
            </Modal.Title>
          </Modal.Header>
          <Modal.Body className="d-flex flex-column gap-3">
            <Form.Group>
              <Form.Label>Ad Soyad</Form.Label>
              <Form.Control required value={form.full_name} onChange={setField('full_name')} />
            </Form.Group>
            <Form.Group>
              <Form.Label>Sicil No</Form.Label>
              <Form.Control required value={form.registration_no} onChange={setField('registration_no')} />
            </Form.Group>
            <div className="row g-3">
              <Form.Group className="col-6">
                <Form.Label>Departman</Form.Label>
                <Form.Select required value={form.department_id} onChange={setField('department_id')}>
                  <option value="">Seçiniz…</option>
                  {departments.map((dep) => (
                    <option key={dep.id} value={dep.id}>{dep.name}</option>
                  ))}
                </Form.Select>
              </Form.Group>
              <Form.Group className="col-6">
                <Form.Label>Pozisyon</Form.Label>
                <Form.Select
                  required
                  value={form.position_id}
                  onChange={setField('position_id')}
                  disabled={!form.department_id}
                >
                  <option value="">
                    {form.department_id ? 'Seçiniz…' : 'Önce departman seçiniz'}
                  </option>
                  {departmentPositions.map((pos) => (
                    <option key={pos.id} value={pos.id}>{pos.name}</option>
                  ))}
                </Form.Select>
              </Form.Group>
            </div>
            <Form.Group>
              <Form.Label>İşe Giriş Tarihi</Form.Label>
              <Form.Control type="date" required value={form.hire_date} onChange={setField('hire_date')} />
            </Form.Group>
            <Form.Check
              type="switch"
              id="personnel-active"
              label="Aktif"
              checked={form.is_active}
              onChange={setField('is_active')}
            />
          </Modal.Body>
          <Modal.Footer>
            <Button variant="secondary" onClick={() => setShowForm(false)}>
              Vazgeç
            </Button>
            <Button type="submit" disabled={saving}>
              {saving ? 'Kaydediliyor…' : 'Kaydet'}
            </Button>
          </Modal.Footer>
        </Form>
      </Modal>

      <ConfirmModal
        show={Boolean(deleting)}
        title="Personeli Sil"
        message={`"${deleting?.full_name}" kaydını silmek istediğinize emin misiniz?`}
        onConfirm={handleDelete}
        onCancel={() => setDeleting(null)}
      />

      <PersonnelImportModal
        show={showImport}
        onClose={() => setShowImport(false)}
        onImported={load}
      />
    </>
  )
}
