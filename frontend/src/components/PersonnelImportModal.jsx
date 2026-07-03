import { useRef, useState } from 'react'
import { Alert, Button, Modal } from 'react-bootstrap'
import api, { downloadFile, extractErrorMessage } from '../api.js'
import { useToast } from '../context/ToastContext.jsx'

/** Excel ile toplu personel içe aktarma: şablon indirme + dosya yükleme + sonuç raporu. */
export default function PersonnelImportModal({ show, onClose, onImported }) {
  const notify = useToast()
  const fileRef = useRef(null)
  const [uploading, setUploading] = useState(false)
  const [result, setResult] = useState(null)

  const handleTemplate = () => {
    downloadFile('/personnel/import/template', {}, 'personel-import-sablonu.xlsx').catch((error) =>
      notify(extractErrorMessage(error), 'danger')
    )
  }

  const handleUpload = (event) => {
    event.preventDefault()
    const file = fileRef.current?.files?.[0]
    if (!file) return

    const formData = new FormData()
    formData.append('file', file)
    setUploading(true)
    setResult(null)

    api
      .post('/personnel/import', formData)
      .then((response) => {
        setResult(response.data)
        if (response.data.imported > 0) {
          notify(response.data.message)
          onImported()
        }
      })
      .catch((error) => notify(extractErrorMessage(error), 'danger'))
      .finally(() => setUploading(false))
  }

  const handleClose = () => {
    setResult(null)
    onClose()
  }

  return (
    <Modal show={show} onHide={handleClose} centered>
      <Modal.Header closeButton>
        <Modal.Title className="fs-5">Excel ile Toplu Personel İçe Aktar</Modal.Title>
      </Modal.Header>
      <Modal.Body>
        <p className="text-muted small">
          Önce şablonu indirip doldurun. Departman ve pozisyon adları sistemdeki kayıtlarla
          birebir eşleşmelidir. Hatalı satırlar atlanır, geçerli satırlar eklenir.
        </p>

        <Button variant="outline-primary" size="sm" className="mb-3" onClick={handleTemplate}>
          <i className="bi bi-download me-1" /> Şablonu İndir
        </Button>

        <form onSubmit={handleUpload}>
          <div className="input-group">
            <input
              ref={fileRef}
              type="file"
              className="form-control"
              accept=".xlsx,.xls,.csv"
              required
            />
            <Button type="submit" disabled={uploading}>
              {uploading ? 'Yükleniyor…' : 'İçe Aktar'}
            </Button>
          </div>
        </form>

        {result && (
          <div className="mt-3">
            <Alert variant={result.imported > 0 ? 'success' : 'warning'} className="py-2 mb-2">
              {result.message}
            </Alert>
            {result.errors.length > 0 && (
              <Alert variant="danger" className="py-2 mb-0">
                <div className="fw-semibold mb-1">Atlanan satırlar:</div>
                <ul className="mb-0 ps-3" style={{ fontSize: '0.85rem' }}>
                  {result.errors.map((err, i) => (
                    <li key={i}>{err}</li>
                  ))}
                </ul>
              </Alert>
            )}
          </div>
        )}
      </Modal.Body>
    </Modal>
  )
}
