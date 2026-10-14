<?php
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">

    <!-- PWA Theme Color -->
    <meta name="theme-color" content="<?php echo esc_attr( cms_editorial_setting( 'color_nav_bg' ) ); ?>">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Saltar al contenido principal', 'pro' ); ?></a>

    <header id="masthead" class="site-header">
        <div class="header-topbar">
            <div class="container topbar-inner">
                <div class="date-display">
                    <?php echo date_i18n( 'l, j \d\e F \d\e Y' ); ?>
                </div>
                <nav id="topbar-navigation" class="topbar-navigation">
                    <?php
                    if ( has_nav_menu( 'topbar' ) ) {
                        wp_nav_menu( array(
                            'theme_location' => 'topbar',
                            'menu_id'        => 'topbar-menu',
                            'container'      => false,
                            'fallback_cb'    => '__return_false',
                        ) );
                    }
                    ?>
                    <button id="dark-mode-toggle" class="dark-mode-toggle" aria-label="Cambiar modo oscuro" title="Modo Oscuro">
                        <span class="material-symbols-outlined" aria-hidden="true">dark_mode</span>
                    </button>
                </nav><!-- #topbar-navigation -->
            </div>
        </div>

        <div class="header-main container">
            <div class="site-branding">
                <?php cms_editorial_render_branding(); ?>
            </div><!-- .site-branding -->

            <div class="header-ad">
                <?php
                // Obtener anuncios dinámicos del Gestor
                $header_ads = function_exists('pro_get_active_ads') ? pro_get_active_ads('header') : array();

                if ( !empty($header_ads) ) :
                    foreach ($header_ads as $index => $ad) :
                        $active_class = ($index === 0) ? ' active' : '';
                        $url = !empty($ad['url']) ? esc_url($ad['url']) : '#';
                        ?>
                        <div class="ad-slide<?php echo $active_class; ?>">
                            <a href="<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                                <img src="<?php echo esc_url($ad['image']); ?>" alt="<?php echo esc_attr($ad['title']); ?>" style="width:100%; height:100%; object-fit:contain;">
                            </a>
                        </div>
                        <?php
                    endforeach;
                else :
                    if ( is_active_sidebar( 'ad-header' ) ) :
                        dynamic_sidebar( 'ad-header' );
                    elseif ( get_theme_mod( 'pro_show_ad_placeholders', true ) ) : ?>
                        <div class="ad-placeholder">
                            <span>Espacio Publicitario (728x90 / Responsive)</span>
                        </div>
                    <?php
                    endif;
                endif;
                ?>
            </div>
        </div><!-- .header-main -->

        <div class="header-navigation-wrapper">
            <div class="container navigation-inner">
                <nav id="site-navigation" class="main-navigation">
                    <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false" aria-label="<?php esc_attr_e( 'Menú', 'pro' ); ?>"><span class="material-symbols-outlined" aria-hidden="true" style="vertical-align: middle;">menu</span></button>
                    <?php
                    // Menú Escritorio
                    wp_nav_menu( array(
                        'theme_location' => 'primary',
                        'menu_id'        => 'primary-menu-desktop',
                        'container_class'=> 'desktop-menu-wrapper',
                        'fallback_cb'    => '__return_false',
                    ) );

                    // Menú Móvil
                    if ( has_nav_menu( 'mobile' ) ) {
                        wp_nav_menu( array(
                            'theme_location' => 'mobile',
                            'menu_id'        => 'primary-menu-mobile',
                            'container_class'=> 'mobile-menu-wrapper',
                            'fallback_cb'    => '__return_false',
                        ) );
                    } else {
                        // Si no hay menú móvil, usar el principal
                        wp_nav_menu( array(
                            'theme_location' => 'primary',
                            'menu_id'        => 'primary-menu-mobile',
                            'container_class'=> 'mobile-menu-wrapper',
                            'fallback_cb'    => '__return_false',
                        ) );
                    }
                    ?>
                </nav><!-- #site-navigation -->

                <div class="header-actions">
                    <div class="search-container">
                        <form role="search" method="get" class="search-form ajax-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                            <label>
                                <span class="screen-reader-text"><?php echo _x( 'Buscar:', 'label', 'pro' ); ?></span>
                                <input type="search" class="search-field ajax-search-input" placeholder="<?php echo esc_attr_x( 'Buscar noticias...', 'placeholder', 'pro' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" autocomplete="off" />
                            </label>
                            <button type="submit" class="search-submit" aria-label="Buscar">
                                <span class="material-symbols-outlined" aria-hidden="true">search</span>
                            </button>
                        </form>
                        <div class="ajax-search-results-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        // Ticker de Último Minuto — con transient para evitar query en cada carga
        $ticker_posts = get_transient( 'pro_ticker_posts' );
        if ( false === $ticker_posts ) {
            $ticker_query_tmp = new WP_Query( array(
                'post_type'      => 'post',
                'posts_per_page' => 10,
                'post_status'    => 'publish',
                'fields'         => 'ids',
            ) );
            $ticker_posts = $ticker_query_tmp->posts;
            set_transient( 'pro_ticker_posts', $ticker_posts, 5 * MINUTE_IN_SECONDS );
        }
        $ticker_query = new WP_Query( array(
            'post_type'   => 'post',
            'post_status' => 'publish',
            'post__in'    => ! empty( $ticker_posts ) ? $ticker_posts : array( 0 ),
            'orderby'     => 'post__in',
        ) );

        if ( $ticker_query->have_posts() ) : ?>
            <div class="news-ticker-wrapper">
                <div class="container news-ticker-inner">
                    <div class="news-ticker-label">Último minuto</div>
                    <div class="news-ticker-slider" id="newsTickerSlider">
                        <?php
                        $ticker_index = 0;
                        while ( $ticker_query->have_posts() ) : $ticker_query->the_post();
                        ?>
                            <div class="ticker-slide <?php echo ($ticker_index === 0) ? 'active' : ''; ?>">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </div>
                        <?php
                        $ticker_index++;
                        endwhile;
                        wp_reset_postdata();
                        ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if ( is_single() ) : ?>
            <div id="reading-progress-container" class="reading-progress-container">
                <div id="reading-progress-bar" class="reading-progress-bar"></div>
            </div>
        <?php endif; ?>
    </header><!-- #masthead -->

    <div id="swup" class="transition-fade">
