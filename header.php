<?php /** Cabecera de CMS-Editorial. */ ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="<?php echo esc_attr( cms_editorial_setting( 'color_nav_bg' ) ); ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<script>try{if(localStorage.getItem('theme')==='dark'){document.body.classList.add('dark-mode');}}catch(e){}</script>
<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#primary">Saltar al contenido principal</a>
    <header id="masthead" class="site-header ed-masthead">
        <div class="ed-utility-bar"><div class="container ed-utility-inner"><span class="date-display"><?php echo esc_html( wp_date( 'l, j F Y' ) ); ?></span><nav class="ed-utility-links" aria-label="Información del medio"><?php if ( has_nav_menu( 'topbar' ) ) { wp_nav_menu( array( 'theme_location' => 'topbar', 'container' => false, 'fallback_cb' => false ) ); } else { ?><a href="<?php echo esc_url( home_url( '/contacto/' ) ); ?>">Contacto</a><a href="<?php echo esc_url( home_url( '/clasificados/' ) ); ?>">Clasificados</a><?php } ?><span class="ed-edition-label">Edición digital</span></nav></div></div>
        <div class="container header-main ed-brand-row">
            <div class="site-branding"><?php cms_editorial_render_branding(); ?><?php if ( get_bloginfo( 'description' ) ) : ?><p class="ed-brand-description"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p><?php endif; ?></div>
            <div class="header-ad ed-header-ad">
                <?php $ads = pro_get_active_ads( 'header' );
                if ( $ads ) : foreach ( $ads as $index => $ad ) : ?>
                    <div class="ad-slide<?php echo $index === 0 ? ' active' : ''; ?>"><a href="<?php echo esc_url( $ad['url'] ?: home_url( '/contacto/' ) ); ?>" target="_blank" rel="noopener noreferrer"><img src="<?php echo esc_url( $ad['image'] ); ?>" alt="<?php echo esc_attr( $ad['title'] ); ?>"></a></div>
                <?php endforeach; elseif ( is_active_sidebar( 'ad-header' ) ) : dynamic_sidebar( 'ad-header' ); elseif ( get_theme_mod( 'pro_show_ad_placeholders', true ) ) : ?>
                    <a class="ed-ad-placeholder ad-placeholder" href="<?php echo esc_url( home_url( '/contacto/' ) ); ?>"><span class="ed-ad-label">Publicidad</span><span>Tu marca, junto a nuestras historias</span><span aria-hidden="true">↗</span></a>
                <?php endif; ?>
            </div>
        </div>
        <div class="header-navigation-wrapper"><div class="container navigation-inner">
            <nav id="site-navigation" class="main-navigation" aria-label="Secciones de la publicación">
                <button class="menu-toggle" type="button" aria-controls="primary-menu-mobile" aria-expanded="false" aria-label="Abrir menú de secciones"><span class="material-symbols-outlined" aria-hidden="true">menu</span><span class="ed-menu-caption">Secciones</span></button>
                <?php wp_nav_menu( array( 'theme_location' => 'primary', 'menu_id' => 'primary-menu-desktop', 'container_class' => 'desktop-menu-wrapper', 'fallback_cb' => '__return_false' ) );
                wp_nav_menu( array( 'theme_location' => has_nav_menu( 'mobile' ) ? 'mobile' : 'primary', 'menu_id' => 'primary-menu-mobile', 'container_class' => 'mobile-menu-wrapper', 'fallback_cb' => '__return_false' ) ); ?>
            </nav>
            <div class="header-actions"><div class="search-container">
                <form role="search" method="get" class="search-form ajax-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>"><label><span class="screen-reader-text">Buscar noticias</span><input type="search" class="search-field ajax-search-input" placeholder="Buscar noticias" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" autocomplete="off" aria-controls="cms-search-results" aria-keyshortcuts="/"></label><button type="submit" class="search-submit" aria-label="Buscar"><span class="material-symbols-outlined" aria-hidden="true">search</span></button></form><div id="cms-search-results" class="ajax-search-results-container" role="region" aria-label="Resultados de búsqueda" aria-live="polite"></div>
            </div><button id="dark-mode-toggle" type="button" class="dark-mode-toggle ed-icon-button" aria-label="Cambiar entre modo claro y oscuro" title="Cambiar apariencia"><span class="material-symbols-outlined" aria-hidden="true">dark_mode</span></button></div>
        </div></div>
        <?php
        $ticker_ids = get_transient( 'pro_ticker_posts' );
        if ( $ticker_ids === false ) { $ticker_ids = cms_editorial_home_posts( array( 'numberposts' => 10, 'fields' => 'ids' ) ); set_transient( 'pro_ticker_posts', $ticker_ids, 5 * MINUTE_IN_SECONDS ); }
        $ticker = $ticker_ids ? cms_editorial_home_posts( array( 'numberposts' => 10, 'post__in' => $ticker_ids, 'orderby' => 'post__in' ) ) : array();
        if ( $ticker ) : ?>
        <div class="news-ticker-wrapper"><div class="container news-ticker-inner"><div class="news-ticker-label"><span class="ed-live-dot" aria-hidden="true"></span> Última hora</div><div class="news-ticker-slider" id="newsTickerSlider"><?php foreach ( $ticker as $index => $story ) : ?><div class="ticker-slide<?php echo $index === 0 ? ' active' : ''; ?>"><a href="<?php echo esc_url( get_permalink( $story ) ); ?>"><?php echo esc_html( get_the_title( $story ) ); ?></a></div><?php endforeach; ?></div><button type="button" id="cms-ticker-toggle" class="ed-ticker-toggle" aria-label="Pausar titulares" aria-pressed="false"><span class="material-symbols-outlined" aria-hidden="true">pause</span></button></div></div>
        <?php endif; ?>
        <?php if ( is_single() ) : ?><div id="reading-progress-container" class="reading-progress-container"><div id="reading-progress-bar" class="reading-progress-bar"></div></div><?php endif; ?>
    </header>
    <div id="swup" class="transition-fade">
