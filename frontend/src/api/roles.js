import api from './axios'

export const rolesApi = {
  list:             () => api.get('/roles'),
  create:           (data) => api.post('/roles', data),
  update:           (id, data) => api.put(`/roles/${id}`, data),
  delete:           (id) => api.delete(`/roles/${id}`),
  permissions:      () => api.get('/permissions'),
  getRolePerms:     (id) => api.get(`/roles/${id}/permissions`),
  syncPermissions:  (id, ids) => api.post(`/roles/${id}/permissions/sync`, { permission_ids: ids }),
}
