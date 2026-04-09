/* ============================================================
   SaaS Platform — Global JavaScript
   Mirrors the reference project's AJAX pattern:
   - $.ajax replaced with fetch + handleReply()
   - Flash toasts instead of page reloads for AJAX actions
   - Modal open/close helpers
   ============================================================ */

'use strict';

// ── CSRF Token ───────────────────────────────────────────────
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// ── Toast Notifications ──────────────────────────────────────
window.showToast = function (message, type = 'success', duration = 4000) {
    const icons = { success: 'check_circle', error: 'error', info: 'info', warning: 'warning' };
    const colors = {
        success: 'background:#16a34a',
        error:   'background:#dc2626',
        info:    'background:#2563eb',
        warning: 'background:#d97706',
    };

    const toast = document.createElement('div');
    toast.setAttribute('role', 'alert');
    toast.style.cssText = `
        position:fixed;bottom:1.25rem;right:1.25rem;z-index:9999;
        color:#fff;padding:.875rem 1.125rem;border-radius:.625rem;
        box-shadow:0 10px 25px rgba(0,0,0,.2);
        display:flex;align-items:center;gap:.625rem;
        font-size:.875rem;font-weight:500;max-width:26rem;
        animation:slideIn .25s ease;
        ${colors[type] ?? colors.success}
    `;
    toast.innerHTML = `
        <span class="material-icons" style="font-size:1.2rem">${icons[type] ?? icons.success}</span>
        <span style="flex:1">${message}</span>
        <button onclick="this.parentElement.remove()" style="background:transparent;border:0;color:rgba(255,255,255,.7);cursor:pointer;padding:.2rem;font-size:1.1rem;line-height:1">×</button>
    `;

    if (!document.getElementById('toast-styles')) {
        const style = document.createElement('style');
        style.id = 'toast-styles';
        style.textContent = `
            @keyframes slideIn { from{transform:translateX(110%);opacity:0} to{transform:translateX(0);opacity:1} }
            @keyframes slideOut{ from{transform:translateX(0);opacity:1} to{transform:translateX(110%);opacity:0} }
        `;
        document.head.appendChild(style);
    }

    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'slideOut .25s ease forwards';
        setTimeout(() => toast.remove(), 250);
    }, duration);
};

// ── Reply Handler (mirrors reference project pattern) ────────
window.handleReply = function (response) {
    if (!response) return;

    if (response.status === 'success') {
        if (response.message) showToast(response.message, 'success');
        if (response.action === 'redirect') {
            setTimeout(() => window.location.href = response.url, 500);
            return;
        }
        if (response.html && response.selector) {
            const target = document.querySelector(response.selector);
            if (target) target.innerHTML = response.html;
        }
    } else {
        const msg = response.message || 'Something went wrong.';
        showToast(msg, 'error');

        if (response.errors) {
            Object.entries(response.errors).forEach(([field, messages]) => {
                const input = document.querySelector(`[name="${field}"]`);
                if (input) {
                    input.classList.add('error');
                    let errEl = input.nextElementSibling;
                    if (!errEl || !errEl.classList.contains('form-error')) {
                        errEl = document.createElement('p');
                        errEl.className = 'form-error';
                        input.after(errEl);
                    }
                    errEl.textContent = Array.isArray(messages) ? messages[0] : messages;
                }
            });
        }
    }
};

// ── Fetch Wrapper ────────────────────────────────────────────
window.apiPost = async function (url, data = {}, opts = {}) {
    const isFormData = data instanceof FormData;
    const headers = {
        'X-CSRF-TOKEN': csrf(),
        'Accept': 'application/json',
        ...opts.headers,
    };
    if (!isFormData) headers['Content-Type'] = 'application/json';

    const response = await fetch(url, {
        method:  opts.method ?? 'POST',
        headers,
        body:    isFormData ? data : JSON.stringify(data),
    });

    return response.json();
};

// ── AJAX Form Submission ──────────────────────────────────────
window.submitAjaxForm = async function (formEl, opts = {}) {
    if (typeof formEl === 'string') formEl = document.querySelector(formEl);
    if (!formEl) return;

    const btn = formEl.querySelector('[type=submit]');
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="material-icons spin">refresh</span> Saving…'; }

    try {
        const formData = new FormData(formEl);
        const json = await apiPost(formEl.action, formData, { method: formEl.method?.toUpperCase() || 'POST' });
        handleReply(json);
        if (opts.onSuccess && json.status === 'success') opts.onSuccess(json);
        if (opts.reload && json.status === 'success') setTimeout(() => window.location.reload(), 800);
    } catch (err) {
        showToast('Network error. Please try again.', 'error');
        console.error(err);
    } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = opts.btnText ?? 'Save'; }
    }
};

// ── Modal Helpers ────────────────────────────────────────────
window.openModal = function (id) {
    const modal = document.getElementById(id);
    if (modal) { modal.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
};
window.closeModal = function (id) {
    const modal = document.getElementById(id);
    if (modal) { modal.classList.add('hidden'); document.body.style.overflow = ''; }
};

// Close modal when clicking outside the panel
document.addEventListener('click', (e) => {
    if (e.target.matches('[data-modal-close]')) {
        closeModal(e.target.dataset.modalClose);
    }
    if (e.target.matches('.modal-backdrop')) {
        e.target.closest('.modal-wrap')?.classList.add('hidden');
        document.body.style.overflow = '';
    }
});

// ── Confirm Delete Helper ─────────────────────────────────────
window.confirmDelete = async function (url, label = 'this item') {
    if (!confirm(`Are you sure you want to delete ${label}? This cannot be undone.`)) return;
    const json = await apiPost(url, {}, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' } });
    handleReply(json);
    if (json.status === 'success') setTimeout(() => window.location.reload(), 800);
};

// ── Flash message auto-dismiss ────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Auto-dismiss flash alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity .4s, max-height .4s';
            el.style.opacity    = '0';
            el.style.maxHeight  = '0';
            el.style.padding    = '0';
            el.style.margin     = '0';
            setTimeout(() => el.remove(), 400);
        }, 5000);
    });

    // Mobile sidebar toggle
    document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
        const sidebar = document.getElementById('sidebar');
        sidebar?.classList.toggle('open');
        sidebar?.classList.toggle('hidden');
    });

    // Spinning icon CSS
    const spinStyle = document.createElement('style');
    spinStyle.textContent = '@keyframes spin{to{transform:rotate(360deg)}} .spin{animation:spin 1s linear infinite;display:inline-block}';
    document.head.appendChild(spinStyle);

    // Wire up all [data-ajax-form] forms
    document.querySelectorAll('[data-ajax-form]').forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            submitAjaxForm(form, {
                reload: form.dataset.reload === 'true',
                btnText: form.querySelector('[type=submit]')?.dataset.original,
            });
        });
        // Save original button text
        const btn = form.querySelector('[type=submit]');
        if (btn) btn.dataset.original = btn.innerHTML;
    });
});

// ── Color picker preview sync ──────────────────────────────
document.addEventListener('input', (e) => {
    if (e.target.type === 'color' && e.target.dataset.preview) {
        const preview = document.querySelector(e.target.dataset.preview);
        if (preview) preview.style.backgroundColor = e.target.value;
        const text = document.querySelector(e.target.dataset.text);
        if (text) text.value = e.target.value;
    }
});
