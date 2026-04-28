<?php

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
get_header();

$tudor_autonomy = get_nested_data($post->ID, 'AlternateDescriptions', 'TUDOR_AUTONOMIA');
$tudor_bracelet = get_nested_data($post->ID, 'AlternateDescriptions', 'TUDOR_BRACCIALE');
$tudor_case = get_nested_data($post->ID, 'AlternateDescriptions', 'TUDOR_CASSA');
$tudor_winding_crown = get_nested_data($post->ID, 'AlternateDescriptions', 'TUDOR_CORONADIC');
$tudor_warranty = get_nested_data($post->ID, 'AlternateDescriptions', 'TUDOR_GARANZIA');
$tudor_waterproofness = get_nested_data($post->ID, 'AlternateDescriptions', 'TUDOR_IMPERMEAB');
$tudor_bezel = get_nested_data($post->ID, 'AlternateDescriptions', 'TUDOR_LUNETTA');
$tudor_movement = get_nested_data($post->ID, 'AlternateDescriptions', 'TUDOR_MOVIMENTO');
$tudor_dial = get_nested_data($post->ID, 'AlternateDescriptions', 'TUDOR_QUADRANTE');
$tudor_glass = get_nested_data($post->ID, 'AlternateDescriptions', 'TUDOR_VETRO');

$tudor_autonomy_en = get_nested_data($post->ID, 'AlternateDescriptions', 'EN_TUDOR_AUTONO');
$tudor_bracelet_en = get_nested_data($post->ID, 'AlternateDescriptions', 'EN_TUDOR_BRACCI');
$tudor_case_en = get_nested_data($post->ID, 'AlternateDescriptions', 'EN_TUDOR_CASSA');
$tudor_winding_crown_en = get_nested_data($post->ID, 'AlternateDescriptions', 'EN_TUDOR_CORONA');
$tudor_warranty_en = get_nested_data($post->ID, 'AlternateDescriptions', 'EN_TUDOR_GARANZ');
$tudor_waterproofness_en = get_nested_data($post->ID, 'AlternateDescriptions', 'EN_TUDOR_IMPERM');
$tudor_bezel_en = get_nested_data($post->ID, 'AlternateDescriptions', 'EN_TUDOR_LUNETT');
$tudor_movement_en = get_nested_data($post->ID, 'AlternateDescriptions', 'EN_TUDOR_MOVIME');
$tudor_dial_en = get_nested_data($post->ID, 'AlternateDescriptions', 'EN_TUDOR_QUADRA');
$tudor_glass_en = get_nested_data($post->ID, 'AlternateDescriptions', 'EN_TUDOR_VETRO');

$tudor_case = str_replace('Anse', '<br> Anse', $tudor_case);
$tudor_case = str_replace('Spessore', '<br> Spessore', $tudor_case);
$tudor_case_en = str_replace('Lugs', '<br> Lugs', $tudor_case_en);
$tudor_case_en = str_replace('Case', '<br> Case', $tudor_case_en);

$watch_details = get_prod_data($post->ID, 'WatchDetail');
$family_name = $watch_details['WCollection'];

include(TUDOR_ROOT . "/sections/header/header-tudor.php");
include(TUDOR_ROOT . "/sections/single/preview-tudor.php");
include(TUDOR_ROOT . "/sections/single/specs-tudor.php");
include(TUDOR_ROOT . "/sections/single/banner-single-tudor.php");
include(TUDOR_ROOT . "/sections/single/related-tudor.php");
include(TUDOR_ROOT . "/sections/footer/footer-tudor.php");

get_footer();
