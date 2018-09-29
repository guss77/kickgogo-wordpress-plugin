<?php

global $kickgogo_db_version;
$kickgogo_db_version = '5';

class KickgogoAdmin {
	
	private $settings;
	
	public function __construct(KickgogoSettingsPage $settings) {
		$this->settings = $settings;
	}

	function kickgogo_install() {
		global $kickgogo_db_version;
		$this->kickgogo_create_table();
		add_option( 'kickgogo_db_version', $kickgogo_db_version );
	}
	
	function kickgogo_create_table() {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	
		$table_name = $this->settings->getCampaignTable();
		$charset_collate = $wpdb->get_charset_collate();
		
		$sql = "CREATE TABLE $table_name (
			id int NOT NULL AUTO_INCREMENT,
			active tinyint(1) NOT NULL DEFAULT 1,
			name varchar(50) NOT NULL,
			goal decimal(12,2) NOT NULL,
			current decimal(12,2) NOT NULL DEFAULT 0,
			default_buy decimal(12,2) DEFAULT NULL,
			success_langing_page varchar(255) DEFAULT NULL,
			failure_landing_page varchar(255) DEFAULT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";
		
		dbDelta( $sql );
	
		$table_name = $this->settings->getTransactionsTable();
		$sql = "CREATE TABLE $table_name (
			id int NOT NULL AUTO_INCREMENT,
			campaign_id int NOT NULL,
			amount decimal(12,2) NOT NULL,
			details TEXT DEFAULT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";
		
		error_log("debug: updating DB");
		dbDelta( $sql );
	}
	
	function kickgogo_update_db() {
		global $kickgogo_db_version;
		$version = get_option( 'kickgogo_db_version', $kickgogo_db_version );
		if (version_compare($version, $kickgogo_db_version) < 0) {
			$this->kickgogo_create_table();
			update_option( 'kickgogo_db_version', $kickgogo_db_version );
		}
	}
	
	function kickgogo_custom_wp_admin_style($hook) {
		// Load only on ?page=mypluginname
		if($hook != 'toplevel_page_kickgogo') {
			return;
		}
		wp_enqueue_style( 'kickgogo_wp_admin_css', plugins_url('admin-style.css', __FILE__) );
		wp_enqueue_style( 'kickgogo_wp_admin_fa', 'https://use.fontawesome.com/releases/v5.0.10/css/all.css' );
	}

}
