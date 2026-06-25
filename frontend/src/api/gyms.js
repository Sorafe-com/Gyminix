import api from './axios'

export const gymsApi = {
  list:         (params) => api.get('/gyms', { params }),
  get:          (id) => api.get(`/gyms/${id}`),
  create:       (data) => api.post('/gyms', data),
  update:       (id, data) => api.put(`/gyms/${id}`, data),
  toggleStatus: (id) => api.patch(`/gyms/${id}/status`),
  getSettings:  (id) => api.get(`/gyms/${id}/settings`),
  saveSettings: (id, data) => api.post(`/gyms/${id}/settings`, data),
  uploadLogo:   (id, form) => api.post(`/gyms/${id}/logo`, form, { headers: { 'Content-Type': 'multipart/form-data' } }),
}
