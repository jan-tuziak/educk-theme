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
      .educk-swiper { width: 100%; }
      .educk-swiper .swiper { height: 100%; }
      .educk-swiper .swiper-wrapper { height: 100%; }
      .educk-swiper .swiper-slide { height: 100%; display: flex; }
      .educk-swiper .swiper-slide > * { width: 100%; }
      .educk-swiper .swiper-button-prev,
      .educk-swiper .swiper-button-next {
        z-index: 9999;
        min-width: 44px;
        min-height: 44px;
        touch-action: manipulation;
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
          el: root.querySelector('.swiper-pagination'),
          clickable: true
        },
        navigation: {
          nextEl: root.querySelector('.swiper-button-next'),
          prevEl: root.querySelector('.swiper-button-prev')
        }
      };

      if (autoplayEnabled) {
        opts.autoplay = { delay: delay, disableOnInteraction: false };
      }

      new Swiper(swiperEl, opts);
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
