<?php
/** Listados generales y búsqueda de CMS-Editorial. */
get_header();
global $wp_query;
$title = is_search() ? ( get_search_query() ? 'Resultados para «' . get_search_query() . '»' : 'Buscar noticias' ) : ( is_archive() ? wp_strip_all_tags( get_the_archive_title() ) : 'Todas las noticias' );
?>
<main id="primary" class="site-main container ed-archive-page">
    <header class="page-header"><span class="ed-eyebrow"><?php echo is_search() ? 'Encuentra una historia' : 'El archivo de la publicación'; ?></span><h1 class="page-title"><?php echo esc_html( $title ); ?></h1><p class="archive-description"><?php echo esc_html( sprintf( _n( '%s historia encontrada', '%s historias encontradas', $wp_query->found_posts, 'pro' ), number_format_i18n( $wp_query->found_posts ) ) ); ?></p></header>
    <?php if ( have_posts() ) : ?>
        <div class="ed-archive-grid posts-grid"><?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/content/card' ); endwhile; ?></div>
        <?php $state = pro_get_main_query_loadmore_state(); if ( $state['has_more'] ) : ?>
        <div class="load-more-container text-center"><button type="button" id="load-more-btn" class="btn-primary"<?php pro_listing_data_attributes( $state['context'], $state['offset'], $state['per_page'], '.ed-archive-grid' ); ?>>Cargar más noticias</button></div>
        <?php endif; ?>
    <?php else : ?><section class="ed-empty"><h2>No encontramos historias con esos términos</h2><p>Prueba una búsqueda más breve o explora las secciones del medio.</p><?php get_search_form(); ?></section><?php endif; ?>
</main>
<?php get_footer(); ?>
