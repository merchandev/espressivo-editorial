<?php
namespace SSIVO_SEO\Includes;

/** Metadatos basados en la identidad y el contenido de la instalación. */
class FrontendMeta {
    private $database;

    public function __construct( Database $database ) {
        $this->database = $database;
        add_action( 'wp_head', array( $this, 'inject_meta_tags' ), 1 );
        remove_action( 'wp_head', '_wp_render_title_tag', 1 );
        remove_action( 'wp_head', 'rel_canonical' );
    }

    public function inject_meta_tags() {
        $site = get_bloginfo( 'name' );
        $suffix = cms_editorial_setting( 'seo_suffix' ) ?: $site;
        $title = wp_get_document_title();
        $desc = get_bloginfo( 'description' );
        $canonical = home_url( '/' );
        $type = 'website';
        $image = wp_get_attachment_image_url( absint( cms_editorial_setting( 'social_image' ) ), 'full' ) ?: cms_editorial_logo_url();

        if ( is_singular() ) {
            $id = get_queried_object_id();
            $data = $this->database->get_seo_data( $id );
            $title = get_post_meta( $id, '_ssivo_seo_custom_title', true ) ?: ( $data['meta_title'] ?? get_the_title( $id ) );
            if ( ! is_front_page() ) { $title .= $suffix ? ' | ' . $suffix : ''; }
            $desc = get_post_meta( $id, '_ssivo_seo_custom_desc', true ) ?: ( $data['meta_desc'] ?? $desc );
            $canonical = get_permalink( $id );
            $type = is_singular( 'post' ) ? 'article' : 'website';
            if ( has_post_thumbnail( $id ) ) { $image = get_the_post_thumbnail_url( $id, 'large' ); }
        } elseif ( is_home() || is_front_page() ) {
            $title = $site;
        } elseif ( is_category() || is_tag() || is_tax() ) {
            $term = get_queried_object();
            $title = $term->name . ( $suffix ? ' | ' . $suffix : '' );
            $desc = wp_strip_all_tags( term_description() ) ?: $desc;
            $link = get_term_link( $term );
            $canonical = is_wp_error( $link ) ? home_url( '/' ) : $link;
        } elseif ( is_post_type_archive() ) {
            $title = post_type_archive_title( '', false ) . ( $suffix ? ' | ' . $suffix : '' );
            $canonical = get_post_type_archive_link( get_query_var( 'post_type' ) );
        } elseif ( is_author() ) {
            $canonical = get_author_posts_url( get_queried_object_id() );
        } elseif ( is_search() ) {
            $canonical = get_search_link();
        }
        if ( get_query_var( 'paged' ) > 1 && ! is_singular() ) {
            $page = (int) get_query_var( 'paged' );
            $title .= ' | Página ' . $page;
            $canonical = get_pagenum_link( $page );
        }

        echo '<title>' . esc_html( $title ) . '</title>' . "\n";
        $tags = array( 'description' => $desc, 'twitter:card' => 'summary_large_image', 'twitter:title' => $title, 'twitter:description' => $desc, 'twitter:image' => $image );
        foreach ( $tags as $name => $value ) {
            if ( $value ) { echo '<meta name="' . esc_attr( $name ) . '" content="' . esc_attr( $value ) . '">' . "\n"; }
        }
        $tags = array( 'og:type' => $type, 'og:title' => $title, 'og:description' => $desc, 'og:url' => $canonical, 'og:site_name' => $site, 'og:image' => $image );
        foreach ( $tags as $name => $value ) {
            if ( $value ) { echo '<meta property="' . esc_attr( $name ) . '" content="' . esc_attr( $value ) . '">' . "\n"; }
        }
        if ( $canonical && ! is_404() ) { echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n"; }
    }
}
