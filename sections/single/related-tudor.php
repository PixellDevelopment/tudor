<section class="related-tudor">
  <div class="custom-container">
    <div class="related-tudor-title">
      <h2 class="primary-title-tudor">
        <?php echo pxl_translate('TI POTREBBE PIACERE', 'YOU MAY LIKE'); ?>
        <span>TUDOR COLLECTION</span>
      </h2>
    </div>
    <div class="related-tudor-list">
      <?php
      $args = [
        'post_type'      => 'product',
        'posts_per_page' => 4,
        'post_status'    => 'publish',
        'orderby'        => 'rand',
        'tax_query'      => [
          [
            'taxonomy' => 'product_brand',
            'field'    => 'slug',
            'terms'    => 'tudor',
          ],
        ],
      ];
      $query = new WP_Query($args);

      if ($query->have_posts()) :
        while ($query->have_posts()) :
          $query->the_post();
          $product = wc_get_product(get_the_ID());
          $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
      ?>

      <div class="related-tudor-single">
        <a href="<?php the_permalink(); ?>">
          <div class="related-tudor-single-image">
            <img src="<?php echo $image[0]; ?>" alt="<?php echo $product->get_name(); ?>">
          </div>
          <div class="related-tudor-single-text">
            <span>Tudor</span>
            <span class="related-tudor-single-title">
              <?php echo $product->get_name(); ?>
            </span>
            <span class="related-tudor-single-price">
              <?php
                  $formatted_price = number_format($product->get_price(), 0, ',', '.');
                  echo $formatted_price . "&euro;";
                  //echo 'Lol: ' . $product->get_price_html();
                  ?>
            </span>
          </div>
        </a>
      </div>

      <?php
        endwhile;
      endif;
      wp_reset_postdata();
      ?>
    </div>
  </div>
</section>
