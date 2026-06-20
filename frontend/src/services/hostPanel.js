import api from './api'

export const getHostPanelData = async (token) => {
  const response = await api.get(`/h/${token}`)
  return response.data
}

export const getHostPeopleData = async (token, filters = {}) => {
  const params = new URLSearchParams()
  if (filters.limit) params.append('limit', filters.limit)
  if (filters.page) params.append('page', filters.page)

  const response = await api.get(`/h/${token}/people?${params}`)
  return response.data
}

export const verifyTicket = async (token, reference) => {
  const response = await api.post(`/h/${token}/verify`, { text: reference })
  return response.data
}
