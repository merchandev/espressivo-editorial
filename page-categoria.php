<?php
/**
 * @author Arturo Merchan | Merchan.Dev | Espressivo Venezuela,C.A
 * 
 * ADVERTENCIA LEGAL:
 * Queda totalmente prohibida su reproduccion, edicion, venta, propaganda, alteracion 
 * o cualquier otra accion que de una u otra forma violente la propiedad intelectual, 
 * material y digital de este proyecto. Esta infraccion esta prohibida y penada por la ley.
 */
/**
 * Template Name: Página de Categoría Automática
 *
 * Plantilla para las páginas físicas que muestran las noticias de su misma categoría.
 *
 * @package Pro
 */

get_header();

// Obtenemos el slug de la página (ej: "deportes", "salud", "opinion")
global $post;
$cat_slug = $post->post_name;
$cat_name = get_the_title(); // Solo para mostrar en el título visualmente

$category = get_term_by( 'slug', $cat_slug, 'category' );

if ( ! $category ) {
    $category = get_term_by( 'name', $cat_name, 'category' );
}

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

// Misma consulta que el scroll infinito (pro_load_more_posts): tamaño de página,
// orden fecha + ID y regla de subcategorías (Opinión y Bienestar no las mezclan).
$args = pro_get_listing_query_args(
    array( 'cat' => $category ? $category->term_id : 0 ),
    array(
        'posts_per_page' => PRO_CATEGORY_PAGE_PER_PAGE,
        'paged'          => $paged,
    )
);

if ( ! $category ) {
    // Categoría no encontrada: devolver conjunto vacío.
    $args['post__in'] = array( 0 );
}

$query = new WP_Query( $args );
?>

<main id="primary" class="site-main container archive-container">

    <header class="page-header">
        <h1 class="page-title"><?php echo esc_html( $cat_name ); ?></h1>
    </header><!-- .page-header -->

    <!-- BANNER PATROCINADOR DE CATEGORÍA -->
    <?php
    get_template_part( 'template-parts/ads/category-sponsor', null, array(
        'cat_name' => $cat_name,
        'location' => 'category-top',
    ) );
    ?>

    <?php
    // MOSTRAR SUBCATEGORÍAS DE LA CATEGORÍA ACTUAL (Oculto a petición)
    /*
    if ( $category && $category->taxonomy === 'category' ) {
        $parent_id = ($category->category_parent == 0) ? $category->term_id : $category->category_parent;
        
        $subcategories = get_categories( array(
            'child_of'   => $parent_id,
            'hide_empty' => false,
        ) );

        if ( ! empty( $subcategories ) ) {
            echo '<div class="category-subnav"><ul class="subnav-list">';
            if ($parent_id != $category->term_id) {
                echo '<li><a href="' . esc_url( get_category_link( $parent_id ) ) . '">' . esc_html__( 'Todas', 'pro' ) . '</a></li>';
            } else {
                echo '<li class="current-cat"><a href="' . esc_url( get_category_link( $parent_id ) ) . '">' . esc_html__( 'Todas', 'pro' ) . '</a></li>';
            }

            foreach ( $subcategories as $subcat ) {
                $current_class = ($subcat->term_id == $category->term_id) ? 'current-cat' : '';
                echo '<li class="' . $current_class . '"><a href="' . esc_url( get_category_link( $subcat->term_id ) ) . '">' . esc_html( $subcat->name ) . '</a></li>';
            }
            echo '</ul></div>';
        }
    }
    */
    ?>

    <?php if ( $query->have_posts() ) : ?>

        <div class="category-grid-wrapper">
            <?php
            $post_count = 0;
            if ( $paged == 1 && $query->have_posts() ) {
                $query->the_post();
                $post_count++;
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('category-hero'); ?>>
                    <a href="<?php the_permalink(); ?>" class="post-thumbnail hero-thumbnail" aria-hidden="true" tabindex="-1">
                        <?php pro_the_post_image( 'large', array( 'loading' => 'eager' ) ); ?>
                    </a>
                    <div class="hero-content">
                        <div class="post-meta">
                            <?php pro_post_categories( null, $cat_slug ); ?>
                            <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
                        </div>
                        <h2 class="entry-title hero-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
                        <div class="entry-excerpt">
                            <?php echo esc_html( wp_trim_words( get_the_excerpt(), 35, '...' ) ); ?>
                        </div>
                    </div>
                </article>
                <?php
            }
            ?>
            <div class="category-grid">
                <?php
                while ( $query->have_posts() ) :
                    $query->the_post();
                    get_template_part( 'template-parts/content/card', null, array( 'cat_slug' => $cat_slug ) );
                endwhile;
                wp_reset_postdata();
                ?>
            </div>
        </div>

        <?php
        $shown_offset = ( $paged - 1 ) * PRO_CATEGORY_PAGE_PER_PAGE + $query->post_count;
        if ( $shown_offset < $query->found_posts ) :
            ?>
            <div class="infinite-scroll-trigger"<?php pro_listing_data_attributes( array( 'cat' => $category->term_id ), $shown_offset, PRO_CATEGORY_PAGE_PER_PAGE, '.category-grid' ); ?>>
                <div class="loading-spinner">Cargando más noticias...</div>
            </div>
        <?php endif; ?>

    <?php else : ?>
        <section class="no-results not-found">
            <div class="page-content">
                <p><?php esc_html_e( 'Aún no hay noticias en esta sección.', 'pro' ); ?></p>
            </div>
        </section>
    <?php endif; ?>

</main><!-- #primary -->

<?php
get_footer();
