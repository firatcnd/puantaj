import { useState } from 'react'
import { Button } from 'react-bootstrap'
import { downloadFile, extractErrorMessage } from '../api.js'
import { useToast } from '../context/ToastContext.jsx'

/**
 * Liste ekranları için Excel / PDF indirme butonları.
 * Aktif filtreleri (params) export isteğine taşır; listede ne varsa o iner.
 */
export default function ExportButtons({ resource, params = {}, fileBase }) {
  const notify = useToast()
  const [busy, setBusy] = useState(null) // 'excel' | 'pdf' | null

  const handleDownload = (format, extension) => {
    setBusy(format)
    downloadFile(`/${resource}/export/${format}`, params, `${fileBase}.${extension}`)
      .catch((error) => notify(extractErrorMessage(error), 'danger'))
      .finally(() => setBusy(null))
  }

  return (
    <div className="d-flex gap-2">
      <Button
        variant="outline-primary"
        size="sm"
        disabled={busy !== null}
        onClick={() => handleDownload('excel', 'xlsx')}
      >
        <i className="bi bi-file-earmark-excel me-1" />
        {busy === 'excel' ? 'İndiriliyor…' : 'Excel'}
      </Button>
      <Button
        variant="outline-primary"
        size="sm"
        disabled={busy !== null}
        onClick={() => handleDownload('pdf', 'pdf')}
      >
        <i className="bi bi-file-earmark-pdf me-1" />
        {busy === 'pdf' ? 'İndiriliyor…' : 'PDF'}
      </Button>
    </div>
  )
}
