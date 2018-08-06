<?php

class KickgogoShortcodes {
	
	private $table_name;
	private $processor;
	private $settings;
	
	public function __construct(KickgogoSettingsPage $settings) {
		global $wpdb;
		$this->settings = $settings;
		$this->table_name = $wpdb->prefix . "_kickgogo_campaigns";
		$this->processor = new KickgogoPelepayProcessor($this->settings->getPelepayAccount());
		add_shortcode('kickgogo', [ $this, 'pay_form' ]);
		add_shortcode('kickgogo-goal', [ $this, 'display_goal' ]);
		add_shortcode('kickgogo-status', [ $this, 'display_status' ]);
		add_shortcode('kickgogo-amount', [ $this, 'display_amount' ]);
		add_shortcode('kickgogo-percent', [ $this, 'display_percent' ]);
		add_shortcode('kickgogo-club-login', [ $this, 'display_club_login' ]);
	}
	
	public function pay_form($atts, $content = null) {
		$atts = shortcode_atts([
			'name' => '',
			'amount' => '',
			'club' => false
		], $atts, 'kickgogo');
		$checkClub = (bool)$atts['club'];
		$content = $content ?: 'Donate';
		
		if (!($campaign = $this->get_campaign($atts['name']))) {
			return "Invalid Kickgogo Campaign '{$atts['name']}'";
		}
		
		$amount = (int)($atts['amount'] ?: $campaign->default_buy);
		if ($amount <= 0)
			return "Invalid purchase amount";
		
		if ($checkClub)
			return $this->getClubForm($content, $amount, $atts['name']);
		
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
		if (is_numeric($name)) {
			$sql = "
				SELECT * FROM $this->table_name
				WHERE id = " . ((int)$name);
		} else {
			$sql = "
				SELECT * FROM $this->table_name
				WHERE name = '" . esc_sql($name) . "'";
		}
		$res = $wpdb->get_results($sql);
		$campaign = current($res);
		if (!$campaign)
			return false;
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
	
	private function getClubForm($buttonText, $amount, $campaign) {
		ob_start();
		$clubPage = $this->settings->getClubLoginPage();
			
		?>
		<form method="post" action="<?php echo $clubPage?>">
		<input type="hidden" name="kickgogo-fund-amount" value="<?php echo $amount?>">
		<input type="hidden" name="kickgogo-campaign" value="<?php echo $campaign?>">
		<button type="submit"><?php echo $buttonText?></button>
		</form>
		<?php
		return ob_get_clean();
	}
	
	public function display_club_login($atts, $content) {
		$atts = shortcode_atts([
		], $atts, 'kickgogo-club-login');
		$content = $content ?: "Submit";
		$amount = @$_POST['kickgogo-fund-amount'] ?: 0;
		$campaignName = @$_POST['kickgogo-campaign'];
		$error = null;
		
		if (isset($_POST['email'])) {
			$email = $_POST['email'];
			$campaign = $this->get_campaign($campaignName);
			if (!$campaign) {
				$error = "Invalid campaign name";
			} else {
				$token = @file_get_contents($this->settings->getClubAPIURL() . "/club/email/$email");
				var_dump($this->settings->getClubAPIURL() . "/club/email/$email");
				if ($token === false) {
					$error = "כתובת מועדון לא חוקית";
				} else {
					return $this->processor->get_form(true, $amount, $campaign->name,
						$campaign->id, $campaign->success_langing_page,
						$campaign->failure_landing_page);
				}
			}
		}
		
		ob_start();
		?>
		<div class="kickgogo-club">
		<?php if ($error):?>
		<h3>שגיאה: <?php echo $error?></h3>
		<?php endif;?>
		<form method="post" action="<?php echo $_SERVER['REQUEST_URI']?>">
		<input type="email" name="email">
		<input type="hidden" name="kickgogo-fund-amount" value="<?php echo $amount?>">
		<input type="hidden" name="kickgogo-campaign" value="<?php echo $campaignName?>">
		<button type="submit"><?php echo $content?></button>
		</form>
		</div>
		<?php
		return ob_get_clean();
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
