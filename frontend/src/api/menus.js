import api from './axios'

export const menusApi = {
  list:      () => api.get('/menus'),
  tree:      () => api.get('/menus/tree'),
  userMenus: () => api.get('/menus/user'),
  create:    (data) => api.post('/menus', data),
  update:    (id, data) => api.put(`/menus/${id}`, data),
  delete:    (id) => api.delete(`/menus/${id}`),
}
