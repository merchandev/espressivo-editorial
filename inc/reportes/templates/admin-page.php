<?php
/** @var WP_User $user */
/** @var string $role_label */
/** @var bool $dompdf_available */
/** @var string[] $reportable_types */
/** @var string $today */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$periods = array(
    '1d'     => __( '1 día', 'pro' ),
    '7d'     => __( '7 días', 'pro' ),
    '15d'    => __( '15 días', 'pro' ),
    '30d'    => __( '30 días', 'pro' ),
    '3m'     => __( '3 meses', 'pro' ),
    '6m'     => __( '6 meses', 'pro' ),
    '1y'     => __( '1 año', 'pro' ),
    'custom' => __( 'Personalizado', 'pro' ),
);
?>
<div class="wrap espressivo-reportes-wrap">
    <header class="espressivo-reportes-hero">
        <div>
            <span class="espressivo-reportes-eyebrow"><?php esc_html_e( 'Producción editorial', 'pro' ); ?></span>
            <h1><?php esc_html_e( 'Mis reportes', 'pro' ); ?></h1>
            <p>
                <?php
                echo esc_html(
                    sprintf(
                        /* translators: 1: user name, 2: role. */
                        __( '%1$s · %2$s', 'pro' ),
                        $user->display_name,
                        $role_label
                    )
                );
                ?>
            </p>
        </div>
        <span class="espressivo-reportes-private"><?php esc_html_e( 'Reporte privado de tu perfil', 'pro' ); ?></span>
    </header>

    <?php if ( ! $dompdf_available ) : ?>
        <div class="notice notice-error inline">
            <p>
                <?php
                echo wp_kses_post(
                    __( 'El tema no contiene todavía el motor PDF. Antes de distribuirlo ejecuta <code>composer install --no-dev --optimize-autoloader</code> y conserva la carpeta <code>vendor/</code> dentro del ZIP del tema.', 'pro' )
                );
                ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="espressivo-reportes-grid">
        <section class="espressivo-reportes-card">
            <h2><?php esc_html_e( 'Selecciona el período', 'pro' ); ?></h2>
            <p><?php esc_html_e( 'El documento incluirá únicamente el contenido registrado como cargado por tu cuenta.', 'pro' ); ?></p>

            <form class="espressivo-reportes-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="espressivo_export_report_pdf">
                <?php wp_nonce_field( 'espressivo_export_report_pdf', 'espressivo_report_nonce' ); ?>

                <fieldset class="espressivo-reportes-periods">
                    <legend class="screen-reader-text"><?php esc_html_e( 'Período del reporte', 'pro' ); ?></legend>
                    <?php foreach ( $periods as $value => $label ) : ?>
                        <label class="espressivo-reportes-period">
                            <input
                                type="radio"
                                name="period"
                                value="<?php echo esc_attr( $value ); ?>"
                                <?php checked( '7d', $value ); ?>
                            >
                            <span><?php echo esc_html( $label ); ?></span>
                        </label>
                    <?php endforeach; ?>
                </fieldset>

                <div class="espressivo-reportes-dates" data-espressivo-custom-dates hidden>
                    <label>
                        <span><?php esc_html_e( 'Desde', 'pro' ); ?></span>
                        <input type="date" name="date_from" max="<?php echo esc_attr( $today ); ?>">
                    </label>
                    <label>
                        <span><?php esc_html_e( 'Hasta', 'pro' ); ?></span>
                        <input type="date" name="date_to" max="<?php echo esc_attr( $today ); ?>">
                    </label>
                    <p><?php esc_html_e( 'El rango personalizado puede abarcar cualquier período pasado; los reportes excesivamente grandes deberán dividirse.', 'pro' ); ?></p>
                </div>

                <button
                    type="submit"
                    class="button button-primary button-hero espressivo-reportes-submit"
                    <?php disabled( ! $dompdf_available ); ?>
                >
                    <span class="dashicons dashicons-pdf" aria-hidden="true"></span>
                    <?php esc_html_e( 'Exportar PDF', 'pro' ); ?>
                </button>
            </form>
        </section>

        <aside class="espressivo-reportes-card espressivo-reportes-info">
            <h2><?php esc_html_e( 'Contenido del documento', 'pro' ); ?></h2>
            <ul>
                <li><?php esc_html_e( 'Nombre y rol del perfil.', 'pro' ); ?></li>
                <li><?php esc_html_e( 'Título y fecha de carga.', 'pro' ); ?></li>
                <li><?php esc_html_e( 'Estado y tipo de contenido.', 'pro' ); ?></li>
                <li><?php esc_html_e( 'Enlace público o enlace al editor.', 'pro' ); ?></li>
                <li><?php esc_html_e( 'Resumen y numeración de páginas.', 'pro' ); ?></li>
            </ul>

            <?php if ( ! empty( $reportable_types ) ) : ?>
                <div class="espressivo-reportes-types">
                    <strong><?php esc_html_e( 'Incluye:', 'pro' ); ?></strong>
                    <p><?php echo esc_html( implode( ', ', $reportable_types ) ); ?></p>
                </div>
            <?php endif; ?>

            <div class="espressivo-reportes-note">
                <strong><?php esc_html_e( 'Privacidad', 'pro' ); ?></strong>
                <p><?php esc_html_e( 'No puedes seleccionar otro usuario. El servidor obtiene el ID del perfil que tiene la sesión iniciada.', 'pro' ); ?></p>
            </div>
        </aside>
    </div>
</div>
