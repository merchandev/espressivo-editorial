<?php
/** Navegación adaptable con enlaces y controles de subsecciones independientes. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class CMS_Editorial_Nav_Walker extends Walker_Nav_Menu {
    private $submenu_id = '';

    public function start_lvl( &$output, $depth = 0, $args = null ) {
        $attributes = apply_filters( 'nav_menu_submenu_attributes', array( 'id' => $this->submenu_id, 'class' => 'sub-menu' ), $args, $depth );
        $output .= '<ul';
        foreach ( $attributes as $name => $value ) {
            if ( is_scalar( $value ) && $value !== '' ) { $output .= ' ' . esc_attr( $name ) . '="' . esc_attr( $value ) . '"'; }
        }
        $output .= '>';
    }

    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $markup = '';
        parent::start_el( $markup, $item, $depth, $args, $id );
        if ( in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
            $this->submenu_id = $args->menu_id . '-children-' . $item->ID;
            $label = wp_strip_all_tags( $item->title );
            $attributes = ' type="button" aria-expanded="false" aria-controls="' . esc_attr( $this->submenu_id ) . '"';
            $chevron = '<span class="cms-menu-chevron" aria-hidden="true"></span>';
            if ( in_array( trim( $item->url ), array( '', '#', 'javascript:void(0);' ), true ) ) {
                $button = '<button class="cms-menu-group"' . $attributes . '><span>' . esc_html( $label ) . '</span>' . $chevron . '</button>';
                $markup = preg_replace_callback( '/<a\b[^>]*>.*?<\/a>/s', function() use ( $button ) { return $button; }, $markup, 1 );
            } else {
                $markup .= '<button class="cms-submenu-toggle"' . $attributes . ' aria-label="' . esc_attr( 'Subsecciones de ' . $label ) . '">' . $chevron . '</button>';
            }
        }
        $output .= $markup;
    }
}

add_filter( 'nav_menu_item_id', function( $id, $item, $args ) {
    return empty( $args->cms_editorial_navigation ) ? $id : $args->menu_id . '-item-' . $item->ID;
}, 10, 3 );

function cms_editorial_menu_fallback( $args ) {
    echo '<ul id="' . esc_attr( $args['menu_id'] ) . '" class="cms-menu-list"><li><a href="' . esc_url( home_url( '/' ) ) . '">Inicio</a></li>';
    foreach ( get_categories( array( 'parent' => 0, 'number' => 8, 'hide_empty' => true ) ) as $category ) {
        echo '<li><a href="' . esc_url( get_category_link( $category ) ) . '">' . esc_html( $category->name ) . '</a></li>';
    }
    echo '</ul>';
}

function cms_editorial_navigation_menu( $location, $id ) {
    wp_nav_menu( array(
        'theme_location' => $location, 'container' => false, 'menu_id' => $id, 'menu_class' => 'cms-menu-list',
        'walker' => new CMS_Editorial_Nav_Walker(), 'fallback_cb' => 'cms_editorial_menu_fallback', 'cms_editorial_navigation' => true,
    ) );
}

function cms_editorial_menu_drawer() {
    ?>
    <div id="cms-menu-drawer" class="cms-menu-overlay" hidden>
        <div class="cms-menu-backdrop" data-cms-menu-close aria-hidden="true"></div>
        <section class="cms-menu-panel" role="dialog" aria-modal="true" aria-labelledby="cms-menu-title" tabindex="-1">
            <header class="cms-menu-header"><div><span class="cms-menu-brand"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span><h2 id="cms-menu-title">Explora nuestras secciones</h2></div><button type="button" class="cms-menu-close" data-cms-menu-close aria-label="Cerrar menú"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="m6 6 12 12M6 18 18 6"/></svg></button></header>
            <form class="cms-drawer-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>"><label class="screen-reader-text" for="cms-menu-search">Buscar noticias</label><input id="cms-menu-search" type="search" name="s" placeholder="¿Qué quieres encontrar?" value="<?php echo esc_attr( get_search_query() ); ?>"><button type="submit" aria-label="Buscar noticias"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4 4"/></svg></button></form>
            <nav class="cms-drawer-navigation" aria-label="Secciones de la publicación"><?php cms_editorial_navigation_menu( has_nav_menu( 'mobile' ) ? 'mobile' : 'primary', 'primary-menu-mobile' ); ?></nav>
            <footer class="cms-menu-footer"><span>Una conversación abierta</span><a href="<?php echo esc_url( home_url( '/contacto/' ) ); ?>">Contacta con la redacción <span aria-hidden="true">↗</span></a></footer>
        </section>
    </div>
    <?php
}

add_action( 'wp_enqueue_scripts', function() {
    $dir = get_template_directory();
    wp_enqueue_style( 'cms-editorial-navigation', get_template_directory_uri() . '/assets/css/navigation.css', array( 'cms-editorial-ui' ), filemtime( $dir . '/assets/css/navigation.css' ) );
    wp_enqueue_script( 'cms-editorial-navigation', get_template_directory_uri() . '/assets/js/navigation.js', array( 'cms-editorial-ui' ), filemtime( $dir . '/assets/js/navigation.js' ), true );
}, 120 );
