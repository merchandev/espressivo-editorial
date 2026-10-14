(function () {
    'use strict';
    const settings = window.cmsSectionMigration;
    if (!settings) return;
    const notice = document.getElementById('cms-section-migration');
    async function next() {
        try {
            const body = new URLSearchParams({ action: 'cms_editorial_update_sections', nonce: settings.nonce });
            const response = await fetch(settings.url, { method: 'POST', credentials: 'same-origin', body });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.data?.message || 'No se pudo completar la actualización de secciones.');
            if (notice) notice.querySelector('p').textContent = result.data.complete ? 'Secciones actualizadas. Tus entradas se conservan.' : 'Actualizando secciones: ' + result.data.processed + ' entradas reorganizadas.';
            if (!result.data.complete) setTimeout(next, 350);
            else if (notice) { notice.classList.replace('notice-info', 'notice-success'); }
        } catch (error) {
            if (notice) { notice.classList.replace('notice-info', 'notice-error'); notice.querySelector('p').textContent = error.message; }
        }
    }
    next();
})();
