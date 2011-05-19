<?php
/*
Plugin Name: Image Symlinks
Plugin URI: http://noscope.com/
Description: Extremely simple wrapper for TimThumb&trade; which adds an <code>[img]</code> shortcode for inserting symlink images.
Version: 0.7.1
Author: Joen Asmussen
Author URI: http://noscope.com
*/



/* 

	todo:
		
		- option to customize location of cache directory

		- when no custom upload directory is specified, a 2.9 subdirectory such as "uploads/symlink-images" should be used:

			add_filter('upload_dir', 'my_upload_dir');
			$upload = wp_upload_dir();
			remove_filter('upload_dir', 'my_upload_dir');
 
			funcion my_upload_dir($upload) {
				$upload['subdir']	= '/sub-dir-to-use' . $upload['subdir'];
				$upload['path']		= $upload['basedir'] . $upload['subdir'];
				$upload['url']		= $upload['baseurl'] . $upload['subdir'];
				return $upload;
			}


		- insert tab tweaks
			- perhaps by default when an image is clicked, it is "selected" after which an "insert" button becomes ungreyed	
			- crop should be possible
			
			- improve caching of insert page images (how?)
			- error message when images are too large!


		- plugin should be l10n translatable


	options:
	
		- autolink defaults


	
*/



/**
 * 	Constants, not like in Lost
 */
if ( ! defined( 'WP_CONTENT_URL' ) )
	define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
	
define('SYMLINK_PLUGIN_DIRNAME', plugin_basename(dirname(__FILE__))); 












/**
 *  Media Button
 */
function media_buttons_context($buttons) {
		global $post_ID, $temp_ID;
	
		$image_btn = WP_PLUGIN_URL . '/' . SYMLINK_PLUGIN_DIRNAME . '/media-symlink.gif';
		$image_title = 'Insert symlink image';
		
		$uploading_iframe_ID = (int) (0 == $post_ID ? $temp_ID : $post_ID);

		$media_upload_iframe_src = get_option('siteurl')."/wp-admin/media-upload.php?type=image&tab=symlinks_upload&post_id=$uploading_iframe_ID";
		$new = ' <a href="'.$media_upload_iframe_src.'&TB_iframe=true" class="thickbox" title="'.$image_title.'"><img src="'.$image_btn.'" alt="'.$image_title.'" /></a>';
		
		return $buttons . $new;
}
add_filter('media_buttons_context', 'media_buttons_context');














/**
 * 	Media Tabs
 */
function add_symlinks_tabs($tabs) {
	if (get_option('symlinks_uploaddir')) {
		$tabs['symlinks_upload'] = 'Upload Symlink Images';
	}
	$tabs['symlinks_insert'] = 'Insert Symlink Images';
	return $tabs;
}
add_action('media_upload_tabs','add_symlinks_tabs');







/**
 * 	"Upload Image" Iframe
 */
function media_upload_symlinks_upload() {
	
	wp_deregister_script('jquery');
	wp_deregister_script('swfupload-all');
	
	return wp_iframe( 'media_upload_symlinks', 'symlinks_upload' );

}
add_action('media_upload_symlinks_upload', 'media_upload_symlinks_upload');










/**
 * 	"Insert Image" Iframe
 */

function media_upload_symlinks_insert() {
	
	return wp_iframe( 'media_upload_symlinks', 'symlinks_insert', $errors, $id );

}
add_action('media_upload_symlinks_insert', 'media_upload_symlinks_insert');











/**
 * 	Iframes
 */
function media_upload_symlinks($type,$errors=null,$id=null) {
	
	// show Wordpress defined tabs
	media_upload_header();
	



	/**
	 * 	"Insert" tab
	 */

	if ($type=="symlinks_insert") {
?>	

<div style="padding: 0 10px;">
		
<h3 class="media-title"><?php _e('Insert symlink image'); ?></h3>


	<?php
	
	/**
	 * Pick upload dir
	 */
	
	
	// FIXME: this should be checked for in the same way as the upload section -- create separate return function for this
	if (get_option('symlinks_uploaddir')) {
		
		// is it a subdirectory of the WP installation?
		$firstletter = substr(get_option('symlinks_uploaddir'), 0, 1);
		
		if ($firstletter == "/") {

			// the directory is outside the WP installation!
			$upload_dir = get_option('symlinks_uploaddir');
			$upload_path = $_SERVER['DOCUMENT_ROOT'] . get_option('symlinks_uploaddir');
			
		} else {
			
			// yes, the upload dir is a subdirectory of the wordpress installation
			$upload_dir = get_bloginfo('wpurl') . '/' . get_option('upload_path');
			$upload_dir = str_replace("http://" . $_SERVER['HTTP_HOST'], "", $upload_dir);
			$upload_path = $_SERVER['DOCUMENT_ROOT'] . $upload_dir;
			
		}
				
	}


	
	if ($_GET['subnav'] == "wp" || get_option('symlinks_uploaddir') == "") {
		$wpdir = wp_upload_dir();
		$path = $wpdir['path'] . "/";
		$upload_dir = $wpdir['url'];
	} else {
		$path = $upload_path . "/";
	}
	

		
	
	
	
	
	
	/**
	 * Insert from which library?
	 */
	
	global $subnav;
	$subnav = $_GET['subnav'];
	if ($subnav == "wp") {
		$wpactive = ' class="active"';
		$myactive = '';
	} else {
		$myactive = ' class="active"';
		$wpactive = '';
	}
	
	
	// if a custom directory is defined, show the ability to pick
	if (get_option('symlinks_uploaddir')) {

	echo '
	<ul class="subnav">
		<li'. $myactive .'><a href="?type=image&tab=symlinks_insert">Insert from my custom library</a></li>
		<li'. $wpactive .'><a href="?type=image&tab=symlinks_insert&subnav=wp">Insert from my Wordpress media library</a></li>			
	</ul>
	';
	
	}


	
	
	/**
	 * Insert page contents
	 */

	function getPictureType($file) {
		$split = explode('.', $file); 
		$ext = $split[count($split) - 1];
		if ( preg_match('/jpg|jpeg/i', $ext) ) {
			return 'jpg';
		} else if ( preg_match('/png/i', $ext) ) {
			return 'png';
		} else if ( preg_match('/gif/i', $ext) ) {
			return 'gif';
		} else {
			return '';
		}
	}

	// Settings
	$timthumb = WP_PLUGIN_URL . '/' . SYMLINK_PLUGIN_DIRNAME . "/timthumb.php";
	
	global $s_page, $per_page, $has_previous, $has_next;
	$per_page = 27;	
	$s_page = $_GET['s_page'];
	$has_previous = false;
	$has_next = false;
	


	
	
	

	
	// look at files in directory
	$dh = @opendir($path);
	while (false !== ($file=readdir($dh))) {

		preg_match( "/[^@]+-[0-9]+x[0-9]+\.([^\.]+)/", $file, $thumbmatches );
		
		if (substr($file,0,1)!="." && $file != "js_cache" && count($thumbmatches) == 0 )  {
		
			$files[]=array(filemtime($path.$file),$file);   //2-D array
			
		}

	}
	closedir($dh);


	// output paginated
	$pages = ceil(count($files)/$per_page);

	echo '<ul id="symlink-image-list">';

	if ($files) {
	
		rsort($files); //sorts by filemtime

		foreach ($files as $file){
			$count++;
			
			if ($count > $per_page * ($s_page) && $count <= $per_page * ($s_page +1)) {



					echo '
					<li>
						<a href="'.$upload_dir.'/'.$file[1].'" title="'.$file[1].'" style="background-image: url(\''.$timthumb . '?src=' . $upload_dir.'/'.$file[1].'&w=50&h=50\');">
						<span class="filename">'.$file[1].'</span>
						</a>
					</li>
					';
				


			}
		}		
	}

	echo '</ul>';



	// next/prev
	if ($_GET['subnav'] == "wp") {
		$section = "&subnav=wp";
	}

	echo '<div class="nextprev">';
	
	if ( $s_page > 0 )
		echo '<p class="prev"><a href="?type=image&tab=symlinks_insert'.$section.'&s_page='.($s_page - 1).'">&larr; Previous Page</a></p>';

	if ( $s_page < ($pages -1) )
		echo '<p class="next"><a href="?type=image&tab=symlinks_insert'.$section.'&s_page='.($s_page + 1).'">Next Page &rarr;</a></p>';
	
	echo '</div>';

	// notification message
	echo '
	<div id="notice"><p></p></div>
	';

// pagination
	echo '
	<ul class="pagination">
	<li class="page">Page '.($s_page +1).' of '.$pages.' &nbsp; &nbsp; </li>
	';
	
		for ($i=0; $i < $pages; $i++) {
		
			if ($i == $s_page) {
				echo '<li class="current"><a href="?type=image&tab=symlinks_insert'.$section.'&s_page='.$i.'">'. ($i +1).'</a></li>';
			} else {
				echo '<li><a href="?type=image&tab=symlinks_insert'.$section.'&s_page='.$i.'">'. ($i +1).'</a></li>';
			}
		}
		
	echo 
	'</ul>
	';






	
	
	
	
	
	// CSS
	?>
	<link rel="stylesheet" href="<?php echo WP_PLUGIN_URL . "/" . SYMLINK_PLUGIN_DIRNAME; ?>/image-symlinks.css" type="text/css" />
	<script type="text/javascript" src="<?php echo WP_PLUGIN_URL . "/" . SYMLINK_PLUGIN_DIRNAME; ?>/image-symlinks.js"></script>
	<script type="text/javascript">
	
	jQuery(document).ready(function(){
									
		jQuery('#symlink-image-list a').click(function () {
														
			<?php if ($_GET['subnav'] == "wp" || get_option('symlinks_uploaddir') == "") { 
			
				$wpdir = wp_upload_dir();
				$wpdir = $wpdir['url'];
				$wpdir = str_replace(get_bloginfo('wpurl'), '', $wpdir);
			
			?>
			var filename = ' src="<?=$wpdir ?>/' + jQuery(this).children('span.filename').html() +'"';
			<?php } else { ?>
			var filename = ' src="' + jQuery(this).children('span.filename').html() +'"';
			<?php } ?>

			/*
			// optional
			var width = ""; var height = ""; var alt=""; var class=""; var crop="";
			*/
		
			var symlink = '[img '+filename+'] ';	<?php // need the trailing space, because two shortcodes right next to eachother might fail ?>
	
			symlink_send_to_editor(symlink); 
			
			return false;
	
		});
	
	});
	</script>











</div>

<?php 
	} 






	/**
	 * 	"Upload" tab
	 */

	else {
	
		// path to uploadify script
		$uploadify_url = WP_PLUGIN_URL . "/" . SYMLINK_PLUGIN_DIRNAME;
		
		// default path to upload directory
		if (get_option('symlinks_uploaddir')) {
			
			// is it a subdirectory of the WP installation?
			$firstletter = substr(get_option('symlinks_uploaddir'), 0, 1);
			
			if ($firstletter == "/") {

				// the directory is outside the WP installation!
				$upload_dir = get_option('symlinks_uploaddir');
				
			} else {
				
				// yes, the upload dir is a subdirectory of the wordpress installation
				$upload_dir = get_bloginfo('wpurl') . '/' . get_option('upload_path');
				// make the URL relative
				$upload_dir = str_replace("http://" . $_SERVER['HTTP_HOST'], "", $upload_dir);
				
			}
			
			
		} else {
			
			die('Error: You need to specify an upload directory on the options page.');
			
		}
		
		/*
		// FIXME: this is dirty and doesn't work
		$upload_max_filesize = str_replace("M", "", ini_get('upload_max_filesize'));
		$upload_max_filesize *= 1024;
		*/
		
?>


	<link rel="stylesheet" href="<?php echo $uploadify_url; ?>/uploadify/uploadify.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo WP_PLUGIN_URL . "/" . SYMLINK_PLUGIN_DIRNAME; ?>/image-symlinks.css" type="text/css" />
	<script type="text/javascript" src="<?php echo $uploadify_url; ?>/uploadify/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="<?php echo $uploadify_url; ?>/uploadify/swfobject.js"></script>
	<script type="text/javascript" src="<?php echo $uploadify_url; ?>/uploadify/jquery.uploadify.js"></script>
	<script type="text/javascript" src="<?php echo WP_PLUGIN_URL . "/" . SYMLINK_PLUGIN_DIRNAME; ?>/image-symlinks.js"></script>
		
	<script type="text/javascript">
	jQuery(document).ready(function() {

		jQuery("#fileUpload").uploadify({
			'uploader': '<?php echo $uploadify_url; ?>/uploadify/uploadify.swf'
			,'cancelImg': '<?php echo $uploadify_url; ?>/uploadify/cancel.png'
			,'script': '<?php echo $uploadify_url; ?>/uploadify/uploadify.php'
			,'folder': '<?php echo $upload_dir; ?>'
			,'multi': true
			,'fileExt' : '*.jpg;*.jpeg;*.png;*.gif'
			,'fileDesc' : 'Image Files (*.jpg, *.jpeg, *.png, *.gif)'
			,'buttonText': 'Upload Images'
			//,'checkScript': '<?php echo $uploadify_url; ?>/uploadify/check.php'
			,'displayData': 'speed'
			<?php /* ,'sizeLimit': <?php echo $upload_max_filesize; ?> */?>
			,'simUploadLimit': 2
			//,'auto': true
			//,'method' : 'GET'
			,'onSelect' : function(event, queueID, fileObj) {
				
				// if file doesn't exist, start upload
				jQuery(function () { 
					jQuery.ajax({ 
						url : '<?php echo $upload_dir; ?>/' + fileObj.name,
						success : function () {
							// file already exists
							var answer = confirm(fileObj.name + " already exists. Overwrite?")
							if (answer){
								// user confirms, start upload
								$('#fileUpload').uploadifyUpload(queueID);
							}
							else{
								// user cancels a file upload, remove it
								$('#fileUpload').uploadifyCancel(queueID);
							}
						}, 
						error : function (xhr, d, e) { 
							if (xhr.status == 404) { 
								// file doesn't exist, start upload
								$('#fileUpload').uploadifyUpload(queueID);
							} 
						} 
					}); 
				});

				
			}
			
			/**
			 * Upload complete msg
			 */
			,'onComplete' : function(event, queueID, fileObj, response, data) {

				jQuery('#status').append('<span style="display: none;" id="'+queueID+'">[img src="'+fileObj.name+'"] </span>');				
				var msg = fileObj.name + " [<a href=\"javascript:symlink_send_to_editor(jQuery('#"+queueID+"').html() );\">insert</a>]";
				jQuery('#status').append(msg + "<br />");
				
				
				
			}

		});

	});

</script>


<div style="padding: 0 10px;">

	<h3 class="media-title"><?php _e('Upload Image Files', 'image-symlinks'); ?></h3>

	<div id="fileUpload"><?php _e('You have a problem with your javascript', 'image-symlinks'); ?></div>
	
	<div id="status"></div>

	<div id="notice"><p></p></div>

	<div id="note">
		<p><em>Your server is configured to upload files no larger than <?php echo ini_get('upload_max_filesize'); ?>.<br />
		Also, this totally fast uploader requires the <a href="http://get.adobe.com/flashplayer/">Flash Player</a>.</em></p>
	</div>

</div>

<style type="text/css">
#status {
	margin-top: 10px;
}
#note {
	margin-top: 40px;
	color: #999;
}
#note p {
	font-size: 7pt;
}
</style>
	
	

<?php	
	
	}






	
	// iframe footer
	echo '</div></body></html>';
}
























/**
 * 	Shortcodes
 */

// Register Shortcodes
add_shortcode('img', 'insertSymlinkImage');


// Insert image
function insertSymlinkImage($attr) {

	// required parameters: src, width, album
	// optional parameters: height, class, alt, crop
	
	// timthumb info
	$timthumb = WP_PLUGIN_URL . '/' . SYMLINK_PLUGIN_DIRNAME . "/timthumb.php";



	// FIXME: this should be checked for in the same way as the upload section -- create separate return function for this
	if (get_option('symlinks_uploaddir')) {
		
		// is it a subdirectory of the WP installation?
		$firstletter = substr(get_option('symlinks_uploaddir'), 0, 1);
		
		if ($firstletter == "/") {

			// the directory is outside the WP installation!
			$upload_dir = get_option('symlinks_uploaddir');
			$upload_path = $_SERVER['DOCUMENT_ROOT'] . get_option('symlinks_uploaddir');
			
		} else {
			
			// yes, the upload dir is a subdirectory of the wordpress installation
			$upload_dir = get_bloginfo('wpurl') . '/' . get_option('upload_path');
			$upload_dir = str_replace("http://" . $_SERVER['HTTP_HOST'], "", $upload_dir);
			$upload_path = $_SERVER['DOCUMENT_ROOT'] . $upload_dir;
			
		}
		
	} else {
		
		die(__('Error: You need to specify an upload directory on the options page.', 'image-symlinks'));
		
	}
		
	
	

	
	// src
	$the_symlinks_url = str_replace(get_bloginfo('wpurl'), '', $attr['src']); // compatible with absolute urls runtime
	//$the_symlinks_url = $attr['src'];
	
	
	
	// check if src is deeplink, and if it is, then redefine upload dir
	if (strpos($the_symlinks_url, '/') !== false) {
		$upload_dir = "";
	} else {
		$upload_dir = $upload_dir . '/';
	}
	
	
		
	
	// alt
	if (!$attr['alt']) {
		$attr['alt'] = $attr['src'];
	}


	// link
	/* FIXME if (!$attr['link']) {
		$attr['link'] = $attr['src'];
	}*/


	
	// default width
	if ($attr['width']) {
		$symlinksWidth = $attr['width'];
	} else {
		$symlinksWidth = get_option('symlinks_width');
	}
	
	// default class
	if ($attr['class']) {
		$symlinksClass = $attr['class'];
	} else if (!get_option('symlinks_class')) {
		$attr['class'] = "symlinks-image";
	} else {
		$symlinksClass = get_option('symlinks_class');
	}
	



	/**
	 * form string
	 */
	$string .= '<div class="si">';
	
			
		if ($attr['link'] != "false") {
			$string .= '<a href="'. $upload_dir . $the_symlinks_url.'">';
		}
		
		
		$string .= '<img src="'. $timthumb . '?src=' . $upload_dir . '/' . $the_symlinks_url;
		
		$string .= '&amp;w=' . $symlinksWidth;
		$string .= '&amp;h=' . $attr['height'];
		// $string .= $crop;
		$string .= '" alt="' . $attr['alt'] . '"';
		$string .= ' class="' . $symlinksClass . '"';
	
		$string .= ' />';

		if ($attr['link'] != "false") {
			$string .= '</a>';
		}

	
	$string .= '</div>';



	return $string;



}





























/***************************
* 
* 	Options
*
*/
$symlinks_plugin_name = __("Image Symlinks", 'image-symlinks');
$symlinks_plugin_filename = "image-symlinks.php";

add_option("symlinks_width", "500", "", "yes");
add_option("symlinks_class", "", "", "yes");
add_option("symlinks_uploaddir", "wp-content/uploads", "", "yes");




// Register options page
add_action('admin_init', 'symlinks_admin_init');
add_action('admin_menu', 'add_symlinks_options_page');


function symlinks_admin_init() {
	if ( function_exists('register_setting') ) {
		register_setting('symlinks_settings', 'option-1', '');
	}
}
function add_symlinks_options_page() {
	global $wpdb;
	global $symlinks_plugin_name;
	
	add_options_page($symlinks_plugin_name, $symlinks_plugin_name, 8, basename(__FILE__), 'symlinks_options_page');
}

function symlinks_options_page() {
	if (isset($_POST['info_update'])) {
			
		// Update options
		$symlinks_width = $_POST["symlinks_width"];
		update_option("symlinks_width", $symlinks_width);
		
		$symlinks_class = $_POST["symlinks_class"];
		update_option("symlinks_class", $symlinks_class);
		
		$symlinks_uploaddir = $_POST["symlinks_uploaddir"];
		update_option("symlinks_uploaddir", $symlinks_uploaddir);
		

		// Give an updated message
		echo "<div class='updated fade'><p><strong>" . __('Options updated', 'ratings-shorttags') . "</strong></p></div>";
		
	}

	// Show options page
	?>

		<div class="wrap">
		
			<div class="options">
			

				<style type="text/css">
				
					fieldset {
						/* border: 1px solid #7c7c7c; */
						border: 2px groove #ccc;
						margin-bottom: 20px;
						padding: 20px;
					}
					legend {
						padding: 0 5px;
						font-weight: bold;
					}
				
				</style>
			


		
				<form method="post" action="options-general.php?page=<?php global $symlinks_plugin_filename; echo $symlinks_plugin_filename; ?>">
				<h2><?php global $symlinks_plugin_name; printf(__('%s Settings', 'symlinks_'), $symlinks_plugin_name); ?></h2>
				
					<p><em>What's a symlink image?</em> A symlink is a "symbolic link" to an image. You store a large source image, and the symbolic link fetches an appropriate size for your convenience.</p>
					
					<p>Syntax: <code>[img src="filename.jpg" width="300" height="200"]</code></p>
					
					<p class="submit">
						<?php if ( function_exists('settings_fields') ) settings_fields('symlinks_settings'); ?>
						<input style="padding: 5px;" type='submit' name='info_update' value='<?php _e('Save Changes', 'symlinks_settings'); ?>' />
					</p>

					<fieldset>
						<legend>Defaults</legend>
					

					<p>
					<label>Default width of your symlink images:<br />
					<?php
					echo "<input type='text' size='50' ";
					echo "name='symlinks_width' ";
					echo "id='symlinks_width' ";
					echo "value='".get_option('symlinks_width')."' />\n";
					?>
					</label>
					</p>

					<p>
					<label>Default CSS class name of your symlink images:<br />
					<?php
					echo "<input type='text' size='50' ";
					echo "name='symlinks_class' ";
					echo "id='symlinks_class' ";
					echo "value='".get_option('symlinks_class')."' />\n";
					?>
					</label>
					</p>

					
					</fieldset>



					<fieldset>
						<legend>Super Advanced</legend>
						
						<p>If you specify your own upload directory, you'll be able to use <em>Image Symlinks</em> built-in uploader, which doesn't store anything in your database. The benefit is that you can specify any directory on your website, even one that belongs to a gallery of a different system, such as <a href="http://zenphoto.org">Zenphoto</a>, and then you'll be able to easily insert and scale images in Wordpress posts.</p>
						<p>To do this, you have to:</p>
						<ol>
							<li>Create your directory on your webserver (if you're not specifying an existing directory)</li>
							<li>Make sure that directory is writable (CHMOD 777)</li>
							<li>Input the relative path to the directory below (for instance, if you've installed Zenphoto in <code>http://example.com/zenphoto/</code>, type in <code>/zenphoto/albums/</code>)</li>
						</ol>

					<p>
					<label>Default upload directory:<br />
					<?php
					echo "<input type='text' size='50' ";
					echo "name='symlinks_uploaddir' ";
					echo "id='symlinks_uploaddir' ";
					echo "value='".get_option('symlinks_uploaddir')."' />\n";
					?>
					</label><br />
					<?php /* <em>Default: <code>wp-content/uploads</code>. Careful! Doublecheck that path so you don't overwrite someone elses stuff!</em> */ ?>
					</p>

					</fieldset>



					<p class="submit">
						<?php if ( function_exists('settings_fields') ) settings_fields('symlinks_settings'); ?>
						<input style="padding: 5px;" type='submit' name='info_update' value='<?php _e('Save Changes', 'symlinks_settings'); ?>' />
					</p>
					


					
				</form>
				
				
			</div><?php //.options ?>
			
		</div>

<?php
}
?>