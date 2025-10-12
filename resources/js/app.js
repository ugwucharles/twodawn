import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

Alpine.start();

// Admin: AJAX publish/unpublish toggle
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('[data-toggle-publish]');
  if (!btn) return;
  e.preventDefault();
  const url = btn.getAttribute('data-url');
  const id = btn.getAttribute('data-id');
  const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  try {
    btn.disabled = true;
    const res = await fetch(url, {
      method: 'PATCH',
      headers: {
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json'
      }
    });
    if (!res.ok) throw new Error('Request failed');
    const data = await res.json();
    const badge = document.querySelector(`#published-badge-${id}`);
    if (badge) {
      if (data.is_published) {
        badge.textContent = 'Yes';
        badge.className = 'px-2 py-1 text-xs bg-green-500/20 text-green-300 rounded';
        btn.textContent = 'Unpublish';
      } else {
        badge.textContent = 'No';
        badge.className = 'px-2 py-1 text-xs bg-zinc-500/20 text-zinc-300 rounded';
        btn.textContent = 'Publish';
      }
    }
  } catch (err) {
    console.error(err);
  } finally {
    btn.disabled = false;
  }
});
