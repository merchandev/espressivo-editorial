<?php
/** Portada modular de CMS-Editorial. */
get_header();
$featured = cms_editorial_home_posts( array( 'category_name' => cms_editorial_setting( 'home_featured_category' ) ) );
if ( count( $featured ) < 3 ) {
    $featured = array_merge( $featured, cms_editorial_home_posts( array( 'numberposts' => 3 - count( $featured ), 'post__not_in' => wp_list_pluck( $featured, 'ID' ) ) ) );
}
$featured_ids = wp_list_pluck( $featured, 'ID' );
$latest = cms_editorial_home_posts( array( 'numberposts' => 5, 'post__not_in' => $featured_ids ) );
$cover = get_option( 'pro_portada_actual', '' );
?>
<main id="primary" class="site-main ed-home">
    <div class="container ed-page-intro">
        <div><span class="ed-eyebrow">Edición digital · <?php echo esc_html( wp_date( 'j F Y' ) ); ?></span><h1><?php echo esc_html( cms_editorial_setting( 'home_title' ) ?: 'Actualidad' ); ?></h1></div>
        <a class="ed-text-link ed-intro-link" href="#ed-sections">Explorar secciones <span aria-hidden="true">↓</span></a>
    </div>

    <div class="container">
        <?php if ( $featured ) : ?>
        <section class="ed-hero-grid" aria-label="Noticias destacadas">
            <div class="ed-lead-column">
                <?php cms_editorial_story( $featured[0], 'lead' ); ?>
                <?php if ( count( $featured ) > 1 ) : ?>
                    <div class="ed-featured-secondary"><?php foreach ( array_slice( $featured, 1 ) as $story ) { cms_editorial_story( $story, 'compact' ); } ?></div>
                <?php endif; ?>
            </div>
            <aside class="ed-latest-panel" aria-labelledby="ed-latest-title">
                <div class="ed-panel-heading"><span class="ed-live-dot" aria-hidden="true"></span><h2 id="ed-latest-title">Últimas actualizaciones</h2><span class="ed-panel-caption">Al día</span></div>
                <?php if ( $latest ) : ?>
                    <ol class="ed-latest-list"><?php foreach ( $latest as $index => $story ) : ?><li><span class="ed-list-number" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span><?php cms_editorial_story( $story, 'compact' ); ?></li><?php endforeach; ?></ol>
                <?php else : ?><p class="ed-muted">Aquí encontrarás las próximas actualizaciones.</p><?php endif; ?>
                <a class="ed-panel-action" href="<?php echo esc_url( get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/?post_type=post' ) ); ?>">Ver todas las noticias <span aria-hidden="true">↗</span></a>
            </aside>
        </section>
        <?php else : ?><section class="ed-empty"><h2>Un nuevo espacio para la información</h2><p>Las próximas historias de nuestra publicación estarán aquí.</p></section><?php endif; ?>

        <?php cms_editorial_ad_slot( 'below-hero' ); ?>

        <div class="ed-content-layout" id="ed-sections">
            <div class="ed-sections-feed">
                <?php
                $shown = $featured_ids;
                $section_index = 0;
                $section_ads = array( 'nacional' => 'nacional-ad', 'mundo' => 'internacional-ad', 'internacional' => 'internacional-ad', 'economia' => 'economia-ad', 'seguridad' => 'seguridad-ad', 'sucesos' => 'seguridad-ad', 'deportes' => 'zona-deportiva-ad', 'zona-deportiva' => 'zona-deportiva-ad' );
                $rendered_ads = array();
                $slugs = array_unique( array_filter( array_map( 'trim', explode( ',', cms_editorial_setting( 'home_sections' ) . ',' . cms_editorial_setting( 'local_categories' ) ) ) ) );
                foreach ( $slugs as $slug ) :
                    $category = get_category_by_slug( $slug );
                    if ( ! $category ) { continue; }
                    $stories = cms_editorial_home_posts( array( 'category' => $category->term_id, 'post__not_in' => $shown ) );
                    if ( ! $stories ) { continue; }
                    $shown = array_merge( $shown, wp_list_pluck( $stories, 'ID' ) );
                    ?>
                    <section class="ed-news-section" aria-label="<?php echo esc_attr( $category->name ); ?>">
                        <?php cms_editorial_section_heading( $category->name, get_category_link( $category ), $section_index === 0 ? 'El contexto, en cada sección' : '' ); ?>
                        <div class="ed-section-grid<?php echo count( $stories ) === 1 ? ' ed-section-grid--single' : ''; ?>"><?php foreach ( $stories as $story ) { cms_editorial_story( $story ); } ?></div>
                    </section>
                    <?php
                    if ( isset( $section_ads[ $slug ] ) && ! in_array( $section_ads[ $slug ], $rendered_ads, true ) ) {
                        cms_editorial_ad_slot( $section_ads[ $slug ] );
                        $rendered_ads[] = $section_ads[ $slug ];
                    }
                    ++$section_index;
                    if ( $section_index === 3 ) { cms_editorial_ad_slot( 'in-feed-1' ); }
                    if ( $section_index === 6 ) { cms_editorial_ad_slot( 'in-feed-2' ); }
                    ?>
                <?php endforeach; ?>
                <?php
                if ( $section_index < 3 ) { cms_editorial_ad_slot( 'in-feed-1' ); }
                if ( $section_index < 6 ) { cms_editorial_ad_slot( 'in-feed-2' ); }
                ?>
            </div>

            <aside class="ed-home-sidebar">
                <div class="ed-sidebar-stack">
                    <?php if ( $cover && cms_editorial_setting( 'home_show_cover' ) ) : ?>
                    <section class="ed-edition-panel" id="ed-edition" aria-labelledby="ed-edition-title">
                        <span class="ed-eyebrow">La edición impresa</span><h2 id="ed-edition-title">Nuestra portada</h2><p>Una mirada a las historias del día.</p>
                        <button type="button" class="ed-cover-trigger card-portada" data-full-url="<?php echo esc_url( $cover ); ?>" aria-label="Abrir la portada de la edición impresa"><img src="<?php echo esc_url( $cover ); ?>" alt="Portada de <?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" loading="lazy"><span class="ed-cover-caption"><span class="material-symbols-outlined" aria-hidden="true">zoom_in</span> Ampliar portada</span></button>
                    </section>
                    <?php endif; ?>

                    <?php if ( cms_editorial_setting( 'home_show_services' ) ) : ?>
                    <section class="ed-services-panel" aria-labelledby="ed-services-title">
                        <span class="ed-eyebrow">Más cerca de ti</span><h2 id="ed-services-title">Servicios del medio</h2>
                        <?php foreach ( array( 'clasificados' => array( 'Clasificados', 'Anuncios y oportunidades', 'format_list_bulleted' ), 'carteles' => array( 'Carteles y edictos', 'Avisos y documentos públicos', 'description' ), 'contacto' => array( 'Contacta con la redacción', 'Tu historia también cuenta', 'chat_bubble_outline' ) ) as $path => $service ) : ?>
                        <a class="ed-service-link" href="<?php echo esc_url( home_url( '/' . $path . '/' ) ); ?>"><span class="material-symbols-outlined ed-service-icon" aria-hidden="true"><?php echo esc_html( $service[2] ); ?></span><span><strong><?php echo esc_html( $service[0] ); ?></strong><small><?php echo esc_html( $service[1] ); ?></small></span><span aria-hidden="true">↗</span></a>
                        <?php endforeach; ?>
                    </section>
                    <?php endif; ?>

                    <section class="ed-community-panel"><span class="ed-eyebrow">Una conversación abierta</span><h2>¿Tienes una historia?</h2><p>Comparte una propuesta, una pregunta o algo que tu comunidad necesita contar.</p><a class="ed-button" href="<?php echo esc_url( home_url( '/contacto/' ) ); ?>">Escribir a la redacción <span aria-hidden="true">↗</span></a></section>
                </div>
            </aside>
        </div>
    </div>
</main>
<?php get_footer(); ?>
