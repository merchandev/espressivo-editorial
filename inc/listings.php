<?php
/** Listados e imágenes: conserva las correcciones editoriales existentes. */
if ( ! defined( "ABSPATH" ) ) { exit; }
/**
 * pre_get_posts — Listados de index.php: entradas, etiquetas, autores, fechas y búsqueda.
 *
 * Paginan con "cargar más" por desplazamiento (pro_get_listing_query_args()),
 * que solo cuenta entradas publicadas y ordena por fecha e ID. Si la primera
 * página incluía entradas privadas (sesión de redacción) u ordenaba solo por
 * fecha, el desplazamiento no coincidía y se saltaban o repetían noticias.
 */
add_action( 'pre_get_posts', function ( WP_Query $query ): void {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( ! ( $query->is_home() || $query->is_tag() || $query->is_author() || $query->is_date() || $query->is_search() ) ) {
        return;
    }

    $query->set( 'post_status', 'publish' );

    // La búsqueda conserva el orden por relevancia de WordPress
    if ( ! $query->is_search() && ! $query->get( 'orderby' ) ) {
        $query->set( 'orderby', array( 'date' => 'DESC', 'ID' => 'DESC' ) );
    }
} );

/**
 * pre_get_posts — Normalizar la consulta principal del archivo nativo de categorías.
 *
 * category.php usa have_posts() / the_post() de la consulta principal.
 * WordPress incluye hijos por defecto en is_category(), pero este hook
 * garantiza ordenado preciso, sin sticky posts y con 20 entradas.
 */
add_action( 'pre_get_posts', function ( WP_Query $query ): void {
    if ( is_admin() || ! $query->is_main_query() || ! $query->is_category() ) {
        return;
    }

    $query->set( 'post_status',         'publish' );
    $query->set( 'posts_per_page',      PRO_CATEGORY_ARCHIVE_PER_PAGE );
    $query->set( 'orderby',             array( 'date' => 'DESC', 'ID' => 'DESC' ) );
    $query->set( 'ignore_sticky_posts', true );

    // Misma regla de subcategorías que page-categoria.php y el "cargar más"
    $term = $query->get_queried_object();
    if ( $term instanceof WP_Term && ! pro_category_includes_children( $term ) ) {
        $query->set( 'tax_query', array(
            array(
                'taxonomy'         => 'category',
                'field'            => 'term_id',
                'terms'            => array( $term->term_id ),
                'include_children' => false,
            ),
        ) );
    }
} );

// =============================================================================
// LISTADOS PAGINADOS Y "CARGAR MÁS" / SCROLL INFINITO
// Auditoría fix: la primera página y las cargas AJAX usaban consultas distintas
// (12 o 20 entradas frente a la opción "entradas por página", orden solo por
// fecha y subcategorías incluidas o no). Con páginas de tamaño diferente se
// saltaban bloques enteros de noticias, y las publicadas en el mismo minuto
// (típico de las programadas) cambiaban de orden entre consultas. Ahora ambas
// usan pro_get_listing_query_args() y la carga AJAX avanza por desplazamiento.
// =============================================================================

const PRO_CATEGORY_PAGE_PER_PAGE    = 12; // page-categoria.php
const PRO_CATEGORY_ARCHIVE_PER_PAGE = 20; // category.php

/**
 * Indica si el listado de una categoría incluye sus subcategorías.
 * Opinión y Bienestar no mezclan subcategorías.
 */
function pro_category_includes_children( WP_Term $term ): bool {
    return ! in_array( $term->slug, array( 'opinion', 'bienestar' ), true );
}

/**
 * Argumentos de WP_Query de un listado de noticias.
 *
 * @param array $context Filtros admitidos: cat, tag_id, author, s, year, monthnum, day.
 * @param array $overrides Argumentos adicionales (posts_per_page, offset, paged...).
 */
function pro_get_listing_query_args( array $context, array $overrides = array() ): array {
    $args = array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'orderby'             => array( 'date' => 'DESC', 'ID' => 'DESC' ),
        'ignore_sticky_posts' => true,
    );

    if ( ! empty( $context['cat'] ) ) {
        $term = get_term( (int) $context['cat'], 'category' );
        if ( $term instanceof WP_Term ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy'         => 'category',
                    'field'            => 'term_id',
                    'terms'            => array( $term->term_id ),
                    'include_children' => pro_category_includes_children( $term ),
                ),
            );
        } else {
            $args['post__in'] = array( 0 );
        }
    }

    foreach ( array( 'tag_id', 'author', 'year', 'monthnum', 'day' ) as $key ) {
        if ( ! empty( $context[ $key ] ) ) {
            $args[ $key ] = (int) $context[ $key ];
        }
    }

    // La búsqueda principal de WordPress abarca todos los tipos buscables y ordena
    // por relevancia; las tandas AJAX deben seguir exactamente ese mismo orden.
    if ( isset( $context['s'] ) && '' !== $context['s'] ) {
        $args['s']         = $context['s'];
        $args['post_type'] = 'any';
        unset( $args['orderby'] );
    }

    return array_merge( $args, $overrides );
}

/**
 * Estado de "cargar más" para la consulta principal (category.php, index.php).
 *
 * @return array{context: array, offset: int, per_page: int, has_more: bool}
 */
function pro_get_main_query_loadmore_state(): array {
    global $wp_query;

    $context = array();
    if ( is_category() ) {
        $context['cat'] = get_queried_object_id();
    } elseif ( is_tag() ) {
        $context['tag_id'] = get_queried_object_id();
    } elseif ( is_author() ) {
        $context['author'] = get_queried_object_id();
    } elseif ( is_date() ) {
        $context['year']     = (int) get_query_var( 'year' );
        $context['monthnum'] = (int) get_query_var( 'monthnum' );
        $context['day']      = (int) get_query_var( 'day' );
    }
    if ( is_search() ) {
        $context['s'] = get_search_query( false );
    }

    $per_page = max( 1, (int) $wp_query->get( 'posts_per_page' ) );
    $paged    = max( 1, (int) get_query_var( 'paged' ) );
    // Las fijadas se añaden aparte en la portada; se cuentan solo las del orden cronológico
    $offset   = min( $paged * $per_page, (int) $wp_query->found_posts );

    return array(
        'context'  => $context,
        'offset'   => $offset,
        'per_page' => $per_page,
        'has_more' => $offset < (int) $wp_query->found_posts,
    );
}

/**
 * Imprime los atributos data-* que main.js usa para pedir la siguiente tanda.
 */
function pro_listing_data_attributes( array $context, int $offset, int $per_page, string $target ): void {
    $attrs = array(
        'data-offset'   => $offset,
        'data-per-page' => $per_page,
        'data-target'   => $target,
    );
    $map = array(
        'cat'      => 'data-cat-id',
        'tag_id'   => 'data-tag-id',
        'author'   => 'data-author-id',
        's'        => 'data-search',
        'year'     => 'data-year',
        'monthnum' => 'data-monthnum',
        'day'      => 'data-day',
    );
    foreach ( $map as $key => $attr ) {
        if ( isset( $context[ $key ] ) && '' !== $context[ $key ] && 0 !== $context[ $key ] ) {
            $attrs[ $attr ] = $context[ $key ];
        }
    }
    foreach ( $attrs as $name => $value ) {
        echo ' ' . esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
    }
}

/**
 * Los endpoints públicos (cargar más, buscador, aviso de noticias nuevas) solo
 * leen contenido publicado. Con la caché de página, el nonce impreso en el HTML
 * caduca (12-24 h) y check_ajax_referer() respondía "-1": los bloques dejaban de
 * cargar "a veces" e incluso se insertaba el texto "-1" en la página. El nonce se
 * exige solo con sesión iniciada, cuyas páginas no se cachean.
 */
function pro_check_public_ajax_nonce(): void {
    if ( is_user_logged_in() ) {
        check_ajax_referer( 'pro_ajax_nonce', 'nonce' );
    }
}



function pro_find_content_image_id( string $content ): int {
    if ( '' === $content || ( false === stripos( $content, '<img' ) && false === strpos( $content, 'wp:image' ) ) ) {
        return 0;
    }

    // Imágenes insertadas desde la biblioteca: clase wp-image-{ID} o atributo "id" del bloque
    if ( preg_match_all( '/\bwp-image-(\d+)\b|<!--\s*wp:image\s+\{[^}]*"id":(\d+)/', $content, $matches, PREG_SET_ORDER ) ) {
        foreach ( $matches as $match ) {
            $id = (int) ( ! empty( $match[1] ) ? $match[1] : ( $match[2] ?? 0 ) );
            if ( $id && wp_attachment_is_image( $id ) ) {
                return $id;
            }
        }
    }

    // Último recurso: la URL del primer <img>. attachment_url_to_postid() solo
    // reconoce el archivo original, así que se quita el sufijo de tamaño (-1024x683).
    if ( preg_match( '/<img\s[^>]*?src=["\']([^"\']+)["\']/i', $content, $match ) ) {
        $original = preg_replace( '/-\d+x\d+(?=\.[a-z0-9]+$)/i', '', strtok( $match[1], '?' ) );
        $scaled   = preg_replace( '/(\.[a-z0-9]+)$/i', '-scaled$1', $original );
        foreach ( array( $original, $scaled ) as $url ) {
            $id = attachment_url_to_postid( $url );
            if ( $id && wp_attachment_is_image( $id ) ) {
                return $id;
            }
        }
    }

    return 0;
}

function pro_get_post_image_id( $post = null ): int {
    static $cache = array();

    $post = get_post( $post );
    if ( ! $post ) {
        return 0;
    }
    if ( isset( $cache[ $post->ID ] ) ) {
        return $cache[ $post->ID ];
    }

    $id = (int) get_post_thumbnail_id( $post );
    if ( ! $id || ! wp_attachment_is_image( $id ) ) {
        $id = pro_find_content_image_id( (string) $post->post_content );
    }

    return $cache[ $post->ID ] = $id;
}

function pro_the_post_image( $size = 'post-thumbnail', $attr = array(), $post = null ): bool {
    $post = get_post( $post );
    $id   = pro_get_post_image_id( $post );
    if ( ! $id ) {
        return false;
    }

    // La destacada pasa por get_the_post_thumbnail() para conservar sus filtros
    if ( (int) get_post_thumbnail_id( $post ) === $id ) {
        $html = get_the_post_thumbnail( $post, $size, $attr );
    } else {
        $html = wp_get_attachment_image( $id, $size, false, $attr );
    }

    if ( '' === $html ) {
        return false;
    }
    echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML generado por WordPress
    return true;
}
