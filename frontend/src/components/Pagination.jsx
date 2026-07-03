/** Laravel paginate() meta'sı ile çalışan basit sayfalama. */
export default function Pagination({ meta, onChange }) {
  if (!meta || meta.last_page <= 1) return null

  const pages = []
  for (let page = 1; page <= meta.last_page; page++) {
    // Çok sayfa varsa yalnızca uçları ve aktif sayfanın çevresini göster
    if (
      page === 1 ||
      page === meta.last_page ||
      Math.abs(page - meta.current_page) <= 2
    ) {
      pages.push(page)
    } else if (pages[pages.length - 1] !== '...') {
      pages.push('...')
    }
  }

  return (
    <nav className="d-flex justify-content-between align-items-center flex-wrap gap-2">
      <small className="text-muted">
        {meta.total} kayıttan {meta.from ?? 0}–{meta.to ?? 0} arası gösteriliyor
      </small>
      <ul className="pagination pagination-sm mb-0">
        <li className={`page-item ${meta.current_page === 1 ? 'disabled' : ''}`}>
          <button className="page-link" onClick={() => onChange(meta.current_page - 1)}>
            &laquo;
          </button>
        </li>
        {pages.map((page, index) =>
          page === '...' ? (
            <li key={`gap-${index}`} className="page-item disabled">
              <span className="page-link">…</span>
            </li>
          ) : (
            <li key={page} className={`page-item ${page === meta.current_page ? 'active' : ''}`}>
              <button className="page-link" onClick={() => onChange(page)}>
                {page}
              </button>
            </li>
          )
        )}
        <li className={`page-item ${meta.current_page === meta.last_page ? 'disabled' : ''}`}>
          <button className="page-link" onClick={() => onChange(meta.current_page + 1)}>
            &raquo;
          </button>
        </li>
      </ul>
    </nav>
  )
}
