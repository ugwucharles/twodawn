import api from './api'

export const getQuote = async (eventId, options = {}) => {
  const params = new URLSearchParams()
  if (options.quantity) params.append('quantity', options.quantity)
  if (options.ticket_type) params.append('ticket_type', options.ticket_type)

  const response = await api.get(`/events/${eventId}/quote?${params}`)
  const data = response.data

  return {
    ...data,
    subtotal_kobo: data.subtotal_kobo ?? Math.round((data.subtotal || 0) * 100),
    fees_kobo: data.fees_kobo ?? Math.round((data.fee || 0) * 100),
    discount_kobo: 0,
    total_kobo: data.total_kobo ?? Math.round((data.total || 0) * 100),
  }
}

export const createOrder = async (eventId, orderData) => {
  const response = await api.post(`/events/${eventId}/orders`, orderData)
  return response.data
}

export const getOrder = async (reference) => {
  const response = await api.get(`/orders/${reference}`)
  return response.data
}
