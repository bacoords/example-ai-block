<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php echo esc_html( $attributes['tweet'] ); ?>

	<?php
	$twitter_url = 'https://twitter.com/intent/tweet?text=' . urlencode( $attributes['tweet'] );
	?>

	<a href="<?php echo esc_url( $twitter_url ); ?>" target="_blank">Tweet this</a>
</div>
