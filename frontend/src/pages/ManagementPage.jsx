import { useCallback, useEffect, useState } from 'react'
import { Button, Form, Modal, Tab, Tabs } from 'react-bootstrap'
import api, { extractErrorMessage } from '../api.js'
import ConfirmModal from '../components/ConfirmModal.jsx'
import ActivityLogTab from '../components/ActivityLogTab.jsx'
import { useToast } from '../context/ToastContext.jsx'

const PAGE_LABELS = {
  dashboard: 'Dashboard',
  personel: 'Personel',
  seferler: 'Seferler',
  puantajlar: 'Puantajlar',
}

const EMPTY_ROLE = { name: '', permissions: [] }
const EMPTY_USER = { name: '', email: '', password: '', role_id: '' }

export default function ManagementPage() {
  const notify = useToast()

  const [roles, setRoles] = useState([])
  const [availablePages, setAvailablePages] = useState([])
  const [users, setUsers] = useState([])

  const [roleForm, setRoleForm] = useState(EMPTY_ROLE)
  const [editingRole, setEditingRole] = useState(null)
  const [showRoleForm, setShowRoleForm] = useState(false)
  const [deletingRole, setDeletingRole] = useState(null)

  const [userForm, setUserForm] = useState(EMPTY_USER)
  const [editingUser, setEditingUser] = useState(null)
  const [showUserForm, setShowUserForm] = useState(false)
  const [deletingUser, setDeletingUser] = useState(null)

  const [saving, setSaving] = useState(false)

  const load = useCallback(() => {
    Promise.all([api.get('/roles'), api.get('/users')])
      .then(([roleRes, userRes]) => {
        setRoles(roleRes.data.data)
        setAvailablePages(roleRes.data.available_pages)
        setUsers(userRes.data.data)
      })
      .catch((error) => notify(extractErrorMessage(error), 'danger'))
  }, [notify])

  useEffect(() => {
    load()
  }, [load])

  // --- Rol işlemleri ---

  const openCreateRole = () => {
    setEditingRole(null)
    setRoleForm(EMPTY_ROLE)
    setShowRoleForm(true)
  }

  const openEditRole = (role) => {
    setEditingRole(role)
    setRoleForm({ name: role.name, permissions: role.permissions })
    setShowRoleForm(true)
  }

  const togglePermission = (page) =>
    setRoleForm((current) => ({
      ...current,
      permissions: current.permissions.includes(page)
        ? current.permissions.filter((p) => p !== page)
        : [...current.permissions, page],
    }))

  const submitRole = (event) => {
    event.preventDefault()
    setSaving(true)

    const request = editingRole
      ? api.put(`/roles/${editingRole.id}`, roleForm)
      : api.post('/roles', roleForm)

    request
      .then(() => {
        notify(editingRole ? 'Rol güncellendi.' : 'Rol oluşturuldu.')
        setShowRoleForm(false)
        load()
      })
      .catch((error) => notify(extractErrorMessage(error), 'danger'))
      .finally(() => setSaving(false))
  }

  const deleteRole = () => {
    api
      .delete(`/roles/${deletingRole.id}`)
      .then(() => {
        notify('Rol silindi.')
        setDeletingRole(null)
        load()
      })
      .catch((error) => {
        notify(extractErrorMessage(error), 'danger')
        setDeletingRole(null)
      })
  }

  // --- Kullanıcı işlemleri ---

  const openCreateUser = () => {
    setEditingUser(null)
    setUserForm(EMPTY_USER)
    setShowUserForm(true)
  }

  const openEditUser = (user) => {
    setEditingUser(user)
    setUserForm({ name: user.name, email: user.email, password: '', role_id: user.role_id ?? '' })
    setShowUserForm(true)
  }

  const setUserField = (field) => (event) =>
    setUserForm((current) => ({ ...current, [field]: event.target.value }))

  const submitUser = (event) => {
    event.preventDefault()
    setSaving(true)

    const payload = { ...userForm }
    if (editingUser && !payload.password) {
      delete payload.password
    }

    const request = editingUser
      ? api.put(`/users/${editingUser.id}`, payload)
      : api.post('/users', payload)

    request
      .then(() => {
        notify(editingUser ? 'Kullanıcı güncellendi.' : 'Kullanıcı oluşturuldu.')
        setShowUserForm(false)
        load()
      })
      .catch((error) => notify(extractErrorMessage(error), 'danger'))
      .finally(() => setSaving(false))
  }

  const deleteUser = () => {
    api
      .delete(`/users/${deletingUser.id}`)
      .then(() => {
        notify('Kullanıcı silindi.')
        setDeletingUser(null)
        load()
      })
      .catch((error) => {
        notify(extractErrorMessage(error), 'danger')
        setDeletingUser(null)
      })
  }

  return (
    <>
      <h4 className="fw-bold mb-4">Yönetim</h4>

      <Tabs defaultActiveKey="roles" className="mb-3">
        <Tab eventKey="roles" title="Roller">
          <div className="card">
            <div className="card-body">
              <div className="d-flex justify-content-end mb-3">
                <Button onClick={openCreateRole}>
                  <i className="bi bi-plus-lg me-1" /> Yeni Rol
                </Button>
              </div>
              <div className="table-responsive">
                <table className="table table-hover align-middle">
                  <thead>
                    <tr>
                      <th>Rol Adı</th>
                      <th>Görebileceği Sayfalar</th>
                      <th className="text-center">Kullanıcı Sayısı</th>
                      <th className="text-end">İşlemler</th>
                    </tr>
                  </thead>
                  <tbody>
                    {roles.map((role) => (
                      <tr key={role.id}>
                        <td className="fw-semibold">
                          {role.name}
                          {role.is_admin && (
                            <span className="badge text-bg-warning ms-2">Admin</span>
                          )}
                        </td>
                        <td>
                          <div className="d-flex flex-wrap gap-1">
                            {(role.is_admin ? availablePages : role.permissions).map((page) => (
                              <span key={page} className="badge text-bg-light border">
                                {PAGE_LABELS[page] ?? page}
                              </span>
                            ))}
                            {role.is_admin && (
                              <span className="badge text-bg-light border">Yönetim</span>
                            )}
                          </div>
                        </td>
                        <td className="text-center">{role.users_count}</td>
                        <td className="text-end">
                          {!role.is_admin && (
                            <>
                              <Button size="sm" variant="outline-primary" className="me-1" onClick={() => openEditRole(role)}>
                                <i className="bi bi-pencil" />
                              </Button>
                              <Button size="sm" variant="outline-danger" onClick={() => setDeletingRole(role)}>
                                <i className="bi bi-trash" />
                              </Button>
                            </>
                          )}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </Tab>

        <Tab eventKey="users" title="Kullanıcılar">
          <div className="card">
            <div className="card-body">
              <div className="d-flex justify-content-end mb-3">
                <Button onClick={openCreateUser}>
                  <i className="bi bi-plus-lg me-1" /> Yeni Kullanıcı
                </Button>
              </div>
              <div className="table-responsive">
                <table className="table table-hover align-middle">
                  <thead>
                    <tr>
                      <th>Ad Soyad</th>
                      <th>E-posta</th>
                      <th>Rol</th>
                      <th className="text-end">İşlemler</th>
                    </tr>
                  </thead>
                  <tbody>
                    {users.map((user) => (
                      <tr key={user.id}>
                        <td className="fw-semibold">{user.name}</td>
                        <td>{user.email}</td>
                        <td>
                          <span className={`badge ${user.is_admin ? 'text-bg-warning' : 'text-bg-primary'}`}>
                            {user.role ?? '—'}
                          </span>
                        </td>
                        <td className="text-end">
                          <Button size="sm" variant="outline-primary" className="me-1" onClick={() => openEditUser(user)}>
                            <i className="bi bi-pencil" />
                          </Button>
                          <Button size="sm" variant="outline-danger" onClick={() => setDeletingUser(user)}>
                            <i className="bi bi-trash" />
                          </Button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </Tab>

        <Tab eventKey="logs" title="İşlem Kayıtları">
          <ActivityLogTab />
        </Tab>
      </Tabs>

      {/* Rol formu */}
      <Modal show={showRoleForm} onHide={() => setShowRoleForm(false)} centered>
        <Form onSubmit={submitRole}>
          <Modal.Header closeButton>
            <Modal.Title className="fs-5">{editingRole ? 'Rol Düzenle' : 'Yeni Rol'}</Modal.Title>
          </Modal.Header>
          <Modal.Body className="d-flex flex-column gap-3">
            <Form.Group>
              <Form.Label>Rol Adı</Form.Label>
              <Form.Control
                required
                value={roleForm.name}
                onChange={(event) => setRoleForm((current) => ({ ...current, name: event.target.value }))}
                placeholder="Örn: Operasyon Sorumlusu"
              />
            </Form.Group>
            <Form.Group>
              <Form.Label>Görebileceği Sayfalar</Form.Label>
              {availablePages.map((page) => (
                <Form.Check
                  key={page}
                  type="checkbox"
                  id={`perm-${page}`}
                  label={PAGE_LABELS[page] ?? page}
                  checked={roleForm.permissions.includes(page)}
                  onChange={() => togglePermission(page)}
                />
              ))}
              <Form.Text>En az bir sayfa seçilmelidir.</Form.Text>
            </Form.Group>
          </Modal.Body>
          <Modal.Footer>
            <Button variant="secondary" onClick={() => setShowRoleForm(false)}>
              Vazgeç
            </Button>
            <Button type="submit" disabled={saving}>
              {saving ? 'Kaydediliyor…' : 'Kaydet'}
            </Button>
          </Modal.Footer>
        </Form>
      </Modal>

      {/* Kullanıcı formu */}
      <Modal show={showUserForm} onHide={() => setShowUserForm(false)} centered>
        <Form onSubmit={submitUser}>
          <Modal.Header closeButton>
            <Modal.Title className="fs-5">
              {editingUser ? 'Kullanıcı Düzenle' : 'Yeni Kullanıcı'}
            </Modal.Title>
          </Modal.Header>
          <Modal.Body className="d-flex flex-column gap-3">
            <Form.Group>
              <Form.Label>Ad Soyad</Form.Label>
              <Form.Control required value={userForm.name} onChange={setUserField('name')} />
            </Form.Group>
            <Form.Group>
              <Form.Label>E-posta</Form.Label>
              <Form.Control type="email" required value={userForm.email} onChange={setUserField('email')} />
            </Form.Group>
            <Form.Group>
              <Form.Label>Şifre</Form.Label>
              <Form.Control
                type="password"
                required={!editingUser}
                minLength={8}
                value={userForm.password}
                onChange={setUserField('password')}
                placeholder={editingUser ? 'Değiştirmek istemiyorsanız boş bırakın' : 'En az 8 karakter'}
              />
            </Form.Group>
            <Form.Group>
              <Form.Label>Rol</Form.Label>
              <Form.Select required value={userForm.role_id} onChange={setUserField('role_id')}>
                <option value="">Seçiniz…</option>
                {roles.map((role) => (
                  <option key={role.id} value={role.id}>{role.name}</option>
                ))}
              </Form.Select>
            </Form.Group>
          </Modal.Body>
          <Modal.Footer>
            <Button variant="secondary" onClick={() => setShowUserForm(false)}>
              Vazgeç
            </Button>
            <Button type="submit" disabled={saving}>
              {saving ? 'Kaydediliyor…' : 'Kaydet'}
            </Button>
          </Modal.Footer>
        </Form>
      </Modal>

      <ConfirmModal
        show={Boolean(deletingRole)}
        title="Rolü Sil"
        message={`"${deletingRole?.name}" rolünü silmek istediğinize emin misiniz?`}
        onConfirm={deleteRole}
        onCancel={() => setDeletingRole(null)}
      />
      <ConfirmModal
        show={Boolean(deletingUser)}
        title="Kullanıcıyı Sil"
        message={`"${deletingUser?.name}" kullanıcısını silmek istediğinize emin misiniz?`}
        onConfirm={deleteUser}
        onCancel={() => setDeletingUser(null)}
      />
    </>
  )
}
