<?php

class KickgogoSettingsPage {
	
	private $pelepay_account;
	private $errors = [];
	private $table_name;
	
	const KICK_ICON = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTguMS4xLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIKCSB2aWV3Qm94PSIwIDAgMTguNzQgMTguNzQiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDE4Ljc0IDE4Ljc0OyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+CjxnPgoJPGc+CgkJPHBhdGggc3R5bGU9ImZpbGw6IzAzMDEwNDsiIGQ9Ik0xNC44OTgsMTAuMDMxbDAuMDE0LTAuMDA0YzAsMC0wLjI2NS0xLjg0Ny0xLjg0NS0xLjc0NmMwLDAtMC45NjMsMC4xODQtMi4yOTEsMC4yMDF2MS40NDRIOS45OTgKCQkJVjguNDcyQzkuNzY0LDguNDYzLDkuNTIyLDguNDQ3LDkuMjc2LDguNDI0djEuMzA4SDguNDk4VjguMzI1Yy0wLjIzLTAuMDM3LTAuNDYyLTAuMDgzLTAuNjk0LTAuMTM3VjkuMzdINy4wMjdWNy45NjkKCQkJQzYuNjYzLDcuODQ4LDYuMzA2LDcuNzAyLDUuOTY1LDcuNTI0Yy0wLjIzOC0wLjEyNS0xLjQ5OC0zLjYxLTEuNzE4LTMuNzY2TDAsNS4wOThsMC42MiwzLjgxNQoJCQljLTEuMjcxLDEuNTM5LDEuMjcsNC45OTIsMS4yNyw0Ljk5MkwxLjkwMywxMy45Yy0wLjAwMywwLjAxLTAuMDA5LDAuMDIxLTAuMDA2LDAuMDNsMC4zMiwxLjAwMgoJCQljMC4wMTgsMC4wNTgsMC4xNTksMC4wNjYsMC4zMTIsMC4wMmwyLjU2Ny0wLjc4OWMwLDAtMC41ODYtMS40OTUsMC43MDEtMS44OTJjMC4zMjgtMC4xMDgsMC42NzItMC4wMDIsMC42NzItMC4wMDIKCQkJYzAuOTQ4LDAuMjIyLDIuMTA0LDAuODI0LDIuMTA0LDAuODI0bDYuMzczLTEuOTZjMC4xNTItMC4wNDYsMC4yNjQtMC4xMzMsMC4yNDYtMC4xOTFsLTAuMjc0LTAuODkKCQkJQzE0LjkxNiwxMC4wNDQsMTQuOTA2LDEwLjAzOCwxNC44OTgsMTAuMDMxeiIvPgoJCTxwYXRoIHN0eWxlPSJmaWxsOiMwMzAxMDQ7IiBkPSJNMTcuNDc5LDguNzI2bDEuMjYxLTAuMzg4TDE3LjQ0Niw4LjA4Yy0wLjg2OC0wLjE3My0xLjQ1My0wLjUyNy0xLjczOC0xLjA1MQoJCQljLTAuNDI3LTAuNzgzLTAuMDYzLTEuNzI4LTAuMDYtMS43MzZsMC4zODYtMC45NmwtMC44NzMsMC41NTRjLTAuNjY5LDAuNDI0LTEuMjg4LDAuNTU0LTEuODQsMC4zODUKCQkJYy0wLjk2OS0wLjI5Ni0xLjQ2Ny0xLjQxNS0xLjQ3Mi0xLjQyN0wxMS4yMzgsNC4xMWMwLjAyNCwwLjA1OCwwLjYxOSwxLjQwOCwxLjg4MywxLjc5OGMwLjU0NywwLjE2OSwxLjEyOSwwLjEzMSwxLjczMi0wLjExMgoJCQljLTAuMDQ5LDAuNDM4LTAuMDI1LDEuMDA4LDAuMjY2LDEuNTQ3YzAuMjc3LDAuNTEzLDAuNzQsMC44OTksMS4zODIsMS4xNTVjLTAuMzA1LDAuMjI1LTAuNjE3LDAuNTU2LTAuNzcxLDEuMDIKCQkJYy0wLjI1LDAuNzUtMC4wMTYsMS42MiwwLjY5MywyLjU4OWwwLjUzNy0wLjM5NGMtMC41NzItMC43ODMtMC43NzQtMS40NDktMC42LTEuOTgxQzE2LjU5Nyw5LjAxMSwxNy40NzEsOC43MjgsMTcuNDc5LDguNzI2eiIvPgoJPC9nPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+Cjwvc3ZnPgo=';
	
	/**
	 * Start up
	 */
	public function __construct()
	{
		global $wpdb;
		$this->table_name = $wpdb->prefix . "_kickgogo_campaigns";
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}
	
	
	/**
	 * Add options page
	 */
	public function add_plugin_page()
	{
		// This page will be under "Settings"
		add_options_page(
			'Kickgogo Settings',
			'Kickgogo',
			'manage_options',
			'kickgogo-admin',
			[ $this, 'create_admin_page' ]
			);
		
		add_menu_page( 'Kickgogo', 'Kickgogo', 'manage_options',
			'kickgogo', [$this, 'management_page' ], static::KICK_ICON);
		
// 		add_submenu_page(
// 			'Kickgogo Campaigns',
// 			'Kickgogo',
// 			'manage_options',
// 			'kickgogo-campaigns',
// 			[ $this, 'campaign_builder' ]
// 			);
	}
	
	
	/**
	 * Register and add settings
	 */
	public function page_init()
	{
		register_setting(
			'kickgogo_setting_group', // Option group
			'kickgogo-pelepay-account' // Option name
			);
		
		add_settings_section(
			'kickgogo_section_global', // ID
			'Global Settings', // Title
			array( $this, 'print_section_info_api' ), // Callback
			'kickgogo-settings' // Page
			);
		
		add_settings_field(
			'pelepay-account', // ID
			'Pelepay Account', // Title
			array( $this, 'pelepay_account_callback' ), // Callback
			'kickgogo-settings', // Page
			'kickgogo_section_global' // Section
			);
	}
	
	/**
	 * Options page callback
	 */
	public function create_admin_page()
	{
		// Set class property
		$this->pelepay_account = get_option('kickgogo-pelepay-account');
		?>
        <div class="wrap">
            <h2>Kickgogo Settings</h2>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields('kickgogo_setting_group');
                do_settings_sections('kickgogo-settings');
                submit_button();
            ?>
            </form>
        </div>
		<?php
	}
	
	
	/**
	 * Print the Section text
	 */
	public function print_section_info_api()
	{
		print 'Setup global options that apply to all campaigns';
	}
	
	/**
	 * Print the pelepay account setting field
	 */
	public function pelepay_account_callback() {
		printf(
			'<input type="text" id="pelepay-account" name="kickgogo-pelepay-account" value="%s" style="width: 20em;"/>',
			isset( $this->pelepay_account ) ? esc_attr($this->pelepay_account) : ''
			);
	}
	
	public function management_page() {
		global $wpdb;
		//must check that the user has the required capability
		if (!current_user_can('manage_options'))
			wp_die( __('You do not have sufficient permissions to access this page.') );
		
			switch ($_POST['kickgogo-action']) {
				case 'new':
					$this->handle_create($_POST);
					break;
				case 'delete':
					$this->handle_delete($_POST['id']);
					break;
				case 'enable':
					$this->handle_enable($_POST['id']);
					break;
				case 'disable':
					$this->handle_disable($_POST['id']);
					break;
			}
					
		?>
		<h1>Campaigns</h1>
		<div>
		<table class="widefat">
		<thead>
			<tr><th>#</th><th>Name</th><th>Goal</th><th>Defalt</th><th></th><th></th></tr>
		</thead>
		<tbody>
		<?php
		foreach ($wpdb->get_results("SELECT * FROM $this->table_name") as $row) {
			?>
			<tr>
			<td><?php echo $row->id ?></td>
			<td><?php echo $row->name ?></td>
			<td dir="ltr">
				<?php echo (int)$row->current ?>
				of <?php echo (int)$row->goal ?>
				(<?php echo (int)(100 * $row->current / $row->goal) ?>%)
			</td>
			<td><?php echo $row->default_buy?></td>
			<td>
				<?php if (!$row->active):?>
					<strong>inactive</strong>
				<?php endif;?>
			</td>
			<td>
				<form method="post" action="">
				<input type="hidden" name="id" value="<?php echo $row->id?>">
				<?php if ($row->active):?>
					<button title="Disable <?php echo $row->name?>" type="submit"
						name="kickgogo-action" value="disable">
						<i class="fas fa-toggle-off"></i>
					</button>
				<?php else:?>
					<button title="Enable <?php echo $row->name?>" type="submit"
						name="kickgogo-action" value="enable">
						<i class="fas fa-toggle-on"></i>
					</button>
					<button title="Delete <?php echo $row->name?>" type="submit" onclick="return confirm('Are you sure you want to completely remove this campaign? This is not recoverable.')"
						name="kickgogo-action" value="delete">
						<i class="fas fa-minus-circle"></i>
					</button>
				<?php endif;?>
				</form>
			</td>
			</tr>
			<?php
		}
		?>
		</tbody>
		</table>
		</div>
		<div>
		<h3>Create a new campaign</h3>
		<?php if ($this->has_errors()): ?>
			<div class="error fade">
			<p><strong>Can't create campaign:</strong></p>
			<?php foreach ($this->errors as $error):?>
			<p><?php echo $error;?></p>
			<?php endforeach;?>
			</div>
		<?php endif; ?>
		<form method="post" action="">
		<input type="hidden" name="kickgogo-action" value="new">
		<p>
		<label for="kickgogo-campaign-name">Name:</label>
		</p>
		<p>
		<input type="text" id="kickgogo-campaign-name" name="kickgogo-campaign-name">
		</p>
		<p>
		<label for="kickgogo-campaign-goal">Goal:</label>
		</p>
		<p>
		<input type="number" id="kickgogo-campaign-goal" name="kickgogo-campaign-goal" min="0">
		</p>
		<p>
		<label for="kickgogo-campaign-goal">Default purchase amount:</label>
		</p>
		<p>
		<input type="number" id="kickgogo-campaign-defbuy" name="kickgogo-campaign-defbuy" min="0">
		</p>
		<p>
		(Optional, if not set the amount has to be specified for every Kickgogo button)
		</p>
		<p>
		<label for="kickgogo-campaign-ok">Success langing page:</label>
		</p>
		<p>
		<input type="text" id="kickgogo-campaign-ok" name="kickgogo-campaign-ok">
		</p>
		<p>
		(leave empty to return the user to the page with the Kickgogo form)
		</p>
		<p>
		<label for="kickgogo-campaign-err">Error langing page:</label>
		</p>
		<p>
		<input type="text" id="kickgogo-campaign-err" name="kickgogo-campaign-err">
		</p>
		<p>
		(leave empty to return the user to the success page in case of an error)
		</p>
		<p>
		<button type="submit">Create Campaign</button>
		</p>
		</form>
		</div>
		<?php
	}
	
	public function handle_create($data) {
		global $wpdb;
		
		if (empty($name = $data['kickgogo-campaign-name']))
			$this->report_error('Missing campaign name');
		if (!is_numeric($data['kickgogo-campaign-goal'])) {
			$this->report_error('Missing campaign goal');
		} else {
			$goal = (int)$data['kickgogo-campaign-goal'];
			if ($goal <= 0)
				$this->report_error('Campaign goal must be larger than zero');
		}
		$defbuy = (int)$data['kickgogo-campaign-defbuy'];
		$land_ok = $data['kickgogo-campaign-ok'];
		$land_err = $data['kickgogo-campaign-err'];
		
		if ($this->has_errors())
			return;
		
		$wpdb->insert($this->table_name, [
			'name' => $name,
			'goal' => $goal,
			'default_buy' => $defbuy,
			'success_langing_page' => $land_ok,
			'failure_landing_page' => $land_err,
		]);
	}
	
	public function handle_delete($id) {
		global $wpdb;
		$wpdb->delete($this->table_name, [ 'id' => $id ]);
	}
	
	public function handle_enable($id) {
		global $wpdb;
		$wpdb->update($this->table_name, [ 'active' => 1 ], [ 'id' => $id ]);
	}
	
	public function handle_disable($id) {
		global $wpdb;
		$wpdb->update($this->table_name, [ 'active' => 0 ], [ 'id' => $id ]);
	}
	
	public function report_error($error) {
		$this->errors[] = $error;
	}
	
	public function has_errors() {
		return !empty($this->errors);
	}
}

if(is_admin())
	$kickgogo_setting = new KickgogoSettingsPage();
