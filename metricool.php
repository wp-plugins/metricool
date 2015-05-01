<?php
/**
* Plugin Name: Metricool
* Plugin URI: http://www.metricool.com/
* Version: 1.0
* Author: Metricool
* Author URI: http://www.metricool.com/
* Description: Allows you to track your users and readers using metricool.com
* License: GPL2
*/

/*  Copyright 2015 Metricool

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class metricool {
	/**
	* Constructor
	*/
	public function __construct() {
		// Plugin Details
        $this->plugin = new stdClass;
        $this->plugin->name = 'metricool'; // Plugin Folder
        $this->plugin->displayName = 'Metricool'; // Plugin Name
        $this->plugin->version = '1.0';
        $this->plugin->folder = WP_PLUGIN_DIR.'/'.$this->plugin->name; // Full Path to Plugin Folder
        $this->plugin->url = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
        		
		// Hooks
		add_action('admin_init', array(&$this, 'registerSettings'));
        add_action('admin_menu', array(&$this, 'adminPanelsAndMetaBoxes'));
        
        // Frontend Hooks
		add_action('wp_footer', array(&$this, 'frontendFooter'));
	}
	
	/**
	* Register Settings
	*/
	function registerSettings() {
		register_setting($this->plugin->name, 'metricool_profile_id', 'trim');
	}
	
	/**
    * Register the plugin settings panel
    */
    function adminPanelsAndMetaBoxes() {
    	add_submenu_page('options-general.php', $this->plugin->displayName, $this->plugin->displayName, 'manage_options', $this->plugin->name, array(&$this, 'adminPanel'));
	}
    
    /**
    * Output the Administration Panel
    * Save POSTed data from the Administration Panel into a WordPress option
    */
    function adminPanel() {
    	// Save Settings
        if (isset($_POST['submit'])) {
        	// Check nonce
        	if (!isset($_POST[$this->plugin->name.'_nonce'])) {
	        	// Missing nonce	
	        	$this->errorMessage = __('nonce field is missing. Settings NOT saved.', $this->plugin->name);
        	} elseif (!wp_verify_nonce($_POST[$this->plugin->name.'_nonce'], $this->plugin->name)) {
	        	// Invalid nonce
	        	$this->errorMessage = __('Invalid nonce specified. Settings NOT saved.', $this->plugin->name);
        	} else {        	
	        	// Save
	    		update_option('metricool_profile_id', $_POST['metricool_profile_id']);
				$this->message = __('Settings Saved.', $this->plugin->name);
			}
        }
        
        // Get latest settings
        $this->settings = array(
        	'metricool_profile_id' => get_option('metricool_profile_id')
        );
        
    	// Load Settings Form
        include_once(WP_PLUGIN_DIR.'/'.$this->plugin->name.'/views/settings.php');  
    }
    
    /**
	* Loads plugin textdomain
	*/
	function loadLanguageFiles() {
		load_plugin_textdomain($this->plugin->name, false, $this->plugin->name.'/languages/');
	}
	
	/**
	* Outputs script / CSS to the frontend footer
	*/
	function frontendFooter() {
		$this->output('metricool_profile_id');
	}
	
	/**
	* Outputs the given setting, if conditions are met
	*
	* @param string $setting Setting Name
	* @return output
	*/
	function output($setting) {
		// Ignore admin, feed, robots or trackbacks
		if (is_admin() OR is_feed() OR is_robots() OR is_trackback()) {
			return;
		}
		
		// Get meta
		$meta = get_option($setting);
		if (empty($meta)) {
			return;
		}	
		if (trim($meta) == '') {
			return;
		}
		
		// Output
		echo stripslashes("<script>function loadScript(a){var b=document.getElementsByTagName(\"head\")[0],c=document.createElement(\"script\");c.type=\"text/javascript\",c.src=\"https://tracker.metricool.com/app/resources/be.js\",c.onreadystatechange=a,c.onload=a,b.appendChild(c)}loadScript(function(){beTracker.t({hash:'" . $meta . "'})})</script>");
	}
}
		
$metricool = new metricool();
?>
