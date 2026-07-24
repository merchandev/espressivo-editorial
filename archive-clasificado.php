<?php
/**
 * Archivo público de clasificados.
 *
 * URL: /clasificados/
 *
 * @package Espressivo
 */

defined( 'ABSPATH' ) || exit;

get_header();

$archive_url = get_post_type_archive_link(
    'clasificado'
);

$selected_type = isset( $_GET['tipo'] )
    ? sanitize_title(
        wp_unslash( $_GET['tipo'] )
    )
    : '';

$search_value = isset( $_GET['buscar_clasificado'] )
    ? sanitize_text_field(
        wp_unslash(
            $_GET['buscar_clasificado']
        )
    )
    : '';

$types = get_terms(
    array(
        'taxonomy'   => 'tipo_clasificado',
        'hide_empty' => true,
        'orderby'    => 'name',
        'order'      => 'ASC',
    )
);
?>

<main
    id="primary"
    class="site-main classified-archive"
>
    <header class="classified-hero">
        <div class="classified-container">
            <p class="classified-kicker">
                <?php esc_html_e( 'Diario El Oriental', 'pro' ); ?>
            </p>

            <h1 class="classified-page-title">
                <?php esc_html_e( 'Clasificados', 'pro' ); ?>
            </h1>

            <p class="classified-description">
                <?php
                esc_html_e(
                    'Empleos, inmuebles, vehículos, servicios, compra y venta y avisos profesionales.',
                    'pro'
                );
                ?>
            </p>
        </div>
    </header>

    <div class="classified-container">
        <form
            class="classified-search"
            method="get"
            action="<?php echo esc_url( $archive_url ); ?>"
        >
            <div class="classified-search__field">
                <label
                    class="screen-reader-text"
                    for="classified-search-input"
                >
                    <?php
                    esc_html_e(
                        'Buscar clasificados',
                        'pro'
                    );
                    ?>
                </label>

                <input
                    id="classified-search-input"
                    type="search"
                    name="buscar_clasificado"
                    value="<?php echo esc_attr( $search_value ); ?>"
                    placeholder="<?php
                    echo esc_attr__(
                        'Buscar por palabra o título',
                        'pro'
                    );
                    ?>"
                >
            </div>

            <?php if ( '' !== $selected_type ) : ?>
                <input
                    type="hidden"
                    name="tipo"
                    value="<?php echo esc_attr( $selected_type ); ?>"
                >
            <?php endif; ?>

            <button type="submit">
                <?php esc_html_e( 'Buscar', 'pro' ); ?>
            </button>
        </form>

        <?php if ( ! is_wp_error( $types ) && $types ) : ?>
            <nav
                class="classified-types"
                aria-label="<?php
                echo esc_attr__(
                    'Tipos de clasificados',
                    'pro'
                );
                ?>"
            >
                <a
                    class="classified-type-link <?php
                    echo '' === $selected_type
                        ? 'is-active'
                        : '';
                    ?>"
                    href="<?php echo esc_url( $archive_url ); ?>"
                >
                    <?php esc_html_e( 'Todos', 'pro' ); ?>
                </a>

                <?php foreach ( $types as $type ) : ?>
                    <?php
                    $type_url = add_query_arg(
                        'tipo',
                        $type->slug,
                        $archive_url
                    );
                    ?>

                    <a
                        class="classified-type-link <?php
                        echo $selected_type === $type->slug
                            ? 'is-active'
                            : '';
                        ?>"
                        href="<?php echo esc_url( $type_url ); ?>"
                    >
                        <?php echo esc_html( $type->name ); ?>

                        <span>
                            <?php
                            echo esc_html(
                                (string) $type->count
                            );
                            ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <?php if ( have_posts() ) : ?>
            <div class="classified-results-heading">
                <h2>
                    <?php
                    if ( '' !== $search_value ) {
                        printf(
                            esc_html__(
                                'Resultados para “%s”',
                                'pro'
                            ),
                            esc_html( $search_value )
                        );
                    } else {
                        esc_html_e(
                            'Avisos publicados',
                            'pro'
                        );
                    }
                    ?>
                </h2>
            </div>

            <section
                class="classified-grid"
                aria-label="<?php
                echo esc_attr__(
                    'Listado de clasificados',
                    'pro'
                );
                ?>"
            >
                <?php while ( have_posts() ) : ?>
                    <?php
                    the_post();

                    $post_types = get_the_terms(
                        get_the_ID(),
                        'tipo_clasificado'
                    );

                    $summary = wp_trim_words(
                        wp_strip_all_tags(
                            strip_shortcodes(
                                get_the_content()
                            )
                        ),
                        70,
                        '…'
                    );
                    ?>

                    <article
                        id="post-<?php the_ID(); ?>"
                        <?php post_class( 'classified-card' ); ?>
                    >
                        <?php
                        if (
                            ! empty( $post_types )
                            && ! is_wp_error( $post_types )
                        ) :
                            ?>
                            <div class="classified-card__types">
                                <?php foreach ( $post_types as $post_type ) : ?>
                                    <span>
                                        <?php
                                        echo esc_html(
                                            $post_type->name
                                        );
                                        ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <h2 class="classified-card__title">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_title(); ?>
                            </a>
                        </h2>

                        <div class="classified-card__content">
                            <p>
                                <?php echo esc_html( $summary ); ?>
                            </p>
                        </div>

                        <footer class="classified-card__footer">
                            <time
                                datetime="<?php
                                echo esc_attr(
                                    get_the_date( DATE_W3C )
                                );
                                ?>"
                            >
                                <?php echo esc_html( get_the_date() ); ?>
                            </time>

                            <a
                                class="classified-card__more"
                                href="<?php the_permalink(); ?>"
                            >
                                <?php
                                esc_html_e(
                                    'Ver aviso',
                                    'pro'
                                );
                                ?>
                            </a>
                        </footer>
                    </article>
                <?php endwhile; ?>
            </section>

            <nav class="classified-pagination">
                <?php
                the_posts_pagination(
                    array(
                        'mid_size'  => 2,
                        'prev_text' => __(
                            '← Anteriores',
                            'pro'
                        ),
                        'next_text' => __(
                            'Siguientes →',
                            'pro'
                        ),
                    )
                );
                ?>
            </nav>
        <?php else : ?>
            <section class="classified-empty">
                <h2>
                    <?php
                    esc_html_e(
                        'No encontramos clasificados',
                        'pro'
                    );
                    ?>
                </h2>

                <p>
                    <?php
                    esc_html_e(
                        'Prueba con otra palabra o selecciona otro tipo.',
                        'pro'
                    );
                    ?>
                </p>

                <a href="<?php echo esc_url( $archive_url ); ?>">
                    <?php
                    esc_html_e(
                        'Ver todos los clasificados',
                        'pro'
                    );
                    ?>
                </a>
            </section>
        <?php endif; ?>
    </div>
</main>

<?php
get_footer();
