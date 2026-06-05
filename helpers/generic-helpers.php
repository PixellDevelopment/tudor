<?php

if (!function_exists("is_staging")) {
	function is_staging()
	{
		$current_domain = $_SERVER['HTTP_HOST'];
		if (
			strpos($current_domain, '.pixelldemo') !== false
			|| strpos($current_domain, '.test') !== false
		) {
			return true;
		}
		return false;
	}
}

if (!function_exists("get_tudor_attributes")) {
	function get_tudor_attributes($prod_id)
	{
		$details = [];
		$details_raw = get_post_meta($prod_id, 'eg-details', true);

		if ($details_raw) {
			if (is_object($details_raw) || is_array($details_raw)) {
				$details = (array)$details_raw;
			} else if (is_string($details_raw)) {
				$first = @unserialize($details_raw);
				if ($first !== false) {
					if (is_string($first)) {
						$second = @unserialize($first);
						$details = $second !== false ? (array)$second : (array)$first;
					} else {
						$details = (array)$first;
					}
				}
			}
		}

		// Fallback agli attributi WooCommerce se eg-details è vuoto
		if (empty($details)) {
			$details = get_tudor_attributes_from_wc($prod_id);
		}

		return $details;
	}
}

if (!function_exists("get_tudor_attributes_from_wc")) {
	/**
	 * Fallback: legge gli attributi locali WooCommerce (TUDOR_*)
	 * e li restituisce come array compatibile con la struttura AlternateDescriptions.
	 * Ogni elemento ha la forma: ['Key' => 'TUDOR_CASSA', 'Description' => 'valore']
	 */
	function get_tudor_attributes_from_wc($prod_id)
	{
		$product = wc_get_product($prod_id);
		if (!$product) {
			return [];
		}

		$attributes = $product->get_attributes();
		$result = [];

		foreach ($attributes as $attr_name => $attr_obj) {
			// Gli attributi locali sono istanze di WC_Product_Attribute
			if (!($attr_obj instanceof WC_Product_Attribute)) {
				continue;
			}

			$name = strtoupper($attr_obj->get_name()); // es. "TUDOR_CASSA"

			// Attributi locali: i valori sono in get_options() come array di stringhe
			$options = $attr_obj->get_options();
			$value = !empty($options) ? implode(', ', $options) : '';

			$result[] = [
				'Key'         => $name,
				'Description' => $value,
			];
		}

		return $result;
	}
}

if (!function_exists("get_prod_data")) {
	function get_prod_data($prod_id, $key, $return_array = true)
	{
		$data = get_tudor_attributes($prod_id);
		if (isset($data[$key])) {
			return $return_array ? (array) $data[$key] : $data[$key];
		}
		return null;
	}
}

if (!function_exists("get_nested_data")) {
	function get_nested_data($prod_id, $key, $child_key = null)
	{
		$data = get_prod_data($prod_id, $key);

		// Fallback agli attributi WooCommerce se eg-details non ha il dato
		if (empty($data) && $child_key !== null) {
			$wc_attrs = get_tudor_attributes_from_wc($prod_id);
			$index = array_column($wc_attrs, 'Description', 'Key');
			return isset($index[$child_key]) ? $index[$child_key] : null;
		}

		if (empty($data)) {
			return [];
		}

		$data = array_map(fn($item) => (array) $item, $data);

		if ($child_key === null) {
			return (array) $data;
		}

		$index = array_column($data, null, 'Key');
		return isset($index[$child_key]) ? $index[$child_key]['Description'] : null;
	}
}

if (!function_exists("retrieve_desc_family")) {
	function retrieve_desc_family($family)
	{
		$tudor_family_desc = require_once('tudor-family-desc.php');
		return isset($tudor_family_desc[$family]) ? $tudor_family_desc[$family] : '';
	}
}

if (!function_exists("tudor_breadcrumb")) {
	function tudor_breadcrumb($family_name)
	{
		$home_url = home_url('/');
		$site_name = get_bloginfo('name');
		$orologi_url = '#';
		$tudor_url = '/tudor/collezione/';

		echo '<nav class="tudor-breadcrumb" aria-label="breadcrumb">';
		echo '<ol>';
		echo '<li><a href="' . esc_url($home_url) . '">' . esc_html('Home') . '</a></li>';
		echo '<li><span class="separator">/</span><a href="' . esc_url($orologi_url) . '">Orologi</a></li>';
		echo '<li><span class="separator">/</span><a href="' . esc_url($tudor_url) . '">Tudor</a></li>';

		if (!empty($family_name)) {
			echo '<li><span class="separator">/</span><span class="current">' . esc_html($family_name) . '</span></li>';
		}

		echo '</ol>';
		echo '</nav>';
	}
}

if (!function_exists("check_is_tudor")) {
	function check_is_tudor($product = null)
	{
		// Fallback se non passato
		if (!$product || !is_a($product, 'WC_Product')) {
			global $post;
			$product = wc_get_product($post->ID);
		}

		if (!$product || !is_a($product, 'WC_Product')) {
			return false;
		}

		$prod_id = $product->get_id();

		$tudor_ids = [];
		$tudor_term_it = get_term_by('slug', 'tudor', 'product_cat');
		if ($tudor_term_it) {
			$tudor_ids[] = $tudor_term_it->term_id;
		}
		$tudor_term_en = get_term_by('slug', 'tudor-en', 'product_cat');
		if ($tudor_term_en) {
			$tudor_ids[] = $tudor_term_en->term_id;
		}
		if (empty($tudor_ids)) {
			return false;
		}

		$product_terms = wp_get_post_terms($prod_id, 'product_cat');
		if (is_wp_error($product_terms) || empty($product_terms)) {
			return false;
		}

		foreach ($product_terms as $term) {
			$ancestors = get_ancestors($term->term_id, 'product_cat', 'taxonomy');
			if (in_array($term->term_id, $tudor_ids)) {
				return true;
			}
			if (!empty(array_intersect($tudor_ids, $ancestors))) {
				return true;
			}
		}

		// Brand taxonomy
		$prod_brands = wp_get_post_terms($prod_id, 'product_brand');
		if (!is_wp_error($prod_brands) && !empty($prod_brands)) {
			if (strtolower($prod_brands[0]->slug) === 'tudor') {
				return true;
			}
		}

		return false;
	}
}

/**
 * Ottiene 4 prodotti WooCommerce random con categoria padre "tudor"
 * o con una categoria figlia di "tudor".
 */
function get_prodotti_tudor($numero = 4)
{

	// Raccoglie radici tudor per IT e EN
	$root_slugs = ['tudor', 'tudor-en'];
	$cat_ids = [];

	foreach ($root_slugs as $slug) {
		$root = get_term_by('slug', $slug, 'product_cat');
		if (! $root || is_wp_error($root)) {
			continue;
		}
		$cat_ids[] = $root->term_id;
		$figle = get_terms([
			'taxonomy'   => 'product_cat',
			'child_of'   => $root->term_id,
			'hide_empty' => false,
			'fields'     => 'ids',
		]);
		if (! empty($figle) && ! is_wp_error($figle)) {
			$cat_ids = array_merge($cat_ids, $figle);
		}
	}

	if (empty($cat_ids)) {
		return new WP_Query(['post__in' => [0]]);
	}

	// 3. Query prodotti
	$args = [
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => $numero,
		'orderby'        => 'rand',   // random ad ogni chiamata
		'tax_query'      => [
			[
				'taxonomy'         => 'product_cat',
				'field'            => 'term_id',
				'terms'            => $cat_ids,
				'operator'         => 'IN',
				'include_children' => false, // già gestiti sopra
			],
		],
	];

	return new WP_Query($args);
}

if (!function_exists("pxl_translate")) {
	/**
	 * Ritorna la stringa di traduzione corrispondente in base alla lingua corrente.
	 * @param string $it_string
	 * @param string $en_string
	 * @return string
	 */
	function pxl_translate($it_string, $en_string)
	{
		if (ICL_LANGUAGE_CODE == 'it') {
			return $it_string;
		} else {
			return $en_string;
		}
	}
}

if (!function_exists("has_child_category")) {
	/**
	 * Controlla se un prodotto ha una categoria che è figlia di quella specificata.
	 *
	 * @param int $product_id Riferimento del prodotto.
	 * @param string $cat_slug Slug associato alla categoria del prodotto.
	 * @return boolean
	 */
	function has_child_category($product_id, $cat_slug)
	{
		$term = get_term_by('slug', $cat_slug, 'product_cat');
		$child_categories = get_term_children($term->term_id, 'product_cat');
		$product_categories = wp_get_post_terms($product_id, 'product_cat');

		foreach ($product_categories as $category) {
			if (in_array($category->term_id, $child_categories)) {
				return true;
			}
		}

		return false;
	}
}
