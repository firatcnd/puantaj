import { useCallback, useEffect, useState } from 'react'
import { Button, Form, Modal } from 'react-bootstrap'
import api, { extractErrorMessage } from '../api.js'
import ConfirmModal from '../components/ConfirmModal.jsx'
import Pagination from '../components/Pagination.jsx'
import { useToast } from '../context/ToastContext.jsx'
import { formatCurrency } from '../utils/format.js'

const EMPTY_FORM = {
  name: '',
  code: '',
  departure_point: '',
  arrival_point: '',
  is_active: true,
  rates: [{ position_id: '', rate: '' }],
}

export default function TripsPage() {
  const notify = useToast()

  const [rows, setRows] = useState([])
  const [meta, setMeta] = useState(null)
  const [page, setPage] = useState(1)
  const [search, setSearch] = useState('')
  const [positions, setPositions] = useState([])

  const [showForm, setShowForm] = useState(false)
  const [editing, setEditing] = useState(null)
  const [form, setForm] = useState(EMPTY_FORM)
  const [saving, setSaving] = useState(false)
  const [deleting, setDeleting] = useState(null)

  const load = useCallback(() => {
    api
      .get('/trips', { params: { page, search: search || undefined } })
      .then((response) => {
        setRows(response.data.data)
        setMeta(response.data.meta)
      })
      .catch((error) => notify(extractErrorMessage(error), 'danger'))
  }, [page, search, notify])

  useEffect(() => {
    load()
  }, [load])

  useEffect(() => {
    api
      .get('/positions')
      .then((response) => setPositions(response.data))
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
      name: row.name,
      code: row.code,
      departure_point: row.departure_point,
      arrival_point: row.arrival_point,
      is_active: row.is_active,
      rates: row.rates.map((rate) => ({ position_id: rate.position_id, rate: rate.rate })),
    })
    setShowForm(true)
  }

  const setField = (field) => (event) =>
    setForm((current) => ({
      ...current,
      [field]: field === 'is_active' ? event.target.checked : event.target.value,
    }))

  const setRateField = (index, field) => (event) =>
    setForm((current) => ({
      ...current,
      rates: current.rates.map((rate, i) =>
        i === index ? { ...rate, [field]: event.target.value } : rate
      ),
    }))

  const addRateRow = () =>
    setForm((current) => ({
      ...current,
      rates: [...current.rates, { position_id: '', rate: '' }],
    }))

  const removeRateRow = (index) =>
    setForm((current) => ({
      ...current,
      rates: current.rates.filter((_, i) => i !== index),
    }))

  // Zaten seçilmiş pozisyonları diğer satırların select'lerinde gizle
  const availablePositions = (index) =>
    positions.filter(
      (pos) =>
        !form.rates.some(
          (rate, i) => i !== index && Number(rate.position_id) === pos.id
        )
    )

  const handleSubmit = (event) => {
    event.preventDefault()
    setSaving(true)

    const request = editing
      ? api.put(`/trips/${editing.id}`, form)
      : api.post('/trips', form)

    request
      .then(() => {
        notify(editing ? 'Sefer güncellendi.' : 'Sefer eklendi.')
        setShowForm(false)
        load()
      })
      .catch((error) => notify(extractErrorMessage(error), 'danger'))
      .finally(() => setSaving(false))
  }

  const handleDelete = () => {
    api
      .delete(`/trips/${deleting.id}`)
      .then(() => {
        notify('Sefer silindi.')
        setDeleting(null)
        load()
      })
      .catch((error) => notify(extractErrorMessage(error), 'danger'))
  }

  return (
    <>
      <div className="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h4 className="fw-bold mb-0">Sefer Yönetimi</h4>
        <Button onClick={openCreate}>
          <i className="bi bi-plus-lg me-1" /> Yeni Sefer
        </Button>
      </div>

      <div className="card">
        <div className="card-body">
          <div className="row mb-3">
            <div className="col-12 col-md-5 col-lg-4">
              <Form.Control
                placeholder="Sefer adı, kodu veya güzergâh ile ara…"
                value={search}
                onChange={(event) => {
                  setSearch(event.target.value)
                  setPage(1)
                }}
              />
            </div>
          </div>

          <div className="table-responsive">
            <table className="table table-hover align-middle">
              <thead>
                <tr>
                  <th>Sefer Adı</th>
                  <th>Kod</th>
                  <th>Güzergâh</th>
                  <th>Mesai Ücretleri</th>
                  <th>Durum</th>
                  <th className="text-end">İşlemler</th>
                </tr>
              </thead>
              <tbody>
                {rows.length === 0 && (
                  <tr>
                    <td colSpan={6} className="text-center text-muted py-4">
                      Kayıt bulunamadı.
                    </td>
                  </tr>
                )}
                {rows.map((row) => (
                  <tr key={row.id}>
                    <td className="fw-semibold">{row.name}</td>
                    <td>
                      <span className="badge text-bg-warning">{row.code}</span>
                    </td>
                    <td>
                      {row.departure_point}
                      <i className="bi bi-arrow-right mx-1 text-muted" />
                      {row.arrival_point}
                    </td>
                    <td>
                      <div className="d-flex flex-wrap gap-1">
                        {row.rates.map((rate) => (
                          <span key={rate.id} className="badge text-bg-light border">
                            {rate.position}: {formatCurrency(rate.rate)}
                          </span>
                        ))}
                      </div>
                    </td>
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

      <Modal show={showForm} onHide={() => setShowForm(false)} centered size="lg">
        <Form onSubmit={handleSubmit}>
          <Modal.Header closeButton>
            <Modal.Title className="fs-5">{editing ? 'Sefer Düzenle' : 'Yeni Sefer'}</Modal.Title>
          </Modal.Header>
          <Modal.Body className="d-flex flex-column gap-3">
            <div className="row g-3">
              <Form.Group className="col-12 col-md-8">
                <Form.Label>Sefer Adı</Form.Label>
                <Form.Control required value={form.name} onChange={setField('name')} placeholder="İstanbul - Ankara" />
              </Form.Group>
              <Form.Group className="col-12 col-md-4">
                <Form.Label>Sefer Kodu</Form.Label>
                <Form.Control required value={form.code} onChange={setField('code')} placeholder="IST-ANK" />
              </Form.Group>
              <Form.Group className="col-6">
                <Form.Label>Kalkış Noktası</Form.Label>
                <Form.Control required value={form.departure_point} onChange={setField('departure_point')} />
              </Form.Group>
              <Form.Group className="col-6">
                <Form.Label>Varış Noktası</Form.Label>
                <Form.Control required value={form.arrival_point} onChange={setField('arrival_point')} />
              </Form.Group>
            </div>

            <hr className="my-1" />
            <div className="d-flex justify-content-between align-items-center">
              <h6 className="fw-bold mb-0">Pozisyona Göre Mesai Ücretleri</h6>
              <Button size="sm" variant="outline-primary" onClick={addRateRow} disabled={form.rates.length >= positions.length}>
                <i className="bi bi-plus-lg me-1" /> Ücret Ekle
              </Button>
            </div>

            {form.rates.map((rate, index) => (
              <div className="row g-2 align-items-center" key={index}>
                <div className="col-6">
                  <Form.Select
                    required
                    value={rate.position_id}
                    onChange={setRateField(index, 'position_id')}
                  >
                    <option value="">Pozisyon seçiniz…</option>
                    {availablePositions(index).map((pos) => (
                      <option key={pos.id} value={pos.id}>{pos.name}</option>
                    ))}
                  </Form.Select>
                </div>
                <div className="col-4">
                  <div className="input-group">
                    <Form.Control
                      type="number"
                      min="0"
                      step="0.01"
                      required
                      placeholder="Ücret"
                      value={rate.rate}
                      onChange={setRateField(index, 'rate')}
                    />
                    <span className="input-group-text">TL</span>
                  </div>
                </div>
                <div className="col-2 text-end">
                  <Button
                    size="sm"
                    variant="outline-danger"
                    onClick={() => removeRateRow(index)}
                    disabled={form.rates.length === 1}
                    title="En az bir pozisyona ücret tanımlanmalıdır"
                  >
                    <i className="bi bi-trash" />
                  </Button>
                </div>
              </div>
            ))}

            <Form.Check
              type="switch"
              id="trip-active"
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
        title="Seferi Sil"
        message={`"${deleting?.name}" seferini silmek istediğinize emin misiniz?`}
        onConfirm={handleDelete}
        onCancel={() => setDeleting(null)}
      />
    </>
  )
}
