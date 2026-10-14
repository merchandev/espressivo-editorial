<?php
/** Estructura inicial de CMS-Editorial y migración única de las secciones anteriores. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function cms_editorial_sections() {
    return array( 'nacional' => 'Nacional', 'deportes' => 'Deportes', 'internacional' => 'Internacional', 'farandula' => 'Farándula', 'ciencias' => 'Ciencias', 'genericas' => 'Genéricas' );
}

function cms_editorial_section_aliases() {
    return array(
        'mundo' => 'internacional', 'internacion' => 'internacional', 'analisis-internacional' => 'internacional', 'bitacora-mundial' => 'internacional',
        'zona-deportiva' => 'deportes', 'artes-y-espectaculos' => 'farandula', 'cultura-y-entretenimiento' => 'farandula', 'entretenimiento' => 'farandula', 'cine' => 'farandula', 'streaming' => 'farandula', 'cultura' => 'farandula', 'literatura' => 'farandula',
        'ciencia-y-tecnologia' => 'ciencias', 'ciencia' => 'ciencias', 'inteligencia-artificial' => 'ciencias', 'robotica' => 'ciencias',
        'politica-nacional' => 'nacional', 'asamblea-nacional' => 'nacional', 'regiones' => 'nacional',
    );
}

function cms_editorial_section_url( $slug ) {
    $page = get_page_by_path( $slug );
    if ( $page && $page->post_status === 'publish' && get_page_template_slug( $page ) === 'page-categoria.php' ) { return get_permalink( $page ); }
    $term = get_category_by_slug( $slug );
    return $term ? get_category_link( $term ) : home_url( '/' );
}

/** Guardar el estado anterior fuera de las opciones que WordPress carga en cada visita. */
function cms_editorial_backup_sections( $terms ) {
    if ( get_option( 'cms_editorial_sections_backup_v1' ) ) { return; }
    global $wpdb;
    $relationships = $wpdb->get_results( "SELECT r.object_id,t.term_id FROM {$wpdb->term_relationships} r INNER JOIN {$wpdb->term_taxonomy} t ON r.term_taxonomy_id=t.term_taxonomy_id WHERE t.taxonomy='category'", ARRAY_A );
    $menus = array();
    foreach ( wp_get_nav_menus() as $menu ) { $menus[] = array( 'term' => (array) $menu, 'items' => wp_get_nav_menu_items( $menu->term_id ) ); }
    $pages = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'numberposts' => -1, 'meta_key' => '_wp_page_template', 'meta_value' => 'page-categoria.php' ) );
    $ad_metadata = array();
    foreach ( get_posts( array( 'post_type' => array( 'pro_ad_banner', 'pro_cat_banner' ), 'post_status' => 'any', 'numberposts' => -1 ) ) as $banner ) { $ad_metadata[ $banner->ID ] = get_post_meta( $banner->ID ); }
    add_option( 'cms_editorial_sections_backup_v1', array(
        'created_at' => gmdate( 'c' ), 'terms' => $terms, 'relationships' => $relationships, 'menus' => $menus, 'pages' => $pages,
        'locations' => get_theme_mod( 'nav_menu_locations' ), 'default_category' => get_option( 'default_category' ), 'settings' => get_option( 'cms_editorial_settings' ), 'ad_metadata' => $ad_metadata,
    ), '', false );
}

function cms_editorial_structure_result( $result ) {
    if ( is_wp_error( $result ) ) { throw new RuntimeException( $result->get_error_message() ); }
    if ( ! $result ) { throw new RuntimeException( 'No se pudo actualizar la estructura editorial.' ); }
    return $result;
}

function cms_editorial_structure_menu( $location, $name, $pages, $reserved = 0 ) {
    $locations = get_theme_mod( 'nav_menu_locations', array() );
    $menu = ! empty( $locations[ $location ] ) ? wp_get_nav_menu_object( $locations[ $location ] ) : wp_get_nav_menu_object( $name );
    if ( $menu && $menu->term_id === $reserved ) { $menu = false; }
    $id = $menu ? $menu->term_id : cms_editorial_structure_result( wp_create_nav_menu( $name ) );
    cms_editorial_structure_result( wp_update_term( $id, 'nav_menu', array( 'name' => $name ) ) );
    foreach ( wp_get_nav_menu_items( $id ) ?: array() as $item ) { wp_delete_post( $item->ID, true ); }
    cms_editorial_structure_result( wp_update_nav_menu_item( $id, 0, array( 'menu-item-title' => 'Inicio', 'menu-item-url' => home_url( '/' ), 'menu-item-status' => 'publish' ) ) );
    foreach ( cms_editorial_sections() as $slug => $title ) {
        cms_editorial_structure_result( wp_update_nav_menu_item( $id, 0, array( 'menu-item-title' => $title, 'menu-item-object-id' => $pages[ $slug ], 'menu-item-object' => 'page', 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) ) );
    }
    return $id;
}

/** Se ejecuta una vez al actualizar/activar el tema, desde una sesión de administrador. */
function cms_editorial_install_structure( $from_ajax = false ) {
    if ( get_option( 'cms_editorial_sections_v1' ) || ! current_user_can( 'manage_options' ) || ( wp_doing_ajax() && ! $from_ajax ) || wp_doing_cron() ) { return false; }
    if ( ! pro_install_lock_acquire() ) { return false; }
    try {
        if ( pro_install_already_done( 'cms_editorial_sections_v1' ) ) { return false; }
        global $wpdb;
        $state = get_option( 'cms_editorial_sections_pending', array() );
        if ( ! $state ) {
            $original = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false ) );
            if ( is_wp_error( $original ) ) { throw new RuntimeException( $original->get_error_message() ); }
            cms_editorial_backup_sections( $original );
            $aliases = cms_editorial_section_aliases();
            $original_by_slug = array();
            foreach ( $original as $term ) { $original_by_slug[ $term->slug ] = $term; }
            $terms = array();
            $redirects = get_option( 'cms_editorial_section_redirects', array() );
            foreach ( cms_editorial_sections() as $slug => $name ) {
                $term = get_category_by_slug( $slug );
                if ( ! $term ) {
                    foreach ( $aliases as $alias => $target ) {
                        if ( $target === $slug && isset( $original_by_slug[ $alias ] ) ) { $term = $original_by_slug[ $alias ]; break; }
                    }
                    if ( ! $term && $slug === 'genericas' ) { $term = $original_by_slug['uncategorized'] ?? $original_by_slug['sin-categoria'] ?? null; }
                }
                if ( $term ) {
                    cms_editorial_structure_result( wp_update_term( $term->term_id, 'category', array( 'name' => $name, 'slug' => $slug, 'parent' => 0 ) ) );
                    $terms[ $slug ] = (int) $term->term_id;
                    if ( $term->slug !== $slug ) { $redirects[ $term->slug ] = $slug; }
                } else {
                    $created = cms_editorial_structure_result( wp_insert_term( $name, 'category', array( 'slug' => $slug ) ) );
                    $terms[ $slug ] = (int) $created['term_id'];
                }
            }
            update_option( 'default_category', $terms['genericas'] );
            $ids = array_values( $terms );
            $term_mapping = array_combine( $ids, $ids );
            $old_by_id = array();
            foreach ( $original as $term ) { $old_by_id[ $term->term_id ] = $term; }
            foreach ( $original as $term ) {
                if ( in_array( (int) $term->term_id, $ids, true ) ) { continue; }
                $slug = $aliases[ $term->slug ] ?? 'genericas';
                // Las antiguas subsecciones conservan la sección superior cuando sigue existiendo.
                $parent = (int) $term->parent;
                $visited = array();
                while ( $slug === 'genericas' && $parent && ! in_array( $parent, $visited, true ) ) {
                    $visited[] = $parent;
                    $kept = array_search( $parent, $terms, true );
                    if ( $kept !== false ) { $slug = $kept; break; }
                    $parent = isset( $old_by_id[ $parent ] ) ? (int) $old_by_id[ $parent ]->parent : 0;
                }
                $redirects[ $term->slug ] = $slug;
                $term_mapping[ $term->term_id ] = $terms[ $slug ];
            }
            $state = array( 'original' => $original, 'terms' => $terms, 'term_mapping' => $term_mapping, 'redirects' => $redirects, 'processed' => 0 );
            update_option( 'cms_editorial_sections_pending', $state, false );
            update_option( 'cms_editorial_section_redirects', $redirects, false );
        }
        $original = $state['original']; $terms = $state['terms']; $term_mapping = $state['term_mapping']; $redirects = $state['redirects'];
        $aliases = cms_editorial_section_aliases();
        $ids = array_values( $terms );
        $retired = array_diff( array_keys( $term_mapping ), $ids );
        if ( $retired ) {
            $list = implode( ',', array_map( 'absint', $retired ) );
            $query = "SELECT DISTINCT r.object_id FROM {$wpdb->term_relationships} r INNER JOIN {$wpdb->term_taxonomy} t ON r.term_taxonomy_id=t.term_taxonomy_id WHERE t.taxonomy='category' AND t.term_id IN ($list) ORDER BY r.object_id LIMIT 100";
            $objects = $wpdb->get_col( $query );
            if ( $wpdb->last_error ) { throw new RuntimeException( $wpdb->last_error ); }
            $previous_counting = wp_defer_term_counting();
            wp_defer_term_counting( true );
            try {
                foreach ( $objects as $object_id ) {
                    $current = wp_get_object_terms( $object_id, 'category', array( 'fields' => 'ids' ) );
                    if ( is_wp_error( $current ) ) { throw new RuntimeException( $current->get_error_message() ); }
                    $categories = array();
                    foreach ( $current as $id ) {
                        $mapped = (int) ( $term_mapping[ $id ] ?? $id );
                        if ( $mapped !== $terms['genericas'] || in_array( (int) $id, $ids, true ) ) { $categories[] = $mapped; }
                    }
                    if ( ! $categories ) { $categories[] = $terms['genericas']; }
                    cms_editorial_structure_result( wp_set_object_terms( $object_id, array_values( array_unique( $categories ) ), 'category' ) );
                    ++$state['processed'];
                }
            } finally { wp_defer_term_counting( $previous_counting ); }
            update_option( 'cms_editorial_sections_pending', $state, false );
            $remaining = $wpdb->get_var( $query );
            if ( $wpdb->last_error ) { throw new RuntimeException( $wpdb->last_error ); }
            if ( $remaining ) { return false; }
        }
        foreach ( $original as $term ) {
            if ( ! in_array( (int) $term->term_id, $ids, true ) && term_exists( $term->term_id, 'category' ) ) { cms_editorial_structure_result( wp_delete_term( $term->term_id, 'category', array( 'default' => $terms['genericas'] ) ) ); }
        }
        foreach ( get_posts( array( 'post_type' => 'pro_cat_banner', 'post_status' => 'any', 'numberposts' => -1 ) ) as $banner ) {
            $categories = get_post_meta( $banner->ID, '_pro_cat_ids', true );
            if ( is_array( $categories ) ) {
                $categories = array_values( array_unique( array_map( function( $id ) use ( $term_mapping ) { return $term_mapping[ $id ] ?? $id; }, $categories ) ) );
                update_post_meta( $banner->ID, '_pro_cat_ids', $categories );
            }
        }
        foreach ( get_posts( array( 'post_type' => 'pro_ad_banner', 'post_status' => 'any', 'numberposts' => -1 ) ) as $banner ) {
            if ( in_array( get_post_meta( $banner->ID, '_pro_ad_location', true ), array( 'economia-ad', 'seguridad-ad' ), true ) ) { update_post_meta( $banner->ID, '_pro_ad_location', 'genericas-ad' ); }
        }

        $pages = array();
        foreach ( cms_editorial_sections() as $slug => $title ) {
            $page = pro_find_page( $title, $slug );
            $data = array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug );
            if ( $page ) { $data['ID'] = $page->ID; }
            $pages[ $slug ] = cms_editorial_structure_result( wp_insert_post( $data, true ) );
            update_post_meta( $pages[ $slug ], '_wp_page_template', 'page-categoria.php' );
        }
        $category_pages = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'numberposts' => -1, 'meta_key' => '_wp_page_template', 'meta_value' => 'page-categoria.php' ) );
        $page_mapping = array_combine( array_values( $pages ), array_values( $pages ) );
        foreach ( $category_pages as $page ) {
            if ( in_array( $page->ID, $pages, true ) ) { continue; }
            $target = $redirects[ $page->post_name ] ?? $aliases[ $page->post_name ] ?? 'genericas';
            $redirects[ $page->post_name ] = $target;
            $page_mapping[ $page->ID ] = $pages[ $target ];
            if ( trim( $page->post_content ) === '' && $page->post_status === 'publish' ) { cms_editorial_structure_result( wp_update_post( array( 'ID' => $page->ID, 'post_status' => 'draft' ), true ) ); }
        }
        foreach ( get_posts( array( 'post_type' => 'pro_cat_banner', 'post_status' => 'any', 'numberposts' => -1 ) ) as $banner ) {
            $targets = get_post_meta( $banner->ID, '_pro_cat_page_ids', true );
            if ( is_array( $targets ) ) { update_post_meta( $banner->ID, '_pro_cat_page_ids', array_values( array_unique( array_map( function( $id ) use ( $page_mapping ) { return $page_mapping[ $id ] ?? $id; }, $targets ) ) ) ); }
            $url_page = get_post_meta( $banner->ID, '_pro_cat_url_page_id', true );
            if ( isset( $page_mapping[ $url_page ] ) ) { update_post_meta( $banner->ID, '_pro_cat_url_page_id', $page_mapping[ $url_page ] ); }
        }
        // Servicios y páginas legales siguen siendo páginas, sin categorías adicionales.
        foreach ( array( 'Contacto' => 'page-contacto.php', 'Carteles y Edictos' => 'page-carteles.php', 'Términos y Condiciones' => 'page-terminos-y-condiciones.php', 'Política de Cookies' => 'page-politica-de-cookies.php', 'Política de Privacidad' => 'page-politica-de-privacidad.php', 'Clasificados' => 'archive-clasificado.php' ) as $title => $template ) {
            if ( ! pro_find_page( $title ) ) {
                $id = cms_editorial_structure_result( wp_insert_post( array( 'post_title' => $title, 'post_type' => 'page', 'post_status' => 'publish' ), true ) );
                update_post_meta( $id, '_wp_page_template', $template );
            }
        }
        if ( ! get_option( 'page_on_front' ) ) {
            $home = pro_find_page( 'Inicio' );
            $id = $home ? $home->ID : cms_editorial_structure_result( wp_insert_post( array( 'post_title' => 'Inicio', 'post_type' => 'page', 'post_status' => 'publish' ), true ) );
            update_option( 'show_on_front', 'page' ); update_option( 'page_on_front', $id );
        }
        $primary = cms_editorial_structure_menu( 'primary', 'Menú Principal CMS-Editorial', $pages );
        $mobile = cms_editorial_structure_menu( 'mobile', 'Menú Móvil CMS-Editorial', $pages, $primary );
        $locations = get_theme_mod( 'nav_menu_locations', array() );
        $locations['primary'] = $primary; $locations['mobile'] = $mobile;
        set_theme_mod( 'nav_menu_locations', $locations );
        foreach ( wp_get_nav_menus() as $menu ) {
            if ( ! in_array( (int) $menu->term_id, array( (int) $primary, (int) $mobile ), true ) && in_array( $menu->name, array( 'Menú CMS-Editorial', 'Menu e-movil', 'Menú Principal Nuclear', 'Menú Principal Nuclear v6' ), true ) ) { wp_delete_nav_menu( $menu->term_id ); }
        }
        $settings = get_option( 'cms_editorial_settings', array() );
        $settings['home_sections'] = implode( ',', array_keys( cms_editorial_sections() ) );
        $settings['local_categories'] = ''; $settings['home_featured_category'] = '';
        update_option( 'cms_editorial_settings', $settings );
        foreach ( array_keys( cms_editorial_sections() ) as $slug ) { unset( $redirects[ $slug ] ); }
        update_option( 'cms_editorial_section_redirects', $redirects, false );
        eo_clear_editorial_cache();
        update_option( 'cms_editorial_sections_v1', time(), false );
        delete_option( 'cms_editorial_sections_pending' );
        delete_option( 'cms_editorial_sections_error' );
        return true;
    } catch ( Throwable $error ) {
        update_option( 'cms_editorial_sections_error', $error->getMessage(), false );
        return new WP_Error( 'cms_sections_migration', $error->getMessage() );
    } finally { pro_install_lock_release(); }
}
add_action( 'admin_init', 'cms_editorial_install_structure', 100 );

add_action( 'wp_ajax_cms_editorial_update_sections', function() {
    if ( ! current_user_can( 'manage_options' ) ) { wp_send_json_error( array( 'message' => 'No autorizado.' ), 403 ); }
    check_ajax_referer( 'cms_editorial_sections', 'nonce' );
    $result = cms_editorial_install_structure( true );
    if ( is_wp_error( $result ) ) { wp_send_json_error( array( 'message' => $result->get_error_message() ), 500 ); }
    $state = get_option( 'cms_editorial_sections_pending', array() );
    wp_send_json_success( array( 'complete' => (bool) get_option( 'cms_editorial_sections_v1' ), 'processed' => $state['processed'] ?? 0 ) );
} );

add_action( 'admin_enqueue_scripts', function() {
    if ( get_option( 'cms_editorial_sections_pending' ) && current_user_can( 'manage_options' ) ) {
        wp_enqueue_script( 'cms-section-migration', get_template_directory_uri() . '/assets/js/section-migration.js', array(), '3.2.0', true );
        wp_localize_script( 'cms-section-migration', 'cmsSectionMigration', array( 'url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'cms_editorial_sections' ) ) );
    }
} );

add_action( 'admin_notices', function() {
    $error = get_option( 'cms_editorial_sections_error' );
    if ( $error && current_user_can( 'manage_options' ) ) { echo '<div class="notice notice-error"><p>' . esc_html( 'CMS-Editorial: no se pudo completar la actualización de secciones. ' . $error ) . '</p></div>'; }
    elseif ( get_option( 'cms_editorial_sections_pending' ) && current_user_can( 'manage_options' ) ) { echo '<div class="notice notice-info" id="cms-section-migration" role="status"><p>Actualizando las secciones editoriales. Tus entradas se conservan.</p></div>'; }
} );

add_action( 'template_redirect', function() {
    if ( is_admin() || is_preview() || is_search() || ! in_array( $_SERVER['REQUEST_METHOD'] ?? 'GET', array( 'GET', 'HEAD' ), true ) ) { return; }
    $path = trim( wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH ), '/' );
    $home_path = trim( wp_parse_url( home_url( '/' ), PHP_URL_PATH ) ?: '', '/' );
    if ( $home_path && strpos( $path, $home_path . '/' ) === 0 ) { $path = substr( $path, strlen( $home_path ) + 1 ); }
    $base = trim( get_option( 'category_base' ) ?: 'category', '/' );
    $category_route = strpos( $path, $base . '/' ) === 0;
    $slug = $category_route ? substr( $path, strlen( $base ) + 1 ) : $path;
    $redirects = get_option( 'cms_editorial_section_redirects', array() );
    if ( ! isset( $redirects[ $slug ] ) ) { return; }
    $page = get_queried_object();
    if ( ! $category_route && $page instanceof WP_Post && get_page_template_slug( $page ) !== 'page-categoria.php' ) { return; }
    wp_safe_redirect( cms_editorial_section_url( $redirects[ $slug ] ), 301 ); exit;
}, 5 );
