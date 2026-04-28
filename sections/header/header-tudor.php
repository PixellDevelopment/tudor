<div class="header-tudor">
  <div class="header-tudor-logo">
    <img src="<?php echo TUDOR_FILE . '/dist/assets/images/icons/tudor-plaques-120x60_it.webp'; ?>" alt="Tudor">
  </div>

  <div class="only-desktop">
    <div class="header-tudor-menu">
      <?php
      wp_nav_menu( array(
        'theme_location' => 'tudor-menu',
        'menu_class'     => '',
        'container'      => false,
        'fallback_cb'    => false,
      ) );
      ?>
    </div>
  </div>

  <div class="only-mobile">
    <div class="header-tudor-menu-trigger">
      <span>Menu</span>
      <img src="<?php echo TUDOR_FILE . '/dist/assets/images/icons/downward-arrow.webp'; ?>" alt="">
    </div>
  </div>
</div>

<?php
if ( is_product() ) {
    tudor_breadcrumb( $family_name );
}
include( TUDOR_ROOT . "/sections/parts/menu-mobile-tudor.php" );
?>