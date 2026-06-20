import api from './api'

export const getQuote = async (eventId, options = {}) => {
  const params = new URLSearchParams()
  if (options.quantity) params.append('quantity', options.quantity)
  if (options.ticket_type) params.append('ticket_type', options.ticket_type)
  if (options.coupon_code) params.append('coupon_code', options.coupon_code)

  const response = await api.get(`/events/${eventId}/quote?${params}`)
  return response.data
}

export const createOrder = async (eventId, orderData) => {
  const response = await api.post(`/events/${eventId}/orders`, orderData)
  return response.data
}

export const getOrder = async (reference) => {
  const response = await api.get(`/orders/${reference}`)
  return response.data
}
