<div class="footer-tudor">
  <div class="footer-tudor-logo">
    <img src="<?php echo TUDOR_FILE . '/dist/assets/images/icons/tudor-logotype-white-150x150.webp'; ?>" alt="">
  </div>
  <div class="footer-tudor-menu">
    <?php
    wp_nav_menu( array(
      'theme_location' => 'tudor-menu',
      'container'      => false,
      'fallback_cb'    => false,
    ) );
    ?>
  </div>
</div>