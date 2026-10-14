jQuery(function ($) {
    const tabs = $('.cms-settings-tab');
    const panels = $('.cms-settings-section');
    function selectTab(id, focus) {
        const tab = tabs.filter('[aria-controls="' + id + '"]');
        if (!tab.length) return;
        tabs.attr({ 'aria-selected': 'false', tabindex: '-1' });
        tab.attr({ 'aria-selected': 'true', tabindex: '0' });
        panels.prop('hidden', true);
        $('#' + id).prop('hidden', false);
        try { localStorage.setItem('cms-settings-tab', id); } catch (e) {}
        if (focus) tab.trigger('focus');
    }
    if (tabs.length) {
        let saved;
        try { saved = localStorage.getItem('cms-settings-tab'); } catch (e) {}
        selectTab(panels.filter((_, panel) => panel.id === saved).length ? saved : tabs.first().attr('aria-controls'));
        tabs.on('click', function () { selectTab($(this).attr('aria-controls')); });
        tabs.on('keydown', function (event) {
            const index = tabs.index(this);
            let next;
            if (event.key === 'ArrowDown' || event.key === 'ArrowRight') next = (index + 1) % tabs.length;
            if (event.key === 'ArrowUp' || event.key === 'ArrowLeft') next = (index + tabs.length - 1) % tabs.length;
            if (event.key === 'Home') next = 0;
            if (event.key === 'End') next = tabs.length - 1;
            if (next !== undefined) { event.preventDefault(); selectTab(tabs.eq(next).attr('aria-controls'), true); }
        });
        document.getElementById('cms-theme-settings').addEventListener('invalid', function (event) {
            const panel = event.target.closest('.cms-settings-section');
            if (panel) selectTab(panel.id);
        }, true);
    }
    $('.cms-color').wpColorPicker();
    $('.cms-media-select').on('click', function () {
        const field = $(this).closest('.cms-media-field');
        const frame = wp.media({ title: 'Elegir imagen', button: { text: 'Usar imagen' }, library: { type: 'image' }, multiple: false });
        frame.on('select', function () {
            const image = frame.state().get('selection').first().toJSON();
            field.find('input').val(image.id);
            field.find('img').attr('src', image.sizes && image.sizes.medium ? image.sizes.medium.url : image.url).show();
        });
        frame.open();
    });
    $('.cms-media-remove').on('click', function () {
        const field = $(this).closest('.cms-media-field');
        field.find('input').val('0');
        field.find('img').attr('src', '').hide();
    });
});
