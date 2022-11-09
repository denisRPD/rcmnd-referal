<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://recommend.co
 * @since      1.1
 *
 * @package    Rcmnd_referral
 * @subpackage Rcmnd_referral/admin/partials
 */
?>

<div class="wrap">
	<div style="display:inline-block;width: 100%;max-width: 500px;">
		<div style="width: 5%;float: left;">	
			<img style="margin: 1.4em 0;" src="<?php echo plugin_dir_url(dirname( __DIR__ )) . 'images/rcmnd-logo.ico'; ?>">
		</div>
		<div style="width: 90%;float: left;">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
		</div>
	</div>
	<?php
		$active_tab = sanitize_text_field(isset( $_GET[ "tab"] ) ? $_GET["tab"] : "general");
		$active_general= ($active_tab=='general' ? 'nav-tab-active':'');
		$active_advanced= ($active_tab=='advanced' ? 'nav-tab-active':'');
	?>
	<h2 class="nav-tab-wrapper">
		<a href="?page=rcmnd-referal&tab=general" class="nav-tab '. $active_general . '"><?php echo esc_html( __( 'General', 'recommend-referral-integration' )) ?></a>
		<a href="?page=rcmnd-referal&tab=advanced" class="nav-tab '. $active_advanced . '"><?php echo esc_html( __( 'Advanced', 'recommend-referral-integration' )) ?></a>
	</h2>
	
	<?php
		if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ){
			$this->rcmnd_check_connection();	
		} 
	?>
    <form action="options.php" method="post">
		<?php 
		if( $active_tab == "general" ) {
			settings_fields( 'rcmnd_gso_group' );
			do_settings_sections( 'rcmnd_gso' );
		} 
		if( $active_tab == "advanced" ){
			settings_fields( 'rcmnd_aso_group' );
			do_settings_sections( 'rcmnd_aso' );
		}
        submit_button(__( 'Save changes', 'recommend-referral-integration' ), 'primary');
        ?>
    </form>
</div>
<?php



