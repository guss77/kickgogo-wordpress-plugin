<?php

class KickgogoShortcodes {
	
	private $processor;
	private $settings;
	
	public function __construct(KickgogoSettingsPage $settings) {
		global $wpdb;
		$this->settings = $settings;
		$this->processor = new KickgogoPelepayProcessor($this->settings->getPelepayAccount());
		add_shortcode('kickgogo', [ $this, 'pay_form' ]);
		add_shortcode('kickgogo-goal', [ $this, 'display_goal' ]);
		add_shortcode('kickgogo-status', [ $this, 'display_status' ]);
		add_shortcode('kickgogo-amount', [ $this, 'display_amount' ]);
		add_shortcode('kickgogo-percent', [ $this, 'display_percent' ]);
		add_shortcode('kickgogo-progress', [ $this, 'display_progress' ]);
		add_shortcode('kickgogo-payments', [ $this, 'display_payments' ]);
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
		
		if (!($campaign = $this->settings->getCampaign($atts['name']))) {
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
		if (!($campaign = $this->settings->getCampaign($atts['name']))) {
			return "Invalid Kickgogo Campaign '{$atts['name']}'";
		}
		return $campaign->goal;
	}
	
	public function display_amount($atts, $content = null) {
		$atts = shortcode_atts([ 'name' => '' ], $atts, 'kickgogo-amount');
		if (!($campaign = $this->settings->getCampaign($atts['name']))) {
			return "Invalid Kickgogo Campaign '{$atts['name']}'";
		}
		return $campaign->current;
	}
	
	public function display_percent($atts, $content = null) {
		$atts = shortcode_atts([ 'name' => '' ], $atts, 'kickgogo-percent');
		if (!($campaign = $this->settings->getCampaign($atts['name']))) {
			return "Invalid Kickgogo Campaign '{$atts['name']}'";
		}
		return min(100, (int)(100 * $campaign->current / $campaign->goal));
	}
	
	public function display_progress($atts, $content = null) {
		$atts = shortcode_atts([ 'name' => '' ], $atts, 'kickgogo-percent');
		if (!($campaign = $this->settings->getCampaign($atts['name']))) {
			return "Invalid Kickgogo Campaign '{$atts['name']}'";
		}
		$percent = min(100, (int)(100 * $campaign->current / $campaign->goal));
		if ($percent > 0)
			$width = "{$percent}%";
			else
				$width = "3px";
				
				$hidein = $percent < 70 ? 'style="display: none;"' : '';
				$hideout = $percent >= 70 ? 'style="display: none;"' : '';
				
				ob_start();
				?><div class="kickgogo-progress-container"><?php
			?><div class="kickgogo-progress-bar" style="width: <?php echo $width?>"><?php
				?><div class="kickgogo-percent-progress-in" <?php echo $hidein?>><?php echo $percent?>%</div><?php
			?></div><?php
			?><div class="kickgogo-progress-buffer"><?php
				?><div class="kickgogo-percent-progress-out" <?php echo $hideout?>><?php echo $percent?>%</div><?php
			?></div><?php
		?></div><?php
		return ob_get_clean();
	}
	
	public function display_payments($atts, $content = null) {
		$atts = shortcode_atts([ 'name' => '' ], $atts, 'kickgogo-percent');
		if (!($campaign = $this->settings->getCampaign($atts['name']))) {
			return "Invalid Kickgogo Campaign '{$atts['name']}'";
		}
		return $this->settings->getTransactionCount($atts['name']);
	}
	
	public function display_status($atts, $content = null) {
		$atts = shortcode_atts([ 'name' => '' ], $atts, 'kickgogo-status');
		if (!($campaign = $this->settings->getCampaign($atts['name']))) {
			return "Invalid Kickgogo Campaign '{$atts['name']}'";
		}
		return sprintf($content ?: "%d of %d (%d%%)",
			$campaign->current, $campaign->goal,
			(int)(100 * $campaign->current / $campaign->goal));
	}
	
	private function update_campaign($id, $amount) {
		$fund = (int)$amount;
		if ($fund <= 0) {
			return false;
		}
		global $wpdb;
		$campaigns = $this->settings->getCampaignTable();
		$wpdb->query($wpdb->prepare(
			"UPDATE $campaigns
			SET current = current + %d
			WHERE id = %d",
			$fund, $id));
		return true;
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
			$campaign = $this->settings->getCampaign($campaignName);
			if (!$campaign) {
				$error = "Invalid campaign name";
			} else {
				$token = @file_get_contents($this->settings->getClubAPIURL() . "/club/email/$email");
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
		if ($result and $this->update_campaign($result['campaign'], $result['amount'])) {
			$orig = add_query_arg(['kickgogo' => 'success'], $orig);
		} else {
			$orig = add_query_arg(['kickgogo' => 'failure'], $orig);
		}
		
		header('Location: ' . $orig);
		exit();
	}
	
}
