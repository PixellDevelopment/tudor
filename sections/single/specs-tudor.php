<section class="specs-tudor">
  <div class="custom-container-larger">
    <div class="specs-tudor-family-desc">
      <p class="primary-text-tudor">
        <?php echo retrieve_desc_family($family_name); ?>
      </p>
    </div>
    <div class="specs-tudor-title">
      <span><?php echo pxl_translate('Specifiche dell\'orologio', 'Watch specifications'); ?></span>
    </div>
    <div class="specs-tudor-list">
      <ul>
        <li>
          <img src="<?php echo TUDOR_FILE . '/dist/assets/images/icons/tudor_spec_guarantee.svg'; ?>"
            alt="<?php echo pxl_translate('Garanzia', 'Warranty'); ?>">
          <span>
            <span>
              <?php echo pxl_translate('Garanzia', 'Warranty'); ?>
            </span>
            <?php
            if (!empty($tudor_warranty)):
              echo pxl_translate($tudor_warranty, $tudor_warranty_en);
            else:
              echo pxl_translate(
                "Garanzia di cinque anni, trasferibile, senza registrazione né revisioni obbligatorie",
                "Five-year transferable guarantee with no registration or periodic maintenance checks required"
              );
            endif;
            ?>
          </span>
        </li>
        <li>
          <img src="<?php echo TUDOR_FILE . '/dist/assets/images/icons/tudor_spec_waterproofness.svg'; ?>"
            alt="<?php echo pxl_translate('Impermeabilità', 'Waterproofness'); ?>">
          <span>
            <span><?php echo pxl_translate('Impermeabilità', 'Waterproofness'); ?></span>
            <?php echo pxl_translate($tudor_waterproofness, $tudor_waterproofness_en); ?>
          </span>
        </li>
        <li>
          <img src="<?php echo TUDOR_FILE . '/dist/assets/images/icons/tudor_spec_case.svg'; ?>"
            alt="<?php echo pxl_translate('Cassa', 'Case'); ?>">
          <span>
            <span><?php echo pxl_translate('Cassa', 'Case'); ?></span>
            <?php echo pxl_translate($tudor_case, $tudor_case_en); ?>
          </span>
        </li>
        <li>
          <img src="<?php echo TUDOR_FILE . '/dist/assets/images/icons/tudor_spec_bezel.svg'; ?>"
            alt="<?php echo pxl_translate('Lunetta', 'Bezel'); ?>">
          <span>
            <span><?php echo pxl_translate('Lunetta', 'Bezel'); ?></span>
            <?php echo pxl_translate($tudor_bezel, $tudor_bezel_en); ?>
          </span>
        </li>
        <li>
          <img src="<?php echo TUDOR_FILE . '/dist/assets/images/icons/tudor_spec_movement.svg'; ?>"
            alt="<?php echo pxl_translate('Movimento', 'Movement'); ?>">
          <span>
            <span><?php echo pxl_translate('Movimento', 'Movement'); ?></span>
            <?php echo pxl_translate($tudor_movement, $tudor_movement_en); ?>
          </span>
        </li>
        <li>
          <img src="<?php echo TUDOR_FILE . '/dist/assets/images/icons/tudor_spec_dial.svg'; ?>"
            alt="<?php echo pxl_translate('Quadrante', 'Dial'); ?>">
          <span>
            <span><?php echo pxl_translate('Quadrante', 'Dial'); ?></span>
            <?php echo pxl_translate($tudor_dial, $tudor_dial_en); ?>
          </span>
        </li>
        <li>
          <img src="<?php echo TUDOR_FILE . '/dist/assets/images/icons/tudor_spec_crystal.svg'; ?>"
            alt="<?php echo pxl_translate('Vetro', 'Glass'); ?>">
          <span>
            <span><?php echo pxl_translate('Vetro', 'Glass'); ?></span>
            <?php echo pxl_translate($tudor_glass, $tudor_glass_en); ?>
          </span>
        </li>
        <li>
          <img src="<?php echo TUDOR_FILE . '/dist/assets/images/icons/tudor_spec_autonomy.svg'; ?>"
            alt="<?php echo pxl_translate('Autonomia', 'Autonomy'); ?>">
          <span>
            <span><?php echo pxl_translate('Autonomia', 'Autonomy'); ?></span>
            <?php echo pxl_translate($tudor_autonomy, $tudor_autonomy_en); ?>
          </span>
        </li>
        <li>
          <img src="<?php echo TUDOR_FILE . '/dist/assets/images/icons/tudor_spec_bracelet.svg'; ?>"
            alt="<?php echo pxl_translate('Bracciale', 'Bracelet'); ?>">
          <span>
            <span><?php echo pxl_translate('Bracciale', 'Bracelet'); ?></span>
            <?php echo pxl_translate($tudor_bracelet, $tudor_bracelet_en); ?>
          </span>
        </li>
        <li>
          <img src="<?php echo TUDOR_FILE . '/dist/assets/images/icons/tudor_spec_winding_crown.svg'; ?>"
            alt="<?php echo pxl_translate('Corona di carica', 'Winding Crown'); ?>">
          <span>
            <span><?php echo pxl_translate('Corona di carica', 'Winding Crown'); ?></span>
            <?php echo pxl_translate($tudor_winding_crown, $tudor_winding_crown_en); ?>
          </span>
        </li>
      </ul>
    </div>
  </div>
</section>