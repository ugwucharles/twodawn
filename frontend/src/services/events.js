import api from './api'

export const getEvents = async () => {
  const response = await api.get('/api/v1/events')
  return response.data
}

export const getEventById = async (id) => {
  const response = await api.get(`/api/v1/events/${id}`)
  return response.data
}

export const getRecentEvents = async () => {
  const response = await api.get('/api/v1/events/recent')
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

export const getTopSellingEvents = async (limit = 6, filters = {}) => {
  const params = new URLSearchParams()

  if (limit) params.set('limit', String(limit))

  Object.entries(filters).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') {
      params.set(key, String(value))
    }
  })

  const query = params.toString()
  const response = await api.get(`/api/v1/events/top-selling${query ? `?${query}` : ''}`)
  return response.data
}
