<?php
/**
 * Plugin Name: Backup/Restore Divi Theme Options
 * Description: Backup & Restore your Divi Theme Options.
 * Theme URI: https://github.com/SiteSpace/backup-restore-divi-theme-options
 * Author: Divi Space
 * Author URI: http://divispace.com
 * Version: 1.0.2
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Tags: divi, theme options, theme settings, divi theme options, divi options, divi theme settings, divi settings
 * Text Domain: backup-restore-divi-theme-options
 */


class backup_restore_divi_theme_options {

	/**
	 * add action for admin dashboard wp-admin menu
	 */
	function backup_restore_divi_theme_options() {
		add_action('admin_menu', array(&$this, 'admin_menu'));
	}
	
	/**
	 * function to add menu in admin dashboard wp-admin
	 */
	function admin_menu() {

		$page = add_submenu_page('tools.php', 'Backup/Restore Theme Options', 'Backup/Restore Theme Options', 'manage_options', 'backup-restore-divi-theme-options', array(&$this, 'options_page'));

		add_action("load-{$page}", array(&$this, 'import_export'));

		//add sub menu for backup/restore setting page
		add_submenu_page( 'et_divi_options',__( 'Backup/Restore Theme Options', 'Divi' ), __( 'Backup/Restore Theme Options', 'Divi' ), 'manage_options', 'tools.php?page=backup-restore-divi-theme-options', 'backup-restore-divi-theme-options' );
	}
	
	/**
	 * function to download and upload setting file and save the setting
	 */

	function import_export() {
		//download setting into dat file
		if (isset($_GET['action']) && ($_GET['action'] == 'download')) {
			header("Cache-Control: public, must-revalidate");
			header("Pragma: hack");
			header("Content-Type: text/plain");
			header('Content-Disposition: attachment; filename="divi-theme-options-'.date("dMy").'.dat"');
			echo serialize($this->_get_options());
			die();
		}
		
		//upload file and update theme setting
		if (isset($_POST['upload']) && check_admin_referer('shapeSpace_restoreOptions', 'shapeSpace_restoreOptions')) {
			if ($_FILES["file"]["error"] > 0) {
				// error
			} else {
				$options = unserialize(file_get_contents($_FILES["file"]["tmp_name"]));
				if ($options) {
					foreach ($options as $option) {
						update_option($option->option_name, unserialize($option->option_value));
					}
				}
			}
			wp_redirect(admin_url('tools.php?page=backup-restore-divi-theme-options'));
			exit;
		}
	}
	
	/**
	 * form to backup and restore theme setting
	 */
	function options_page() { ?>

		<div class="wrap">
			<?php screen_icon(); ?>
			<h2>Backup/Restore Theme Options</h2>
			<form action="" method="POST" enctype="multipart/form-data">
				<style>#backup-restore-divi-theme-options td { display: block; margin-bottom: 20px; }</style>
				<table id="backup-restore-divi-theme-options">
					<tr>
						<td>
							<h3>Backup/Export</h3>
							<p>Here are the stored settings for the current theme:</p>
							<p><textarea disabled class="widefat code" rows="20" cols="100" onclick="this.select()"><?php echo serialize($this->_get_options()); ?></textarea></p>
							<p><a href="?page=backup-restore-divi-theme-options&action=download" class="button-secondary">Download as file</a></p>
						</td>
						<td>
							<h3>Restore/Import</h3>
							<p><label class="description" for="upload">Restore a previous backup</label></p>
							<p><input type="file" name="file" /> <input type="submit" name="upload" id="upload" class="button-primary" value="Upload file" /></p>
							<?php if (function_exists('wp_nonce_field')) wp_nonce_field('shapeSpace_restoreOptions', 'shapeSpace_restoreOptions'); ?>
						</td>
					</tr>
				</table>
			</form>
		</div>

	<?php }
	/**
	 * unserialize the setting for et_divi
	 */
	function _display_options() {
		$options = unserialize($this->_get_options());
	}
	
	/**
	 * get setting from database for option et_divi
	 */
	function _get_options() {
		global $wpdb;
		return $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name = 'et_divi'"); // edit 'shapeSpace_options' to match theme options
	}
}
// initialize class
new backup_restore_divi_theme_options();
?>
