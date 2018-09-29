<?php

class KickgogoShortcodes {
	
	private $processor;
	private $settings;
	
	public function __construct(KickgogoSettingsPage $settings) {
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
			return __("Invalid Kickgogo Campaign", 'kickgogo'). " {$atts['name']}'";
		}
		
		$amount = (int)($atts['amount'] ?: $campaign->default_buy);
		if ($amount <= 0)
			return __("Invalid purchase amount", 'kickgogo');
		
		if ($checkClub)
			return $this->getClubForm($content, $amount, $atts['name']);
		
		return $this->processor->get_form($content, $amount, $campaign->name,
			$campaign->id, $campaign->success_langing_page,
			$campaign->failure_landing_page);
	}
	
	public function display_goal($atts, $content = null) {
		$atts = shortcode_atts([ 'name' => '' ], $atts, 'kickgogo-goal');
		if (!($campaign = $this->settings->getCampaign($atts['name']))) {
			return __("Invalid Kickgogo Campaign", 'kickgogo'). " '{$atts['name']}'";
		}
		return $campaign->goal;
	}
	
	public function display_amount($atts, $content = null) {
		$atts = shortcode_atts([ 'name' => '' ], $atts, 'kickgogo-amount');
		if (!($campaign = $this->settings->getCampaign($atts['name']))) {
			return __("Invalid Kickgogo Campaign", 'kickgogo') . " '{$atts['name']}'";
		}
		return $campaign->current;
	}
	
	public function display_percent($atts, $content = null) {
		$atts = shortcode_atts([ 'name' => '' ], $atts, 'kickgogo-percent');
		if (!($campaign = $this->settings->getCampaign($atts['name']))) {
			return __("Invalid Kickgogo Campaign", 'kickgogo') . " '{$atts['name']}'";
		}
		return min(100, ceil(100 * $campaign->current / $campaign->goal));
	}
	
	public function display_progress($atts, $content = null) {
		$atts = shortcode_atts([ 'name' => '' ], $atts, 'kickgogo-percent');
		if (!($campaign = $this->settings->getCampaign($atts['name']))) {
			return __("Invalid Kickgogo Campaign", 'kickgogo') . " '{$atts['name']}'";
		}
		$percent = min(100, ceil(100 * $campaign->current / $campaign->goal));
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
		if (!($this->settings->getCampaign($atts['name']))) {
			return __("Invalid Kickgogo Campaign", 'kickgogo') . " '{$atts['name']}'";
		}
		return $this->settings->getTransactionCount($atts['name']);
	}
	
	public function display_status($atts, $content = null) {
		$atts = shortcode_atts([ 'name' => '' ], $atts, 'kickgogo-status');
		if (!($campaign = $this->settings->getCampaign($atts['name']))) {
			return __("Invalid Kickgogo Campaign", 'kickgogo') . " '{$atts['name']}'";
		}
		return sprintf($content ?: "%d of %d (%d%%)",
			$campaign->current, $campaign->goal,
			ceil(100 * $campaign->current / $campaign->goal));
	}
	
	private function update_campaign($id, $amount, $details, $test = false) {
		$fund = (int)$amount;
		if ($fund <= 0) {
			return false;
		}
		global $wpdb;
		
		$transactions = $this->settings->getTransactionsTable();
		$wpdb->query($wpdb->prepare(
			"INSERT INTO $transactions (campaign_id, amount, details, test) VALUES (%d, %d, %s, %d)",
			$id, $fund, $details, $test ? 1 : 0));
		
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
			try {
				return $this->sendClubPayment($campaignName, $_POST['email'], $amount);
			} catch (Exception $e) {
				$error = $e->getMessage();
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
	
	private function sendClubPayment($campaignName, $email, $amount) {
		$campaign = $this->settings->getCampaign($campaignName);
		if (!$campaign) {
			throw new Exception(__("Invalid Kickgogo Campaign", 'kickgogo'));
		}
		
		$token = @file_get_contents($this->settings->getClubAPIURL() . "/club/email/$email");
		if ($token === false) {
			throw new Exception(__("Invalid club address", 'kickgogo'));
		}
		
		$resp = json_decode($token);
		if (!$resp->status) {
			throw new Exception(__("Invalid club membership", 'kickgogo'));
		}
		
		foreach ($this->settings->getTransactions($campaignName) as $t) {
			if (trim(@$t->details->email) == trim($email) and @$t->details->data->club) {
				$validate = @file_get_contents($this->settings->getClubAPIURL() . "/club/token/" . $t->details->data->club);
				if (!$validate) continue;
				$validate = json_decode($validate);
				if (trim($validate->email) == trim($t->details->email))
					throw new Exception(__("Club membership may only be used once", 'kickgogo'));
			}
		}
		
		return $this->processor->get_form(true, $amount, $campaign->name,
			$campaign->id, $campaign->success_langing_page,
			$campaign->failure_landing_page, [ "club" => $resp->token ]);
	}
	
	public function handle_callbacks($query) {
		if (strpos($_SERVER['REQUEST_URI'], "/kickgogo-handler/") !== 0)
			return;
		
		list($path, $query) = explode("?", $_SERVER['REQUEST_URI']);
		list($nop, $handler, $code) = explode("/",$path);
		$orig = base64_decode($code);
		
		$result = $this->processor->parse(wp_parse_args($query));
		if ($result and $this->update_campaign($result['campaign'], $result['amount'], json_encode($result), $result['test'])) {
			$orig = add_query_arg(['kickgogo' => 'success'], $orig);
		} else {
			$orig = add_query_arg(['kickgogo' => 'failure'], $orig);
		}
		
		header('Location: ' . $orig);
		exit();
	}
	
}
