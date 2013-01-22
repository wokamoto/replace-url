<?php
if(!function_exists('get_option')) {
	$path = (defined('ABSPATH') ? ABSPATH : dirname(dirname(dirname(dirname(__FILE__)))) . '/');
	require_once(file_exists($path.'wp-load.php') ? $path.'wp-load.php' : $path.'wp-config.php');
}

// プラグインが有効になっているかチェック
$active_plugins = get_settings('active_plugins');
$plugin = basename(dirname(__FILE__)) . '/plugin.php';
if ( !array_search($plugin, $active_plugins) ) {
	wp_die('Please activate plugin.');
}

// WP のユーザー/パスワードで BASIC 認証
nocache_headers();
if ( !is_user_logged_in() ) {
	$user = isset($_SERVER["PHP_AUTH_USER"]) ? $_SERVER["PHP_AUTH_USER"] : '';
	$pwd = isset($_SERVER["PHP_AUTH_PW"]) ? $_SERVER["PHP_AUTH_PW"] : '';
	if ( is_wp_error(wp_authenticate($user, $pwd)) ) {
		// BASIC 認証が必要
		header('WWW-Authenticate: Basic realm="Please Enter Your Password"');
		header('HTTP/1.0 401 Unauthorized');
		echo 'Authorization Required';
		die();
	}
}

$old_site = untrailingslashit(isset($_POST['search']) ? $_POST['search'] : home_url());
$new_site = untrailingslashit(isset($_POST['replace']) ? $_POST['replace'] : '');
$path = ABSPATH;

global $wp_version, $wpdb;

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>WordPress &rsaquo; Replace Site URL</title>
<link rel="stylesheet" href="../../../wp-admin/css/install.css?ver=<?php echo $wp_version; ?>" type="text/css" />
<link rel="stylesheet" href="../../../wp-includes/css/buttons.css?ver=<?php echo $wp_version; ?>" type="text/css" />

</head>
<body class="wp-core-ui">
<h1 id="logo"><a href="http://wordpress.org/">WordPress</a></h1>
<form method="post" action="">
<?php wp_nonce_field('replcae_action', 'replcae_nonce'); ?>
	<p></p>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="search">Old Site URL</label></th>
			<td><input name="search" id="search" type="text" size="25" value="<?php echo esc_attr($old_site); ?>" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="replace">New Site URL</label></th>
			<td><input name="replace" id="replace" type="text" size="25" value="<?php echo esc_attr($new_site); ?>" /></td>
		</tr>
	</table>
		<p class="step"><input name="submit" type="submit" value="Submit" class="button button-large" /></p>
</form>
<?php
if ( !empty($new_site) && isset($_POST['replcae_nonce']) && wp_verify_nonce($_POST['replcae_nonce'],'replcae_action') ) {
	if ( !class_exists('ReplaceSiteURL') )
		require_once('./class-replace_site_url.php');
	$replace = new ReplaceSiteURL($new_site, $path, $old_site);

	printf("<p>Replace <strong>'%s'</strong> to <strong>'%s'</strong> ...</p>\n", $replace->old_site, $replace->new_site);

	// wp_options
	printf("<p><strong>%s</strong>: %d</p>\n", $wpdb->options, $replace->options());

	// wp_posts
	printf("<p><strong>%s</strong>: %d</p>\n", $wpdb->posts, $replace->posts());

	// wp_postmeta
	printf("<p><strong>%s</strong>: %d</p>\n", $wpdb->postmeta, $replace->postmeta());

	// wp_usermeta
	printf("<p><strong>%s</strong>: %d</p>\n", $wpdb->usermeta, $replace->usermeta());

	// wp_commentmeta
	printf("<p><strong>%s</strong>: %d</p>\n", $wpdb->commentmeta, $replace->commentmeta());

	printf('<p>Go to <a href="%1$s">%1$s</a></p>'."\n", $replace->new_site);
}
?>
</body>
</html>

