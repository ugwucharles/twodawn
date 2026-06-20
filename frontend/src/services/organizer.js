import api from './api'

export const getOrganizerDashboard = async () => {
  const response = await api.get('/organizer/dashboard')
  return response.data
}

export const getOrganizerEvents = async () => {
  const response = await api.get('/organizer/events')
  return response.data
}

export const getOrganizerOrders = async (filters = {}) => {
  const params = new URLSearchParams()
  if (filters.limit) params.append('limit', filters.limit)
  if (filters.page) params.append('page', filters.page)

  const response = await api.get(`/organizer/orders?${params}`)
  return response.data
}
