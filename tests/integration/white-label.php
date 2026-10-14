<?php
/** Ejecutar con WP-CLI en una instalación local desechable. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI || wp_get_environment_type() !== 'local' ) {
    throw new RuntimeException( 'Esta prueba requiere WP-CLI y WP_ENVIRONMENT_TYPE=local.' );
}
require_once ABSPATH . 'wp-admin/includes/post.php';
require_once ABSPATH . 'wp-admin/includes/taxonomy.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

function cms_qa_assert( $condition, $label ) {
    if ( ! $condition ) { throw new RuntimeException( $label ); }
    WP_CLI::log( 'OK: ' . $label );
}

$admins = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
if ( ! $admins ) { throw new RuntimeException( 'Se requiere un administrador local.' ); }
$previous_user = get_current_user_id();
$original = array();
foreach ( array( 'cms_editorial_settings', 'blogname', 'blogdescription', 'site_icon', 'timezone_string' ) as $key ) { $original[ $key ] = get_option( $key, null ); }
$logo = get_theme_mod( 'custom_logo', 0 );
$posts = array();
$author = 0;
try {
    wp_set_current_user( $admins[0]->ID );
    $settings = cms_editorial_sanitize_settings( array( 'color_accent' => '#123456', 'color_nav_bg' => '#345678', 'color_banner_bg' => '#aabbcc', 'color_text' => 'red;}</style><script>alert(1)</script>', 'radio_stream' => 'javascript:alert(1)', 'social_facebook' => array( 'bad' ), 'footer_text' => '<script>alert(1)</script>Texto', 'contact_email' => 'reader@example.test' ) );
    cms_qa_assert( $settings['color_text'] === '#1f2937' && $settings['radio_stream'] === '' && $settings['social_facebook'] === '', 'Valida colores, protocolos y valores de formulario' );
    cms_qa_assert( strpos( $settings['footer_text'], '<script>' ) === false, 'El texto de marca no admite HTML ejecutable' );
    update_option( 'cms_editorial_settings', $settings );
    $css = cms_editorial_frontend_css();
    cms_qa_assert( strpos( $css, '--cms-banner-bg:#aabbcc' ) !== false && strpos( $css, '--cms-nav-bg:#345678' ) !== false && strpos( $css, '--cms-accent:#123456' ) !== false, 'Paleta guardada alimenta banners, navegación y acentos' );
    update_option( 'blogname', 'Publicación de prueba' );
    update_option( 'blogdescription', 'Descripción editable' );
    update_option( 'timezone_string', 'Europe/Madrid' );
    set_theme_mod( 'custom_logo', 0 );
    ob_start(); cms_editorial_render_branding(); $html = ob_get_clean();
    cms_qa_assert( strpos( $html, 'Publicación de prueba' ) !== false && strpos( $html, '<img' ) === false, 'Sin logo muestra el título nativo, sin imagen predefinida' );
    cms_qa_assert( wp_timezone_string() === 'Europe/Madrid', 'Respeta la zona horaria de WordPress' );
    cms_qa_assert( ! cms_editorial_radio_available(), 'Radio desactivada sin configuración' );
    ob_start(); cms_editorial_settings_page(); $html = ob_get_clean();
    cms_qa_assert( strpos( $html, 'settings[color_banner_bg]' ) !== false && strpos( $html, 'identity[site_icon]' ) !== false && strpos( $html, 'identity[custom_logo]' ) !== false, 'Panel administrativo incluye banners, favicon y logo' );
    $upload = wp_upload_bits( 'cms-qa-logo.png', null, base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a53sAAAAASUVORK5CYII=' ) );
    cms_qa_assert( empty( $upload['error'] ), 'Crea imagen local de prueba' );
    $attachment = wp_insert_attachment( array( 'post_title' => 'Logo de prueba', 'post_mime_type' => 'image/png', 'post_status' => 'inherit' ), $upload['file'] );
    $posts[] = $attachment;
    set_theme_mod( 'custom_logo', $attachment );
    update_option( 'site_icon', $attachment );
    cms_qa_assert( cms_editorial_logo_id( 'admin' ) === $attachment && cms_editorial_logo_id( 'report' ) === $attachment, 'Panel y reportes heredan el logo configurado' );
    cms_qa_assert( strpos( cms_editorial_report_logo_data(), 'data:image/png;base64,' ) === 0 && get_site_icon_url(), 'Logo de PDF y favicon provienen de la biblioteca' );
    ob_start(); cms_editorial_render_branding(); $html = ob_get_clean();
    cms_qa_assert( strpos( $html, '<img' ) !== false, 'Logo principal configurado aparece en cabecera' );

    $author = wp_insert_user( array( 'user_login' => 'cms_qa_' . wp_generate_password( 8, false ), 'user_pass' => wp_generate_password(), 'role' => 'author' ) );
    cms_qa_assert( ! is_wp_error( $author ), 'Crea perfil de autor de prueba' );
    foreach ( range( 1, 24 ) as $i ) {
        $posts[] = wp_insert_post( array( 'post_title' => 'Borrador ' . $i, 'post_content' => 'Contenido de prueba.', 'post_author' => $author, 'post_status' => 'draft' ) );
    }
    $batch = array_slice( $posts, 1 );
    update_post_meta( $batch[1], '_pro_firma_autor', 'Firma existente' );
    $_POST = array();
    bulk_edit_posts( array( 'post_type' => 'post', 'post' => $batch, '_status' => 'publish', 'post_author' => '-1', 'comment_status' => '', 'ping_status' => '' ) );
    foreach ( $batch as $index => $id ) {
        cms_qa_assert( get_post_status( $id ) === 'publish' && get_post_meta( $id, '_pro_firma_autor', true ) === ( $index === 1 ? 'Firma existente' : 'Departamento de redacción' ), 'Administrador publica borrador ' . $id . ' con firma correcta' );
    }
    wp_set_current_user( $author );
    $unsigned = wp_insert_post( array( 'post_title' => 'Autor sin firma', 'post_author' => $author, 'post_status' => 'publish' ) );
    $posts[] = $unsigned;
    cms_qa_assert( get_post_status( $unsigned ) === 'draft', 'Autor sigue obligado a firmar' );
    ob_start(); cms_editorial_settings_page(); $html = ob_get_clean();
    cms_qa_assert( $html === '', 'Autor no puede acceder a ajustes de marca' );
    WP_CLI::success( 'Integración de marca blanca y publicación en lotes verificada.' );
} finally {
    wp_set_current_user( $admins[0]->ID );
    foreach ( $posts as $id ) {
        if ( get_post_type( $id ) === 'attachment' ) { wp_delete_attachment( $id, true ); } else { wp_delete_post( $id, true ); }
    }
    if ( $author && ! is_wp_error( $author ) ) { require_once ABSPATH . 'wp-admin/includes/user.php'; wp_delete_user( $author ); }
    foreach ( $original as $key => $value ) { if ( $value === null ) { delete_option( $key ); } else { update_option( $key, $value ); } }
    set_theme_mod( 'custom_logo', $logo );
    wp_set_current_user( $previous_user );
}
