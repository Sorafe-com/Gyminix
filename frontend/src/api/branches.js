import api from './axios'

export const branchesApi = {
  list:         (params) => api.get('/branches', { params }),
  get:          (id) => api.get(`/branches/${id}`),
  create:       (data) => api.post('/branches', data),
  update:       (id, data) => api.put(`/branches/${id}`, data),
  delete:       (id) => api.delete(`/branches/${id}`),
  toggleStatus: (id) => api.patch(`/branches/${id}/status`),
}
