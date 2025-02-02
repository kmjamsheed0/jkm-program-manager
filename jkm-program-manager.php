<?php
/**
 * Plugin Name: Radio Program Manager
 * Description: A plugin to manage radio programs, list and import data via CSV.
 * Author:      Jamsheed KM
 * Version:     1.0.0
 * Author URI:  https://github.com/kmjamsheed0
 * Plugin URI:  https://github.com/kmjamsheed0/jkm-program-manager
 * Text Domain: jkm-program-manager
 * Domain Path: /languages
 * License:		GPL-2.0-or-later
 * License URI:	https://www.gnu.org/licenses/gpl-2.0.html
 */

if(!defined('ABSPATH')){ exit; }


if(!class_exists('JKM_Program_Manager')){
	class JKM_Program_Manager {
		public function __construct(){
			add_action('plugins_loaded', array($this, 'init'));
		}

		public function init() {
			define('JKMPM_VERSION', '1.0.0');
			!defined('JKMPM_BASE_NAME') && define('JKMPM_BASE_NAME', plugin_basename( __FILE__ ));
			!defined('JKMPM_PATH') && define('JKMPM_PATH', plugin_dir_path( __FILE__ ));
			!defined('JKMPM_URL') && define('JKMPM_URL', plugins_url( '/', __FILE__ ));

			require_once( JKMPM_PATH . 'includes/class-jkmpm.php' );
			JKMPM::instance();
		}
	}
}

new JKM_Program_Manager();
