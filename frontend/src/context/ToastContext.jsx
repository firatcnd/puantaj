import { createContext, useCallback, useContext, useState } from 'react'
import { Toast, ToastContainer } from 'react-bootstrap'

const ToastContext = createContext(() => {})

/** Uygulama genelinde başarı/hata bildirimleri: const notify = useToast() */
export function useToast() {
  return useContext(ToastContext)
}

export function ToastProvider({ children }) {
  const [toasts, setToasts] = useState([])

  const notify = useCallback((message, variant = 'success') => {
    const id = Date.now() + Math.random()
    setToasts((current) => [...current, { id, message, variant }])
  }, [])

  const remove = (id) => setToasts((current) => current.filter((t) => t.id !== id))

  return (
    <ToastContext.Provider value={notify}>
      {children}
      <ToastContainer position="top-end" className="p-3" style={{ zIndex: 1080 }}>
        {toasts.map((toast) => (
          <Toast
            key={toast.id}
            bg={toast.variant}
            onClose={() => remove(toast.id)}
            delay={4500}
            autohide
          >
            <Toast.Body className={toast.variant === 'warning' ? 'text-dark' : 'text-white'}>
              {toast.message.split('\n').map((line, i) => (
                <div key={i}>{line}</div>
              ))}
            </Toast.Body>
          </Toast>
        ))}
      </ToastContainer>
    </ToastContext.Provider>
  )
}
