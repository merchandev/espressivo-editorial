<?php
/**
 * Correcciones de auditoría editorial.
 *
 * Aísla los fixes de consultas, Site Kit/SSIVO-SEO, widgets,
 * codificación y Clasificados para evitar seguir creciendo functions.php.
 *
 * @package Espressivo
 */

defined( 'ABSPATH' ) || exit;

const ESPRESSIVO_EDITORIAL_AUDIT_FIXES_VERSION = '1.0.0';

/**
 * El rol administrador es el único que debe conservar acceso completo a Site Kit.
 * No usamos manage_options porque algunos roles editoriales heredaron capacidades
 * del administrador en instalaciones antiguas.
 */
function espressivo_audit_is_administrator(): bool {
    $user = wp_get_current_user();

    return $user instanceof WP_User
        && in_array( 'administrator', (array) $user->roles, true );
}

/**
 * 1) INVALIDACIÓN EDITORIAL
 *
 * save_post_post no se ejecuta cuando WP-Cron cambia una entrada de future a
 * publish. Por eso una publicación programada podía quedar fuera de caches
 * editoriales hasta la siguiente edición manual.
 */
function espressivo_audit_clear_cache_on_status_transition(
    string $new_status,
    string $old_status,
    WP_Post $post
): void {
    if (
        'post' !== $post->post_type
        || $new_status === $old_status
        || ( 'publish' !== $new_status && 'publish' !== $old_status )
    ) {
        return;
    }

    clean_post_cache( $post->ID );

    if ( function_exists( 'eo_clear_editorial_cache' ) ) {
        eo_clear_editorial_cache( $post->ID );
    } else {
        delete_transient( 'pro_latest_post_date' );
        delete_transient( 'pro_ticker_posts' );
    }
}
add_action(
    'transition_post_status',
    'espressivo_audit_clear_cache_on_status_transition',
    100,
    3
);

/**
 * 2) PAGINACIÓN AJAX CONSISTENTE
 *
 * Las plantillas de categorías trabajan con 20 entradas por página. El endpoint
 * antiguo usaba get_option( 'posts_per_page' ), lo que podía ser 10 y provocar
 * duplicados/saltos al mezclar ambas paginaciones.
 */
function espressivo_audit_load_more_posts(): void {
    check_ajax_referer( 'pro_ajax_nonce', 'nonce' );

    $current_page = isset( $_POST['page'] )
        ? max( 1, absint( $_POST['page'] ) )
        : 1;
    $paged  = $current_page + 1;
    $cat_id = isset( $_POST['category_id'] )
        ? absint( $_POST['category_id'] )
        : 0;

    $args = array(
        'post_type'              => 'post',
        'post_status'            => 'publish',
        'posts_per_page'         => 20,
        'paged'                  => $paged,
        'orderby'                => array(
            'date' => 'DESC',
            'ID'   => 'DESC',
        ),
        'ignore_sticky_posts'    => true,
        'update_post_meta_cache' => true,
        'update_post_term_cache' => true,
    );

    if ( $cat_id > 0 ) {
        $args['tax_query'] = array(
            array(
                'taxonomy'         => 'category',
                'field'            => 'term_id',
                'terms'            => array( $cat_id ),
                'include_children' => true,
                'operator'         => 'IN',
            ),
        );
    }

    $query = new WP_Query( $args );

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class( 'card-post' ); ?>>
                <?php if ( has_post_thumbnail() ) : ?>
                    <a href="<?php the_permalink(); ?>" class="post-thumbnail" aria-hidden="true" tabindex="-1">
                        <?php the_post_thumbnail( 'card-thumbnail', array( 'loading' => 'lazy' ) ); ?>
                    </a>
                <?php endif; ?>
                <div class="card-content">
                    <div class="post-meta">
                        <?php
                        if ( function_exists( 'pro_post_categories' ) ) {
                            pro_post_categories();
                        }
                        ?>
                        <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                            <?php echo esc_html( get_the_date() ); ?>
                        </time>
                    </div>
                    <h2 class="entry-title">
                        <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
                    </h2>
                    <div class="entry-excerpt">
                        <?php echo esc_html( wp_trim_words( get_the_excerpt(), 20, '...' ) ); ?>
                    </div>
                </div>
            </article>
            <?php
        }
    }

    wp_reset_postdata();
    wp_die();
}

// Sustituir el endpoint anterior sin tocar functions.php.
remove_action( 'wp_ajax_nopriv_pro_load_more_posts', 'pro_load_more_posts' );
remove_action( 'wp_ajax_pro_load_more_posts', 'pro_load_more_posts' );
add_action( 'wp_ajax_nopriv_pro_load_more_posts', 'espressivo_audit_load_more_posts' );
add_action( 'wp_ajax_pro_load_more_posts', 'espressivo_audit_load_more_posts' );

/**
 * 3) SSIVO-SEO PARA TODOS / SITE KIT SOLO ADMINISTRADOR
 */
function espressivo_audit_sync_seo_capabilities(): void {
    $wp_roles = wp_roles();
    if ( ! $wp_roles ) {
        return;
    }

    foreach ( array_keys( $wp_roles->roles ) as $role_slug ) {
        $role = get_role( $role_slug );
        if ( $role && ! $role->has_cap( 'view_ssivo_seo' ) ) {
            $role->add_cap( 'view_ssivo_seo' );
        }
    }
}
add_action( 'admin_init', 'espressivo_audit_sync_seo_capabilities', 50 );

function espressivo_audit_hide_site_kit_for_non_admins(): void {
    if ( espressivo_audit_is_administrator() ) {
        return;
    }

    $site_kit_slugs = array(
        'googlesitekit-dashboard',
        'googlesitekit-splash',
        'googlesitekit-settings',
    );

    foreach ( $site_kit_slugs as $slug ) {
        remove_menu_page( $slug );
    }

    // Site Kit cuelga sus vistas de un único menú principal.
    global $submenu;
    if ( isset( $submenu['googlesitekit-dashboard'] ) ) {
        unset( $submenu['googlesitekit-dashboard'] );
    }
}
add_action(
    'admin_menu',
    'espressivo_audit_hide_site_kit_for_non_admins',
    10000
);

function espressivo_audit_redirect_site_kit_for_non_admins(): void {
    if ( espressivo_audit_is_administrator() ) {
        return;
    }

    $page = isset( $_GET['page'] )
        ? sanitize_key( wp_unslash( $_GET['page'] ) )
        : '';

    if ( '' !== $page && 0 === strpos( $page, 'googlesitekit' ) ) {
        wp_safe_redirect( admin_url( 'admin.php?page=ssivo-seo' ) );
        exit;
    }
}
add_action(
    'admin_init',
    'espressivo_audit_redirect_site_kit_for_non_admins',
    1000
);

/**
 * 4) WIDGET "ÚLTIMAS PUBLICACIONES"
 *
 * Orden determinista por modificación + ID y miniatura real también para future.
 */
function espressivo_audit_render_publicaciones_widget(): void {
    $current_user = wp_get_current_user();
    $roles        = (array) $current_user->roles;
    $is_admin     = in_array( 'administrator', $roles, true );
    $is_director  = (bool) array_intersect(
        array( 'direccion', 'gerencia', 'publicista' ),
        $roles
    );

    $args = array(
        'post_type'           => 'post',
        'posts_per_page'      => 8,
        'post_status'         => array( 'publish', 'draft', 'pending', 'future' ),
        'orderby'             => array(
            'modified' => 'DESC',
            'ID'       => 'DESC',
        ),
        'ignore_sticky_posts' => true,
    );

    if ( ! $is_admin && ! $is_director ) {
        $args['author'] = $current_user->ID;
    }

    $query = new WP_Query( $args );

    echo '<div class="pro-widget-container posts espressivo-audit-posts">';

    if ( ! $query->have_posts() ) {
        echo '<div class="pro-empty-state"><span class="dashicons dashicons-admin-post"></span><p>No se encontraron publicaciones recientes.</p></div>';
        echo '</div>';
        return;
    }

    $status_labels = array(
        'publish' => 'Publicado',
        'draft'   => 'Borrador',
        'pending' => 'Pendiente',
        'future'  => 'Programado',
    );

    echo '<ul class="pro-posts-feed">';

    while ( $query->have_posts() ) {
        $query->the_post();

        $post_id     = get_the_ID();
        $author_id   = (int) get_post_field( 'post_author', $post_id );
        $author_name = get_the_author_meta( 'display_name', $author_id );
        $status      = get_post_status( $post_id );
        $label       = $status_labels[ $status ] ?? $status;
        $edit_link   = get_edit_post_link( $post_id );
        $thumb       = get_the_post_thumbnail(
            $post_id,
            array( 72, 72 ),
            array(
                'loading' => 'lazy',
                'class'   => 'espressivo-widget-post-thumb',
                'alt'     => '',
            )
        );

        echo '<li class="pro-post-item ' . esc_attr( $status ) . '">';
        echo '<div class="espressivo-widget-post-row">';
        echo '<div class="espressivo-widget-thumb-wrap">';
        if ( $thumb ) {
            echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        } else {
            echo '<span class="dashicons dashicons-format-image" aria-hidden="true"></span>';
        }
        echo '</div>';
        echo '<div class="espressivo-widget-post-body">';
        echo '<a href="' . esc_url( $edit_link ?: '#' ) . '" class="pro-post-title-link"><strong>' . esc_html( get_the_title() ) . '</strong></a>';
        echo '<div class="pro-post-meta" style="margin-top:4px;">';

        if ( $is_admin || $is_director ) {
            echo '<span class="pro-post-meta-author">' . get_avatar( $author_id, 16 ) . ' ' . esc_html( $author_name ) . '</span> &bull; ';
        }

        if ( 'future' === $status ) {
            echo '<span>Programado para ' . esc_html( get_the_date( 'j/n/Y g:i a' ) ) . '</span>';
        } elseif ( function_exists( 'pro_get_human_time_diff' ) ) {
            echo '<span>Modificado ' . esc_html( pro_get_human_time_diff( get_the_modified_date( 'Y-m-d H:i:s' ) ) ) . '</span>';
        } else {
            echo '<span>' . esc_html( get_the_modified_date() ) . '</span>';
        }

        echo '</div>';
        echo '</div>';
        echo '<div class="espressivo-widget-post-status"><span class="pro-post-status-badge ' . esc_attr( $status ) . '">' . esc_html( $label ) . '</span></div>';
        echo '</div>';
        echo '</li>';
    }

    echo '</ul>';
    wp_reset_postdata();

    echo '<div class="pro-widget-actions" style="margin-top:15px;">';
    echo '<a href="' . esc_url( admin_url( 'edit.php' ) ) . '" class="button"><span class="dashicons dashicons-admin-post"></span> Ver Todas las Entradas</a>';
    echo '</div>';
    echo '</div>';
}

function espressivo_audit_replace_publicaciones_widget(): void {
    remove_meta_box( 'pro_dashboard_publicaciones', 'dashboard', 'normal' );
    remove_meta_box( 'pro_dashboard_publicaciones', 'dashboard', 'side' );

    wp_add_dashboard_widget(
        'pro_dashboard_publicaciones',
        '✏️ Últimas Publicaciones',
        'espressivo_audit_render_publicaciones_widget'
    );
}
add_action(
    'wp_dashboard_setup',
    'espressivo_audit_replace_publicaciones_widget',
    999
);

function espressivo_audit_dashboard_widget_styles(): void {
    global $pagenow;
    if ( 'index.php' !== $pagenow ) {
        return;
    }
    ?>
    <style>
        .espressivo-widget-post-row{display:grid;grid-template-columns:58px minmax(0,1fr) auto;gap:10px;align-items:center}
        .espressivo-widget-thumb-wrap{width:58px;height:58px;border-radius:7px;overflow:hidden;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8}
        .espressivo-widget-post-thumb{width:100%;height:100%;object-fit:cover;display:block}
        .espressivo-widget-post-body{min-width:0}
        .espressivo-widget-post-status{align-self:flex-start;padding-top:2px}
        @media (max-width:782px){.espressivo-widget-post-row{grid-template-columns:52px minmax(0,1fr)}.espressivo-widget-post-status{grid-column:2}}
    </style>
    <?php
}
add_action( 'admin_head', 'espressivo_audit_dashboard_widget_styles', 100 );

/**
 * 5) CLASIFICADOS: imágenes + acceso desde el menú público.
 */
function espressivo_audit_enable_classified_images(): void {
    add_post_type_support( 'clasificado', 'thumbnail' );
}
add_action( 'init', 'espressivo_audit_enable_classified_images', 30 );

function espressivo_audit_classified_image_blocks( $allowed_blocks, $editor_context ) {
    if (
        empty( $editor_context->post )
        || 'clasificado' !== $editor_context->post->post_type
        || true === $allowed_blocks
    ) {
        return $allowed_blocks;
    }

    $allowed_blocks = is_array( $allowed_blocks ) ? $allowed_blocks : array();

    return array_values(
        array_unique(
            array_merge(
                $allowed_blocks,
                array(
                    'core/image',
                    'core/gallery',
                    'core/media-text',
                )
            )
        )
    );
}
add_filter(
    'allowed_block_types_all',
    'espressivo_audit_classified_image_blocks',
    100,
    2
);

function espressivo_audit_add_classifieds_to_public_menu( string $items, $args ): string {
    $location = isset( $args->theme_location ) ? (string) $args->theme_location : '';

    if ( ! in_array( $location, array( 'primary', 'mobile' ), true ) ) {
        return $items;
    }

    $url = get_post_type_archive_link( 'clasificado' );
    if ( ! $url || false !== strpos( $items, $url ) ) {
        return $items;
    }

    $classes = array( 'menu-item', 'menu-item-type-custom', 'menu-item-clasificados' );
    if (
        is_post_type_archive( 'clasificado' )
        || is_singular( 'clasificado' )
        || is_tax( 'tipo_clasificado' )
    ) {
        $classes[] = 'current-menu-item';
    }

    $items .= sprintf(
        '<li class="%1$s"><a href="%2$s">%3$s</a></li>',
        esc_attr( implode( ' ', $classes ) ),
        esc_url( $url ),
        esc_html__( 'Clasificados', 'pro' )
    );

    return $items;
}
add_filter(
    'wp_nav_menu_items',
    'espressivo_audit_add_classifieds_to_public_menu',
    20,
    2
);

/**
 * 6) MOJIBAKE UTF-8
 *
 * Reparación conservadora en salida: solo secuencias conocidas de UTF-8
 * interpretado previamente como Windows-1252/Latin-1. No reescribe el contenido
 * de la base de datos ni toca texto UTF-8 válido.
 */
function espressivo_audit_repair_mojibake( $text ) {
    if ( ! is_string( $text ) || '' === $text ) {
        return $text;
    }

    static $map = array(
        'Ã¡' => 'á', 'Ã©' => 'é', 'Ã­' => 'í', 'Ã³' => 'ó', 'Ãº' => 'ú',
        'Ã' => 'Á', 'Ã‰' => 'É', 'Ã' => 'Í', 'Ã“' => 'Ó', 'Ãš' => 'Ú',
        'Ã±' => 'ñ', 'Ã‘' => 'Ñ', 'Ã¼' => 'ü', 'Ãœ' => 'Ü',
        'Â¿' => '¿', 'Â¡' => '¡', 'Â°' => '°', 'Â·' => '·',
        'â€œ' => '“', 'â€' => '”', 'â€™' => '’', 'â€˜' => '‘',
        'â€“' => '–', 'â€”' => '—', 'â€¦' => '…',
    );

    return strtr( $text, $map );
}
add_filter( 'the_title', 'espressivo_audit_repair_mojibake', 20 );
add_filter( 'get_the_excerpt', 'espressivo_audit_repair_mojibake', 20 );
add_filter( 'the_content', 'espressivo_audit_repair_mojibake', 20 );
add_filter( 'widget_text', 'espressivo_audit_repair_mojibake', 20 );
add_filter( 'nav_menu_item_title', 'espressivo_audit_repair_mojibake', 20 );

function espressivo_audit_repair_term_mojibake( $term, $taxonomy ) {
    if ( $term instanceof WP_Term ) {
        $term              = clone $term;
        $term->name        = espressivo_audit_repair_mojibake( $term->name );
        $term->description = espressivo_audit_repair_mojibake( $term->description );
    }

    return $term;
}
add_filter( 'get_term', 'espressivo_audit_repair_term_mojibake', 20, 2 );

/**
 * Estilos públicos de las nuevas imágenes de Clasificados.
 */
function espressivo_audit_classified_inline_styles(): void {
    if (
        ! is_post_type_archive( 'clasificado' )
        && ! is_singular( 'clasificado' )
        && ! is_tax( 'tipo_clasificado' )
    ) {
        return;
    }

    $css = '
        .classified-card__image{display:block;aspect-ratio:16/9;overflow:hidden;border-radius:10px;margin:-2px -2px 14px}
        .classified-card__image img{width:100%;height:100%;object-fit:cover;display:block}
        .classified-detail__featured-image{margin:0 0 24px;border-radius:14px;overflow:hidden;background:#f1f5f9}
        .classified-detail__featured-image img{display:block;width:100%;height:auto;max-height:720px;object-fit:cover}
        .classified-detail__content .wp-block-image img,.classified-detail__content .wp-block-gallery img{max-width:100%;height:auto}
    ';

    wp_add_inline_style( 'espressivo-clasificados', $css );
}
add_action(
    'wp_enqueue_scripts',
    'espressivo_audit_classified_inline_styles',
    30
);
