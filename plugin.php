<?php
/*
Plugin Name: Replace Site URL
Plugin URI: http://dogmap.jp/
Description: 
Author: wokamoto
Version: 0.0.1
Author URI: http://dogmap.jp/

License:
 Released under the GPL license
  http://www.gnu.org/copyleft/gpl.html

  Copyright 2012 (email : wokamoto1973@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class replace_url_plugin{
	private $include_file;

	function __construct(){
		register_activation_hook(__FILE__, array(&$this, 'activation'));
		register_deactivation_hook(__FILE__, array(&$this, 'deactivation'));

		add_action('generate_rewrite_rules', array(&$this, 'add_rewrite_rules'));
		add_action('parse_request', array(&$this, 'parse_request'));
		
		$this->include_file = $this->plugin_dir() . 'replace.php';
	}

	public function activation(){
		$this->add_rewrite_rules();
		flush_rewrite_rules();
	}

	public function deactivation(){
		flush_rewrite_rules();
	}

	private function plugin_dir(){
		$home_url = trailingslashit(get_home_url('/'));
		if ( $parsed_home_url = parse_url($home_url)) {
			$home_url = trailingslashit(
				(isset($parsed_home_url['scheme']) ? $parsed_home_url['scheme'] : 'http') . '://' .
				(isset($parsed_home_url['host']) ? $parsed_home_url['host'] : '') .
				(isset($parsed_home_url['port']) ? ':' . $parsed_home_url['port'] : '')
				);
			unset($parsed_home_url);
		}
		return trailingslashit(str_replace($home_url, '', plugins_url('/').basename(dirname(__FILE__))));
	}

	public function add_rewrite_rules() {
		add_rewrite_rule('^replace_url/?', 'index.php?' . $this->include_file, 'other' );
	}

	public function add_query_vars($vars){
	    $vars[] = 'include';
	    return $vars;
	}

	public function parse_request(&$request) {
		if ( isset($request->matched_query) && $request->matched_query === $this->include_file ) {
			include(ABSPATH.$this->include_file);
			die();
		}
		return $request;
	}
}
new replace_url_plugin();