<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://pixell.it
 * @since      1.0.0
 *
 * @package    Tudor
 * @subpackage Tudor/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Tudor
 * @subpackage Tudor/admin
 * @author     Pixell <dev@pixell.it>
 */
class Tudor_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Tudor_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tudor_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/tudor-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Tudor_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tudor_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/tudor-admin.js', array('jquery'), $this->version, false);
	}

	/**
	 * Register templates.
	 *
	 * @since    1.0.0
	 */

	public function tudor_templates($templates)
	{
		$templates['home-tudor.php'] = 'New Tudor Bespoke - Home';
		$templates['collection-tudor.php'] = 'New Tudor Bespoke - Collection';
		$templates['contact-tudor.php'] = 'New Tudor Bespoke - Contact';
		return $templates;
	}

	/**
	 * Include templates.
	 *
	 * @since    1.0.0
	 */

	public function include_template_files($template)
	{

		switch (true) {
			case is_page_template('home-tudor.php'):
				$template = TUDOR_ROOT . '/templates/home-tudor.php';
				break;
			case is_page_template('collection-tudor.php'):
				$template = TUDOR_ROOT . '/templates/collection-tudor.php';
				break;
			case is_page_template('contact-tudor.php'):
				$template = TUDOR_ROOT . '/templates/contact-tudor.php';
				break;
			case $this->is_tudor_single_product():
				$template = TUDOR_ROOT . '/templates/single-tudor.php';
				break;
			default:
				# code...
				break;
		}

		return $template;
	}

	public function tudor_register_menus()
	{
		register_nav_menus(array(
			'tudor-menu' => __('Menu Tudor', 'tudor')
		));
	}

	/**
	 * Verifica se siamo su un singolo prodotto Tudor.
	 */
	private function is_tudor_single_product(): bool
	{
		if (!is_singular('product')) {
			return false;
		}

		global $post;

		$locale     = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : 'it';
		$prod_brands = wp_get_post_terms($post->ID, 'product_brand');

		return has_child_category($post->ID, 'tudor', $locale)
			|| (isset($prod_brands[0]) && strtolower($prod_brands[0]->slug) === 'tudor');
	}
}
