import { useCallback, useEffect, useState } from 'react'
import { Badge, Form } from 'react-bootstrap'
import api, { extractErrorMessage } from '../api.js'
import Pagination from './Pagination.jsx'
import { useToast } from '../context/ToastContext.jsx'

const EVENT_VARIANT = {
  created: 'success',
  updated: 'warning',
  deleted: 'danger',
}

/**
 * Değişiklikleri "Etiket: eski → yeni" biçiminde okunur listeye çevirir.
 * Backend her değişikliği { label, old, new, has_old } olarak hazır gönderir.
 */
function renderChanges(changes) {
  if (!Array.isArray(changes) || changes.length === 0) {
    return <span className="text-muted">—</span>
  }

  return (
    <div className="d-flex flex-column gap-1">
      {changes.map((change, index) => (
        <div key={index} style={{ fontSize: '0.8rem' }}>
          <span className="text-secondary">{change.label}:</span>{' '}
          {change.has_old && (
            <>
              <span className="text-muted text-decoration-line-through">{change.old}</span>
              {' → '}
            </>
          )}
          <span className="fw-semibold">{change.new ?? change.old}</span>
        </div>
      ))}
    </div>
  )
}

export default function ActivityLogTab() {
  const notify = useToast()
  const [rows, setRows] = useState([])
  const [meta, setMeta] = useState(null)
  const [page, setPage] = useState(1)
  const [filters, setFilters] = useState({ log_name: '', event: '' })
  const [options, setOptions] = useState({ subjects: {}, events: {} })

  const load = useCallback(() => {
    const params = { page }
    if (filters.log_name) params.log_name = filters.log_name
    if (filters.event) params.event = filters.event

    api
      .get('/activity-logs', { params })
      .then((response) => {
        setRows(response.data.data)
        setMeta(response.data.meta)
        setOptions(response.data.filters)
      })
      .catch((error) => notify(extractErrorMessage(error), 'danger'))
  }, [page, filters, notify])

  useEffect(() => {
    load()
  }, [load])

  const setFilter = (field) => (event) => {
    setFilters((current) => ({ ...current, [field]: event.target.value }))
    setPage(1)
  }

  return (
    <div className="card">
      <div className="card-body">
        <div className="row g-2 mb-3">
          <div className="col-6 col-md-3">
            <Form.Select value={filters.log_name} onChange={setFilter('log_name')}>
              <option value="">Tüm Kayıt Türleri</option>
              {Object.entries(options.subjects).map(([key, label]) => (
                <option key={key} value={key}>{label}</option>
              ))}
            </Form.Select>
          </div>
          <div className="col-6 col-md-3">
            <Form.Select value={filters.event} onChange={setFilter('event')}>
              <option value="">Tüm İşlemler</option>
              {Object.entries(options.events).map(([key, label]) => (
                <option key={key} value={key}>{label}</option>
              ))}
            </Form.Select>
          </div>
        </div>

        <div className="table-responsive">
          <table className="table table-hover align-middle">
            <thead>
              <tr>
                <th>Tarih</th>
                <th>Kullanıcı</th>
                <th>Kayıt Türü</th>
                <th>İşlem</th>
                <th>Değişiklikler</th>
              </tr>
            </thead>
            <tbody>
              {rows.length === 0 && (
                <tr>
                  <td colSpan={5} className="text-center text-muted py-4">
                    Kayıt bulunamadı.
                  </td>
                </tr>
              )}
              {rows.map((row) => (
                <tr key={row.id}>
                  <td className="text-nowrap"><small>{row.created_at}</small></td>
                  <td>{row.causer}</td>
                  <td>{row.subject_type}</td>
                  <td>
                    <Badge bg={EVENT_VARIANT[row.event_key] ?? 'secondary'}>{row.event}</Badge>
                  </td>
                  <td>{renderChanges(row.changes)}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        <Pagination meta={meta} onChange={setPage} />
      </div>
    </div>
  )
}
