<?php
/**
 * @author Arturo Merchan | Merchan.Dev | Espressivo Venezuela,C.A
 * 
 * Plantilla de archivo para la sección de Clasificados.
 * Estilo de diagramación tipo periódico impreso.
 */

get_header();
?>

<main id="primary" class="site-main container clasificados-archive">
    <header class="page-header">
        <h1 class="page-title">Clasificados</h1>
        <p class="clasificados-intro">Encuentra los mejores anuncios de nuestra comunidad.</p>
    </header>

    <?php
    // Obtener todas las categorías (tipos) de clasificados que tengan posts
    $tipos = get_terms( array(
        'taxonomy' => 'tipo_clasificado',
        'hide_empty' => true,
    ) );

    if ( ! empty( $tipos ) && ! is_wp_error( $tipos ) ) {
        // Agrupar visualmente por Tipo de Clasificado
        foreach ( $tipos as $tipo ) {
            $args = array(
                'post_type' => 'clasificado',
                'posts_per_page' => -1, // Mostrar todos o un límite alto para impresos
                'tax_query' => array(
                    array(
                        'taxonomy' => 'tipo_clasificado',
                        'field'    => 'term_id',
                        'terms'    => $tipo->term_id,
                    ),
                ),
            );
            $clasificados_query = new WP_Query( $args );

            if ( $clasificados_query->have_posts() ) {
                echo '<section class="clasificados-seccion">';
                echo '<h2 class="clasificados-tipo-titulo">' . esc_html( $tipo->name ) . '</h2>';
                echo '<div class="clasificados-grid-columnas">';
                
                while ( $clasificados_query->have_posts() ) {
                    $clasificados_query->the_post();
                    
                    // Asegurar que el contenido solo sea texto plano o HTML básico sin bloques complejos
                    $content = wp_strip_all_tags( get_the_content() );
                    
                    echo '<article class="clasificado-item">';
                    echo '<h3 class="clasificado-titulo">' . esc_html( get_the_title() ) . ' - </h3> ';
                    echo '<div class="clasificado-contenido">' . esc_html( $content ) . '</div>';
                    echo '</article>';
                }
                
                echo '</div>'; // .clasificados-grid-columnas
                echo '</section>';
            }
            wp_reset_postdata();
        }
    } else {
        // Fallback: Si no hay tipos creados pero hay posts
        if ( have_posts() ) {
            echo '<div class="clasificados-grid-columnas">';
            while ( have_posts() ) : the_post();
                $content = wp_strip_all_tags( get_the_content() );
                echo '<article class="clasificado-item">';
                echo '<h3 class="clasificado-titulo">' . esc_html( get_the_title() ) . ' - </h3> ';
                echo '<div class="clasificado-contenido">' . esc_html( $content ) . '</div>';
                echo '</article>';
            endwhile;
            echo '</div>';
        } else {
            echo '<p>No hay clasificados publicados por el momento.</p>';
        }
    }
    ?>

</main><!-- #primary -->

<?php
get_footer();
