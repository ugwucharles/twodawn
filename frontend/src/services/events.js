import api from './api'

export const getEvents = async (filters = {}) => {
  const params = new URLSearchParams()
  if (filters.mood) params.append('mood', filters.mood)
  if (filters.state) params.append('state', filters.state)
  if (filters.price) params.append('price', filters.price)
  if (filters.date) params.append('date', filters.date)
  if (filters.q) params.append('q', filters.q)
  if (filters.limit) params.append('limit', filters.limit)
  if (filters.page) params.append('page', filters.page)

  const response = await api.get(`/api/v1/events?${params}`)
  return response.data
}

export const getEventById = async (id) => {
  const response = await api.get(`/api/v1/events/${id}`)
  return response.data
}

export const getEventBySlug = async (slug) => {
  const response = await api.get(`/event/${slug}`)
  return response.data
}

export const getEventRemaining = async (id) => {
  const response = await api.get(`/events/${id}/remaining`)
  return response.data
}
