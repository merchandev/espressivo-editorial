(function () {
    'use strict';
    const storage = {
        get(key) { try { return localStorage.getItem(key); } catch (e) { return null; } },
        set(key, value) { try { localStorage.setItem(key, value); } catch (e) {} }
    };
    function applyReadingSize(size) {
        size = Math.min(26, Math.max(16, Number(size) || 19));
        document.body.style.setProperty('--cms-reading-size', size + 'px');
        storage.set('cms-reading-size', size);
        return size;
    }
    function status(button, text) {
        const output = button.closest('.ed-reading-toolbar')?.querySelector('.ed-tool-status');
        if (output) output.textContent = text;
    }
    async function copyLink(button) {
        try {
            await navigator.clipboard.writeText(button.dataset.url || window.location.href);
            status(button, 'Enlace copiado.');
        } catch (e) {
            status(button, 'Puedes copiar el enlace desde la barra de direcciones.');
        }
    }
    document.addEventListener('click', async function (event) {
        const button = event.target.closest('[data-cms-copy], [data-cms-share], [data-cms-font]');
        if (!button) return;
        event.preventDefault();
        if (button.hasAttribute('data-cms-font')) {
            const size = applyReadingSize((Number(storage.get('cms-reading-size')) || 19) + Number(button.dataset.cmsFont));
            status(button, 'Tamaño del texto: ' + size + ' píxeles.');
        } else if (button.hasAttribute('data-cms-share') && navigator.share) {
            try { await navigator.share({ title: button.dataset.title || document.title, url: button.dataset.url || window.location.href }); }
            catch (e) { if (e.name !== 'AbortError') await copyLink(button); }
        } else {
            await copyLink(button);
        }
    });
    function boot() {
        if (storage.get('cms-reading-size')) applyReadingSize(storage.get('cms-reading-size'));
        const top = document.querySelector('[data-cms-top]');
        function updateTop() { if (top) top.hidden = window.scrollY < 500; }
        window.addEventListener('scroll', updateTop, { passive: true });
        updateTop();
        top?.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
            const heading = document.querySelector('#primary h1');
            if (heading) { heading.setAttribute('tabindex', '-1'); heading.focus({ preventScroll: true }); }
        });
        const menu = document.getElementById('site-navigation');
        const modal = document.getElementById('portada-lightbox-modal');
        let modalTrigger;
        if (modal) {
            new MutationObserver(() => {
                if (modal.getAttribute('aria-hidden') === 'false') {
                    modalTrigger = document.activeElement;
                    modal.querySelector('.portada-modal-close')?.focus({ preventScroll: true });
                } else {
                    modalTrigger?.focus({ preventScroll: true });
                }
            }).observe(modal, { attributes: true, attributeFilter: ['aria-hidden'] });
            document.addEventListener('keydown', function (event) {
                if (event.key !== 'Tab' || modal.getAttribute('aria-hidden') !== 'false') return;
                const items = [...modal.querySelectorAll('button:not([disabled]),a[href]')].filter(el => el.getClientRects().length);
                const first = items[0], last = items[items.length - 1];
                if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
                else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
            });
        }
        const toggle = menu?.querySelector('.menu-toggle');
        window.addEventListener('resize', function () {
            if (window.innerWidth > 1024 && menu?.classList.contains('toggled')) toggle.click();
        });
        document.addEventListener('keydown', function (event) {
            if (menu?.classList.contains('toggled')) {
                if (event.key === 'Escape') { event.preventDefault(); toggle.click(); toggle.focus(); }
                if (event.key === 'Tab') {
                    const elements = [toggle, ...menu.querySelectorAll('.mobile-menu-wrapper a')].filter(el => el.getClientRects().length);
                    const first = elements[0], last = elements[elements.length - 1];
                    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
                    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
                }
                return;
            }
            if (event.key === '/' && !event.ctrlKey && !event.metaKey && !event.altKey && !event.target.closest('input,textarea,select,[contenteditable="true"]')) {
                const form = document.querySelector('.header-actions .search-form');
                const input = form?.querySelector('input[type="search"]');
                if (input) { event.preventDefault(); form.classList.add('is-expanded'); input.focus(); }
            }
            if (event.key === 'Escape') {
                document.querySelector('.header-actions .search-form')?.classList.remove('is-expanded');
                const results = document.querySelector('.ajax-search-results-container');
                if (results) results.innerHTML = '';
            }
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
})();
