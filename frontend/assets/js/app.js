// DOST FMS — API Client & Auth Utilities
const API_BASE = 'http://localhost:8000/api';

// ── Token management ─────────────────────────────────────────────────────────
const Auth = {
  getToken:   () => localStorage.getItem('fms_token'),
  getUser:    () => JSON.parse(localStorage.getItem('fms_user') || 'null'),
  setSession: (token, user) => { localStorage.setItem('fms_token', token); localStorage.setItem('fms_user', JSON.stringify(user)); },
  clearSession: () => { localStorage.removeItem('fms_token'); localStorage.removeItem('fms_user'); },
  isLoggedIn: () => !!localStorage.getItem('fms_token'),
  hasRole:    (...roles) => roles.includes(Auth.getUser()?.role),
};

// ── Base fetch wrapper ────────────────────────────────────────────────────────
async function apiFetch(path, options = {}) {
  const token = Auth.getToken();
  const res = await fetch(`${API_BASE}${path}`, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...(options.headers || {}),
    },
  });

  if (res.status === 401) {
    Auth.clearSession();
    window.location.href = '/pages/login.html';
    return;
  }

  const data = await res.json().catch(() => ({}));
  if (!res.ok) throw { status: res.status, data };
  return data;
}

const api = {
  get:    (path)         => apiFetch(path),
  post:   (path, body)   => apiFetch(path, { method: 'POST',   body: JSON.stringify(body) }),
  put:    (path, body)   => apiFetch(path, { method: 'PUT',    body: JSON.stringify(body) }),
  patch:  (path, body)   => apiFetch(path, { method: 'PATCH',  body: JSON.stringify(body) }),
  delete: (path)         => apiFetch(path, { method: 'DELETE' }),
};

// ── UI Helpers ────────────────────────────────────────────────────────────────
function badge(value, type = '') {
  const cls = type ? `badge-${type}` : `badge-${(value||'').toLowerCase().replace(/ /g,'_')}`;
  return `<span class="badge ${cls}">${value || '—'}</span>`;
}

function formatDate(d) {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('en-PH', { year:'numeric', month:'short', day:'numeric' });
}

function formatCurrency(n) {
  if (n == null) return '—';
  return '₱' + parseFloat(n).toLocaleString('en-PH', { minimumFractionDigits: 2 });
}

function showAlert(container, msg, type='error') {
  container.innerHTML = `<div class="alert alert-${type}">${msg}</div>`;
  setTimeout(() => container.innerHTML = '', 5000);
}

function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// ── Sidebar builder ───────────────────────────────────────────────────────────
function buildSidebar(activePage) {
  const user = Auth.getUser();
  if (!user) { window.location.href = '/pages/login.html'; return; }

  const allNav = [
    { id:'dashboard', label:'Dashboard',    icon:'📊', href:'dashboard.html', roles:['system_admin','facility_manager','maintenance_staff','requestor','viewer'] },
    { id:'assets',    label:'Asset Registry',icon:'🏷️', href:'assets.html',   roles:['system_admin','facility_manager','maintenance_staff','viewer'] },
    { id:'work-orders',label:'Work Orders', icon:'🔧', href:'work-orders.html',roles:['system_admin','facility_manager','maintenance_staff','requestor','viewer'] },
    { id:'maintenance',label:'Maint. Requests',icon:'📋',href:'maintenance.html',roles:['system_admin','facility_manager','maintenance_staff','requestor'] },
    { id:'facilities', label:'Facilities',  icon:'🏢', href:'facilities.html', roles:['system_admin','facility_manager','viewer'] },
    { id:'users',      label:'User Management',icon:'👥',href:'users.html',    roles:['system_admin'] },
    { id:'reports',    label:'Reports',     icon:'📈', href:'reports.html',    roles:['system_admin','facility_manager','viewer'] },
  ];

  const navHtml = allNav
    .filter(n => n.roles.includes(user.role))
    .map(n => `<a class="nav-item ${activePage===n.id?'active':''}" href="${n.href}"><span class="icon">${n.icon}</span>${n.label}</a>`)
    .join('');

  return `
    <div class="sidebar-logo">
      <h2>DOST FMS</h2>
      <span>Facility Management System</span>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section">Main Menu</div>
      ${navHtml}
    </nav>
    <div class="sidebar-user">
      <div class="name">${user.full_name}</div>
      <div class="role">${user.role.replace(/_/g,' ')}</div>
      <a href="#" onclick="logout()" style="font-size:12px;color:var(--secondary);text-decoration:none;display:block;margin-top:6px;">Sign out</a>
    </div>`;
}

async function logout() {
  try { await api.post('/auth/logout'); } catch(e) {}
  Auth.clearSession();
  window.location.href = '/pages/login.html';
}

// Guard: redirect to login if not authenticated
function requireAuth() {
  if (!Auth.isLoggedIn()) window.location.href = '/pages/login.html';
}
