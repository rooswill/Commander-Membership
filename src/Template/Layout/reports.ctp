<!DOCTYPE html>
<html>
<head>
    <?php echo $this->Html->charset(); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $this->fetch('title'); ?>
    </title>
    <?php
    $this->Html->meta(
        'favicon.ico',
        '/img/favicon.ico',
        ['type' => 'icon']
    );
      //echo $this->Html->meta('icon'); 
    ?>

    <?php echo $this->Html->css('base.css'); ?>
    <?php echo $this->Html->css('cake.css'); ?>
    <?php echo $this->Html->css('fonts/fonts.css'); ?>
    <?php echo $this->Html->css('bootstrap.min.css'); ?>
    <?php echo $this->Html->css('style.css'); ?>

    <?php echo $this->fetch('meta'); ?>

    <?php echo $this->Html->script('jquery-1.10.1.min.js'); ?>
    <?php echo $this->Html->script('jquery.slides.min.js'); ?>
    <?php echo $this->Html->script('bootstrap.min.js'); ?>
    <?php echo $this->Html->script('jquery.validate.min.js'); ?>
    <?php echo $this->Html->script('jquery.film_roll.min.js'); ?>
    <?php echo $this->Html->script('functions.js'); ?>
    <script type="text/javascript">
      $(window).load(function() {
        // Animate loader off screen
        $(".se-pre-con").fadeOut("slow");
      });
    </script>
</head>
<body>
  <div class="se-pre-con"></div>
    <div class="container">
      <div class="main-headings">
        <div class="mobile-display header-image-mobile">
          <img src='/img/site-images/logo.png' />
        </div>
        <div class="desktop-display">
          <div class="desktop-logo">
            <img src='/img/site-images/desktop-version-header.jpg' />
          </div>
          <div class="desktop-menu">
            <?php echo $this->element('navigation'); ?>
          </div>
          <div class="clear"></div>
        </div>
      </div>      
        <?php echo $this->fetch('content'); ?>
      <div class="clear"></div>
      <footer class="site-footer desktop-display" role="contentinfo">
        <div class="wrapper">
          <?php echo $this->element('footer-navigation'); ?>
        </div>
      </footer>
    </div>
</body>
</html>
