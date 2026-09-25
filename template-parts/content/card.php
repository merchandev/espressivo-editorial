<?php
/**
 * @author Arturo Merchan | Merchan.Dev | Espressivo Venezuela,C.A
 *
 * ADVERTENCIA LEGAL:
 * Queda totalmente prohibida su reproduccion, edicion, venta, propaganda, alteracion
 * o cualquier otra accion que de una u otra forma violente la propiedad intelectual,
 * material y digital de este proyecto. Esta infraccion esta prohibida y penada por la ley.
 */
/**
 * Tarjeta de noticia de los listados (categorías, archivo y "cargar más").
 *
 * La usan tanto el render inicial como el endpoint AJAX, para que las noticias
 * añadidas por scroll infinito sean idénticas a las de la primera carga.
 *
 * Argumentos opcionales:
 *   'cat_slug' (string) – Slug de la categoría de la página, para la etiqueta.
 *
 * @package Pro
 */

$cat_slug = ! empty( $args['cat_slug'] ) ? $args['cat_slug'] : null;
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'card-post' ); ?>>
    <?php if ( pro_get_post_image_id() ) : ?>
        <a href="<?php the_permalink(); ?>" class="post-thumbnail" aria-hidden="true" tabindex="-1">
            <?php pro_the_post_image( 'card-thumbnail', array( 'loading' => 'lazy' ) ); ?>
        </a>
    <?php endif; ?>
    <div class="card-content">
        <div class="post-meta">
            <?php pro_post_categories( null, $cat_slug ); ?>
            <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
        </div>
        <h2 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
        <div class="entry-excerpt">
            <?php echo esc_html( wp_trim_words( get_the_excerpt(), 20, '...' ) ); ?>
        </div>
    </div>
</article>
