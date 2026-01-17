<?php
// Get host in a WP-safe way (works in front-end, admin, and most contexts)
$host = wp_parse_url(home_url('/'), PHP_URL_HOST);

// Fallbacks (WP-CLI/cron/edge cases)
if (empty($host)) {
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
}

$parts = explode('.', $host);
$TLD = strtolower(end($parts)); // 'pl' or 'org'
$include_file = ($TLD === 'pl') ? 'include/pl.php' : 'include/org.php';

include_once get_theme_file_path($include_file);
include_once get_theme_file_path('include/wp-login-modified.php');
include_once get_theme_file_path('include/woo.php');
include_once get_theme_file_path('include/elementor-form-turnstile-handler.php');
//include_once get_theme_file_path('include/educk-force-coupons.php');

add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles');
function theme_enqueue_styles() {
	//Skipping parent style, because it was messing up headings on Blog posts
	//wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
        //wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style') );
	
        wp_enqueue_style(
			'child-style', 
			get_stylesheet_directory_uri() . '/style.css', 
			array(),
			wp_get_theme()->get( 'Version' ), //proper xstyle versioning for production
			//filemtime(get_stylesheet_directory() . "/style.css" ), //quick style versioning for development (omits caching)
			'all');
}

/**
 * Add MailerLite JavaScript code
 * to add MailerLite forms as JavaScript snippets
 **/
function add_mailerlite_js_code(){
	wp_register_script( 'add_mailerlite_js_code', '', [], '', true );
	wp_enqueue_script( 'add_mailerlite_js_code'  );
	wp_add_inline_script( 'add_mailerlite_js_code', '
			<!-- MailerLite Universal -->
			<script>
			(function(m,a,i,l,e,r){ m["MailerLiteObject"]=e;function f(){
			var c={ a:arguments,q:[]};var r=this.push(c);return "number"!=typeof r?r:f.bind(c.q);}
			f.q=f.q||[];m[e]=m[e]||f.bind(f.q);m[e].q=m[e].q||f.q;r=a.createElement(i);
			var _=a.getElementsByTagName(i)[0];r.async=1;r.src=l+"?v"+(~~(new Date().getTime()/1000000));
			_.parentNode.insertBefore(r,_);})(window, document, "script", "https://static.mailerlite.com/js/universal.js", "ml");

			var ml_account = ml("accounts", "3008326", "m2s0x0f0v7", "load");
			</script>
			<!-- End MailerLite Universal -->
		' );
}
add_action( 'wp_enqueue_scripts', 'add_mailerlite_js_code' );

/**
 * Enqueue LearnDash Focus Mode styles
 **/
function enqueue_learndash_styles(){
	wp_register_style( 'learndash-focus-styles', false );
	wp_enqueue_style( 'learndash-focus-styles' );
	wp_add_inline_style( 'learndash-focus-styles', '
		/* Lesson Title*/
		div.ld-focus-content > h1 {
			font-size: 1.6em;
		}
		
		/* Mark as complete button (at the top and the bottom) */
		input.learndash_mark_complete_button {
			font-size: 0.8em !important;
		}
		
		/* Registration message */
		div.ld-login-modal-register  div.ld-alert-messages {
			color: #fff;
		}
	' );
}
add_action( 'wp_enqueue_scripts', 'enqueue_learndash_styles' );

/**
 * @snippet       Remove Tax if Field Value - WooCommerce Checkout
 * @how-to        businessbloomer.com/woocommerce-customization
 * @author        Rodolfo Melogli, Business Bloomer, BusinessBloomer.com
 * @testedwith    WooCommerce 8
 * @community     https://businessbloomer.com/club/
 */
add_action( 'woocommerce_checkout_update_order_review', 'bbloomer_taxexempt_checkout_based_on_country' );
function bbloomer_taxexempt_checkout_based_on_country( $post_data ) {
        WC()->customer->set_is_vat_exempt( false );
        parse_str( $post_data, $output );
        if ( $output['billing_country'] !== 'PL' && $output['billing_tax_no'] !== '' && $output['billing_company_name'] !== '' ){
			WC()->customer->set_is_vat_exempt( true );
		} 
}

/**
 * @snippet       Refresh Checkout Upon Input Field Change
 * @how-to        businessbloomer.com/woocommerce-customization
 * @author        Rodolfo Melogli, Business Bloomer
 * @compatible    WooCommerce 7
 * @community     https://businessbloomer.com/club/
 */
add_filter( 'woocommerce_checkout_fields', 'bbloomer_checkout_fields_trigger_refresh', 9999 );
function bbloomer_checkout_fields_trigger_refresh( $fields ) {
   $fields['billing']['billing_company_name']['class'][] = 'update_totals_on_change';
   $fields['billing']['billing_tax_no']['class'][] = 'update_totals_on_change';  
   return $fields;
}

/**
 * Dynamically add CSS classes if user is logged in or out
 **/
add_action('wp_head', 'dyanmicCss');
function dyanmicCss() {
    if (is_user_logged_in()) {
        echo '<style> .hide-when-logged-in { display: none !important; }</style>';
    } else {
        echo '<style> .hide-when-logged-out, #my-account-menu-link { display: none !important; }</style>';
    }
}

/**
 * Increase time limit for Action Scheduler
 **/
function eg_increase_time_limit( $time_limit ) {
	return 60;
}
add_filter( 'action_scheduler_queue_runner_time_limit', 'eg_increase_time_limit' );

/**
 * Increase Concurrent Batches for Action Scheduler
 **/
function eg_increase_action_scheduler_concurrent_batches( $concurrent_batches ) {
	return 2;
}
add_filter( 'action_scheduler_queue_runner_concurrent_batches', 'eg_increase_action_scheduler_concurrent_batches' );

// Lengthen  session time
add_filter('auth_cookie_expiration', 'my_expiration_filter', 99, 3);
function my_expiration_filter($seconds, $user_id, $remember){

    //if "remember me" is checked;
    if ( $remember ) {
        //WP defaults to 2 weeks;
        $expiration = 14*24*60*60; //UPDATE HERE;
    } else {
        //WP defaults to 48 hrs/2 days;
        $expiration = 2*24*60*60; //UPDATE HERE;
    }

    //http://en.wikipedia.org/wiki/Year_2038_problem
    if ( PHP_INT_MAX - time() < $expiration ) {
        //Fix to a little bit earlier!
        $expiration =  PHP_INT_MAX - time() - 5;
    }

    return $expiration;
}


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
