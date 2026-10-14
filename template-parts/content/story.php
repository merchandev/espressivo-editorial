<?php
$story = $args['story'] ?? null;
if ( ! $story instanceof WP_Post ) { return; }
$variant = in_array( $args['variant'] ?? '', array( 'lead', 'compact', 'card' ), true ) ? $args['variant'] : 'card';
$id = $story->ID;
$category = cms_editorial_story_category( $id );
$image = pro_get_post_image_id( $story );
$title = get_the_title( $id );
$url = get_permalink( $id );
$eager = $variant === 'lead';
$heading = $variant === 'lead' ? 2 : ( ( $args['heading_level'] ?? 3 ) === 2 ? 2 : 3 );
?>
<article id="post-<?php echo absint( $id ); ?><?php echo $variant === 'compact' ? '-compact' : ''; ?>" class="ed-story ed-story--<?php echo esc_attr( $variant ); ?><?php echo $variant === 'card' ? ' card-post' : ''; ?>">
    <?php if ( $image ) : ?>
        <a class="ed-story-image post-thumbnail" href="<?php echo esc_url( $url ); ?>" aria-hidden="true" tabindex="-1">
            <?php echo wp_get_attachment_image( $image, $eager ? 'hero-thumbnail' : 'card-thumbnail', false, array( 'alt' => '', 'loading' => $eager ? 'eager' : 'lazy', 'fetchpriority' => $eager ? 'high' : 'auto' ) ); ?>
        </a>
    <?php endif; ?>
    <div class="ed-story-body">
        <?php if ( $category ) : ?><a class="ed-category" href="<?php echo esc_url( get_category_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a><?php endif; ?>
        <h<?php echo $heading; ?> class="entry-title ed-story-title"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a></h<?php echo $heading; ?>>
        <?php if ( $variant !== 'compact' ) : ?><p class="ed-story-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $story ), $eager ? 30 : 19, '…' ) ); ?></p><?php endif; ?>
        <div class="ed-story-meta"><time datetime="<?php echo esc_attr( get_the_date( 'c', $id ) ); ?>"><?php echo esc_html( get_the_date( 'j M · H:i', $id ) ); ?></time><span aria-hidden="true">·</span><span><?php echo absint( cms_editorial_reading_minutes( $story ) ); ?> min de lectura</span></div>
    </div>
</article>
