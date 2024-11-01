<div class="wppf-profile">
  <div class="wppf-profile__img">
    <?php echo $wp_user_profile->avatar(); ?>
  </div>
  <div class="wppf-profile__content">

    <div class="wppf-profile__name"><?php echo $wp_user_profile->name(); ?></div>
    <?php echo $wp_user_profile->icons(); ?>

    <div class="wppf-profile__description">
      <?php echo $wp_user_profile->description(); ?>
    </div>
  </div>
</div>
