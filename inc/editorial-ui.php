<?php
/** Presentación editorial y herramientas de lectura. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_filter( 'body_class', function( $classes ) {
    $classes[] = 'cms-editorial-ui';
    return $classes;
} );

add_action( 'wp_enqueue_scripts', function() {
    $dir = get_template_directory();
    wp_enqueue_style( 'cms-editorial-ui', get_template_directory_uri() . '/assets/css/editorial-ui.css', array( 'pro-style' ), filemtime( $dir . '/assets/css/editorial-ui.css' ) );
    wp_enqueue_script( 'cms-editorial-ui', get_template_directory_uri() . '/assets/js/editorial-ui.js', array( 'pro-main-js' ), filemtime( $dir . '/assets/js/editorial-ui.js' ), true );
}, 110 );

function cms_editorial_reading_minutes( $post ) {
    $post = get_post( $post );
    $text = $post ? trim( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) ) : '';
    $words = $text ? preg_split( '/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY ) : array();
    return max( 1, (int) ceil( count( $words ?: array() ) / 220 ) );
}

function cms_editorial_story_category( $post_id ) {
    $categories = get_the_category( $post_id );
    foreach ( $categories as $category ) {
        if ( $category->slug !== cms_editorial_setting( 'home_featured_category' ) ) { return $category; }
    }
    return $categories[0] ?? null;
}

function cms_editorial_story( $post, $variant = 'card' ) {
    get_template_part( 'template-parts/content/story', null, array( 'story' => get_post( $post ), 'variant' => $variant ) );
}

function cms_editorial_home_posts( $args = array() ) {
    return get_posts( array_merge( array(
        'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 3,
        'orderby' => array( 'date' => 'DESC', 'ID' => 'DESC' ), 'ignore_sticky_posts' => true,
    ), $args ) );
}

function cms_editorial_section_heading( $title, $url = '', $eyebrow = '' ) {
    echo '<div class="ed-section-heading"><div>';
    if ( $eyebrow ) { echo '<span class="ed-eyebrow">' . esc_html( $eyebrow ) . '</span>'; }
    echo '<h2>' . esc_html( $title ) . '</h2></div>';
    if ( $url ) { echo '<a class="ed-text-link" href="' . esc_url( $url ) . '">Ver sección <span aria-hidden="true">↗</span></a>'; }
    echo '</div>';
}

/** Mantener las ubicaciones publicitarias sin crear huecos cuando están vacías. */
function cms_editorial_ad_slot( $location ) {
    ob_start();
    get_template_part( 'template-parts/ads/in-feed', null, array( 'location' => $location ) );
    $html = ob_get_clean();
    if ( trim( $html ) ) { echo '<div class="ed-ad-slot">' . $html . '</div>'; }
}

add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( $hook === 'appearance_page_cms-editorial' ) {
        wp_enqueue_style( 'cms-editorial-settings-ui', get_template_directory_uri() . '/assets/css/theme-settings.css', array(), '3.1.0' );
    }
} );
