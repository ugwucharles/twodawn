// src/utils/image.js
export function getEventImage(event) {
  if (event.image_url) return event.image_url;
  if (event.image_path) return `/storage/${event.image_path}`;
  return null;
}
