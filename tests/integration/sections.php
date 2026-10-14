<?php
/** Prueba destructiva exclusivamente en una copia local desechable de la base de datos. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI || wp_get_environment_type() !== 'local' || ! defined( 'CMS_EDITORIAL_STRUCTURE_TEST' ) || ! CMS_EDITORIAL_STRUCTURE_TEST || ! current_user_can( 'manage_options' ) ) {
    throw new RuntimeException( 'Esta prueba requiere una base local desechable, WP-CLI, administrador y CMS_EDITORIAL_STRUCTURE_TEST=true.' );
}
function cms_sections_assert( $condition, $message ) {
    if ( ! $condition ) { throw new RuntimeException( $message ); }
    WP_CLI::log( 'OK: ' . $message );
}
delete_option( 'cms_editorial_sections_v1' );
delete_option( 'cms_editorial_sections_pending' );
delete_option( 'cms_editorial_sections_backup_v1' );
delete_option( 'pro_install_lock' );
$old_generic = wp_insert_term( 'Categoría retirada de prueba', 'category', array( 'slug' => 'retirada-qa' ) )['term_id'];
$world = get_category_by_slug( 'mundo' );
if ( ! $world ) { $id = wp_insert_term( 'Mundo', 'category', array( 'slug' => 'mundo' ) )['term_id']; $world = get_term( $id ); }
$science = get_category_by_slug( 'ciencia-y-tecnologia' );
if ( ! $science ) { $id = wp_insert_term( 'Ciencia y Tecnología', 'category', array( 'slug' => 'ciencia-y-tecnologia' ) )['term_id']; $science = get_term( $id ); }
$expected_world = get_category_by_slug( 'internacional' )->term_id ?? $world->term_id;
$expected_science = get_category_by_slug( 'ciencias' )->term_id ?? $science->term_id;
$national = get_category_by_slug( 'nacional' );
if ( ! $national ) { $id = wp_insert_term( 'Nacional', 'category', array( 'slug' => 'nacional' ) )['term_id']; $national = get_term( $id ); }
$fixtures = array();
foreach ( range( 1, 220 ) as $i ) {
    $fixtures[] = wp_insert_post( array( 'post_title' => 'Borrador de migración ' . $i, 'post_status' => 'draft', 'post_content' => 'Contenido que debe conservarse.', 'post_category' => array( $old_generic ) ) );
}
$mapped = wp_insert_post( array( 'post_title' => 'Noticia internacional de prueba', 'post_status' => 'publish', 'post_content' => 'Contenido internacional.', 'post_category' => array( $world->term_id ) ) );
$mixed = wp_insert_post( array( 'post_title' => 'Noticia con categoría vigente y retirada', 'post_status' => 'draft', 'post_category' => array( $national->term_id, $old_generic ) ) );
$banner = wp_insert_post( array( 'post_type' => 'pro_cat_banner', 'post_title' => 'Patrocinador de prueba', 'post_status' => 'draft', 'meta_input' => array( '_pro_cat_ids' => array( $world->term_id, $old_generic ) ) ) );
$existing_old_page = pro_find_page( 'Mundo', 'mundo' );
$old_page = wp_insert_post( array( 'ID' => $existing_old_page ? $existing_old_page->ID : 0, 'post_type' => 'page', 'post_title' => 'Mundo', 'post_name' => 'mundo', 'post_status' => 'publish', 'meta_input' => array( '_wp_page_template' => 'page-categoria.php' ) ) );
update_post_meta( $banner, '_pro_cat_page_ids', array( $old_page ) );
update_post_meta( $banner, '_pro_cat_url_page_id', $old_page );
$previous_news = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => -1 ) );
$snapshots = array();
foreach ( $previous_news as $post ) { $snapshots[ $post->ID ] = array( $post->post_status, $post->post_content, $post->post_author, get_post_meta( $post->ID, '_pro_firma_autor', true ) ); }
$admin = get_current_user_id();
$author = wp_insert_user( array( 'user_login' => 'section_author_' . wp_generate_password( 8, false ), 'user_pass' => wp_generate_password(), 'role' => 'author' ) );
wp_set_current_user( $author );
cms_sections_assert( cms_editorial_install_structure() === false && ! get_option( 'cms_editorial_sections_pending' ), 'Un autor no puede iniciar la migración' );
wp_set_current_user( $admin );
$result = cms_editorial_install_structure();
cms_sections_assert( $result === false && get_option( 'cms_editorial_sections_pending' )['processed'] === 100, 'El primer lote procesa 100 entradas y conserva la migración pendiente' );
$calls = 1;
while ( ! get_option( 'cms_editorial_sections_v1' ) && $calls < 10 ) {
    $result = cms_editorial_install_structure();
    if ( is_wp_error( $result ) ) { throw new RuntimeException( $result->get_error_message() ); }
    ++$calls;
}
cms_sections_assert( (bool) get_option( 'cms_editorial_sections_v1' ) && $calls >= 3, 'La migración termina por lotes y puede reanudarse' );
$terms = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false ) );
$slugs = wp_list_pluck( $terms, 'slug' ); sort( $slugs );
$expected = array_keys( cms_editorial_sections() ); sort( $expected );
cms_sections_assert( $slugs === $expected && ! array_filter( $terms, function( $term ) { return $term->parent !== 0; } ), 'Solo quedan las seis categorías, sin subcategorías' );
cms_sections_assert( get_category_by_slug( 'internacional' )->term_id === $expected_world && get_category_by_slug( 'ciencias' )->term_id === $expected_science, 'Las categorías vigentes o equivalentes conservan sus identificadores' );
foreach ( $snapshots as $id => $snapshot ) {
    $post = get_post( $id );
    if ( ! $post || array( $post->post_status, $post->post_content, $post->post_author, get_post_meta( $id, '_pro_firma_autor', true ) ) !== $snapshot ) { throw new RuntimeException( 'La migración alteró la entrada ' . $id ); }
}
cms_sections_assert( true, 'Se conservan contenido, estado, autor y firma de las ' . count( $snapshots ) . ' entradas verificadas' );
$generic = get_category_by_slug( 'genericas' )->term_id;
cms_sections_assert( (int) get_option( 'default_category' ) === $generic && wp_get_post_categories( $fixtures[0] ) === array( $generic ), 'Las noticias sin otra sección pasan a Genéricas, que es la predeterminada' );
cms_sections_assert( wp_get_post_categories( $mapped ) === array( $expected_world ) && wp_get_post_categories( $mixed ) === array( $national->term_id ), 'La migración respeta las secciones equivalentes y las asignaciones vigentes' );
cms_sections_assert( get_post_meta( $banner, '_pro_cat_ids', true ) === array( $expected_world, $generic ), 'Los patrocinadores siguen apuntando a las categorías correspondientes' );
cms_sections_assert( get_post_meta( $banner, '_pro_cat_page_ids', true ) === array( pro_find_page( 'Internacional' )->ID ) && (int) get_post_meta( $banner, '_pro_cat_url_page_id', true ) === pro_find_page( 'Internacional' )->ID && get_post_status( $old_page ) === 'draft', 'Las páginas y destinos de patrocinadores se reorganizan con las secciones' );
foreach ( array( 'primary', 'mobile' ) as $location ) {
    $items = wp_get_nav_menu_items( get_theme_mod( 'nav_menu_locations' )[ $location ] );
    cms_sections_assert( wp_list_pluck( $items, 'title' ) === array_merge( array( 'Inicio' ), array_values( cms_editorial_sections() ) ), 'Menú ' . $location . ' con Inicio y las seis secciones' );
}
cms_sections_assert( get_post_status( pro_find_page( 'Contacto' ) ) === 'publish' && get_post_status( pro_find_page( 'Clasificados' ) ) === 'publish', 'Los servicios siguen publicados' );
cms_sections_assert( cms_editorial_setting( 'home_sections' ) === implode( ',', array_keys( cms_editorial_sections() ) ) && cms_editorial_setting( 'home_featured_category' ) === '' && cms_editorial_setting( 'local_categories' ) === '', 'La portada usa solo las seis secciones y destaca las últimas noticias' );
cms_sections_assert( (bool) get_option( 'cms_editorial_sections_backup_v1' ) && ! get_option( 'cms_editorial_sections_pending' ), 'Guarda el estado anterior y retira el progreso al terminar' );
$count = count( get_posts( array( 'post_type' => array( 'post', 'page', 'nav_menu_item' ), 'post_status' => 'any', 'numberposts' => -1 ) ) );
pro_nuclear_install_pages(); pro_apply_user_updates(); pro_setup_espressivo_categories_and_menu();
cms_sections_assert( count( get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false ) ) ) === 6 && $count === count( get_posts( array( 'post_type' => array( 'post', 'page', 'nav_menu_item' ), 'post_status' => 'any', 'numberposts' => -1 ) ) ), 'Los instaladores antiguos no recrean categorías, páginas ni menús' );
WP_CLI::success( 'Migración editorial de seis secciones verificada.' );
