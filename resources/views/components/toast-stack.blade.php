{{-- OmniaCSS toast stack: listens for the `oc-toast` browser event (Livewire: $this->ocToast('success', 'Saved') from the WithOcToast trait;
     plain JavaScript: window.dispatchEvent(new CustomEvent('oc-toast', { detail: { type: 'info', title: 'Hi' } }))).
     Props: top (stack at the top), timeout (ms, default 4000; 0 = stay until dismissed). Place it once in your layout. --}}
@props(['top' => false, 'timeout' => 4000])
<div wire:ignore {{ $attributes->class(['oc-toast-container', 'oc-toast-container--top' => $top])->merge(['data-oc-toast-stack' => '', 'data-timeout' => $timeout, 'aria-live' => 'polite']) }}></div>
@once
    <script>
        (() => {
            const glyphs = { success: '✓', error: '!', warning: '!', info: 'i' };
            const el = (tag, className, text) => {
                const node = document.createElement(tag);
                if (className) node.className = className;
                if (text !== undefined && text !== null) node.textContent = text;
                return node;
            };
            window.addEventListener('oc-toast', (event) => {
                const detail = Array.isArray(event.detail) ? event.detail[0] : event.detail;
                const stack = document.querySelector('[data-oc-toast-stack]');
                if (!stack || !detail) return;
                const type = ['success', 'error', 'warning', 'info'].includes(detail.type) ? detail.type : 'info';
                const toast = el('div', 'oc-toast oc-toast--' + type);
                toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
                const icon = el('span', 'oc-toast-icon', glyphs[type]);
                icon.setAttribute('aria-hidden', 'true');
                const body = el('div', 'oc-toast-body');
                body.append(el('p', 'oc-toast-title', detail.title || ''));
                if (detail.description) body.append(el('p', 'oc-toast-description', detail.description));
                const close = el('button', 'oc-toast-close', '×');
                close.type = 'button';
                close.setAttribute('aria-label', 'Dismiss');
                close.addEventListener('click', () => toast.remove());
                toast.append(icon, body, close);
                stack.append(toast);
                const timeout = detail.timeout ?? Number(stack.dataset.timeout || 4000);
                if (timeout > 0) setTimeout(() => toast.remove(), timeout);
            });
        })();
    </script>
@endonce
