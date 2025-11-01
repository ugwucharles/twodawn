import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

// Chat widget component (global) so Alpine can find it before start()
window.chatWidget = function () {
  const root = document.getElementById('chat-root');
  const tokenKey = 'chat_token_v1';
  const startUrl = root ? root.getAttribute('data-chat-start') : null;
  const baseUrl = root ? root.getAttribute('data-chat-base') : '';
  const showMode = root ? (root.getAttribute('data-chat-show') || 'after-host') : 'after-host';
  const msgUrl = (t) => `${baseUrl}/${t}/messages`;
  const loadPos = () => { try { return JSON.parse(localStorage.getItem('chat_pos_v1')||'{}'); } catch(_) { return {}; } };
  const savePos = (pos) => { try { localStorage.setItem('chat_pos_v1', JSON.stringify(pos)); } catch(_){} };
  return {
    open: false,
    headVisible: false,
    body: '',
    name: localStorage.getItem('chat_name_v1') || '',
    email: localStorage.getItem('chat_email_v1') || '',
    msgs: [],
    sending: false,
    token: localStorage.getItem(tokenKey) || null,
    lastId: 0,
    timer: null,
    seenIds: new Set(),
    closed: false,
    drag:{active:false, startX:0, startY:0, startRight:16, startBottom:16},
    pos: Object.assign({right:16,bottom:16}, loadPos()),
    style() { return `bottom:${this.pos.bottom}px; right:${this.pos.right}px;`; },
    hasIdentity(){ try { const n=(this.name||'').trim(); const e=(this.email||'').trim(); if(n.length<2) return false; return /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(e); } catch(_) { return false; } },
    async init() {
      try { if (showMode === 'always') { this.headVisible = true; } else {
        const host = document.getElementById('host');
        if (host && 'IntersectionObserver' in window) {
          const obs = new IntersectionObserver((entries)=>{ if(entries.some(e=>e.isIntersecting)){ this.headVisible = true; obs.disconnect(); } }, {threshold: 0.2});
          obs.observe(host);
        }
      }
      if (this.token) { this.poll(); if (this.hasIdentity()) { this.updateIdentityIfNeeded(); } }
      this.$nextTick(()=>{ this.applyPos(); });
      } catch (_) {}
    },
    applyPos(){ try{ root.style.bottom=this.pos.bottom+'px'; root.style.right=this.pos.right+'px'; }catch(_){} },
    startDrag(e){ try { this.drag.active=true; const rect=root.getBoundingClientRect(); this.drag.startX=e.clientX; this.drag.startY=e.clientY; this.drag.startRight=parseInt(window.getComputedStyle(root).right)||this.pos.right; this.drag.startBottom=parseInt(window.getComputedStyle(root).bottom)||this.pos.bottom; const move=(ev)=>{ if(!this.drag.active) return; const dx=ev.clientX - this.drag.startX; const dy=ev.clientY - this.drag.startY; this.pos.right = Math.max(0, Math.min(window.innerWidth-56, this.drag.startRight - dx)); this.pos.bottom = Math.max(0, Math.min(window.innerHeight-56, this.drag.startBottom + dy)); this.applyPos(); };
      const up=()=>{ this.drag.active=false; savePos(this.pos); window.removeEventListener('pointermove', move); window.removeEventListener('pointerup', up); };
      window.addEventListener('pointermove', move, {passive:true}); window.addEventListener('pointerup', up, {passive:true}); } catch(_){} },
    toggle() {
      this.open = !this.open;
      if (this.open) {
        if (!this.token) {
          if (this.hasIdentity()) {
            this.startWithIdentity();
          } else {
            this.$nextTick(() => { try { this.$refs.name && this.$refs.name.focus(); } catch (_) {} });
          }
        } else {
          this.$nextTick(() => { this.scroll(); try { this.$refs.input && this.$refs.input.focus(); } catch (_) {} });
        }
      }
    },
    scroll() { try { const el = this.$refs.list; el.scrollTop = el.scrollHeight; } catch (_) {} },
    async startWithIdentity(){ if (!startUrl) return; if (!this.hasIdentity()) return; this.sending = true; try { const res = await fetch(startUrl, { method: 'POST', headers: { 'Content-Type':'application/json','X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' }, body: JSON.stringify({ name: (this.name||'').trim(), email: (this.email||'').trim() }) }); const data = await res.json(); if (data.token) { this.token = data.token; localStorage.setItem(tokenKey, data.token); localStorage.setItem('chat_name_v1', (this.name||'').trim()); localStorage.setItem('chat_email_v1', (this.email||'').trim()); this.poll(); this.$nextTick(()=> this.scroll()); } } catch (_) {} finally { this.sending = false; } },
    async updateIdentityIfNeeded(){ if (!this.token || !this.hasIdentity()) return; try { await fetch(`${baseUrl}/${this.token}`, { method:'PATCH', headers:{ 'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' }, body: JSON.stringify({ name:(this.name||'').trim(), email:(this.email||'').trim() }) }); } catch(_){} },
    async send() { if (this.closed) return; if (!this.body.trim()) return; if (!this.token) { if (this.hasIdentity()) { await this.startWithIdentity(); } } if (!this.token) return; this.sending = true; try { const res = await fetch(msgUrl(this.token), { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' }, body: JSON.stringify({ body: this.body }) }); const data = await res.json(); if (data.closed) { this.closed = true; return; } if (data.ok) { const createdAt = new Date().toISOString(); if (!this.seenIds.has(data.id)) { this.msgs.push({ id: data.id, sender: 'user', body: this.body, created_at: createdAt }); this.seenIds.add(data.id); } this.body = ''; this.lastId = Math.max(this.lastId, data.id || 0); this.$nextTick(() => this.scroll()); } } catch (_) {} finally { this.sending = false; } },
    async sendImage(file) { if (this.closed) return; if (!file) return; if (!this.token) { if (this.hasIdentity()) { await this.startWithIdentity(); } } if (!this.token) return; const fd = new FormData(); fd.append('image', file); this.sending = true; try { const res = await fetch(msgUrl(this.token), { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' }, body: fd }); const data = await res.json(); if (data.closed) { this.closed = true; return; } if (data.ok) { if (!this.seenIds.has(data.id)) { this.msgs.push({ id: data.id, sender: 'user', body: '', created_at: data.at, media_url: data.image_url }); this.seenIds.add(data.id); } this.lastId = Math.max(this.lastId, data.id || 0); this.$nextTick(() => this.scroll()); } } catch(_) {} finally { this.sending=false; } },
    async poll() { if (!this.token) return; const run = async () => { try { const res = await fetch(`${msgUrl(this.token)}?after_id=${this.lastId}`, { headers: { 'Accept': 'application/json' } }); const data = await res.json(); if (data && data.messages) { if (data.closed) this.closed = true; if (data.messages.length) { data.messages.forEach(m => { if (m && typeof m.id === 'number' && !this.seenIds.has(m.id)) { this.msgs.push(m); this.seenIds.add(m.id); } this.lastId = Math.max(this.lastId, m.id || 0); }); this.$nextTick(() => this.scroll()); } } } catch (_) {} finally { this.timer = setTimeout(run, 4000); } }; run(); },
    fmt(t) { try { if (!t) return ''; const d = new Date(t); if (isNaN(d.getTime())) return ''; return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }); } catch (_) { return ''; } },
  }
};

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
