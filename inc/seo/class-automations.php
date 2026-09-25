<?php
namespace SSIVO_SEO\Includes;

class Automations {
    private $database;

    public function __construct( Database $database ) {
        $this->database = $database;
        add_action( 'save_post', [ $this, 'handle_save' ], 10, 2 );
    }

    public function handle_save( $post_id, $post ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }
        // Solo contenido público (no menús, anuncios internos, mensajes ni borradores automáticos)
        if ( 'auto-draft' === $post->post_status || ! is_post_type_viewable( $post->post_type ) ) {
            return;
        }

        $raw_content = apply_filters( 'ssivo_seo_before_meta_extraction', $post->post_content, $post );

        $seo_data = [
            'meta_title' => sanitize_text_field( get_the_title( $post->ID ) ),
            'meta_desc'  => $this->extract_pure_text( $raw_content, 155 )
        ];

        $this->database->save_seo_data( $post->ID, $seo_data );
        $this->ensure_featured_image( $post );
    }

    private function extract_pure_text( $content, $max_length ) {
        if ( empty( $content ) ) return '';
        $text = strip_shortcodes( $content );
        $text = wp_strip_all_tags( $text );
        // Entidades (&nbsp;, &aacute;, &#8220;...) a caracteres reales, para no
        // cortar una entidad a la mitad ni mostrarla literal en los buscadores
        $text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
        // Modificador /u: sin él, \s puede tratar bytes de caracteres acentuados
        // (según el locale del servidor) como espacios y romper la ñ o las tildes
        $text = preg_replace( '/[\s\x{00A0}]+/u', ' ', $text );
        $text = trim( (string) $text );
        return mb_strlen( $text, 'UTF-8' ) > $max_length ? mb_strimwidth( $text, 0, $max_length, '...', 'UTF-8' ) : $text;
    }

    /**
     * Si la entrada no tiene una imagen destacada válida, usa la primera imagen
     * de la biblioteca insertada en el contenido.
     *
     * Auditoría fix: antes se buscaba el adjunto por la URL del <img>, que casi
     * siempre es una versión redimensionada (foto-1024x683.jpg) que
     * attachment_url_to_postid() no reconoce; las entradas (en especial las
     * programadas) quedaban sin imagen en los widgets de noticias. Se ejecuta
     * también cuando el cron publica una programada (save_post).
     */
    private function ensure_featured_image( $post ) {
        if ( ! post_type_supports( $post->post_type, 'thumbnail' ) ) {
            return;
        }

        $thumbnail_id = (int) get_post_thumbnail_id( $post->ID );
        if ( $thumbnail_id && wp_attachment_is_image( $thumbnail_id ) ) {
            return;
        }

        $attachment_id = \pro_find_content_image_id( (string) $post->post_content );
        if ( $attachment_id ) {
            set_post_thumbnail( $post->ID, $attachment_id );
        }
    }
}
