import api from './axios'

export const authApi = {
  login:      (data) => api.post('/auth/login', data),
  refresh:    (token) => api.post('/auth/refresh', { refresh_token: token }),
  logout:     (refreshToken) => api.post('/auth/logout', { refresh_token: refreshToken }),
  logoutAll:  () => api.post('/auth/logout-all'),
  me:         () => api.get('/auth/me'),
}
