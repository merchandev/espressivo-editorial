<?php
/**
 * Sistema editorial de clasificados.
 *
 * @package Espressivo
 */

defined( 'ABSPATH' ) || exit;

const ESPRESSIVO_CLASIFICADOS_VERSION = '1.0.0';

/**
 * Registrar el CPT y su única taxonomía.
 */
function espressivo_register_clasificados(): void {
    $labels = array(
        'name'                  => __( 'Clasificados', 'pro' ),
        'singular_name'         => __( 'Clasificado', 'pro' ),
        'menu_name'             => __( 'Clasificados', 'pro' ),
        'name_admin_bar'        => __( 'Clasificado', 'pro' ),

        'add_new'               => __( 'Añadir clasificado', 'pro' ),
        'add_new_item'          => __( 'Añadir nuevo clasificado', 'pro' ),
        'new_item'              => __( 'Nuevo clasificado', 'pro' ),
        'edit_item'             => __( 'Editar clasificado', 'pro' ),
        'view_item'             => __( 'Ver clasificado', 'pro' ),

        'all_items'             => __( 'Todos los clasificados', 'pro' ),
        'search_items'          => __( 'Buscar clasificados', 'pro' ),
        'not_found'             => __( 'No se encontraron clasificados.', 'pro' ),
        'not_found_in_trash'    => __( 'No hay clasificados en la papelera.', 'pro' ),

        'archives'              => __( 'Archivo de clasificados', 'pro' ),
        'attributes'            => __( 'Atributos del clasificado', 'pro' ),
        'featured_image'        => __( 'Imagen', 'pro' ),
        'set_featured_image'    => __( 'Asignar imagen', 'pro' ),
        'remove_featured_image' => __( 'Eliminar imagen', 'pro' ),

        'item_published'        => __( 'Clasificado publicado.', 'pro' ),
        'item_updated'          => __( 'Clasificado actualizado.', 'pro' ),
    );

    register_post_type(
        'clasificado',
        array(
            'labels' => $labels,

            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_admin_bar'  => true,
            'show_in_nav_menus'  => true,
            'show_in_rest'       => true,

            'menu_position' => 25,
            'menu_icon'     => 'dashicons-megaphone',

            /*
             * Archivo:
             * /clasificados/
             *
             * Individual:
             * /clasificados/titulo-del-aviso/
             */
            'has_archive' => 'clasificados',

            'rewrite' => array(
                'slug'       => 'clasificados',
                'with_front' => false,
                'feeds'      => true,
                'pages'      => true,
            ),

            'query_var' => true,

            /*
             * Solo texto.
             * No se habilita thumbnail.
             */
            'supports' => array(
                'title',
                'editor',
                'author',
                'revisions',
            ),

            'taxonomies' => array(
                'tipo_clasificado',
            ),

            'capability_type' => 'post',
            'map_meta_cap'    => true,

            'can_export'         => true,
            'delete_with_user'   => false,
            'exclude_from_search'=> false,
        )
    );

    register_taxonomy(
        'tipo_clasificado',
        array( 'clasificado' ),
        array(
            'labels' => array(
                'name'                       => __( 'Tipos de clasificado', 'pro' ),
                'singular_name'              => __( 'Tipo de clasificado', 'pro' ),
                'menu_name'                  => __( 'Tipos de clasificado', 'pro' ),
                'search_items'               => __( 'Buscar tipos', 'pro' ),
                'all_items'                  => __( 'Todos los tipos', 'pro' ),
                'parent_item'                => __( 'Tipo superior', 'pro' ),
                'parent_item_colon'          => __( 'Tipo superior:', 'pro' ),
                'edit_item'                  => __( 'Editar tipo', 'pro' ),
                'update_item'                => __( 'Actualizar tipo', 'pro' ),
                'add_new_item'               => __( 'Añadir tipo', 'pro' ),
                'new_item_name'              => __( 'Nombre del nuevo tipo', 'pro' ),
                'not_found'                  => __( 'No se encontraron tipos.', 'pro' ),
                'back_to_items'              => __( 'Volver a tipos', 'pro' ),
                'separate_items_with_commas' => __( 'Separar tipos con comas', 'pro' ),
            ),

            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_admin_column'  => true,
            'show_in_nav_menus'  => true,
            'show_in_rest'       => true,

            /*
             * Funciona como categorías:
             * admite tipos y subtipos.
             */
            'hierarchical' => true,

            'rewrite' => array(
                'slug'         => 'clasificados/tipo',
                'with_front'   => false,
                'hierarchical' => true,
            ),

            'query_var' => true,
        )
    );
}
add_action( 'init', 'espressivo_register_clasificados', 8 );

/**
 * Limitar el editor a bloques de texto.
 *
 * Evita imágenes, videos, galerías y archivos.
 */
function espressivo_clasificados_allowed_blocks(
    $allowed_blocks,
    $editor_context
) {
    if (
        empty( $editor_context->post )
        || 'clasificado' !== $editor_context->post->post_type
    ) {
        return $allowed_blocks;
    }

    return array(
        'core/paragraph',
        'core/heading',
        'core/list',
        'core/list-item',
        'core/quote',
        'core/separator',
    );
}
add_filter(
    'allowed_block_types_all',
    'espressivo_clasificados_allowed_blocks',
    10,
    2
);

/**
 * Cambiar el placeholder del título.
 */
function espressivo_clasificado_title_placeholder(
    string $placeholder,
    WP_Post $post
): string {
    if ( 'clasificado' === $post->post_type ) {
        return __(
            'Escribe el título del clasificado',
            'pro'
        );
    }

    return $placeholder;
}
add_filter(
    'enter_title_here',
    'espressivo_clasificado_title_placeholder',
    10,
    2
);

/**
 * Comprobar que un clasificado tenga:
 *
 * - Título.
 * - Texto.
 * - Tipo de clasificado.
 *
 * Si falta algo, se guarda como borrador.
 */
function espressivo_validate_clasificado(
    int $post_id,
    WP_Post $post,
    bool $update,
    ?WP_Post $post_before
): void {
    static $validating = false;

    if (
        $validating
        || 'clasificado' !== $post->post_type
        || 'publish' !== $post->post_status
        || wp_is_post_revision( $post_id )
        || wp_is_post_autosave( $post_id )
    ) {
        return;
    }

    $errors = array();

    $title = trim(
        wp_strip_all_tags( $post->post_title )
    );

    $content = trim(
        wp_strip_all_tags(
            strip_shortcodes( $post->post_content )
        )
    );

    if ( '' === $title ) {
        $errors[] = __(
            'Debes escribir un título.',
            'pro'
        );
    }

    if ( '' === $content ) {
        $errors[] = __(
            'Debes escribir el texto del clasificado.',
            'pro'
        );
    }

    $type_ids = wp_get_post_terms(
        $post_id,
        'tipo_clasificado',
        array(
            'fields' => 'ids',
        )
    );

    if (
        is_wp_error( $type_ids )
        || empty( $type_ids )
    ) {
        $errors[] = __(
            'Debes seleccionar un tipo de clasificado.',
            'pro'
        );
    }

    if ( empty( $errors ) ) {
        return;
    }

    $validating = true;

    wp_update_post(
        array(
            'ID'          => $post_id,
            'post_status' => 'draft',
        )
    );

    $validating = false;

    set_transient(
        'espressivo_clasificado_errors_'
            . get_current_user_id(),
        $errors,
        MINUTE_IN_SECONDS
    );
}
add_action(
    'wp_after_insert_post',
    'espressivo_validate_clasificado',
    100,
    4
);

/**
 * Mostrar las validaciones.
 */
function espressivo_clasificado_admin_notice(): void {
    $transient_key =
        'espressivo_clasificado_errors_'
        . get_current_user_id();

    $errors = get_transient( $transient_key );

    if ( empty( $errors ) || ! is_array( $errors ) ) {
        return;
    }

    delete_transient( $transient_key );
    ?>
    <div class="notice notice-error is-dismissible">
        <p>
            <strong>
                <?php
                esc_html_e(
                    'El clasificado se guardó como borrador:',
                    'pro'
                );
                ?>
            </strong>
        </p>

        <ul>
            <?php foreach ( $errors as $error ) : ?>
                <li>
                    <?php echo esc_html( $error ); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
}
add_action(
    'admin_notices',
    'espressivo_clasificado_admin_notice'
);

/**
 * Configurar la consulta pública.
 */
function espressivo_clasificados_main_query(
    WP_Query $query
): void {
    if (
        is_admin()
        || ! $query->is_main_query()
    ) {
        return;
    }

    if (
        ! $query->is_post_type_archive( 'clasificado' )
        && ! $query->is_tax( 'tipo_clasificado' )
    ) {
        return;
    }

    $query->set( 'post_status', 'publish' );
    $query->set( 'posts_per_page', 36 );
    $query->set(
        'orderby',
        array(
            'date' => 'DESC',
            'ID'   => 'DESC',
        )
    );

    /*
     * Búsqueda dentro de /clasificados/.
     */
    if ( isset( $_GET['buscar_clasificado'] ) ) {
        $search = sanitize_text_field(
            wp_unslash(
                $_GET['buscar_clasificado']
            )
        );

        if ( '' !== $search ) {
            $query->set( 's', $search );
        }
    }

    /*
     * Filtrar por tipo sin abandonar /clasificados/.
     */
    if (
        $query->is_post_type_archive( 'clasificado' )
        && isset( $_GET['tipo'] )
    ) {
        $type_slug = sanitize_title(
            wp_unslash( $_GET['tipo'] )
        );

        if ( '' !== $type_slug ) {
            $query->set(
                'tax_query',
                array(
                    array(
                        'taxonomy' => 'tipo_clasificado',
                        'field'    => 'slug',
                        'terms'    => array( $type_slug ),
                    ),
                )
            );
        }
    }
}
add_action(
    'pre_get_posts',
    'espressivo_clasificados_main_query'
);

/**
 * Columnas administrativas.
 */
function espressivo_clasificado_columns(
    array $columns
): array {
    $new_columns = array();

    foreach ( $columns as $key => $label ) {
        $new_columns[ $key ] = $label;

        if ( 'title' === $key ) {
            $new_columns['clasificado_type'] =
                __( 'Tipo', 'pro' );
        }
    }

    return $new_columns;
}
add_filter(
    'manage_clasificado_posts_columns',
    'espressivo_clasificado_columns'
);

function espressivo_clasificado_column_content(
    string $column,
    int $post_id
): void {
    if ( 'clasificado_type' !== $column ) {
        return;
    }

    $terms = get_the_terms(
        $post_id,
        'tipo_clasificado'
    );

    if (
        empty( $terms )
        || is_wp_error( $terms )
    ) {
        echo '<span aria-hidden="true">—</span>';
        return;
    }

    echo esc_html(
        implode(
            ', ',
            wp_list_pluck( $terms, 'name' )
        )
    );
}
add_action(
    'manage_clasificado_posts_custom_column',
    'espressivo_clasificado_column_content',
    10,
    2
);

/**
 * Crear tipos iniciales.
 */
function espressivo_seed_clasificado_types(): void {
    $types = array(
        'Empleos',
        'Inmuebles',
        'Vehículos',
        'Compra y venta',
        'Servicios',
        'Mascotas',
        'Educación',
        'Avisos profesionales',
        'Otros',
    );

    foreach ( $types as $type_name ) {
        if (
            ! term_exists(
                $type_name,
                'tipo_clasificado'
            )
        ) {
            wp_insert_term(
                $type_name,
                'tipo_clasificado'
            );
        }
    }
}

/**
 * Migración versionada.
 *
 * No ejecutar flush_rewrite_rules() en cada visita.
 */
function espressivo_upgrade_clasificados(): void {
    $installed_version = get_option(
        'espressivo_clasificados_version',
        '0'
    );

    if (
        version_compare(
            $installed_version,
            ESPRESSIVO_CLASIFICADOS_VERSION,
            '>='
        )
    ) {
        return;
    }

    espressivo_seed_clasificado_types();

    /*
     * Soft flush: solo se ejecuta al cambiar versión.
     */
    flush_rewrite_rules( false );

    update_option(
        'espressivo_clasificados_version',
        ESPRESSIVO_CLASIFICADOS_VERSION,
        false
    );
}
add_action(
    'admin_init',
    'espressivo_upgrade_clasificados'
);

/**
 * Al activar el tema.
 */
function espressivo_activate_clasificados(): void {
    espressivo_register_clasificados();
    espressivo_seed_clasificado_types();

    flush_rewrite_rules( false );

    update_option(
        'espressivo_clasificados_version',
        ESPRESSIVO_CLASIFICADOS_VERSION,
        false
    );
}
add_action(
    'after_switch_theme',
    'espressivo_activate_clasificados'
);

/**
 * Cargar estilos únicamente en clasificados.
 */
function espressivo_enqueue_clasificados_assets(): void {
    if (
        ! is_post_type_archive( 'clasificado' )
        && ! is_singular( 'clasificado' )
        && ! is_tax( 'tipo_clasificado' )
    ) {
        return;
    }

    $css_path =
        get_template_directory()
        . '/assets/css/clasificados.css';

    $version = is_readable( $css_path )
        ? (string) filemtime( $css_path )
        : ESPRESSIVO_CLASIFICADOS_VERSION;

    wp_enqueue_style(
        'espressivo-clasificados',
        get_template_directory_uri()
            . '/assets/css/clasificados.css',
        array( 'pro-main-style' ),
        $version
    );
}
add_action(
    'wp_enqueue_scripts',
    'espressivo_enqueue_clasificados_assets',
    20
);

/**
 * Migración de clasificados actuales (Si no tienen tipo, pasan a 'Otros')
 */
function espressivo_classify_legacy_classifieds(): void {
    if (
        get_option(
            'espressivo_legacy_classifieds_migrated'
        )
    ) {
        return;
    }

    $other_term = term_exists(
        'Otros',
        'tipo_clasificado'
    );

    if ( ! $other_term ) {
        $other_term = wp_insert_term(
            'Otros',
            'tipo_clasificado'
        );
    }

    if ( is_wp_error( $other_term ) ) {
        return;
    }

    $other_term_id = is_array( $other_term )
        ? (int) $other_term['term_id']
        : (int) $other_term;

    $classified_ids = get_posts(
        array(
            'post_type'      => 'clasificado',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',

            'tax_query' => array(
                array(
                    'taxonomy' => 'tipo_clasificado',
                    'operator' => 'NOT EXISTS',
                ),
            ),
        )
    );

    foreach ( $classified_ids as $classified_id ) {
        wp_set_object_terms(
            $classified_id,
            array( $other_term_id ),
            'tipo_clasificado',
            false
        );
    }

    update_option(
        'espressivo_legacy_classifieds_migrated',
        true,
        false
    );
}
add_action(
    'admin_init',
    'espressivo_classify_legacy_classifieds',
    30
);
