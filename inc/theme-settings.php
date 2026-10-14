<?php
/** Identidad y apariencia administrables de CMS-Editorial. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function cms_editorial_fields() {
    $fields = array(
        'logo_dark' => array( 'Logo para modo oscuro', 'image', '', 'Identidad' ),
        'logo_admin' => array( 'Logo del acceso y del panel', 'image', '', 'Identidad' ),
        'logo_footer' => array( 'Logo del pie de página', 'image', '', 'Identidad' ),
        'logo_report' => array( 'Logo para reportes PDF', 'image', '', 'Identidad' ),
        'social_image' => array( 'Imagen predeterminada al compartir', 'image', '', 'Identidad' ),
        'seo_suffix' => array( 'Sufijo de títulos SEO (vacío: título del sitio)', 'text', '', 'Identidad' ),
        'footer_text' => array( 'Texto del pie de página', 'textarea', '', 'Identidad' ),
        'contact_email' => array( 'Correo de contacto público', 'email', '', 'Contacto y redes' ),
        'contact_phone' => array( 'Teléfono', 'text', '', 'Contacto y redes' ),
        'contact_address' => array( 'Dirección', 'textarea', '', 'Contacto y redes' ),
        'social_facebook' => array( 'Facebook', 'url', '', 'Contacto y redes' ),
        'social_instagram' => array( 'Instagram', 'url', '', 'Contacto y redes' ),
        'social_twitter' => array( 'X / Twitter', 'url', '', 'Contacto y redes' ),
        'social_telegram' => array( 'Telegram', 'url', '', 'Contacto y redes' ),
        'social_whatsapp' => array( 'WhatsApp', 'url', '', 'Contacto y redes' ),
        'google_news' => array( 'Página de la publicación en Google Noticias', 'url', '', 'Contacto y redes' ),
        'radio_enabled' => array( 'Mostrar reproductor de radio', 'checkbox', false, 'Radio' ),
        'radio_name' => array( 'Nombre de la radio', 'text', 'Radio en vivo', 'Radio' ),
        'radio_description' => array( 'Descripción de la radio', 'text', '', 'Radio' ),
        'radio_stream' => array( 'URL de transmisión (HTTPS)', 'url', '', 'Radio' ),
        'local_categories' => array( 'Secciones locales de portada (slugs separados por comas)', 'slugs', 'local,comunidad', 'Portada' ),
        'ad_placeholders' => array( 'Mostrar espacios publicitarios vacíos', 'checkbox', true, 'Publicidad' ),
    );
    $colors = array(
        'bg' => array( 'Fondo de página', '#ffffff' ),
        'bg_secondary' => array( 'Fondo secundario', '#f8fafc' ),
        'text' => array( 'Texto principal', '#1f2937' ),
        'text_muted' => array( 'Texto secundario', '#64748b' ),
        'primary' => array( 'Color principal y enlaces', '#334155' ),
        'primary_hover' => array( 'Enlaces al pasar el cursor', '#0f172a' ),
        'accent' => array( 'Acentos y destacados', '#2563eb' ),
        'accent_alt' => array( 'Acento alternativo', '#0d9488' ),
        'border' => array( 'Bordes', '#e2e8f0' ),
        'nav_bg' => array( 'Fondo de navegación', '#0f172a' ),
        'nav_text' => array( 'Texto de navegación', '#ffffff' ),
        'footer_bg' => array( 'Fondo del pie de página', '#0f172a' ),
        'footer_text' => array( 'Texto del pie de página', '#f8fafc' ),
        'banner_bg' => array( 'Fondo de banners y espacios publicitarios', '#f1f5f9' ),
        'banner_text' => array( 'Texto de espacios publicitarios', '#475569' ),
        'ticker_bg' => array( 'Fondo de última hora', '#2563eb' ),
        'ticker_text' => array( 'Texto de última hora', '#ffffff' ),
        'button_bg' => array( 'Fondo de botones y reproductores', '#2563eb' ),
        'button_text' => array( 'Texto de botones', '#ffffff' ),
        'admin_primary' => array( 'Color principal del administrador', '#0f172a' ),
        'admin_accent' => array( 'Acento del administrador', '#2563eb' ),
        'dark_bg' => array( 'Modo oscuro: fondo', '#0f172a' ),
        'dark_secondary' => array( 'Modo oscuro: fondo secundario', '#1e293b' ),
        'dark_text' => array( 'Modo oscuro: texto', '#f8fafc' ),
        'dark_muted' => array( 'Modo oscuro: texto secundario', '#cbd5e1' ),
        'dark_accent' => array( 'Modo oscuro: acento', '#60a5fa' ),
        'dark_border' => array( 'Modo oscuro: bordes', '#334155' ),
    );
    foreach ( $colors as $key => $color ) {
        $fields[ 'color_' . $key ] = array( $color[0], 'color', $color[1], 'Colores' );
    }
    return $fields;
}

function cms_editorial_setting( $key ) {
    $fields = cms_editorial_fields();
    $settings = get_option( 'cms_editorial_settings', array() );
    if ( ! array_key_exists( $key, $settings ) && strpos( $key, 'social_' ) === 0 ) {
        $legacy = get_theme_mod( 'pro_' . $key, '' );
        if ( $legacy && $legacy !== '#' ) { return esc_url_raw( $legacy, array( 'http', 'https' ) ); }
    }
    if ( $key === 'ad_placeholders' && ! array_key_exists( $key, $settings ) ) { return get_theme_mod( 'pro_show_ad_placeholders', true ); }
    return $settings[ $key ] ?? ( $fields[ $key ][2] ?? '' );
}

add_filter( 'theme_mod_pro_show_ad_placeholders', function( $value ) {
    $settings = get_option( 'cms_editorial_settings', array() );
    return $settings['ad_placeholders'] ?? $value;
} );

function cms_editorial_sanitize_settings( $input ) {
    $clean = array();
    $input = is_array( $input ) ? $input : array();
    foreach ( cms_editorial_fields() as $key => $field ) {
        $value = $input[ $key ] ?? $field[2];
        if ( ! is_scalar( $value ) ) { $value = $field[2]; }
        switch ( $field[1] ) {
            case 'color': $value = sanitize_hex_color( $value ) ?: $field[2]; break;
            case 'image':
                $value = absint( $value );
                $value = $value && wp_attachment_is_image( $value ) ? $value : 0;
                break;
            case 'url': $value = esc_url_raw( $value, array( 'http', 'https' ) ); break;
            case 'email': $value = sanitize_email( $value ); break;
            case 'textarea': $value = sanitize_textarea_field( $value ); break;
            case 'checkbox': $value = rest_sanitize_boolean( $value ); break;
            case 'slugs': $value = implode( ',', array_filter( array_map( 'sanitize_title', explode( ',', $value ) ) ) ); break;
            default: $value = sanitize_text_field( $value );
        }
        $clean[ $key ] = $value;
    }
    return $clean;
}

function cms_editorial_logo_id( $context = 'default' ) {
    $logo = $context === 'default' ? 0 : absint( cms_editorial_setting( 'logo_' . $context ) );
    return $logo ?: absint( get_theme_mod( 'custom_logo' ) );
}

function cms_editorial_logo_url( $context = 'default' ) {
    return wp_get_attachment_image_url( cms_editorial_logo_id( $context ), 'full' ) ?: '';
}

function cms_editorial_render_branding( $context = 'default' ) {
    $id = cms_editorial_logo_id( $context );
    echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="custom-logo-link" rel="home">';
    if ( $id ) {
        $classes = $context === 'default' && cms_editorial_setting( 'logo_dark' ) ? 'custom-logo logo-light' : 'custom-logo';
        echo wp_get_attachment_image( $id, 'full', false, array( 'class' => $classes, 'alt' => get_bloginfo( 'name' ), 'loading' => 'eager' ) );
        if ( $context === 'default' && cms_editorial_setting( 'logo_dark' ) ) {
            echo wp_get_attachment_image( cms_editorial_logo_id( 'dark' ), 'full', false, array( 'class' => 'custom-logo logo-dark', 'alt' => get_bloginfo( 'name' ), 'loading' => 'eager' ) );
        }
    } else {
        echo '<span class="site-title">' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
    }
    echo '</a>';
}

function cms_editorial_radio_available() {
    return cms_editorial_setting( 'radio_enabled' ) && cms_editorial_setting( 'radio_stream' );
}

add_action( 'admin_menu', function() {
    add_theme_page( 'CMS-Editorial', 'CMS-Editorial', 'manage_options', 'cms-editorial', 'cms_editorial_settings_page' );
} );

add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( $hook !== 'appearance_page_cms-editorial' ) { return; }
    wp_enqueue_media();
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'cms-editorial-settings', get_template_directory_uri() . '/assets/js/theme-settings.js', array( 'jquery', 'wp-color-picker' ), '3.0.0', true );
} );

function cms_editorial_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    echo '<div class="wrap"><h1>CMS-Editorial</h1><p>Configura la identidad y apariencia de tu publicación. Los colores afectan al diseño de los banners; las imágenes publicitarias se gestionan en Publicidad.</p>';
    if ( isset( $_GET['updated'] ) ) { echo '<div class="notice notice-success"><p>Ajustes guardados.</p></div>'; }
    echo '<p><a href="' . esc_url( admin_url( 'customize.php' ) ) . '">Abrir personalizador con vista previa</a> · <a href="' . esc_url( admin_url( 'options-general.php' ) ) . '">Idioma y zona horaria</a></p>';
    echo '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="post"><input type="hidden" name="action" value="cms_editorial_save_settings">';
    wp_nonce_field( 'cms_editorial_save_settings' );
    $native = array(
        'blogname' => array( 'Título del sitio', 'text', get_option( 'blogname' ), 'Identidad' ),
        'blogdescription' => array( 'Descripción del sitio', 'textarea', get_option( 'blogdescription' ), 'Identidad' ),
        'custom_logo' => array( 'Logo principal', 'image', get_theme_mod( 'custom_logo', 0 ), 'Identidad' ),
        'site_icon' => array( 'Favicon / icono del sitio', 'image', get_option( 'site_icon', 0 ), 'Identidad' ),
    );
    $group = '';
    foreach ( array_merge( $native, cms_editorial_fields() ) as $key => $field ) {
        if ( $field[3] !== $group ) {
            if ( $group ) { echo '</tbody></table>'; }
            $group = $field[3];
            echo '<h2>' . esc_html( $group ) . '</h2><table class="form-table"><tbody>';
        }
        $value = isset( $native[ $key ] ) ? $field[2] : cms_editorial_setting( $key );
        $name = isset( $native[ $key ] ) ? 'identity[' . $key . ']' : 'settings[' . $key . ']';
        echo '<tr><th scope="row"><label for="cms-' . esc_attr( $key ) . '">' . esc_html( $field[0] ) . '</label></th><td>';
        if ( $field[1] === 'image' ) {
            $url = wp_get_attachment_image_url( absint( $value ), 'medium' );
            echo '<div class="cms-media-field"><input type="hidden" id="cms-' . esc_attr( $key ) . '" name="' . esc_attr( $name ) . '" value="' . absint( $value ) . '"><img alt="Vista previa" src="' . esc_url( $url ?: '' ) . '" style="max-width:220px;max-height:100px;display:' . ( $url ? 'block' : 'none' ) . '"><button type="button" class="button cms-media-select">Elegir imagen</button> <button type="button" class="button cms-media-remove">Quitar</button></div>';
        } elseif ( $field[1] === 'textarea' ) {
            echo '<textarea class="large-text" rows="3" id="cms-' . esc_attr( $key ) . '" name="' . esc_attr( $name ) . '">' . esc_textarea( $value ) . '</textarea>';
        } elseif ( $field[1] === 'checkbox' ) {
            echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="0"><input type="checkbox" id="cms-' . esc_attr( $key ) . '" name="' . esc_attr( $name ) . '" value="1" ' . checked( $value, true, false ) . '>';
        } else {
            $type = in_array( $field[1], array( 'email', 'url' ), true ) ? $field[1] : 'text';
            echo '<input type="' . esc_attr( $type ) . '" class="' . ( $field[1] === 'color' ? 'cms-color' : 'regular-text' ) . '" id="cms-' . esc_attr( $key ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
        }
        echo '</td></tr>';
    }
    echo '</tbody></table>';
    submit_button( 'Guardar ajustes' );
    echo '</form></div>';
}

function cms_editorial_save_settings() {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'No tienes permisos para modificar la configuración.' ); }
    check_admin_referer( 'cms_editorial_save_settings' );
    $input = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : array();
    update_option( 'cms_editorial_settings', cms_editorial_sanitize_settings( $input ) );
    $identity = isset( $_POST['identity'] ) && is_array( $_POST['identity'] ) ? wp_unslash( $_POST['identity'] ) : array();
    foreach ( array( 'blogname', 'blogdescription' ) as $key ) {
        if ( isset( $identity[ $key ] ) && is_string( $identity[ $key ] ) ) { update_option( $key, sanitize_text_field( $identity[ $key ] ) ); }
    }
    foreach ( array( 'custom_logo', 'site_icon' ) as $key ) {
        $id = absint( $identity[ $key ] ?? 0 );
        $id = $id && wp_attachment_is_image( $id ) ? $id : 0;
        if ( $key === 'custom_logo' ) { set_theme_mod( $key, $id ); } else { update_option( $key, $id ); }
    }
    wp_safe_redirect( admin_url( 'themes.php?page=cms-editorial&updated=1' ) );
    exit;
}
add_action( 'admin_post_cms_editorial_save_settings', 'cms_editorial_save_settings' );

// Mantener las páginas que ya tenían asignada la plantilla de radio anterior.
add_action( 'admin_init', function() {
    if ( ! current_user_can( 'manage_options' ) || get_option( 'cms_editorial_templates_v3' ) ) { return; }
    global $wpdb;
    $pages = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s", '_wp_page_template', 'page-vital-101-5fm.php' ) );
    foreach ( $pages as $id ) { update_post_meta( $id, '_wp_page_template', 'page-radio.php' ); }
    update_option( 'cms_editorial_templates_v3', true, false );
} );

add_action( 'customize_register', function( $customize ) {
    $customize->add_panel( 'cms_editorial', array( 'title' => 'CMS-Editorial', 'priority' => 25 ) );
    $groups = array();
    foreach ( cms_editorial_fields() as $key => $field ) {
        $section = 'cms_editorial_' . sanitize_title( $field[3] );
        if ( ! isset( $groups[ $section ] ) ) {
            $customize->add_section( $section, array( 'title' => $field[3], 'panel' => 'cms_editorial' ) );
            $groups[ $section ] = true;
        }
        $name = 'cms_editorial_settings[' . $key . ']';
        $sanitize = function( $value ) use ( $key ) { return cms_editorial_sanitize_settings( array( $key => $value ) )[ $key ]; };
        $customize->add_setting( $name, array( 'type' => 'option', 'default' => $field[2], 'sanitize_callback' => $sanitize, 'capability' => 'manage_options' ) );
        $args = array( 'label' => $field[0], 'section' => $section, 'settings' => $name );
        if ( $field[1] === 'color' ) {
            $customize->add_control( new WP_Customize_Color_Control( $customize, $name, $args ) );
        } elseif ( $field[1] === 'image' ) {
            $customize->add_control( new WP_Customize_Media_Control( $customize, $name, array_merge( $args, array( 'mime_type' => 'image' ) ) ) );
        } else {
            $customize->add_control( $name, array_merge( $args, array( 'type' => $field[1] === 'slugs' ? 'text' : $field[1] ) ) );
        }
    }
} );

function cms_editorial_frontend_css() {
    $vars = '';
    foreach ( cms_editorial_fields() as $key => $field ) {
        if ( $field[1] === 'color' ) {
            $vars .= '--cms-' . str_replace( '_', '-', substr( $key, 6 ) ) . ':' . ( sanitize_hex_color( cms_editorial_setting( $key ) ) ?: $field[2] ) . ';';
        }
    }
    return ':root{' . $vars . '}
    body{--color-bg:var(--cms-bg);--color-bg-secondary:var(--cms-bg-secondary);--color-text:var(--cms-text);--color-text-muted:var(--cms-text-muted);--color-primary:var(--cms-primary);--color-primary-hover:var(--cms-primary-hover);--color-accent:var(--cms-accent);--color-accent-alt:var(--cms-accent-alt);--color-border:var(--cms-border);background-color:var(--color-bg);color:var(--color-text)}
    body.dark-mode{--color-bg:var(--cms-dark-bg);--color-bg-secondary:var(--cms-dark-secondary);--color-text:var(--cms-dark-text);--color-text-muted:var(--cms-dark-muted);--color-primary:var(--cms-dark-text);--color-primary-hover:var(--cms-dark-accent);--color-accent:var(--cms-dark-accent);--color-border:var(--cms-dark-border)}
    .site-header .header-navigation-wrapper{background:var(--cms-nav-bg)!important}
    .site-header .main-navigation a,.site-header .header-navigation-wrapper .menu-toggle{color:var(--cms-nav-text)!important}
    .site-header .main-navigation .sub-menu{background:var(--cms-nav-bg)!important}
    .site-footer{background:var(--cms-footer-bg);color:var(--cms-footer-text)}
    .site-footer a,.site-footer p,.site-footer .footer-heading,.site-footer .site-title{color:var(--cms-footer-text)}
    .ad-placeholder,.header-ad,.in-feed-ad,.category-sponsor-banner,.sponsor-banner-placeholder,.sponsor-header{background:var(--cms-banner-bg)!important;color:var(--cms-banner-text)!important}
    .sponsor-section-name{background:var(--cms-accent)!important;color:var(--cms-button-text)!important}
    .site-main,#swup{background-color:var(--color-bg)}
    .site-main .entry-title a{color:var(--color-text)!important}
    .site-header .news-ticker-wrapper,.site-header .news-ticker-label{background:var(--cms-ticker-bg)!important;color:var(--cms-ticker-text)!important}
    .site-header .ticker-slide a{color:var(--cms-ticker-text)!important}
    .home-carteles-banner{background:var(--cms-banner-bg)!important;color:var(--cms-banner-text)}
    .carteles-banner-content .banner-title,.carteles-banner-content .banner-desc{color:var(--cms-banner-text)}
    .carteles-banner-content .banner-icon,.carteles-banner-content .banner-icon .material-symbols-outlined{color:var(--cms-accent);background:var(--cms-bg-secondary)}
    .button-primary,.btn-footer-denuncia,.btn-carteles,.es-float-btn,.es-resp-btn{background:var(--cms-button-bg)!important;color:var(--cms-button-text)!important}
    .radio-hero-section::before{background:linear-gradient(135deg,var(--cms-accent),var(--cms-nav-bg))}
    .reading-progress-bar,.es-float-bar,.es-resp-w,.es-resp-fill{background:var(--cms-accent)}
    .site-title{font-family:var(--font-heading);font-size:2rem;font-weight:700;color:var(--color-text)}
    .site-footer .custom-logo{max-width:220px;max-height:90px;object-fit:contain}
    .logo-dark{display:none!important}body.dark-mode .logo-dark{display:inline-block!important}';
}
add_action( 'wp_enqueue_scripts', function() {
    wp_add_inline_style( 'pro-style', cms_editorial_frontend_css() );
    // Fallback para instalaciones que usan otro handle de la hoja del tema.
    if ( ! wp_style_is( 'pro-style', 'enqueued' ) ) { wp_add_inline_style( 'pro-main-style', cms_editorial_frontend_css() ); }
    wp_localize_script( 'pro-main-js', 'cmsEditorial', array( 'radioStream' => cms_editorial_radio_available() ? cms_editorial_setting( 'radio_stream' ) : '' ) );
}, 100 );

add_action( 'enqueue_block_editor_assets', function() {
    if ( wp_script_is( 'ssivo-seo-editor', 'enqueued' ) ) {
        wp_localize_script( 'ssivo-seo-editor', 'cmsEditorialSEO', array(
            'suffix' => cms_editorial_setting( 'seo_suffix' ) ?: get_bloginfo( 'name' ),
            'image' => wp_get_attachment_image_url( absint( cms_editorial_setting( 'social_image' ) ), 'full' ) ?: cms_editorial_logo_url(),
        ) );
    }
}, 100 );

function cms_editorial_admin_brand_colors() {
    $primary = sanitize_hex_color( cms_editorial_setting( 'color_admin_primary' ) ) ?: '#0f172a';
    $accent = sanitize_hex_color( cms_editorial_setting( 'color_admin_accent' ) ) ?: '#2563eb';
    echo '<style>#wpadminbar,#adminmenu,#adminmenuback,#adminmenuwrap{background:' . esc_html( $primary ) . '!important}#adminmenu .wp-has-current-submenu a.wp-has-current-submenu,#adminmenu .current a.menu-top,.wp-core-ui .button-primary{background:' . esc_html( $accent ) . '!important;border-color:' . esc_html( $accent ) . '!important}</style>';
}
add_action( 'admin_head', 'cms_editorial_admin_brand_colors', 100 );

// Logos en reportes: solo archivos locales de la biblioteca, sin descargas externas.
function cms_editorial_report_logo_data() {
    $id = cms_editorial_logo_id( 'report' );
    $file = $id ? get_attached_file( $id ) : '';
    $mime = $id ? get_post_mime_type( $id ) : '';
    if ( ! $file || ! is_readable( $file ) || ! in_array( $mime, array( 'image/png', 'image/jpeg', 'image/gif' ), true ) ) { return ''; }
    return 'data:' . $mime . ';base64,' . base64_encode( file_get_contents( $file ) );
}
