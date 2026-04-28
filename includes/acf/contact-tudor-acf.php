<?php
/**
 * ACF Field Group: Tudor Contact CF7 Shortcodes
 *
 * Registra i campi ACF per i shortcode CF7 (IT e EN)
 * collegati al page template contact-tudor.php
 *
 * Da includere nel functions.php del tema oppure in un plugin dedicato.
 */

if ( function_exists( 'acf_add_local_field_group' ) ) :

    acf_add_local_field_group( [
        'key'    => 'group_tudor_contact_cf7',
        'title'  => 'Tudor Contact – CF7 Shortcodes',

        // ── Mostra questo group solo sulle pagine che usano il template contact-tudor.php ──
        'location' => [
            [
                [
                    'param'    => 'page_template',
                    'operator' => '==',
                    'value'    => 'contact-tudor.php',
                ],
            ],
        ],

        // ── Opzioni di visualizzazione ──
        'menu_order'            => 0,
        'position'              => 'normal',      // normal | acf_after_title | side
        'style'                 => 'default',     // default | seamless
        'label_placement'       => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen'        => [],
        'active'                => true,
        'description'           => 'Shortcode del form Contact Form 7 per le versioni IT e EN della pagina contatti Tudor.',

        // ── Campi ──
        'fields' => [

            // Campo 1 – Shortcode CF7 (Italiano)
            [
                'key'               => 'field_tudor_cf7_shortcode_it',
                'label'             => 'CF7 Shortcode (IT)',
                'name'              => 'tudor_cf7_shortcode',        // corrisponde a get_option('tudor_cf7_shortcode')
                'type'              => 'text',
                'instructions'      => 'Incolla qui lo shortcode del form CF7 per la versione italiana. Es: [contact-form-7 id="123" title="Contatti IT"]',
                'required'          => 0,
                'default_value'     => '',
                'placeholder'       => '[contact-form-7 id="" title=""]',
                'prepend'           => '',
                'append'            => '',
                'maxlength'         => '',
                'wrapper'           => [
                    'width' => '50',
                    'class' => '',
                    'id'    => '',
                ],
            ],

            // Campo 2 – Shortcode CF7 (Inglese)
            [
                'key'               => 'field_tudor_cf7_shortcode_en',
                'label'             => 'CF7 Shortcode (EN)',
                'name'              => 'tudor_cf7_shortcode_en',     // corrisponde a get_option('tudor_cf7_shortcode_en')
                'type'              => 'text',
                'instructions'      => 'Incolla qui lo shortcode del form CF7 per la versione inglese. Es: [contact-form-7 id="456" title="Contact EN"]',
                'required'          => 0,
                'default_value'     => '',
                'placeholder'       => '[contact-form-7 id="" title=""]',
                'prepend'           => '',
                'append'            => '',
                'maxlength'         => '',
                'wrapper'           => [
                    'width' => '50',
                    'class' => '',
                    'id'    => '',
                ],
            ],

        ],
    ] );

endif;


/**
 * ──────────────────────────────────────────────────────────────────────────────
 * SYNC automatico: salva i valori ACF anche nelle opzioni WordPress
 * ──────────────────────────────────────────────────────────────────────────────
 *
 * Il codice nel template usa get_option() invece di get_field().
 * Questo hook copia il valore ACF nell'opzione WP ogni volta che la pagina
 * viene salvata, mantenendo la compatibilità con il codice esistente.
 */
add_action( 'acf/save_post', 'tudor_sync_cf7_shortcodes_to_options', 20 );

function tudor_sync_cf7_shortcodes_to_options( $post_id ) {

    // Esegui solo per le pagine con il template corretto
    if ( get_page_template_slug( $post_id ) !== 'contact-tudor.php' ) {
        return;
    }

    $shortcode_it = get_field( 'tudor_cf7_shortcode',    $post_id );
    $shortcode_en = get_field( 'tudor_cf7_shortcode_en', $post_id );

    if ( $shortcode_it !== null ) {
        update_option( 'tudor_cf7_shortcode',    sanitize_text_field( $shortcode_it ) );
    }

    if ( $shortcode_en !== null ) {
        update_option( 'tudor_cf7_shortcode_en', sanitize_text_field( $shortcode_en ) );
    }
}