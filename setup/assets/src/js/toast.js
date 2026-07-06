const TOAST_DURATION = 5000;

let dismissTimer = null;

function getContainer() {
    return document.getElementById('toast-container');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function dismissToast(toast) {
    if (!toast?.isConnected) return;

    toast.classList.remove('animate-toast-in');
    toast.classList.add('animate-toast-out');
    toast.addEventListener('animationend', () => toast.remove(), { once: true });

    if (dismissTimer) {
        clearTimeout(dismissTimer);
        dismissTimer = null;
    }
}

export function clearToast() {
    if (dismissTimer) {
        clearTimeout(dismissTimer);
        dismissTimer = null;
    }

    const container = getContainer();
    if (container) container.innerHTML = '';
}

export function showToast(message, variant = 'error') {
    const container = getContainer();
    if (!container || !message) return;

    clearToast();

    const toast = document.createElement('div');
    toast.className = `setup-toast setup-toast-${variant} animate-toast-in`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="setup-toast-content">
            <span class="setup-toast-icon" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <p class="setup-toast-message">${escapeHtml(message)}</p>
        </div>
        <button type="button" class="setup-toast-dismiss" aria-label="Dismiss notification">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;

    toast.querySelector('.setup-toast-dismiss')?.addEventListener('click', () => dismissToast(toast));

    container.appendChild(toast);
    dismissTimer = setTimeout(() => dismissToast(toast), TOAST_DURATION);
}
