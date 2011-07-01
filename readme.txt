=== Image Symlinks ===
Contributors: Joen
Tags: gallery, images, pictures
Requires at least: 2.7
Tested up to: 3.1
Stable tag: trunk

== Description ==

This plugin is an alternative to the builtin Wordpress image system. Instead of resizing images on upload, it resizes images when you need them, and according to the sizes you specify. The only difference is, you insert images using an [img] shortcode instead of the img HTML tag. 

Why you should use this plugin:

* Quick bulk upload
* Quick way to resize full-width images if your theme changes
* Auto-cleaning resize cache
* Bulk insertion

<strong>New in 0.7:</strong> If your `wp-content` directory is writable, the cache directory will be created automatically.
<strong>New in 0.8:</strong> [latestimages] shortcode shows the last 15 images in 100x100px chunks. Configurable of course.

== Installation ==

1. Upload the plugin to your `wp-content/plugins` directory
2. Activate the plugin
3. Go to the plugin directory (for instance, `wp-content/plugins/image-symlinks`) and make sure the `cache` is writable (`CHMOD 777`). 

<em>Hopefully step 3 will be handled by the plugin upon activation in the future.</em>

<strong>Advanced usage:</strong>

* Go to the options page, and specify plugin defaults
* If you want to specify a different upload folder than the default (`wp-content/uploads`), you need to make sure that directory is writable.

== Screenshots ==

1. The new media button.
2. The insertion dialog. Just click a thumbnail to insert it.
3. The options page where you can configure the defaults.
4. The builtin uploader which circumvents the Wordpress Media Library (if you want it).

== Syntax ==

> `[img src="filename.png" width="500" height="400" /]`

Required parameters:

* `src`

Optional parameters:

* `width`
* `height`
* `class`
* `alt`
* `crop`

> `[latestimages num="2" size="200"]`

Optional parameters:

* `num`
* `size`

== Changelog ==

* 0.1: First functional plugin.
* 0.2: Switched to use Uploadify to upload images, for a bunch of reasons.
* 0.3: Tweaks and polish.
* 0.4: Tweak to the options page, including the ability to select upload directory.
* 0.5: HUGE fixes to the page insertion dialog box. Now you can even insert images that are uploaded using Wordpress' uploader.
* 0.6: Insert images in the bulk.
* 0.7: Updated TimThumb to latest version (a bit faster it seems), and updated icon to fit 3.1 style. Additionally, made it so cache directory is automatically generated. Should make it easier to upgrade. Default directory is wp-content/image-symlinks-cache
* 0.8: Various tweaks. Made plugin translatable, and provided po file as well as danish translation. Increased max filesize of uploaded image. Massively polished the "insert after upload" experienced, and now showing a clickable thumbnail for insertion. Also added beta shortcode, [latestimages] which shows the last 15 images you've uploaded.
* 0.8.3: Security fix.
* 0.8.4: Security hardening release.