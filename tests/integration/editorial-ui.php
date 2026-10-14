<?php
/** Ejecutar con WP-CLI en una instalación local desechable. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI || wp_get_environment_type() !== 'local' || ! current_user_can( 'manage_options' ) ) {
    throw new RuntimeException( 'Esta prueba requiere WP-CLI, un administrador y WP_ENVIRONMENT_TYPE=local.' );
}

$original_settings = get_option( 'cms_editorial_settings', null );
$created_posts = array();
$created_terms = array();
$prefix = 'cms-ui-' . wp_generate_password( 8, false );
$original_server_name = $_SERVER['SERVER_NAME'] ?? null;
if ( $original_server_name === null ) { $_SERVER['SERVER_NAME'] = wp_parse_url( home_url(), PHP_URL_HOST ); }
try {
    $slugs = array( 'nacional', 'mundo', 'economia', 'sucesos', 'deportes', $prefix . '-local' );
    $settings = cms_editorial_sanitize_settings( array(
        'home_title' => 'Portada de prueba', 'home_featured_category' => $prefix . '-destacados',
        'home_sections' => implode( ',', $slugs ), 'local_categories' => '',
        'home_show_cover' => false, 'home_show_services' => false,
    ) );
    update_option( 'cms_editorial_settings', $settings );
    foreach ( array_merge( array( $settings['home_featured_category'] ), $slugs ) as $slug ) {
        $term = get_category_by_slug( $slug );
        if ( ! $term ) {
            $result = wp_insert_term( $slug, 'category', array( 'slug' => $slug ) );
            if ( is_wp_error( $result ) ) { throw new RuntimeException( $result->get_error_message() ); }
            $created_terms[] = $result['term_id'];
            $term_id = $result['term_id'];
        } else { $term_id = $term->term_id; }
        foreach ( range( 1, 3 ) as $index ) {
            $created_posts[] = wp_insert_post( array(
                'post_title' => $prefix . ' ' . $slug . ' ' . $index, 'post_content' => 'Una historia de prueba.',
                'post_status' => 'publish', 'post_category' => array( $term_id ),
            ) );
        }
    }
    $locations = array( 'below-hero', 'in-feed-1', 'in-feed-2', 'nacional-ad', 'internacional-ad', 'economia-ad', 'seguridad-ad', 'zona-deportiva-ad' );
    foreach ( $locations as $location ) {
        $created_posts[] = wp_insert_post( array(
            'post_type' => 'pro_ad_banner', 'post_title' => $prefix . ' ' . $location, 'post_status' => 'publish',
            'meta_input' => array( '_pro_ad_location' => $location, '_pro_ad_image_2' => 'https://example.test/' . $prefix . '.png' ),
        ) );
    }
    ob_start();
    include get_template_directory() . '/front-page.php';
    $html = ob_get_clean();
    foreach ( $locations as $location ) {
        if ( substr_count( $html, '<!-- PUBLICIDAD: ' . strtoupper( $location ) . ' -->' ) !== 1 ) {
            throw new RuntimeException( 'Ubicación ausente o repetida: ' . $location );
        }
    }
    if ( strpos( $html, 'Portada de prueba' ) === false || strpos( $html, 'ed-services-panel' ) !== false || strpos( $html, 'ed-cover-trigger' ) !== false ) {
        throw new RuntimeException( 'La portada no respeta sus módulos configurados.' );
    }
    preg_match_all( '/<article id="([^"]+)"/', $html, $matches );
    if ( count( $matches[1] ) !== count( array_unique( $matches[1] ) ) ) { throw new RuntimeException( 'IDs de artículos repetidos.' ); }
    ob_start(); cms_editorial_ad_slot( $prefix . '-vacio' ); $empty = ob_get_clean();
    if ( $empty !== '' ) { throw new RuntimeException( 'Un anuncio vacío deja un hueco.' ); }
    WP_CLI::success( 'Portada configurable, IDs únicos y ocho ubicaciones publicitarias verificados.' );
} finally {
    foreach ( $created_posts as $id ) { wp_delete_post( $id, true ); }
    foreach ( $created_terms as $id ) { wp_delete_term( $id, 'category' ); }
    if ( $original_settings === null ) { delete_option( 'cms_editorial_settings' ); }
    else { update_option( 'cms_editorial_settings', $original_settings ); }
    if ( $original_server_name === null ) { unset( $_SERVER['SERVER_NAME'] ); }
    eo_clear_editorial_cache();
    wp_reset_postdata();
}
