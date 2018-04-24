<?php 


defined( 'ABSPATH' ) or die( 'Blank Space' );

final class Emc_Shortcode {
	/* SINGLETON */
	private static $instance = null;
	private $css_added = false;
	private $desktop = EMCASINO_PLUGIN_URL.'assets/css/emcasino.css?v=0.0.1';
	private $mobile = EMCASINO_PLUGIN_URL.'assets/css/emcasino-mobile.css?v=0.0.1';

	public static function get_instance() {
		if (self::$instance === null) self::$instance = new self();

		return self::$instance;
	}

	private function __construct() {
		$this->wp_hooks();
	}	

	private function wp_hooks() {
		$tag = 'casino';

		if (shortcode_exists($tag)) $tag = 'emcasino';

		add_shortcode($tag, array($this, 'shortcode'));
		add_shortcode($tag.'-image', array($this, 'shortcode_image'));
		add_shortcode($tag.'-signup', array($this, 'shortcode_signup'));

        add_filter('pre_get_posts', array($this, 'set_search'), 99);

	}

	private function add_css() {
		if ($this->css_added) return;
		$this->css_added = true;

		add_action('wp_footer', array($this, 'add_footer'));
	}

	public function add_footer() {
		echo '<script defer>
				(function() {
					var o = document.createElement("link");
					o.setAttribute("rel", "stylesheet");
					o.setAttribute("href", "'.esc_html($this->desktop).'");
					o.setAttribute("media", "(min-width: 1025px)");
					document.head.appendChild(o);

					var m = document.createElement("link");
					m.setAttribute("rel", "stylesheet");
					m.setAttribute("href", "'.esc_html($this->mobile).'");
					m.setAttribute("media", "(max-width: 1024px)");
					document.head.appendChild(m);

				})();
			  </script>';
	}

	public function set_search($query) {
        if ($query->is_search) {
	        if (!$query->get('post_type')) $query->set('post_type', array('page', 'post', 'emcasino'));
	        else $query->set('post_type', array_merge(array('emcasino'), $query->get('post_type')));
		}
	}

	public function shortcode($atts, $content = null) {

		$args = [
			'post_type' 		=> 'emcasino',
			'posts_per_page' 	=> -1,
			'orderby' 			=> [
										'meta_value_num' 	=> 'ASC',
										'title' 			=> 'ASC'
								   ],
			'meta_key' 			=> 'emcasino_sort'
		];

		// adds slug name(s) to search 
		if (isset($atts['name'])) $args['post_name__in'] = explode(',', preg_replace('/ /', '', $atts['name']));


		$posts = get_posts($args);

		// if no posts found, then return nothing
		if (sizeof($posts) == 0) return;

		// adding css
		$this->add_css();

		// making html
		$html = '<div class="emcasino-list">';

		// iterating posts
		foreach ($posts as $post)
			// getting html for each casino-item
			$html .= $this->make_casino($post);

		$html .= '</div>';

		// returns to front-end
		return $html;
	}

	/**
		CREATES AND RETURNS ONE ITEM IN THE CASINO LIST
	*/
	private function make_casino($post) {

		return 'heya';
	}

	public function shortcode_image($atts, $content = null) {
		if (!isset($atts['name'])) return;
		$this->add_css();

		$post = $this->get_post($atts['name']);


		$url = get_the_post_thumbnail_url($post, 'full');

		if (!$url) return;

		return '<div class="emcasino-bilde-container-alene"><img class="emcasino-bilde-alene" src="'.esc_url($url).'"></div>';
	}

	public function shortcode_signup($atts, $content = null) {
		if (!isset($atts['name'])) return;
		$this->add_css();

		$post = $this->get_post($atts['name']);
		$meta = get_post_meta($post->ID, 'emcasino');

		if (!isset($meta[0]) || !isset($meta[0]['signup'])) return;

		return '<div class="emcasino-signup-alene"><a class="emcasino-signup-lenke emcasino-signup-lenke-alene" href="'.esc_url($meta[0]['signup']).'">Meld deg på</a></div>';
	}

	private function get_post($name) {
		$args = [
			'name' => sanitize_text_field($name),
			'post_type' => 'emcasino',
			'posts_per_page' => 1
		];

		$post = get_posts($args);

		if (isset($post[0])) return $post[0];

		return null;
	}
}