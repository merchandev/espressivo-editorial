jQuery(function ($) {
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
