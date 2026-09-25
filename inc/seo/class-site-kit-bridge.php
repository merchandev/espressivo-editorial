<?php
namespace SSIVO_SEO\Includes;

/**
 * SSIVO-SEO · SiteKitBridge
 *
 * El panel SEO es la vista de Google Site Kit para todo el equipo y el Site Kit
 * completo queda reservado a los administradores:
 *
 *  1. Quien no es administrador no ve Site Kit (menú, widget del escritorio,
 *     barra de administración, estadísticas por entrada). Si abre una pantalla
 *     de Site Kit se le envía al panel SEO.
 *  2. El panel SEO lee los datos con las credenciales del administrador que
 *     conectó cada servicio (el "propietario" del módulo). Es el mismo
 *     mecanismo del "Dashboard Sharing" de Site Kit, habilitado solo mientras
 *     dura esa lectura (with_shared_read()).
 *
 * Auditoría fix: el proxy anterior hacía wp_set_current_user() al administrador,
 * pero Site Kit fija el usuario de su token OAuth en 'init' y no lo cambia con
 * wp_set_current_user(); para cualquier otro usuario (y para el cron) todas las
 * consultas fallaban y el panel mostraba "No disponible".
 */
class SiteKitBridge {

    const CAP_PREFIX    = 'googlesitekit_';
    const SCREEN_PREFIX = 'googlesitekit-';

    /**
     * Capacidades de Site Kit que se conceden durante la lectura compartida:
     * permiso de la ruta REST de datos y uso del token del propietario.
     */
    const SHARED_READ_CAPS = [
        'googlesitekit_view_posts_insights',
        'googlesitekit_read_shared_module_data',
        'googlesitekit_view_shared_dashboard',
        'googlesitekit_view_dashboard',
    ];

    /** @var bool */
    private static $shared_read = false;

    public static function register(): void {
        add_filter( 'map_meta_cap', [ __CLASS__, 'filter_capabilities' ], PHP_INT_MAX, 4 );
        add_action( 'admin_page_access_denied', [ __CLASS__, 'redirect_site_kit_screens' ] );
        add_action( 'admin_init', [ __CLASS__, 'redirect_site_kit_screens' ] );
    }

    public static function is_active(): bool {
        return defined( 'GOOGLESITEKIT_VERSION' );
    }

    /**
     * Solo los administradores ven el Site Kit completo.
     */
    public static function user_gets_full_site_kit( int $user_id ): bool {
        return $user_id > 0 && user_can( $user_id, 'manage_options' );
    }

    /**
     * Filtro map_meta_cap (prioridad máxima, tras el de Site Kit).
     */
    public static function filter_capabilities( $caps, $cap, $user_id, $args ) {
        if ( ! is_string( $cap ) || 0 !== strpos( $cap, self::CAP_PREFIX ) ) {
            return $caps;
        }

        if ( self::$shared_read && (int) $user_id === get_current_user_id() && in_array( $cap, self::SHARED_READ_CAPS, true ) ) {
            return [ 'exist' ];
        }

        if ( ! self::user_gets_full_site_kit( (int) $user_id ) ) {
            return [ 'do_not_allow' ];
        }

        return $caps;
    }

    /**
     * Ejecuta $callback con acceso de solo lectura a los datos compartidos de Site Kit.
     *
     * @return mixed Lo que devuelva $callback.
     */
    public static function with_shared_read( callable $callback ) {
        $previous          = self::$shared_read;
        self::$shared_read = true;

        try {
            return $callback();
        } finally {
            self::$shared_read = $previous;
        }
    }

    /**
     * Redirige al panel SEO a quien intente abrir una pantalla de Site Kit sin ser administrador.
     */
    public static function redirect_site_kit_screens(): void {
        if ( wp_doing_ajax() || ! is_user_logged_in() || self::user_gets_full_site_kit( get_current_user_id() ) ) {
            return;
        }

        $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
        if ( 0 !== strpos( $page, self::SCREEN_PREFIX ) || ! current_user_can( 'view_ssivo_seo' ) ) {
            return;
        }

        wp_safe_redirect( admin_url( 'admin.php?page=ssivo-seo' ) );
        exit;
    }

    /**
     * Nombre del administrador que conectó un servicio de Site Kit (propietario del módulo).
     *
     * @param string $module_slug 'analytics-4' o 'search-console'.
     */
    public static function get_module_owner_name( string $module_slug ): string {
        $settings = get_option( 'googlesitekit_' . $module_slug . '_settings', [] );
        $owner_id = is_array( $settings ) && ! empty( $settings['ownerID'] ) ? (int) $settings['ownerID'] : 0;
        $owner    = $owner_id ? get_user_by( 'id', $owner_id ) : false;

        return $owner ? $owner->display_name : '';
    }
}
