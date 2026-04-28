<?php

/**
 * Sezione contatti – richiama il form CF7 in base alla lingua WPML attiva.
 *
 * @package Tudor
 * @since   1.2.0
 */

if (! defined('ABSPATH')) {
    exit;
}

// Rileva lingua corrente tramite WPML; fallback su 'it'
$lang = (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE)
    ? ICL_LANGUAGE_CODE
    : 'it';

// Seleziona lo shortcode in base alla lingua
switch ($lang) {
    case 'en':
        $cf7_shortcode = get_option('tudor_cf7_shortcode_en', '');
        break;
    case 'it':
    default:
        $cf7_shortcode = get_option('tudor_cf7_shortcode', '');
        break;
}
?>

<section class="contact" id="contact-tudor">
  <div class="custom-container">
    <div class="contact-title">
      <h2 class="primary-title-tudor">
        <span>
          <?php echo pxl_translate('SEI INTERESSATO?', 'ARE YOU INTERESTED?'); ?>
        </span>
        <?php echo pxl_translate('CONTATTACI', 'CONTACT US'); ?>
      </h2>
    </div>
    <div class="contact-form tudor-contact__form" data-lang="<?php echo esc_attr($lang); ?>">
      <?php if ($cf7_shortcode) : ?>
      <?php echo do_shortcode($cf7_shortcode); ?>
      <?php endif; ?>
    </div>
  </div>
</section>
