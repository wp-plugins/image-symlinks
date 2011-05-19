<?php /*

	Cache dir for TimThumb

	Todo:
	- make this dir configurable through admin
	- move this stuff to plugin onactivate somehow
	- move mkdir back to wp_mkdir_p
	
*/

// TimThumb parameters
//define ('DIRECTORY_CACHE', './cache');


define('WP_CONTENT_DIR', '../..'); // ugly, but will work for this
define('DIRECTORY_CACHE', WP_CONTENT_DIR . '/image-symlinks-cache');

function cache_is_writable($file = false) {
	if ($file)
		$file = DIRECTORY_CACHE . '/' . $file;
		
	$writable = false;
	
	if ($file && file_exists($file))
		$writable = is_writable($file);
	elseif (file_exists(DIRECTORY_CACHE))
		$writable = is_writable(DIRECTORY_CACHE);
	elseif (is_writable(WP_CONTENT_DIR))
		//$writable = wp_mkdir_p(DIRECTORY_CACHE);
		$writable = mkdir(DIRECTORY_CACHE);
	
	return $writable;
}

if (!cache_is_writable()) {
	die('For Image Symlinks to work, your <code>wp-content</code> needs to be writable (<code>chmod 777</code>), at least once. Once you\'ve run Image Symlinks once without this warning, you can safely change back the permissions to read-only (<code>chmod 755</code>)!');
}
