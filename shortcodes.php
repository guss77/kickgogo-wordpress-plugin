<?php

class KickgogoShortcodes {
	
	private $table_name;
	private $pelepay_account;
	private $processor;
	
	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . "_kickgogo_campaigns";
		$this->pelepay_account = get_option('kickgogo-pelepay-account');
		$this->processor = new KickgogoPelepayProcessor($this->pelepay_account);
		add_shortcode('kickgogo', [ $this, 'pay_form' ]);
		add_shortcode('kickgogo-goal', [ $this, 'display_goal' ]);
		add_shortcode('kickgogo-status', [ $this, 'display_status' ]);
		add_shortcode('kickgogo-amount', [ $this, 'display_amount' ]);
		add_shortcode('kickgogo-percent', [ $this, 'display_percent' ]);
	}
	
	public function pay_form($atts, $content = null) {
		$atts = shortcode_atts([
			'name' => '',
			'amount' => '',
		], $atts, 'kickgogo');
		
		$content = $content ?: 'Donate';
		
		if (!($campaign = $this->get_campaign($atts['name']))) {
			return "Invalid Kickgogo Campaign '{$atts['name']}'";
		}
		
		$amount = (int)($atts['amount'] ?: $campaign->default_buy);
		if ($amount <= 0)
			return "Invalid purchase amount";
		
		return $this->processor->get_form($content, $amount, $campaign->name,
			$campaign->id, $campaign->success_langing_page,
			$campaign->failure_landing_page);
	}
	
	public function display_goal($atts, $content = null) {
		$atts = shortcode_atts([ 'name' => '' ], $atts, 'kickgogo-goal');
		if (!($campaign = $this->get_campaign($atts['name']))) {
			return "Invalid Kickgogo Campaign '{$atts['name']}'";
		}
		return $campaign->goal;
	}
	
	public function display_amount($atts, $content = null) {
		$atts = shortcode_atts([ 'name' => '' ], $atts, 'kickgogo-amount');
		if (!($campaign = $this->get_campaign($atts['name']))) {
			return "Invalid Kickgogo Campaign '{$atts['name']}'";
		}
		return $campaign->current;
	}
	
	public function display_percent($atts, $content = null) {
		$atts = shortcode_atts([ 'name' => '' ], $atts, 'kickgogo-percent');
		if (!($campaign = $this->get_campaign($atts['name']))) {
			return "Invalid Kickgogo Campaign '{$atts['name']}'";
		}
		return (int)(100 * $campaign->current / $campaign->goal);
	}
	
	public function display_status($atts, $content = null) {
		$atts = shortcode_atts([ 'name' => '' ], $atts, 'kickgogo-status');
		if (!($campaign = $this->get_campaign($atts['name']))) {
			return "Invalid Kickgogo Campaign '{$atts['name']}'";
		}
		return sprintf($content ?: "%d of %d (%d%%)",
			$campaign->current, $campaign->goal,
			(int)(100 * $campaign->current / $campaign->goal));
	}
	
	private function get_campaign($name) {
		global $wpdb;
		$res = $wpdb->get_results("
			SELECT * FROM $this->table_name
			WHERE name = '" . esc_sql($name) . "'"
			);
		$campaign = current($res);
		$self = home_url(add_query_arg([]));
		$campaign->success_langing_page = home_url('/kickgogo-handler/') . base64_encode(
			$campaign->success_langing_page ?: $self);
		$campaign->failure_landing_page = home_url('/kickgogo-handler/') . base64_encode(
			$campaign->failure_landing_page ?: $campaign->success_langing_page);
		return $campaign;
	}
	
	private function update_campaign($id, $amount) {
		global $wpdb;
		$wpdb->query($wpdb->prepare(
			"UPDATE $this->table_name
			SET current = current + %s
			WHERE id = %d",
			$amount, $id));
	}
	
	public function handle_callbacks($query) {
		if (strpos($_SERVER['REQUEST_URI'], "/kickgogo-handler/") !== 0)
			return;
		
		list($path, $query) = explode("?", $_SERVER['REQUEST_URI']);
		list($nop, $handler, $code) = explode("/",$path);
		$orig = base64_decode($code);
		
		$result = $this->processor->parse(wp_parse_args($query));
		if ($result) {
			$this->update_campaign($result['campaign'], $result['amount']);
			$orig = add_query_arg(['kickgogo' => 'success'], $orig);
		} else {
			$orig = add_query_arg(['kickgogo' => 'failure'], $orig);
		}
		
		header('Location: ' . $orig);
		exit();
	}
	
}

if (!is_admin())
	$kickgogo_ref = new KickgogoShortcodes();