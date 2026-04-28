<?php

class Vite_Loader_Tudor
{
	private static $vite_url;

	public function __construct()
	{
		$this->register_constant();
		$this->include_helpers();
		add_action('wp_enqueue_scripts', array($this, 'vite_enqueue_scripts'));
		add_action('wp_head', array($this, 'vite_inject_dev_scripts'));

		// FIX: aggiunge type="module" al tag <script> della collection Vue.
		// wp_script_add_data('type','module') NON è supportato da WordPress.
		// Senza type="module" il bundle ESM di Vue esegue nel global scope
		// e la variabile 'wc' generata da Rollup/esbuild confligge con
		// window.wc di WooCommerce → SyntaxError "wc has already been declared".
		add_filter('script_loader_tag', array($this, 'add_module_type_to_collection'), 10, 3);
	}

	private function register_constant()
	{
		self::$vite_url = plugin_dir_url(dirname(__FILE__));
	}

	private function include_helpers()
	{
		require plugin_dir_path(__FILE__) . 'generic-helpers.php';
	}

	// ── TYPE="MODULE" FILTER ─────────────────────────────────────────────────

	/**
	 * Sostituisce il tag <script> della collection con type="module".
	 * Garantisce module scope isolato → nessun conflitto con window.wc di WooCommerce.
	 */
	public function add_module_type_to_collection(string $tag, string $handle, string $src): string
	{
		if ($handle !== 'tudor-collection-vue') {
			return $tag;
		}
		return '<script type="module" src="' . esc_url($src) . '"></script>' . "\n";
	}

	// ── DEV DETECTION ────────────────────────────────────────────────────────

	private function is_vite_development(): bool
	{
		$host = $_SERVER['HTTP_HOST'] ?? '';
		return (
			strpos($host, '.pixelldemo') !== false ||
			strpos($host, '.test') !== false
		) && defined('VITE_DEVELOPMENT') && VITE_DEVELOPMENT === true;
	}

	/**
	 * La collection usa VITE_COLLECTION_DEV separato da VITE_DEVELOPMENT
	 * per non interferire con il Vite principale del tema (porta 5173 vs 5174).
	 * Aggiungi in tudor.php SOLO durante lo sviluppo della collection:
	 *   define('VITE_COLLECTION_DEV', true);
	 */
	private function is_vite_collection_dev(): bool
	{
		return defined('VITE_COLLECTION_DEV') && VITE_COLLECTION_DEV === true;
	}

	private function get_vite_origin(): string
	{
		$vite_host = defined('VITE_HOST') ? VITE_HOST : 'localhost:5173';
		return 'https://' . $vite_host;
	}

	private function get_vite_collection_origin(): string
	{
		$vite_host = defined('VITE_HOST') ? VITE_HOST : 'localhost:5174';
		return 'https://' . $vite_host . ':5174';
	}

	// ── DEV: INJECT SCRIPTS IN WP_HEAD ───────────────────────────────────────

	public function vite_inject_dev_scripts(): void
	{
		if ($this->is_vite_development()) {
			$origin = $this->get_vite_origin();
?>
			<script type="module" src="<?php echo esc_url($origin . '/@vite/client'); ?>"></script>
			<script type="module" src="<?php echo esc_url($origin . '/src/sjs/main.js'); ?>"></script>
		<?php
		}

		if ($this->is_vite_collection_dev()) {
			$origin = $this->get_vite_collection_origin();
		?>
			<script type="module" src="<?php echo esc_url($origin . '/@vite/client'); ?>"></script>
			<script type="module" src="<?php echo esc_url($origin . '/src/collection/main.js'); ?>"></script>
<?php
		}
	}

	// ── PRODUZIONE: ENQUEUE DA MANIFEST ──────────────────────────────────────

	public function vite_enqueue_scripts(): void
	{
		$this->enqueue_main_assets();
		$this->enqueue_collection_assets();
	}

	private function enqueue_main_assets(): void
	{
		if ($this->is_vite_development()) return;

		$manifest_path = plugin_dir_path(dirname(__FILE__)) . 'dist/assets/.vite/manifest.json';
		if (!file_exists($manifest_path)) return;

		$manifest = json_decode(file_get_contents($manifest_path), true);
		$base_uri = self::$vite_url . 'dist/assets/';

		if (isset($manifest['src/sjs/main.js'])) {
			$entry = $manifest['src/sjs/main.js'];
			wp_enqueue_script('tudor_main', $base_uri . $entry['file'], [], null, true);
			if (!empty($entry['css'][0])) {
				wp_enqueue_style('tudor_main', $base_uri . $entry['css'][0]);
			}
		}

		if (isset($manifest['src/scss/style.scss']['file'])) {
			wp_enqueue_style('tudor_style', $base_uri . $manifest['src/scss/style.scss']['file']);
		}
	}

	private function enqueue_collection_assets(): void
	{
		if ($this->is_vite_collection_dev()) return;
		if (!$this->is_collection_page()) return;

		$manifest_path = plugin_dir_path(dirname(__FILE__)) . 'dist/collection/.vite/manifest.json';
		if (!file_exists($manifest_path)) return;

		$manifest = json_decode(file_get_contents($manifest_path), true);
		if (!isset($manifest['src/collection/main.js'])) return;

		$entry    = $manifest['src/collection/main.js'];
		$base_uri = self::$vite_url . 'dist/collection/';

		// type="module" viene aggiunto dal filtro add_module_type_to_collection()
		wp_enqueue_script('tudor-collection-vue', $base_uri . $entry['file'], [], null, true);

		if (!empty($entry['css'])) {
			foreach ($entry['css'] as $i => $css_file) {
				wp_enqueue_style('tudor-collection-vue-style-' . $i, $base_uri . $css_file, [], null);
			}
		}
	}

	private function is_collection_page(): bool
	{
		if (did_action('tudor_collection_page')) return true;

		global $post;
		if ($post && has_shortcode($post->post_content, 'tudor_collection')) return true;

		if (is_page_template('collection-tudor.php')) return true;

		return false;
	}
}

new Vite_Loader_Tudor();
