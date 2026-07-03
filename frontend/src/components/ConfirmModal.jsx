import { Button, Modal } from 'react-bootstrap'

export default function ConfirmModal({ show, title, message, onConfirm, onCancel }) {
  return (
    <Modal show={show} onHide={onCancel} centered>
      <Modal.Header closeButton>
        <Modal.Title className="fs-5">{title ?? 'Emin misiniz?'}</Modal.Title>
      </Modal.Header>
      <Modal.Body>{message}</Modal.Body>
      <Modal.Footer>
        <Button variant="secondary" onClick={onCancel}>
          Vazgeç
        </Button>
        <Button variant="danger" onClick={onConfirm}>
          Evet, Sil
        </Button>
      </Modal.Footer>
    </Modal>
  )
}
