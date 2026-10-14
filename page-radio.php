<?php
/**
 * Template Name: Radio en vivo
 */

get_header(); ?>

<main id="primary" class="site-main radio-page-main">
    <div class="radio-hero-section">
        <div class="radio-hero-overlay"></div>
        <div class="radio-hero-content archive-container">
            <h1 class="radio-title"><?php echo esc_html( cms_editorial_setting( 'radio_name' ) ); ?></h1>
            <p class="radio-subtitle"><?php echo esc_html( cms_editorial_setting( 'radio_description' ) ); ?></p>

            <?php if ( cms_editorial_radio_available() ) : ?>
            <div class="es-resp-wrapper">
                <div class="es-resp-bar" id="esRespBar">
                    <!-- PLAY SECTION -->
                    <div class="es-resp-play-zone">
                        <div class="es-resp-halo"></div>
                        <button class="es-resp-btn" id="esRespPlay" onclick="toggleRespRadio()" aria-label="<?php echo esc_attr( 'Reproducir ' . cms_editorial_setting( 'radio_name' ) ); ?>">
                            <span class="material-symbols-rounded" aria-hidden="true" id="esRespIcon">play_arrow</span>
                        </button>
                    </div>

                    <!-- INFO & PROGRESS SECTION -->
                    <div class="es-resp-main-zone">
                        <div class="es-resp-meta">
                            <span class="es-resp-status" id="esRespStatus">LISTO PARA ESCUCHAR</span>
                            <div class="es-resp-live">
                                <span class="es-resp-dot"></span>
                                <span>EN VIVO</span>
                            </div>
                        </div>
                        <div class="es-resp-track-wrap">
                            <div class="es-resp-track">
                                <div class="es-resp-fill" id="esRespFill"></div>
                                <div class="es-resp-waves">
                                    <?php for($i=0; $i<40; $i++): ?>
                                        <div class="es-resp-w" style="--d: <?php echo $i * 0.04; ?>s"></div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- VOLUME SECTION -->
                    <div class="es-resp-vol-zone">
                        <button class="es-resp-mute" onclick="toggleRespMute()" aria-label="Silenciar o activar volumen">
                            <span class="material-symbols-rounded" aria-hidden="true" id="esRespMuteIcon">volume_up</span>
                        </button>
                        <div class="es-resp-vol-slider">
                            <input type="range" id="esRespVol" min="0" max="1" step="0.01" value="1" oninput="updateRespVol()">
                        </div>
                    </div>
                </div>
            </div>

            <audio id="esRespAudio" preload="none"></audio>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
