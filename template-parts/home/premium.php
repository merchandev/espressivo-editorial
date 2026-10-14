<?php
$premium_cats = cms_editorial_sections();
foreach ( $premium_cats as $cat_slug => $cat_name ) :
    $cat_obj = get_category_by_slug( $cat_slug );
    if ( ! $cat_obj ) continue; // La categoría no existe en WordPress, saltar

    $cat_args = array(
        'cat'                    => $cat_obj->term_id, // ID directo — más fiable
        'posts_per_page'         => 6,
        'post_status'            => 'publish',
        'orderby'                => 'date',
        'order'                  => 'DESC',
        'ignore_sticky_posts'    => 1,
        'cache_results'          => false,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'no_found_rows'          => true,
    );
    $cat_query = new WP_Query( $cat_args );
    if ( $cat_query->have_posts() ) :
    ?>
    <section class="cms-category-section">
        <h2 class="cms-section-title"><span><?php echo esc_html( $cat_name ); ?></span></h2>
        <div class="cms-grid">
            <?php
                $count = 0;
                while ( $cat_query->have_posts() ) : $cat_query->the_post();
                    if ( $count === 0 ) :
                        ?>
                        <article class="cms-main-article">
                            <a href="<?php the_permalink(); ?>" class="post-thumbnail">
                                <?php
                                if ( ! pro_the_post_image( 'card-thumbnail', array( 'loading' => 'lazy' ) ) ) {
                                    echo '<div class="placeholder-image"><span>Foto</span></div>';
                                }
                                ?>
                            </a>
                            <div class="cms-main-content">
                                <h3 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <div class="entry-summary"><?php the_excerpt(); ?></div>
                            </div>
                        </article>
                        <div class="cms-side-articles">
                        <?php
                    else :
                        ?>
                        <article class="cms-list-item">
                            <h4 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                        </article>
                        <?php
                    endif;
                    $count++;
                endwhile;
                if ( $count > 1 ) { echo '</div>'; } elseif ( $count === 1 ) { echo '<div class="cms-side-articles empty-side"></div>'; }
            ?>
        </div>
    </section>
    <?php endif; wp_reset_postdata(); ?>

    <?php if ( $cat_slug === 'internacional' ) : ?>
        <!-- Publicidad Extra Entre Secciones -->
        <div class="premium-ads-single" style="margin: 40px 0;">
            <?php get_template_part('template-parts/ads/in-feed', null, array('location' => 'internacional-ad', 'size' => '1200x200')); ?>
        </div>
    <?php endif; ?>

<?php endforeach; ?>
