<?php
/*
Uploadify v2.1.0
Release Date: August 24, 2009

Copyright (c) 2009 Ronnie Garcia, Travis Nickels

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

//define constants
define('WP_ADMIN',true);
define('DOING_AJAX',true);

//load wp
require_once '../../../../wp-load.php';

// security check
$i	= wp_nonce_tick();
$uid	= $_REQUEST['userid'];
$nonce 	= $_REQUEST['_wpnonce'];

global $current_user;
wp_set_current_user($uid);


if(!current_user_can("upload_files"))  die();


check_admin_referer('symlinksnonce');

// okay then
if (!empty($_FILES)) {
	$tempFile = $_FILES['Filedata']['tmp_name'];
	$targetPath = $_SERVER['DOCUMENT_ROOT'] . $_REQUEST['folder'] . '/';
	$targetFile =  str_replace('//','/',$targetPath) . $_FILES['Filedata']['name'];
	
	// Uncomment the following line if you want to make the directory if it doesn't exist
	// mkdir(str_replace('//','/',$targetPath), 0755, true);
	
	// Define allowed extensions
	$allowable = array ( 'jpg', 'gif', 'png' );
	$fileext = strtolower(substr( $_FILES['Filedata']['name'], -3 ));
  
	// Assume evil upload  
	$noMatch = 0;
  
	// Give it a try with this tiny extensionckeck 	
	foreach( $allowable as $ext ) {
		if ( strcasecmp( $fileext, $ext ) == 0 ) {
			$noMatch = 1;
		}
	}
	
	
	if(!$noMatch){ // People are bad. I told you...	
		echo "This file is not allowed...";
		exit();
	}
	else { // Or some may not...
		move_uploaded_file($tempFile,$targetFile);
		echo "1";
	}
}


?>