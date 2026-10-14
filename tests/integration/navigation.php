<?php
/** Comprueba el HTML de los menús nativos con tres niveles y ubicaciones distintas. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI || wp_get_environment_type() !== 'local' || ! current_user_can( 'manage_options' ) ) {
    throw new RuntimeException( 'Esta prueba requiere WP-CLI, un administrador y WP_ENVIRONMENT_TYPE=local.' );
}
$menu_id = wp_create_nav_menu( 'cms-navigation-qa-' . wp_generate_password( 8, false ) );
if ( is_wp_error( $menu_id ) ) { throw new RuntimeException( $menu_id->get_error_message() ); }
try {
    $parent = wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => 'Sección', 'menu-item-url' => home_url( '/seccion-de-prueba/' ), 'menu-item-status' => 'publish' ) );
    $child = wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => 'Subsección', 'menu-item-url' => '#', 'menu-item-parent-id' => $parent, 'menu-item-status' => 'publish' ) );
    wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => 'Tercer nivel', 'menu-item-url' => home_url( '/historia-de-prueba/' ), 'menu-item-parent-id' => $child, 'menu-item-status' => 'publish' ) );
    $html = '';
    foreach ( array( 'qa-desktop', 'qa-drawer' ) as $id ) {
        $html .= wp_nav_menu( array( 'menu' => $menu_id, 'container' => false, 'menu_id' => $id, 'echo' => false, 'walker' => new CMS_Editorial_Nav_Walker(), 'cms_editorial_navigation' => true ) );
    }
    $dom = new DOMDocument();
    $dom->loadHTML( '<!doctype html><html><body>' . $html . '</body></html>' );
    $xpath = new DOMXPath( $dom );
    $ids = array();
    foreach ( $xpath->query( '//*[@id]' ) as $node ) { $ids[] = $node->getAttribute( 'id' ); }
    if ( count( $ids ) !== count( array_unique( $ids ) ) ) { throw new RuntimeException( 'IDs duplicados entre las dos ubicaciones.' ); }
    $controls = $xpath->query( '//button[@aria-controls]' );
    if ( $controls->length !== 4 ) { throw new RuntimeException( 'Faltan controles para los dos niveles desplegables.' ); }
    foreach ( $controls as $control ) {
        $target = $xpath->query( '//ul[@id="' . $control->getAttribute( 'aria-controls' ) . '"]' );
        if ( $target->length !== 1 || $control->getAttribute( 'aria-expanded' ) !== 'false' ) { throw new RuntimeException( 'Control de subsección sin destino único o estado inicial.' ); }
    }
    if ( $xpath->query( '//a[@href="' . home_url( '/seccion-de-prueba/' ) . '"]' )->length !== 2 || strpos( $html, 'javascript:' ) !== false || strpos( $html, 'dropdown-arrow' ) !== false ) {
        throw new RuntimeException( 'El menú altera los enlaces o duplica los indicadores antiguos.' );
    }
    WP_CLI::success( 'Menús nativos de tres niveles: enlaces, controles, destinos e IDs únicos verificados.' );
} finally { wp_delete_nav_menu( $menu_id ); }
