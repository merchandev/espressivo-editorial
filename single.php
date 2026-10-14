<?php
/** Lectura de artículos de CMS-Editorial. */
get_header();
while ( have_posts() ) : the_post();
    $article_id = get_the_ID();
    $category = cms_editorial_story_category( $article_id );
    $firma = get_post_meta( $article_id, '_pro_firma_autor', true ) ?: get_the_author();
    $related = cms_editorial_home_posts( array( 'numberposts' => 3, 'post__not_in' => array( $article_id ), 'category' => $category ? $category->term_id : 0 ) );
    $image = pro_get_post_image_id( get_post() );
    ?>
<main id="primary" class="site-main ed-article-page container">
    <nav class="ed-breadcrumbs" aria-label="Ruta de navegación"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Inicio</a><span aria-hidden="true">/</span><?php if ( $category ) : ?><a href="<?php echo esc_url( get_category_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a><?php endif; ?><span aria-hidden="true">/</span><span aria-current="page">Artículo</span></nav>
    <div class="ed-article-layout">
        <article id="post-<?php the_ID(); ?>" <?php post_class( 'ed-article' ); ?>>
            <header class="ed-article-header">
                <?php if ( $category ) : ?><a class="ed-category" href="<?php echo esc_url( get_category_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a><?php endif; ?>
                <h1 class="entry-title"><?php the_title(); ?></h1>
                <?php if ( has_excerpt() ) : ?><p class="ed-article-deck"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
                <div class="ed-byline"><span class="ed-author-avatar" aria-hidden="true"><span class="material-symbols-outlined">edit_note</span></span><div><span class="ed-author-name"><?php echo esc_html( $firma ); ?></span><span class="ed-story-meta"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y · H:i' ) ); ?></time><span aria-hidden="true">·</span><?php echo absint( cms_editorial_reading_minutes( get_post() ) ); ?> min de lectura</span></div></div>
                <div class="ed-reading-toolbar" aria-label="Herramientas de lectura"><button type="button" class="ed-tool-button" data-cms-share data-url="<?php the_permalink(); ?>" data-title="<?php echo esc_attr( get_the_title() ); ?>"><span class="material-symbols-outlined" aria-hidden="true">share</span> Compartir</button><button type="button" class="ed-tool-button" data-cms-copy data-url="<?php the_permalink(); ?>"><span class="material-symbols-outlined" aria-hidden="true">link</span> Copiar enlace</button><div class="ed-font-tools"><span>Texto</span><button type="button" data-cms-font="-1" aria-label="Reducir el tamaño del texto">A−</button><button type="button" data-cms-font="1" aria-label="Aumentar el tamaño del texto">A+</button></div><span class="ed-tool-status" role="status" aria-live="polite"></span></div>
            </header>
            <?php if ( $image ) : ?><figure class="ed-article-image"><?php echo wp_get_attachment_image( $image, 'full', false, array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?><?php if ( wp_get_attachment_caption( $image ) ) : ?><figcaption><?php echo esc_html( wp_get_attachment_caption( $image ) ); ?></figcaption><?php endif; ?></figure><?php endif; ?>
            <div class="entry-content ed-reading-content" data-paywall="content"><?php the_content(); ?></div>
            <?php if ( is_active_sidebar( 'ad-in-feed' ) ) : ?><div class="ed-ad-slot"><?php dynamic_sidebar( 'ad-in-feed' ); ?></div><?php endif; ?>
            <footer class="ed-article-footer"><?php the_tags( '<div class="ed-article-tags"><span>Temas:</span> ', ' ', '</div>' ); ?><a class="ed-text-link" href="<?php echo esc_url( home_url( '/contacto/' ) ); ?>">Contactar con la redacción <span aria-hidden="true">↗</span></a></footer>
        </article>
        <aside class="ed-article-sidebar"><div class="ed-sidebar-stack"><section class="ed-related-panel"><span class="ed-eyebrow">Sigue explorando</span><h2>También te puede interesar</h2><?php foreach ( $related as $story ) { cms_editorial_story( $story, 'compact' ); } ?></section><?php if ( is_active_sidebar( 'sidebar-1' ) ) { dynamic_sidebar( 'sidebar-1' ); } ?></div></aside>
    </div>
    <?php
    $schema = array( '@context' => 'https://schema.org', '@type' => 'NewsArticle', 'headline' => get_the_title(), 'datePublished' => get_the_date( 'c' ), 'dateModified' => get_the_modified_date( 'c' ), 'author' => array( '@type' => $firma === 'Departamento de redacción' ? 'Organization' : 'Person', 'name' => $firma ), 'publisher' => array( '@type' => 'NewsMediaOrganization', 'name' => get_bloginfo( 'name' ) ) );
    if ( $image ) { $schema['image'] = wp_get_attachment_image_url( $image, 'full' ); }
    ?>
    <script type="application/ld+json"><?php echo wp_json_encode( $schema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?></script>
</main>
<?php endwhile; get_footer(); ?>
