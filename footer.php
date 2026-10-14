<?php /** Pie de página de CMS-Editorial. */ ?>
    </div><!-- /#swup -->
    <footer id="colophon" class="site-footer">
        <div class="footer-widgets container"><div class="footer-grid">
            <div class="footer-about">
                <?php cms_editorial_render_branding( 'footer' ); ?>
                <?php if ( cms_editorial_setting( 'footer_text' ) ) : ?>
                    <p><?php echo nl2br( esc_html( cms_editorial_setting( 'footer_text' ) ) ); ?></p>
                <?php endif; ?>
                <h3 class="footer-heading">Más información</h3>
                <?php if ( has_nav_menu( 'footer' ) ) {
                    wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'menu_class' => 'footer-category-list', 'fallback_cb' => false ) );
                } else { ?>
                    <ul class="footer-category-list">
                        <li><a href="<?php echo esc_url( home_url( '/contacto/' ) ); ?>">Contacto</a></li>
                        <li><a href="<?php echo esc_url( home_url( '/clasificados/' ) ); ?>">Clasificados</a></li>
                    </ul>
                <?php } ?>
            </div>
            <div class="footer-categories"><h3 class="footer-heading">Secciones populares</h3><ul class="footer-category-list">
                <?php foreach ( get_categories( array( 'orderby' => 'count', 'order' => 'DESC', 'number' => 5, 'exclude' => get_option( 'default_category' ) ) ) as $cat ) : ?>
                    <li><a href="<?php echo esc_url( get_category_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a></li>
                <?php endforeach; ?>
            </ul></div>
            <div class="footer-social-section"><h3 class="footer-heading">Contacto y redes</h3><ul class="footer-category-list">
                <?php foreach ( array( 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'twitter' => 'X', 'telegram' => 'Telegram', 'whatsapp' => 'WhatsApp' ) as $key => $label ) :
                    $url = cms_editorial_setting( 'social_' . $key ); if ( ! $url ) { continue; } ?>
                    <li><a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $label ); ?></a></li>
                <?php endforeach; ?>
                <?php if ( cms_editorial_setting( 'google_news' ) ) : ?><li><a href="<?php echo esc_url( cms_editorial_setting( 'google_news' ) ); ?>" target="_blank" rel="noopener noreferrer">Google Noticias</a></li><?php endif; ?>
                <?php if ( cms_editorial_setting( 'contact_email' ) ) : ?><li><a href="mailto:<?php echo esc_attr( cms_editorial_setting( 'contact_email' ) ); ?>"><?php echo esc_html( cms_editorial_setting( 'contact_email' ) ); ?></a></li><?php endif; ?>
                <?php if ( cms_editorial_setting( 'contact_phone' ) ) : ?><li><?php echo esc_html( cms_editorial_setting( 'contact_phone' ) ); ?></li><?php endif; ?>
                <?php if ( cms_editorial_setting( 'contact_address' ) ) : ?><li><?php echo nl2br( esc_html( cms_editorial_setting( 'contact_address' ) ) ); ?></li><?php endif; ?>
            </ul><a href="<?php echo esc_url( home_url( '/contacto/' ) ); ?>" class="btn-footer-denuncia">Contactar con la redacción</a></div>
        </div></div>
        <div class="site-info container"><div class="site-info-inner">
            <p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>. Todos los derechos reservados.</p>
            <div class="footer-legal">
                <a href="<?php echo esc_url( home_url( '/terminos-y-condiciones/' ) ); ?>">Términos y condiciones</a>
                <a href="<?php echo esc_url( home_url( '/politica-de-cookies/' ) ); ?>">Política de cookies</a>
                <a href="<?php echo esc_url( home_url( '/politica-de-privacidad/' ) ); ?>">Privacidad</a>
            </div>
        </div></div>
    </footer>
</div><!-- #page -->
<?php cms_editorial_menu_drawer(); ?>
<button type="button" class="ed-back-to-top" data-cms-top hidden aria-label="Volver al inicio de la página"><span class="material-symbols-outlined" aria-hidden="true">arrow_upward</span></button>
<!-- Visor Lightbox Modal Interactivo de Portadas -->
<div id="portada-lightbox-modal" class="portada-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="portada-modal-title">
    <div class="portada-modal-backdrop"></div>

    <!-- Controles superiores e información -->
    <div class="portada-modal-bar">
        <h3 id="portada-modal-title">Visor de Portada</h3>
        <div class="portada-modal-actions">
            <!-- Botón de Descarga -->
            <a id="portada-download-btn" href="" download class="portada-modal-download" title="Descargar Portada" aria-label="Descargar Portada" style="color: white; margin-right: 15px; text-decoration: none; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.1); border-radius: 50%; width: 40px; height: 40px; transition: background 0.3s;">
                <span class="material-symbols-outlined" aria-hidden="true">download</span>
            </a>
            <!-- Botón de Cerrar -->
            <button class="portada-modal-close" aria-label="Cerrar visor" title="Cerrar">
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
        </div>
    </div>

    <!-- Contenedor principal interactivo -->
    <div class="portada-modal-body">
        <div class="portada-viewport" id="portada-viewport">
            <div class="portada-image-wrapper" style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; gap: 20px; height: auto; max-width: 100%; position: relative;">
                <img id="portada-lightbox-image" src="" alt="Portada" draggable="false">
            </div>
        </div>
    </div>

    <!-- Controles flotantes inferiores de Zoom -->
    <div class="portada-zoom-controls">
        <button id="portada-zoom-out" class="zoom-btn" title="Alejar (Zoom -)" aria-label="Alejar (Zoom -)">
            <span class="material-symbols-outlined" aria-hidden="true">zoom_out</span>
        </button>
        <button id="portada-zoom-reset" class="zoom-btn text-btn" title="Restablecer" aria-label="Restablecer zoom al 100%">
            100%
        </button>
        <button id="portada-zoom-in" class="zoom-btn" title="Acercar (Zoom +)" aria-label="Acercar (Zoom +)">
            <span class="material-symbols-outlined" aria-hidden="true">zoom_in</span>
        </button>
    </div>
</div>

<!-- Swup JS -->
<script src="https://unpkg.com/swup@4"></script>
<script src="https://unpkg.com/@swup/scripts-plugin@2"></script>
<?php wp_footer(); ?>
<!-- Organization Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "NewsMediaOrganization",
  "name": <?php echo wp_json_encode( get_bloginfo( 'name' ) ); ?>,
  "url": <?php echo wp_json_encode( esc_url( home_url( '/' ) ) ); ?>,
  "logo": <?php echo wp_json_encode( has_custom_logo() ? wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' ) : '' ); ?>
}
</script>
<!-- Reproductor Flotante Global (Fuera de Swup para que no se corte) -->
<?php if ( cms_editorial_radio_available() ) : ?>
<div class="es-floating-radio" id="esFloatingRadio">
    <div class="es-floating-inner">
        <button class="es-float-btn" id="esFloatPlay" onclick="toggleFloatRadio()" aria-label="<?php echo esc_attr( 'Reproducir ' . cms_editorial_setting( 'radio_name' ) ); ?>">
            <span class="material-symbols-outlined" aria-hidden="true" id="esFloatIcon">play_arrow</span>
        </button>
        <div class="es-float-info">
            <span class="es-float-title"><?php echo esc_html( cms_editorial_setting( 'radio_name' ) ); ?></span>
            <span class="es-float-status" id="esFloatStatus">Haz clic para escuchar</span>
        </div>
        <div class="es-float-waves" id="esFloatWaves">
            <div class="es-float-bar" style="--d: 0s"></div>
            <div class="es-float-bar" style="--d: 0.2s"></div>
            <div class="es-float-bar" style="--d: 0.4s"></div>
            <div class="es-float-bar" style="--d: 0.1s"></div>
            <div class="es-float-bar" style="--d: 0.3s"></div>
        </div>
        <button class="es-float-close" onclick="closeFloatRadio()" title="Cerrar reproductor" aria-label="Cerrar reproductor">
            <span class="material-symbols-outlined" aria-hidden="true">close</span>
        </button>
    </div>
</div>
<audio id="esFloatAudio" preload="none"></audio>
<?php endif; ?>

</body>
</html>
