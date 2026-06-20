import api from './api'

export const getAdminDashboard = async () => {
  const response = await api.get('/admin/dashboard')
  return response.data
}

export const toggleEventPublish = async (eventId) => {
  const response = await api.patch(`/admin/events/${eventId}/toggle`)
  return response.data
}

export const getAdminOrders = async (filters = {}) => {
  const params = new URLSearchParams()
  if (filters.limit) params.append('limit', filters.limit)
  if (filters.page) params.append('page', filters.page)

  const response = await api.get(`/admin/orders?${params}`)
  return response.data
}

export const createHostToken = async (eventId, label) => {
  const response = await api.post(`/admin/events/${eventId}/host-tokens`, { label })
  return response.data
}
