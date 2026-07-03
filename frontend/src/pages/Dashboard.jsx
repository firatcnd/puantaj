import { useEffect, useState } from 'react'
import { Bar, Doughnut } from 'react-chartjs-2'
import {
  ArcElement,
  BarElement,
  CategoryScale,
  Chart as ChartJS,
  Legend,
  LinearScale,
  Tooltip,
} from 'chart.js'
import api, { extractErrorMessage } from '../api.js'
import { useToast } from '../context/ToastContext.jsx'
import { formatCurrency } from '../utils/format.js'

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Tooltip, Legend)

const PALETTE = ['#587b7f', '#e2c044', '#393e41', '#a4b8ba', '#d3d0cb']

function StatCard({ icon, label, value, sub, color }) {
  return (
    <div className="card stat-card h-100">
      <div className="card-body d-flex align-items-center gap-3">
        <div className="stat-icon" style={{ backgroundColor: `${color}22`, color }}>
          <i className={`bi ${icon}`} />
        </div>
        <div style={{ minWidth: 0 }}>
          <div className="text-muted small">{label}</div>
          <div className="fs-5 fw-bold text-truncate">{value}</div>
          {sub && <div className="text-muted small text-truncate">{sub}</div>}
        </div>
      </div>
    </div>
  )
}

export default function Dashboard() {
  const [stats, setStats] = useState(null)
  const notify = useToast()

  useEffect(() => {
    api
      .get('/dashboard')
      .then((response) => setStats(response.data))
      .catch((error) => notify(extractErrorMessage(error), 'danger'))
  }, [notify])

  if (!stats) {
    return <div className="text-center py-5 text-muted">Yükleniyor…</div>
  }

  const cards = [
    { icon: 'bi-people', label: 'Toplam Personel', value: stats.total_personnel, color: '#587b7f' },
    { icon: 'bi-signpost-split', label: 'Toplam Sefer', value: stats.total_trips, color: '#e2c044' },
    { icon: 'bi-calendar-check', label: 'Bu Ay Oluşturulan Puantaj', value: stats.timesheets_this_month, color: '#393e41' },
    { icon: 'bi-cash-stack', label: 'Toplam Mesai Tutarı', value: formatCurrency(stats.total_overtime_amount), color: '#587b7f' },
    {
      icon: 'bi-star',
      label: 'En Çok Görev Yapılan Sefer',
      value: stats.top_trip?.name ?? '-',
      sub: stats.top_trip ? `${stats.top_trip.total_trips} sefer` : null,
      color: '#e2c044',
    },
    {
      icon: 'bi-trophy',
      label: 'En Çok Sefere Çıkan Personel',
      value: stats.top_personnel?.full_name ?? '-',
      sub: stats.top_personnel ? `${stats.top_personnel.position} · ${stats.top_personnel.total_trips} sefer` : null,
      color: '#393e41',
    },
  ]

  return (
    <>
      <h4 className="fw-bold mb-4">Dashboard</h4>

      <div className="row g-3 mb-4">
        {cards.map((card) => (
          <div className="col-12 col-sm-6 col-xl-4" key={card.label}>
            <StatCard {...card} />
          </div>
        ))}
      </div>

      <div className="row g-3">
        <div className="col-12 col-lg-7">
          <div className="card h-100">
            <div className="card-body">
              <h6 className="fw-bold mb-3">Aylık Toplam Mesai Tutarı</h6>
              <Bar
                data={{
                  labels: stats.monthly_totals.map((row) => row.label),
                  datasets: [
                    {
                      label: 'Mesai Tutarı (TL)',
                      data: stats.monthly_totals.map((row) => row.total),
                      backgroundColor: '#587b7f',
                      borderRadius: 6,
                    },
                  ],
                }}
                options={{ plugins: { legend: { display: false } } }}
              />
            </div>
          </div>
        </div>
        <div className="col-12 col-lg-5">
          <div className="card h-100">
            <div className="card-body">
              <h6 className="fw-bold mb-3">Sefer Dağılımı (İlk 5)</h6>
              <Doughnut
                data={{
                  labels: stats.trip_distribution.map((row) => row.trip),
                  datasets: [
                    {
                      data: stats.trip_distribution.map((row) => row.total_trips),
                      backgroundColor: PALETTE,
                    },
                  ],
                }}
                options={{ plugins: { legend: { position: 'bottom' } } }}
              />
            </div>
          </div>
        </div>
      </div>
    </>
  )
}
