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
 *  2. El panel SEO lee los datos con la cuenta de Google de un administrador
 *     conectado a Site Kit. Es el mismo mecanismo del "Dashboard Sharing" de
 *     Site Kit, habilitado solo mientras dura esa lectura (with_shared_read()).
 *
 * Cuenta de lectura de cada servicio (Analytics, Search Console): por defecto,
 * la del administrador que lo conectó en Site Kit (su "propietario"). Si esa
 * cuenta de Google no tiene permiso en la propiedad, el panel prueba la cuenta
 * del administrador que lo está consultando (la misma con la que él ve Site
 * Kit) y, si funciona, la recuerda para todo el equipo y para el cron.
 *
 * Auditoría fix: el proxy anterior hacía wp_set_current_user() al administrador,
 * pero Site Kit fija el usuario de su token OAuth en 'init' y no lo cambia con
 * wp_set_current_user(); para cualquier otro usuario (y para el cron) todas las
 * consultas fallaban y el panel mostraba "No disponible".
 */
class SiteKitBridge {

    const CAP_PREFIX    = 'googlesitekit_';
    const SCREEN_PREFIX = 'googlesitekit-';

    /** Servicios de Site Kit que usa el panel. */
    const MODULES = [ 'analytics-4', 'search-console' ];

    /** Administrador cuya cuenta de Google lee cada servicio: [ slug => user_id ]. */
    const READERS_OPTION = 'ssivo_seo_sitekit_readers';

    /** Opción de usuario de Site Kit con el token OAuth de su cuenta de Google. */
    const TOKEN_OPTION = 'googlesitekit_access_token';

    const READ_SHARED_CAP = 'googlesitekit_read_shared_module_data';

    /**
     * Capacidades de Site Kit que se conceden durante la lectura compartida:
     * permiso de la ruta REST de datos y uso del token del propietario.
     */
    const SHARED_READ_CAPS = [
        'googlesitekit_view_posts_insights',
        self::READ_SHARED_CAP,
        'googlesitekit_view_shared_dashboard',
        'googlesitekit_view_dashboard',
    ];

    /** @var bool */
    private static $shared_read = false;

    /** @var bool */
    private static $own_read = false;

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
     * Indica si el usuario inició sesión en Site Kit con su cuenta de Google.
     */
    public static function user_has_google_token( int $user_id ): bool {
        return $user_id > 0 && (bool) get_user_option( self::TOKEN_OPTION, $user_id );
    }

    /**
     * Administrador que puede leer los datos con su propia cuenta de Google.
     */
    public static function can_read_with_own_account( int $user_id ): bool {
        return self::is_active() && self::user_gets_full_site_kit( $user_id ) && self::user_has_google_token( $user_id );
    }

    /**
     * Filtro map_meta_cap (prioridad máxima, tras el de Site Kit).
     */
    public static function filter_capabilities( $caps, $cap, $user_id, $args ) {
        if ( ! is_string( $cap ) || 0 !== strpos( $cap, self::CAP_PREFIX ) ) {
            return $caps;
        }

        $is_current_user = (int) $user_id === get_current_user_id();

        // Lectura con la cuenta propia: Site Kit usa el token de quien consulta
        if ( self::$own_read && $is_current_user && self::READ_SHARED_CAP === $cap ) {
            return [ 'do_not_allow' ];
        }

        if ( self::$shared_read && $is_current_user && in_array( $cap, self::SHARED_READ_CAPS, true ) ) {
            return [ 'exist' ];
        }

        if ( ! self::user_gets_full_site_kit( (int) $user_id ) ) {
            return [ 'do_not_allow' ];
        }

        return $caps;
    }

    /**
     * Ejecuta $callback con acceso de solo lectura a los datos compartidos de Site Kit,
     * leídos con la cuenta de lectura de cada servicio (ver get_reader()).
     *
     * Site Kit usa el token del "propietario" del módulo (ownerID de sus ajustes).
     * Mientras dura la lectura, ese dato se sustituye en memoria por la cuenta de
     * lectura; los ajustes guardados de Site Kit no se modifican.
     *
     * @return mixed Lo que devuelva $callback.
     */
    public static function with_shared_read( callable $callback ) {
        $previous          = self::$shared_read;
        self::$shared_read = true;

        $filters = [];
        foreach ( self::MODULES as $slug ) {
            $reader = self::get_reader( $slug );
            if ( ! $reader ) {
                continue;
            }
            $filter = static function ( $settings ) use ( $reader ) {
                if ( is_array( $settings ) ) {
                    $settings['ownerID'] = $reader;
                }
                return $settings;
            };
            foreach ( [ 'option_', 'site_option_' ] as $prefix ) {
                $hook = $prefix . 'googlesitekit_' . $slug . '_settings';
                add_filter( $hook, $filter, PHP_INT_MAX );
                $filters[] = [ $hook, $filter ];
            }
        }

        try {
            return $callback();
        } finally {
            foreach ( $filters as list( $hook, $filter ) ) {
                remove_filter( $hook, $filter, PHP_INT_MAX );
            }
            self::$shared_read = $previous;
        }
    }

    /**
     * Ejecuta $callback leyendo con la cuenta de Google del usuario actual, igual
     * que su propio Site Kit (sin datos compartidos).
     *
     * @return mixed Lo que devuelva $callback.
     */
    public static function with_own_credentials( callable $callback ) {
        $previous       = self::$own_read;
        self::$own_read = true;

        try {
            return $callback();
        } finally {
            self::$own_read = $previous;
        }
    }

    /**
     * Administrador cuya cuenta de Google lee un servicio (0 = el propietario en Site Kit).
     * Se ignora si dejó de ser administrador o cerró su sesión de Site Kit.
     */
    public static function get_reader( string $module_slug ): int {
        $readers = get_option( self::READERS_OPTION, [] );
        $user_id = is_array( $readers ) && ! empty( $readers[ $module_slug ] ) ? (int) $readers[ $module_slug ] : 0;

        return $user_id && self::can_read_with_own_account( $user_id ) ? $user_id : 0;
    }

    /**
     * Guarda la cuenta de lectura de un servicio.
     *
     * @return bool True si cambió.
     */
    public static function set_reader( string $module_slug, int $user_id ): bool {
        $readers = get_option( self::READERS_OPTION, [] );
        $readers = is_array( $readers ) ? $readers : [];
        if ( isset( $readers[ $module_slug ] ) && (int) $readers[ $module_slug ] === $user_id ) {
            return false;
        }
        $readers[ $module_slug ] = $user_id;
        update_option( self::READERS_OPTION, $readers, false );
        return true;
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
     * ID del administrador que conectó un servicio de Site Kit (propietario del módulo).
     *
     * @param string $module_slug 'analytics-4' o 'search-console'.
     */
    public static function get_module_owner_id( string $module_slug ): int {
        $settings = get_option( 'googlesitekit_' . $module_slug . '_settings', [] );
        return is_array( $settings ) && ! empty( $settings['ownerID'] ) ? (int) $settings['ownerID'] : 0;
    }

    /**
     * Nombre del administrador que conectó un servicio de Site Kit (propietario del módulo).
     *
     * @param string $module_slug 'analytics-4' o 'search-console'.
     */
    public static function get_module_owner_name( string $module_slug ): string {
        $owner_id = self::get_module_owner_id( $module_slug );
        $owner    = $owner_id ? get_user_by( 'id', $owner_id ) : false;

        return $owner ? $owner->display_name : '';
    }
}
