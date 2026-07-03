import { useEffect, useState } from 'react'
import { Modal, Table } from 'react-bootstrap'
import api, { extractErrorMessage } from '../api.js'
import { useToast } from '../context/ToastContext.jsx'
import { formatCurrency, formatDate, monthName } from '../utils/format.js'

/** Puantaj detay görüntüleme: başlık bilgileri + sefer satırları dökümü. */
export default function TimesheetDetailModal({ timesheetId, onClose }) {
  const notify = useToast()
  const [data, setData] = useState(null)

  useEffect(() => {
    if (!timesheetId) {
      setData(null)
      return
    }

    api
      .get(`/timesheets/${timesheetId}`)
      .then((response) => setData(response.data.data))
      .catch((error) => {
        notify(extractErrorMessage(error), 'danger')
        onClose()
      })
  }, [timesheetId, notify, onClose])

  const dayFields = data
    ? [
        ['Çalışma Günü', data.work_days],
        ['İzin Günü', data.leave_days],
        ['Rapor Günü', data.sick_days],
        ['Resmi Tatil', data.public_holiday_days],
        ['Hafta Tatili', data.weekend_days],
        ['Fazla Mesai (Saat)', data.overtime_hours],
        ['Eksik Mesai (Saat)', data.undertime_hours],
      ]
    : []

  return (
    <Modal show={Boolean(timesheetId)} onHide={onClose} centered size="lg">
      <Modal.Header closeButton>
        <Modal.Title className="fs-5">
          Puantaj Detayı
          {data && (
            <span className="text-muted fw-normal fs-6 ms-2">
              {monthName(data.month)} {data.year}
            </span>
          )}
        </Modal.Title>
      </Modal.Header>
      <Modal.Body>
        {!data ? (
          <div className="text-center py-4 text-muted">Yükleniyor…</div>
        ) : (
          <>
            <div className="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
              <div>
                <div className="fw-bold fs-6">{data.personnel?.full_name}</div>
                <small className="text-muted">
                  {data.personnel?.registration_no} · {data.personnel?.department}
                </small>
              </div>
              <span className="badge text-bg-primary">{data.position}</span>
            </div>

            <div className="row g-2 mb-3">
              {dayFields.map(([label, value]) => (
                <div className="col-6 col-md-3" key={label}>
                  <div className="border rounded p-2 text-center h-100">
                    <div className="text-muted" style={{ fontSize: '0.75rem' }}>{label}</div>
                    <div className="fw-bold">{value}</div>
                  </div>
                </div>
              ))}
            </div>

            {data.description && (
              <p className="text-muted small mb-3">
                <i className="bi bi-chat-left-text me-1" />
                {data.description}
              </p>
            )}

            <h6 className="fw-bold">Görev Yapılan Seferler</h6>
            {data.entries.length === 0 ? (
              <p className="text-muted small">Bu puantajda sefer kaydı bulunmuyor.</p>
            ) : (
              <Table size="sm" className="align-middle">
                <thead>
                  <tr>
                    <th>Sefer</th>
                    <th>Görev Tarihi</th>
                    <th className="text-center">Adet</th>
                    <th className="text-end">Birim Ücret</th>
                    <th className="text-end">Toplam</th>
                  </tr>
                </thead>
                <tbody>
                  {data.entries.map((entry) => (
                    <tr key={entry.id}>
                      <td>
                        {entry.trip?.name}{' '}
                        <span className="badge text-bg-warning">{entry.trip?.code}</span>
                      </td>
                      <td>{formatDate(entry.duty_date)}</td>
                      <td className="text-center">{entry.trip_count}</td>
                      <td className="text-end">{formatCurrency(entry.unit_rate)}</td>
                      <td className="text-end fw-semibold">{formatCurrency(entry.line_total)}</td>
                    </tr>
                  ))}
                </tbody>
                <tfoot>
                  <tr>
                    <td colSpan={4} className="text-end fw-bold border-0">
                      Toplam Mesai Tutarı:
                    </td>
                    <td className="text-end fw-bold border-0" style={{ color: 'var(--teal)' }}>
                      {formatCurrency(data.total_amount)}
                    </td>
                  </tr>
                </tfoot>
              </Table>
            )}
          </>
        )}
      </Modal.Body>
    </Modal>
  )
}
