<?php
/** Template Name: Página de categoría */
get_header();
$page = get_queried_object();
$category = $page instanceof WP_Post ? get_category_by_slug( $page->post_name ) : null;
if ( ! $category && $page instanceof WP_Post ) { $category = get_term_by( 'name', $page->post_title, 'category' ); }
$paged = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );
$per_page = PRO_CATEGORY_PAGE_PER_PAGE;
?>
<main id="primary" class="site-main container archive-container ed-archive-page">
    <header class="page-header"><span class="ed-eyebrow">Las historias de esta sección</span><h1 class="page-title"><?php echo esc_html( $category ? $category->name : get_the_title( $page ) ); ?></h1><?php if ( $category && $category->description ) : ?><div class="archive-description"><?php echo wp_kses_post( wpautop( $category->description ) ); ?></div><?php endif; ?></header>
    <?php get_template_part( 'template-parts/ads/category-sponsor', null, array( 'cat_name' => $category ? $category->name : get_the_title( $page ), 'location' => 'category-top' ) ); ?>
    <?php if ( $category ) :
        $children = get_categories( array( 'parent' => $category->parent ?: $category->term_id, 'hide_empty' => false ) );
        if ( $children ) : ?><nav class="category-subnav" aria-label="Subsecciones"><ul class="subnav-list"><?php foreach ( $children as $child ) : ?><li><a href="<?php echo esc_url( get_category_link( $child ) ); ?>"><?php echo esc_html( $child->name ); ?></a></li><?php endforeach; ?></ul></nav><?php endif;
        $context = array( 'cat' => $category->term_id );
        $news = new WP_Query( pro_get_listing_query_args( $context, array( 'posts_per_page' => $per_page, 'paged' => $paged ) ) );
        if ( $news->have_posts() ) : ?>
            <div class="category-grid"><?php while ( $news->have_posts() ) : $news->the_post(); get_template_part( 'template-parts/content/card' ); endwhile; ?></div>
            <?php $offset = ( $paged - 1 ) * $per_page + $news->post_count;
            if ( $offset < $news->found_posts ) : ?><div class="load-more-container text-center"><button type="button" id="load-more-btn" class="btn-primary"<?php pro_listing_data_attributes( $context, $offset, $per_page, '.category-grid' ); ?>>Cargar más noticias</button></div><?php endif;
        else : ?><section class="ed-empty"><h2>Las próximas historias estarán aquí</h2><p>Vuelve a la portada para explorar otras secciones.</p><a class="ed-text-link" href="<?php echo esc_url( home_url( '/' ) ); ?>">Ir a la portada ↗</a></section><?php endif;
        wp_reset_postdata();
    else : ?><section class="ed-empty"><p>Esta sección todavía no tiene historias disponibles.</p></section><?php endif; ?>
</main>
<?php get_footer(); ?>
