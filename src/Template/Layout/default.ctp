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
      <div class="mobile-display">
        <div class="main-content">
            <div id="slides2">
              <img src="/img/sliders/bottom/Boxing-bottom.jpg" />
              <img src="/img/sliders/bottom/Cross-bottom.jpg" />
              <img src="/img/sliders/bottom/Cycling-bottom.jpg" />
              <img src="/img/sliders/bottom/Running-bottom.jpg" />
              <img src="/img/sliders/bottom/Stand-up-bottom.jpg" />
              <img src="/img/sliders/bottom/Strength-Bottom.jpg" />
              <img src="/img/sliders/bottom/Swimming-bottom.jpg" />
              <img src="/img/sliders/bottom/Tennis-bottom.jpg" />
              <img src="/img/sliders/bottom/Yoga-bottom.jpg" />
            </div>
        </div>
      </div>
      <div class="desktop-display">
        <div class="main-content">
            <div id="filmroll">
              <div>
                <a href="#"><img src="/img/sliders/bottom/Boxing-bottom.jpg" /></a>
              </div>
              <div>
                <a href="#"><img src="/img/sliders/bottom/Cross-bottom.jpg" /></a>
              </div>
              <div>
                <a href="#"><img src="/img/sliders/bottom/Cycling-bottom.jpg" /></a>
              </div>
              <div>
                <a href="#"><img src="/img/sliders/bottom/Running-bottom.jpg" /></a>
              </div>
              <div>
                <a href="#"><img src="/img/sliders/bottom/Stand-up-bottom.jpg" /></a>
              </div>
              <div>
                <a href="#"><img src="/img/sliders/bottom/Strength-Bottom.jpg" /></a>
              </div>
              <div>
                <a href="#"><img src="/img/sliders/bottom/Swimming-bottom.jpg" /></a>
              </div>
              <div>
                <a href="#"><img src="/img/sliders/bottom/Tennis-bottom.jpg" /></a>
              </div>
              <div>
                <a href="#"><img src="/img/sliders/bottom/Yoga-bottom.jpg" /></a>
              </div>
            </div>
        </div>
      </div>
      <div class="terms-block mobile-display">
        Click here to read our <a href="http://www.commanderstore.com/pages/terms">terms and conditions</a>
      </div>
      <div class="clear"></div>
      <footer class="site-footer desktop-display" role="contentinfo">
        <div class="wrapper">
          <?php echo $this->element('footer-navigation'); ?>
        </div>
      </footer>


    </div>
    <script>

        jQuery(window).load(function() {
          var film_roll = new FilmRoll({
              configure_load: true,
              container: '#filmroll',
              pager: false
            });
        });

        $(function() {

            $('#slides2').slidesjs({
                width: 630,
                height: 403,
                navigation: {
                  active: false,
                  effect: "slide"
                },
                pagination: {
                  active: false,
                  effect: "slide"
                },
                play: {
                  active: false,
                    // [boolean] Generate the play and stop buttons.
                    // You cannot use your own buttons. Sorry.
                  effect: "slide",
                    // [string] Can be either "slide" or "fade".
                  interval: 6000,
                    // [number] Time spent on each slide in milliseconds.
                  auto: true,
                    // [boolean] Start playing the slideshow on load.
                  swap: false,
                    // [boolean] show/hide stop and play buttons
                  pauseOnHover: false,
                    // [boolean] pause a playing slideshow on hover
                  restartDelay: 2500
                    // [number] restart delay on inactive slideshow
                }
            });

        });
    </script>
</body>
</html>
