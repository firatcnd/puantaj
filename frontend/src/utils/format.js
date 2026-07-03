export const MONTHS = [
  'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran',
  'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık',
]

export function monthName(month) {
  return MONTHS[month - 1] ?? month
}

const currencyFormatter = new Intl.NumberFormat('tr-TR', {
  style: 'currency',
  currency: 'TRY',
  minimumFractionDigits: 2,
})

export function formatCurrency(value) {
  return currencyFormatter.format(value ?? 0)
}

export function formatDate(value) {
  if (!value) return '-'
  return new Date(value).toLocaleDateString('tr-TR')
}
