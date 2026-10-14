<?php
/**
 * Ocultar wp-login.php y cambiar la URL de inicio de sesión a /turpial
 *
 * @package Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Acceso directo no permitido
}

// 1. Redirigir wp-login.php y wp-admin a 404, a menos que se acceda por /turpial
add_action( 'init', 'pro_custom_login_redirect' );
function pro_custom_login_redirect() {
    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
    $login_path  = 'turpial';

    $parsed_path = wp_parse_url( $request_uri, PHP_URL_PATH );
    $parsed_path = trim( (string) $parsed_path, '/' );

    // 1. Acceso permitido a la URL de login personalizada
    if ( $parsed_path === $login_path ) {
        if ( ! defined( 'PRO_CUSTOM_LOGIN' ) ) {
            define( 'PRO_CUSTOM_LOGIN', true );
        }

        global $pagenow, $error, $user_login, $action;
        $pagenow    = 'wp-login.php';
        $error      = '';
        $user_login = '';
        $action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : 'login';

        $_SERVER['REQUEST_URI'] = '/wp-login.php';

        // Evitar que WP intente redirigir de nuevo a la URL canónica
        remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );

        require_once ABSPATH . 'wp-login.php';
        exit;
    }

    // 2. Bloquear acceso a wp-admin para no logueados (mandar a inicio)
    if ( strpos( $parsed_path, 'wp-admin' ) === 0 && ! is_user_logged_in() && strpos( $parsed_path, 'admin-ajax.php' ) === false ) {
        wp_redirect( home_url() );
        exit;
    }

    // 3. Bloquear acceso directo a wp-login.php (mandar a inicio)
    if ( $parsed_path === 'wp-login.php' && ! defined( 'PRO_CUSTOM_LOGIN' ) ) {
        wp_redirect( home_url() );
        exit;
    }
}

// 2. Reescribir las URLs generadas por WordPress para que apunten a /turpial
add_filter( 'site_url', 'pro_filter_login_url', 10, 4 );
function pro_filter_login_url( $url, $path, $scheme, $blog_id ) {
    if ( $path === 'wp-login.php' || preg_match( '/wp-login\.php\?action=\w+/', $path ) ) {
        return str_replace( 'wp-login.php', 'turpial', $url );
    }
    return $url;
}

add_filter( 'network_site_url', 'pro_filter_login_url', 10, 3 );
add_filter( 'wp_redirect', 'pro_filter_login_redirect', 10, 2 );

function pro_filter_login_redirect( $location, $status ) {
    if ( strpos( $location, 'wp-login.php' ) !== false ) {
        $location = str_replace( 'wp-login.php', 'turpial', $location );
    }
    return $location;
}

/**
 * 3. Cambiar el logo de la pantalla de inicio de sesión (/turpial)
 */
function pro_custom_login_logo() {
    $logo_url = cms_editorial_logo_url( 'admin' );
    ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
            background-image: <?php echo $logo_url ? 'url("' . esc_url( $logo_url ) . '")' : 'none'; ?>;
            <?php if ( ! $logo_url ) : ?>
            text-indent: 0;
            width: auto;
            font-size: 24px;
            <?php endif; ?>
            height: 80px;
            width: 100%;
            background-size: contain;
            background-repeat: no-repeat;
            padding-bottom: 20px;
        }
        /* Color del botón de login para mantener el tema */
        .login .button-primary {
            background: <?php echo esc_html( cms_editorial_setting( 'color_admin_primary' ) ); ?> !important;
            border-color: <?php echo esc_html( cms_editorial_setting( 'color_admin_primary' ) ); ?> !important;
            color: #fff !important;
        }
        .login .button-primary:hover {
            background: <?php echo esc_html( cms_editorial_setting( 'color_admin_accent' ) ); ?> !important;
            border-color: <?php echo esc_html( cms_editorial_setting( 'color_admin_accent' ) ); ?> !important;
        }
    </style>
    <?php
}
add_action( 'login_enqueue_scripts', 'pro_custom_login_logo' );

function pro_custom_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'pro_custom_login_logo_url' );

function pro_custom_login_logo_url_title() {
    return get_bloginfo( 'name' );
}
add_filter( 'login_headertext', 'pro_custom_login_logo_url_title' );
