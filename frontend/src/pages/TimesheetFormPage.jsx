import { useEffect, useMemo, useState } from 'react'
import { Alert, Button, Form } from 'react-bootstrap'
import { Link, useNavigate, useParams } from 'react-router-dom'
import api, { extractErrorMessage } from '../api.js'
import { useToast } from '../context/ToastContext.jsx'
import { formatCurrency, MONTHS } from '../utils/format.js'

const now = new Date()

const EMPTY_FORM = {
  personnel_id: '',
  year: now.getFullYear(),
  month: now.getMonth() + 1,
  work_days: 0,
  leave_days: 0,
  sick_days: 0,
  public_holiday_days: 0,
  weekend_days: 0,
  overtime_hours: 0,
  undertime_hours: 0,
  description: '',
}

const EMPTY_ENTRY = { trip_id: '', duty_date: '', trip_count: 1 }

const YEARS = Array.from({ length: 6 }, (_, i) => now.getFullYear() - i)

const DAY_FIELDS = [
  ['work_days', 'Çalışma Günü'],
  ['leave_days', 'İzin Günü'],
  ['sick_days', 'Rapor Günü'],
  ['public_holiday_days', 'Resmi Tatil'],
  ['weekend_days', 'Hafta Tatili'],
]

export default function TimesheetFormPage() {
  const { id } = useParams()
  const isEdit = Boolean(id)
  const navigate = useNavigate()
  const notify = useToast()

  const [form, setForm] = useState(EMPTY_FORM)
  const [entries, setEntries] = useState([{ ...EMPTY_ENTRY }])
  const [personnel, setPersonnel] = useState([])
  const [trips, setTrips] = useState([])
  const [saving, setSaving] = useState(false)
  const [loading, setLoading] = useState(isEdit)

  useEffect(() => {
    Promise.all([api.get('/lookup/personnel'), api.get('/lookup/trips')])
      .then(([people, tripList]) => {
        setPersonnel(people.data.filter((person) => person.is_active))
        setTrips(tripList.data.filter((trip) => trip.is_active))
      })
      .catch((error) => notify(extractErrorMessage(error), 'danger'))
  }, [notify])

  useEffect(() => {
    if (!isEdit) return

    api
      .get(`/timesheets/${id}`)
      .then((response) => {
        const data = response.data.data
        setForm({
          personnel_id: data.personnel_id,
          year: data.year,
          month: data.month,
          work_days: data.work_days,
          leave_days: data.leave_days,
          sick_days: data.sick_days,
          public_holiday_days: data.public_holiday_days,
          weekend_days: data.weekend_days,
          overtime_hours: data.overtime_hours,
          undertime_hours: data.undertime_hours,
          description: data.description ?? '',
        })
        setEntries(
          data.entries.length > 0
            ? data.entries.map((entry) => ({
                trip_id: entry.trip_id,
                duty_date: entry.duty_date,
                trip_count: entry.trip_count,
              }))
            : [{ ...EMPTY_ENTRY }]
        )
      })
      .catch((error) => notify(extractErrorMessage(error), 'danger'))
      .finally(() => setLoading(false))
  }, [id, isEdit, notify])

  const selectedPersonnel = useMemo(
    () => personnel.find((person) => person.id === Number(form.personnel_id)),
    [personnel, form.personnel_id]
  )

  /**
   * Birim ücret yalnızca GÖSTERİM içindir; kullanıcı değiştiremez ve
   * sunucuya gönderilmez. Nihai hesap her zaman backend'de yapılır.
   */
  const resolveRate = (tripId) => {
    if (!selectedPersonnel || !tripId) return null
    const trip = trips.find((t) => t.id === Number(tripId))
    const rate = trip?.rates.find((r) => r.position_id === selectedPersonnel.position_id)
    return rate?.rate ?? null
  }

  const grandTotal = entries.reduce((sum, entry) => {
    const rate = resolveRate(entry.trip_id)
    return rate === null ? sum : sum + rate * (Number(entry.trip_count) || 0)
  }, 0)

  const daysInMonth = new Date(form.year, form.month, 0).getDate()
  const totalDays = DAY_FIELDS.reduce((sum, [field]) => sum + (Number(form[field]) || 0), 0)

  const setField = (field) => (event) =>
    setForm((current) => ({ ...current, [field]: event.target.value }))

  const setEntryField = (index, field) => (event) =>
    setEntries((current) =>
      current.map((entry, i) =>
        i === index ? { ...entry, [field]: event.target.value } : entry
      )
    )

  const addEntry = () => setEntries((current) => [...current, { ...EMPTY_ENTRY }])

  const removeEntry = (index) =>
    setEntries((current) => current.filter((_, i) => i !== index))

  const handleSubmit = (event) => {
    event.preventDefault()
    setSaving(true)

    const payload = {
      ...form,
      description: form.description || null,
      entries: entries.filter((entry) => entry.trip_id !== ''),
    }

    const request = isEdit
      ? api.put(`/timesheets/${id}`, payload)
      : api.post('/timesheets', payload)

    request
      .then(() => {
        notify(isEdit ? 'Puantaj güncellendi.' : 'Puantaj oluşturuldu.')
        navigate('/puantajlar')
      })
      .catch((error) => notify(extractErrorMessage(error), 'danger'))
      .finally(() => setSaving(false))
  }

  if (loading) {
    return <div className="text-center py-5 text-muted">Yükleniyor…</div>
  }

  return (
    <>
      <div className="d-flex align-items-center gap-2 mb-4">
        <Button as={Link} to="/puantajlar" variant="outline-secondary" size="sm">
          <i className="bi bi-arrow-left" />
        </Button>
        <h4 className="fw-bold mb-0">{isEdit ? 'Puantaj Düzenle' : 'Yeni Puantaj'}</h4>
      </div>

      <Form onSubmit={handleSubmit}>
        <div className="card mb-3">
          <div className="card-body">
            <h6 className="fw-bold mb-3">Puantaj Bilgileri</h6>
            <div className="row g-3">
              <Form.Group className="col-12 col-md-6 col-xl-4">
                <Form.Label>Personel</Form.Label>
                <Form.Select required value={form.personnel_id} onChange={setField('personnel_id')}>
                  <option value="">Seçiniz…</option>
                  {personnel.map((person) => (
                    <option key={person.id} value={person.id}>
                      {person.full_name} ({person.position})
                    </option>
                  ))}
                </Form.Select>
                {selectedPersonnel && (
                  <Form.Text>
                    Pozisyon: <strong>{selectedPersonnel.position}</strong> — ücretler bu pozisyona göre otomatik belirlenir.
                  </Form.Text>
                )}
              </Form.Group>
              <Form.Group className="col-6 col-md-3 col-xl-2">
                <Form.Label>Ay</Form.Label>
                <Form.Select required value={form.month} onChange={setField('month')}>
                  {MONTHS.map((name, index) => (
                    <option key={name} value={index + 1}>{name}</option>
                  ))}
                </Form.Select>
              </Form.Group>
              <Form.Group className="col-6 col-md-3 col-xl-2">
                <Form.Label>Yıl</Form.Label>
                <Form.Select required value={form.year} onChange={setField('year')}>
                  {YEARS.map((year) => (
                    <option key={year} value={year}>{year}</option>
                  ))}
                </Form.Select>
              </Form.Group>
            </div>

            <div className="row g-3 mt-1">
              {DAY_FIELDS.map(([field, label]) => (
                <Form.Group className="col-6 col-md-4 col-xl-2" key={field}>
                  <Form.Label>{label}</Form.Label>
                  <Form.Control
                    type="number"
                    min="0"
                    max="31"
                    required
                    value={form[field]}
                    onChange={setField(field)}
                  />
                </Form.Group>
              ))}
              <div className="col-6 col-md-4 col-xl-2 d-flex align-items-end">
                <small className={totalDays > daysInMonth ? 'text-danger fw-bold' : 'text-muted'}>
                  Toplam gün: {totalDays} / {daysInMonth}
                </small>
              </div>
            </div>

            <div className="row g-3 mt-1">
              <Form.Group className="col-6 col-md-3 col-xl-2">
                <Form.Label>Fazla Mesai (Saat)</Form.Label>
                <Form.Control type="number" min="0" step="0.5" required value={form.overtime_hours} onChange={setField('overtime_hours')} />
              </Form.Group>
              <Form.Group className="col-6 col-md-3 col-xl-2">
                <Form.Label>Eksik Mesai (Saat)</Form.Label>
                <Form.Control type="number" min="0" step="0.5" required value={form.undertime_hours} onChange={setField('undertime_hours')} />
              </Form.Group>
              <Form.Group className="col-12 col-md-6 col-xl-8">
                <Form.Label>Açıklama</Form.Label>
                <Form.Control value={form.description} onChange={setField('description')} placeholder="Opsiyonel" />
              </Form.Group>
            </div>
          </div>
        </div>

        <div className="card mb-3">
          <div className="card-body">
            <div className="d-flex justify-content-between align-items-center mb-3">
              <h6 className="fw-bold mb-0">Görev Yapılan Seferler</h6>
              <Button size="sm" variant="outline-primary" onClick={addEntry}>
                <i className="bi bi-plus-lg me-1" /> Sefer Ekle
              </Button>
            </div>

            {!selectedPersonnel && (
              <Alert variant="secondary" className="py-2">
                Sefer eklemek için önce personel seçiniz.
              </Alert>
            )}

            <div className="table-responsive">
              <table className="table align-middle">
                <thead>
                  <tr>
                    <th style={{ minWidth: 220 }}>Sefer</th>
                    <th style={{ minWidth: 150 }}>Görev Tarihi</th>
                    <th style={{ width: 110 }}>Sefer Adedi</th>
                    <th className="text-end">Birim Ücret</th>
                    <th className="text-end">Toplam</th>
                    <th style={{ width: 60 }} />
                  </tr>
                </thead>
                <tbody>
                  {entries.map((entry, index) => {
                    const rate = resolveRate(entry.trip_id)
                    const missingRate = entry.trip_id !== '' && selectedPersonnel && rate === null

                    return (
                      <tr key={index}>
                        <td>
                          <Form.Select
                            required
                            disabled={!selectedPersonnel}
                            value={entry.trip_id}
                            onChange={setEntryField(index, 'trip_id')}
                            isInvalid={missingRate}
                          >
                            <option value="">Seçiniz…</option>
                            {trips.map((trip) => (
                              <option key={trip.id} value={trip.id}>
                                {trip.name} ({trip.code})
                              </option>
                            ))}
                          </Form.Select>
                          {missingRate && (
                            <small className="text-danger">
                              Bu sefer için "{selectedPersonnel.position}" pozisyonuna ücret tanımlı değil.
                            </small>
                          )}
                        </td>
                        <td>
                          <Form.Control
                            type="date"
                            required
                            disabled={!selectedPersonnel}
                            value={entry.duty_date}
                            onChange={setEntryField(index, 'duty_date')}
                          />
                        </td>
                        <td>
                          <Form.Control
                            type="number"
                            min="1"
                            required
                            disabled={!selectedPersonnel}
                            value={entry.trip_count}
                            onChange={setEntryField(index, 'trip_count')}
                          />
                        </td>
                        <td className="text-end">
                          {/* İş kuralı: birim ücret kullanıcı tarafından girilemez/değiştirilemez */}
                          {rate !== null ? formatCurrency(rate) : '—'}
                        </td>
                        <td className="text-end fw-semibold">
                          {rate !== null
                            ? formatCurrency(rate * (Number(entry.trip_count) || 0))
                            : '—'}
                        </td>
                        <td className="text-end">
                          <Button
                            size="sm"
                            variant="outline-danger"
                            onClick={() => removeEntry(index)}
                            disabled={entries.length === 1}
                          >
                            <i className="bi bi-trash" />
                          </Button>
                        </td>
                      </tr>
                    )
                  })}
                </tbody>
                <tfoot>
                  <tr>
                    <td colSpan={4} className="text-end fw-bold border-0">
                      Toplam Mesai Tutarı:
                    </td>
                    <td className="text-end fw-bold fs-5 border-0" style={{ color: 'var(--teal)' }}>
                      {formatCurrency(grandTotal)}
                    </td>
                    <td className="border-0" />
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>

        <div className="d-flex justify-content-end gap-2">
          <Button as={Link} to="/puantajlar" variant="secondary">
            Vazgeç
          </Button>
          <Button type="submit" disabled={saving}>
            {saving ? 'Kaydediliyor…' : isEdit ? 'Güncelle' : 'Kaydet'}
          </Button>
        </div>
      </Form>
    </>
  )
}
