// src/utils/price.js
export function formatPrice(event) {
  // If a direct price is set and greater than zero, use it
  if (event.price && event.price > 0) {
    return `₦${Number(event.price).toLocaleString()}`;
  }
  // Otherwise, derive from ticket types
  const ticketTypes = (event.ticket_types || []).filter(t => Number(t.price) > 0);
  if (ticketTypes.length === 0) {
    return 'Free';
  }
  const prices = ticketTypes.map(t => Number(t.price));
  const min = Math.min(...prices);
  const max = Math.max(...prices);
  if (min === max) {
    return `₦${min.toLocaleString()}`;
  }
  return `₦${min.toLocaleString()} – ₦${max.toLocaleString()}`;
}

export function getEventImage(event) {
  if (event.image_url) {
    return event.image_url;
  }
  return '/placeholder-event.jpg';
}
