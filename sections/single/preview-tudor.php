<?php
global $post;
$product = wc_get_product($post);
$attachment_ids = $product->get_gallery_attachment_ids();

// rimuove duplicati
$attachment_ids = array_unique($attachment_ids);

$category_product = get_the_terms($post->ID, 'product_cat');

// rimuove la prima immagine in quanto è quella in evidenza
if (count($attachment_ids) > 1) {
	array_shift($attachment_ids);
}

$itDescription = '';
$enDescription = '';

foreach ($category_product as $cat) {
	$slug = $cat->slug;
	if (strstr($slug, '-it')) {
		$itDescription = $cat->description;
	}
	if (strstr($slug, '-en')) {
		$enDescription = $cat->description;
	}
}

$tudor_attributes = get_tudor_attributes($post->ID);

// Fallback WCollection → nome categoria
$watch_details = get_prod_data($post->ID, 'WatchDetail');
$family_name = $watch_details['WCollection'] ?? null;
if (empty($family_name)) {
	$terms = wp_get_post_terms($post->ID, 'product_cat');
	if (!is_wp_error($terms) && !empty($terms)) {
		$family_name = $terms[0]->name;
	}
}


// Fallback Reference → nome prodotto
function get_original_sku($product_id, $original_lang = 'it')
{
	$original_id = apply_filters('wpml_object_id', $product_id, 'product', true, $original_lang);

	if (! $original_id) {
		return '';
	}

	return get_post_meta($original_id, '_sku', true);
}

// Uso:
$sku = get_original_sku(get_the_ID());
$reference = $tudor_attributes['Reference'] ?? $sku;

$image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');


?>

<section class="preview-tudor">
	<div class="custom-container-larger">
		<div class="preview-tudor-container">
			<div class="preview-tudor-slider">
				<div class="swiper swiper-tudor-product">
					<div class="swiper-wrapper">
						<div class="swiper-slide">
							<img src="<?php echo $image[0]; ?>" alt="<?php echo $product->get_name(); ?>">
						</div>
						<?php foreach ($attachment_ids as $attachment_id) : ?>
						<div class="swiper-slide">
							<img src="<?php echo wp_get_attachment_image_src($attachment_id, 'large')[0]; ?>"
								alt="<?php echo $product->get_name(); ?>">
						</div>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="swiper swiper-tudor-product-thumbs">
					<div class="swiper-wrapper">
						<div class="swiper-slide">
							<img src="<?php echo $image[0]; ?>" alt="<?php echo $product->get_name(); ?>">
						</div>
						<?php foreach ($attachment_ids as $attachment_id) : ?>
						<div class="swiper-slide">
							<img src="<?php echo wp_get_attachment_image_src($attachment_id, 'large')[0]; ?>"
								alt="<?php echo $product->get_name(); ?>">
						</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			<div class="preview-tudor-info">
				<form class="cart"
					action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>"
					method="post" enctype='multipart/form-data'>
					<div class="preview-tudor-info-details">
						<h1 class="primary-title-tudor">
							<?php echo esc_html($family_name); ?>
							<span class="preview-tudor-info-details-name">
								<?php echo esc_html($reference); ?>
							</span>
						</h1>
						<div class="preview-tudor-main-features">
							<span>
								<?php echo pxl_translate($tudor_case, $tudor_case_en); ?>
							</span>
						</div>
						<span class="preview-tudor-info-details-brand-price">
							<?php
							$price = (float) $product->get_price();
							if ($price > 0) {
								$formatted_price = number_format($price, 0, ',', '.');
								echo $formatted_price . "&euro;";
							} else {
								echo ICL_LANGUAGE_CODE == 'it' ? 'Prezzo su richiesta' : 'Price on request';
							}
							?>
						</span>
					</div>
					<?php do_action('woocommerce_simple_add_to_cart'); ?>
					<div class="preview-tudor-info-details-brand-information">
						<a href="<?php echo esc_url(get_permalink(get_page_by_path('tudor/contatti'))); ?>"
							class="primary-cta-tudor alt tudor-contact-trigger" data-reference="<?php echo esc_attr($reference); ?>"
							data-collection="<?php echo esc_attr($family_name); ?>"
							data-name="<?php echo esc_attr($product->get_name()); ?>"
							data-url="<?php echo esc_url($product->get_permalink()); ?>">
							<?php if (ICL_LANGUAGE_CODE == 'it') : ?>
							Richiedi informazioni
							<?php else : ?>
							Contact Us
							<?php endif; ?>
						</a>
					</div>
					<div class="preview-tudor-info-details-brand-desc">
						<p>
							<?php echo ICL_LANGUAGE_CODE == 'it' ? $itDescription : $enDescription; ?>
						</p>
					</div>
				</form>
			</div>
		</div>
	</div>
</section>
