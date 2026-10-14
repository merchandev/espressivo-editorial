<?php
/** Template Name: Política de privacidad */
get_header(); ?>
<main id="primary" class="site-main container" style="padding-top:40px;padding-bottom:40px">
<?php while ( have_posts() ) : the_post(); ?>
    <article <?php post_class(); ?>>
        <h1 class="entry-title"><?php the_title(); ?></h1>
        <div class="entry-content"><?php the_content(); ?></div>
        <?php if ( current_user_can( 'edit_post', get_the_ID() ) && ! trim( get_the_content() ) ) : ?>
            <p>Redacta el contenido de esta página desde el editor de WordPress.</p>
        <?php endif; ?>
    </article>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>
