(function () {
    'use strict';
    function boot() {
        const toggle = document.querySelector('.cms-menu-toggle');
        const overlay = document.getElementById('cms-menu-drawer');
        const panel = overlay?.querySelector('.cms-menu-panel');
        const desktop = document.querySelector('.cms-desktop-menu');
        const drawer = overlay?.querySelector('.cms-drawer-navigation');
        const bar = document.querySelector('.navigation-inner');
        if (!toggle || !overlay || !panel || !desktop || !drawer || !bar) return;
        const roots = [desktop, drawer];
        let inertStates = [], previousOverflow = '', layoutFrame;
        drawer.classList.add('cms-nav-enhanced');
        desktop.querySelectorAll('.cms-menu-list > li').forEach(item => {
            if ((item.querySelector(':scope > .sub-menu')?.children.length || 0) > 9) item.classList.add('cms-menu-wide');
        });

        function control(item) { return item.querySelector(':scope > .cms-submenu-toggle, :scope > .cms-menu-group'); }
        function expand(item, open) {
            const button = control(item);
            if (!button) return;
            if (open) {
                [...item.parentElement.children].filter(sibling => sibling !== item).forEach(sibling => expand(sibling, false));
            } else {
                item.querySelectorAll('.cms-submenu-open').forEach(child => expand(child, false));
            }
            button.setAttribute('aria-expanded', String(open));
            item.classList.toggle('cms-submenu-open', open);
            if (open && item.parentElement === desktop.querySelector('.cms-menu-list')) {
                item.classList.remove('cms-submenu-align-end');
                const submenu = item.querySelector(':scope > .sub-menu');
                if (submenu) submenu.style.maxHeight = Math.max(80, window.innerHeight - submenu.getBoundingClientRect().top - 16) + 'px';
                if (submenu?.getBoundingClientRect().right > window.innerWidth - 16) item.classList.add('cms-submenu-align-end');
            }
        }
        function close(returnFocus = true) {
            if (overlay.hidden) return;
            overlay.hidden = true;
            document.body.classList.remove('cms-menu-open');
            toggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = previousOverflow;
            inertStates.forEach(([element, value]) => { element.inert = value; });
            inertStates = [];
            if (returnFocus && toggle.getClientRects().length) toggle.focus({ preventScroll: true });
        }
        function open() {
            drawer.querySelectorAll('.cms-submenu-open').forEach(item => expand(item, false));
            let ancestor = drawer.querySelector('a[aria-current=page]')?.parentElement.parentElement.closest('li');
            const ancestors = [];
            while (ancestor && drawer.contains(ancestor)) { ancestors.unshift(ancestor); ancestor = ancestor.parentElement.closest('li'); }
            ancestors.forEach(item => expand(item, true));
            previousOverflow = document.body.style.overflow;
            overlay.hidden = false;
            drawer.scrollTop = 0;
            document.body.classList.add('cms-menu-open');
            toggle.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
            inertStates = [...document.body.children]
                .filter(element => element !== overlay && !['SCRIPT', 'STYLE', 'LINK'].includes(element.tagName))
                .map(element => [element, element.inert]);
            inertStates.forEach(([element]) => { element.inert = true; });
            panel.querySelector('.cms-menu-close').focus({ preventScroll: true });
        }
        function layout() {
            const style = getComputedStyle(bar);
            const available = bar.clientWidth - parseFloat(style.paddingLeft) - parseFloat(style.paddingRight)
                - (bar.querySelector('.header-actions')?.getBoundingClientRect().width || 0) - parseFloat(style.columnGap || 0);
            const needed = desktop.querySelector('.cms-menu-list')?.getBoundingClientRect().width || 0;
            const compact = window.innerWidth <= 1024 || needed > available;
            document.body.classList.toggle('cms-menu-compact', compact);
            if (compact) desktop.querySelectorAll('.cms-menu-list > .cms-submenu-open').forEach(item => expand(item, false));
            else {
                if (!overlay.hidden) close(false);
                desktop.querySelectorAll('.cms-menu-list > .cms-submenu-open').forEach(item => expand(item, true));
            }
        }
        function requestLayout() { cancelAnimationFrame(layoutFrame); layoutFrame = requestAnimationFrame(layout); }
        function sync() {
            desktop.querySelectorAll('.cms-menu-list > .cms-submenu-open').forEach(item => expand(item, false));
            const path = window.location.pathname.replace(/\/$/, '') || '/';
            roots.forEach(root => {
                root.querySelectorAll('.current-menu-item,.current-menu-ancestor,.current-menu-parent').forEach(item => item.classList.remove('current-menu-item', 'current-menu-ancestor', 'current-menu-parent'));
                root.querySelectorAll('a[href]').forEach(link => {
                    let url;
                    try { url = new URL(link.href, window.location.href); } catch (e) { return; }
                    const current = url.origin === window.location.origin && (url.pathname.replace(/\/$/, '') || '/') === path
                        && !new URLSearchParams(window.location.search).has('s') && !new URLSearchParams(window.location.search).has('post_type');
                    if (current) {
                        link.setAttribute('aria-current', 'page');
                        link.parentElement.classList.add('current-menu-item');
                    } else { link.removeAttribute('aria-current'); }
                });
            });
            layout();
        }

        toggle.addEventListener('click', () => overlay.hidden ? open() : close());
        overlay.querySelectorAll('[data-cms-menu-close]').forEach(button => button.addEventListener('click', () => close()));
        roots.forEach(root => {
            root.addEventListener('click', event => {
                const button = event.target.closest('.cms-submenu-toggle,.cms-menu-group');
                if (button) { expand(button.parentElement, button.getAttribute('aria-expanded') !== 'true'); return; }
                if (root === drawer && event.target.closest('a[href]')) close();
            });
        });
        desktop.querySelectorAll('.cms-menu-list > li.menu-item-has-children').forEach(item => {
            item.addEventListener('pointerenter', event => { if (event.pointerType === 'mouse' && !document.body.classList.contains('cms-menu-compact')) expand(item, true); });
            item.addEventListener('pointerleave', () => { if (!item.contains(document.activeElement)) expand(item, false); });
        });
        desktop.addEventListener('focusout', event => {
            desktop.querySelectorAll('.cms-menu-list > .cms-submenu-open').forEach(item => { if (!item.contains(event.relatedTarget)) expand(item, false); });
        });
        document.addEventListener('click', event => {
            if (!desktop.contains(event.target)) desktop.querySelectorAll('.cms-menu-list > .cms-submenu-open').forEach(item => expand(item, false));
        });
        document.addEventListener('keydown', event => {
            if (!overlay.hidden) {
                if (event.key === 'Escape') { event.preventDefault(); event.stopImmediatePropagation(); close(); }
                if (event.key === '/' && !event.target.closest('input,textarea,[contenteditable]')) {
                    event.preventDefault(); event.stopImmediatePropagation(); document.getElementById('cms-menu-search').focus();
                }
                if (event.key === 'Tab') {
                    const items = [...panel.querySelectorAll('a[href],button:not([disabled]),input:not([disabled])')].filter(element => element.getClientRects().length);
                    const first = items[0], last = items[items.length - 1];
                    if (event.shiftKey && (document.activeElement === first || !panel.contains(document.activeElement))) { event.preventDefault(); last.focus(); }
                    else if (!event.shiftKey && (document.activeElement === last || !panel.contains(document.activeElement))) { event.preventDefault(); first.focus(); }
                }
            } else if (event.key === 'Escape' && desktop.contains(event.target)) {
                const item = event.target.closest('.cms-submenu-open');
                if (item) { event.preventDefault(); expand(item, false); control(item).focus(); }
            }
        }, true);
        if ('ResizeObserver' in window) {
            const observer = new ResizeObserver(requestLayout);
            [bar, desktop.querySelector('.cms-menu-list'), bar.querySelector('.header-actions')].filter(Boolean).forEach(element => observer.observe(element));
        }
        window.addEventListener('resize', requestLayout);
        document.fonts?.ready.then(requestLayout);
        window.cmsEditorialNavigation = { close, sync };
        sync();
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
})();
