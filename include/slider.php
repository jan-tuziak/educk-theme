<?php

/**
 * Educk: Elementor Templates Carousel using Swiper.js (frontend-safe)
 *
 * Usage:
 * [educk_swiper templates="123,456,789" height="80vh" loop="true" autoplay="false" delay="5000"]
 */

add_shortcode('educk_swiper', function ($atts) {
    if (!did_action('elementor/loaded')) {
        return '';
    }

    $atts = shortcode_atts([
        'templates' => '',
        'height'    => '80vh',
        'loop'      => 'true',
        'autoplay'  => 'false',
        'delay'     => '5000',
    ], $atts, 'educk_swiper');

    $ids = array_filter(array_map('absint', explode(',', (string) $atts['templates'])));
    if (!$ids) {
        return '';
    }

    // Enqueue Swiper assets right here (works reliably on frontend).
    wp_enqueue_style(
        'educk-swiper',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
        [],
        '11.0.0'
    );

    wp_enqueue_script(
        'educk-swiper',
        'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
        [],
        '11.0.0',
        true
    );

    // Base CSS (inline, tied to the enqueued Swiper style handle).
    wp_add_inline_style('educk-swiper', '
      .educk-swiper { width: 100%; position: relative; }
      .educk-swiper .swiper { height: 100%; }
      .educk-swiper .swiper-wrapper { height: 100%; }
      .educk-swiper .swiper-slide { height: 100%; display: flex; }
      .educk-swiper .swiper-slide > * { width: 100%; }
      .educk-swiper .swiper-button-prev,
      .educk-swiper .swiper-button-next {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 9999;
        width: 44px;
        height: 44px;
        margin: 0;
        background: rgba(0,0,0,0.5);
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        transition: background-color 0.3s;
        touch-action: manipulation;
        -webkit-tap-highlight-color: rgba(255,255,255,0.3);
      }
      .educk-swiper .swiper-button-prev {
        left: 10px;
      }
      .educk-swiper .swiper-button-next {
        right: 10px;
      }
      .educk-swiper .swiper-button-prev:hover,
      .educk-swiper .swiper-button-next:hover {
        background: rgba(0,0,0,0.8);
      }
      .educk-swiper .swiper-button-prev::after,
      .educk-swiper .swiper-button-next::after {
        font-family: swiper-icons;
        font-size: 18px;
        font-weight: 400;
      }
    ');

    // Enqueue ONE initializer (only once per page), guaranteed to run AFTER Swiper loads.
    static $init_added = false;
    if (!$init_added) {
        $init_added = true;

        $init_js = <<<JS
(function(){
  function initAll(){
    document.querySelectorAll('.educk-swiper .swiper').forEach(function(swiperEl){
      if (swiperEl.classList.contains('swiper-initialized')) return;

      var root = swiperEl.closest('.educk-swiper');
      if (!root) return;

      var loop = root.dataset.loop === 'true';
      var autoplayEnabled = root.dataset.autoplay === 'true';
      var delay = parseInt(root.dataset.delay || '5000', 10);

      var opts = {
        slidesPerView: 1,
        watchOverflow: true,
        speed: 450,
        loop: loop,
        preloadImages: false,
        lazy: true,
        pagination: {
          el: swiperEl.querySelector('.swiper-pagination'),
          clickable: true
        },
        navigation: {
          nextEl: swiperEl.querySelector('.swiper-button-next'),
          prevEl: swiperEl.querySelector('.swiper-button-prev')
        }
      };

      if (autoplayEnabled) {
        opts.autoplay = { delay: delay, disableOnInteraction: false };
      }

      var swiper = new Swiper(swiperEl, opts);

      // Manual event listeners for navigation buttons to ensure they work on mobile
      var nextBtn = swiperEl.querySelector('.swiper-button-next');
      var prevBtn = swiperEl.querySelector('.swiper-button-prev');

      function handleNext(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Next button clicked/touched, swiper instance:', swiper);
        if (swiper && typeof swiper.slideNext === 'function') {
          swiper.slideNext();
          console.log('Called slideNext');
        } else {
          console.error('Swiper instance or slideNext method not available');
        }
      }

      function handlePrev(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Prev button clicked/touched, swiper instance:', swiper);
        if (swiper && typeof swiper.slidePrev === 'function') {
          swiper.slidePrev();
          console.log('Called slidePrev');
        } else {
          console.error('Swiper instance or slidePrev method not available');
        }
      }

      if (nextBtn) {
        nextBtn.addEventListener('click', handleNext);
        nextBtn.addEventListener('touchstart', handleNext);
        nextBtn.addEventListener('touchend', function(e) { e.preventDefault(); });
      }

      if (prevBtn) {
        prevBtn.addEventListener('click', handlePrev);
        prevBtn.addEventListener('touchstart', handlePrev);
        prevBtn.addEventListener('touchend', function(e) { e.preventDefault(); });
      }
    });
  }

  // Run after everything is loaded (Swiper is enqueued in footer).
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }

  // Safety: also run on full load (covers aggressive caches / defer combos).
  window.addEventListener('load', initAll);
})();
JS;

        wp_add_inline_script('educk-swiper', $init_js, 'after');
    }

    $height   = trim((string) $atts['height']) ?: '80vh';
    $loop     = ($atts['loop'] === 'true') ? 'true' : 'false';
    $autoplay = ($atts['autoplay'] === 'true') ? 'true' : 'false';
    $delay    = max(1000, (int) $atts['delay']);

    ob_start();
    ?>
    <div class="educk-swiper"
         style="height: <?php echo esc_attr($height); ?>;"
         data-loop="<?php echo esc_attr($loop); ?>"
         data-autoplay="<?php echo esc_attr($autoplay); ?>"
         data-delay="<?php echo esc_attr((string) $delay); ?>">

      <div class="swiper">
        <div class="swiper-wrapper">
          <?php foreach ($ids as $template_id): ?>
            <div class="swiper-slide">
              <?php
              // IMPORTANT: second arg = true to ensure Elementor template CSS is printed/enqueued
              echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display($template_id, true);
              ?>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="swiper-pagination"></div>
        <button class="swiper-button-prev" type="button" aria-label="Previous slide"></button>
        <button class="swiper-button-next" type="button" aria-label="Next slide"></button>
      </div>

    </div>
    <?php
    return ob_get_clean();
});
