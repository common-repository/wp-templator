=== Templator ===
Contributors: brainstormforce
Donate link: https://wptemplator.com/
Tags: page builder, templates
Requires at least: 4.4
Requires PHP: 5.3
Tested up to: 5.1
Stable tag: 1.0.3.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Save your templates in the cloud and access them on any other site.

== Description ==

**Important:** We're Sunsetting Templator on 15 May 2020. You can download your templates from the cloud before it shuts down. [Read More](https://wptemplator.com/good-bye-templator-cloud/)<a href=""></a>

How would like to be able to save your own pages and templates in the cloud; and access them across any other websites – in just a click? This plugin will help you do exactly that. Here is how it works:
1. Install the plugin 
2. Create account on our portal and get your API Key
3. Enter the API Key in the plugin
4. Export any pages or templates from one website
5. The exported template will be available for importing on your other websites.
https://www.youtube.com/watch?v=-GLPslmY6DA
The plugin supports following page builders:
1. Elementor
2. Beaver Builder (Coming soon)
3. Divi (Coming soon)
Currently the plugin is available as free service. It may become as a paid service, or just remain as a free in the future.
<pre>DISCLAIMER: 
Templator is a stand-alone service. You MUST have an active API key to use this plugin.</pre>
== Frequently Asked Questions ==
= Where my my templates be exported? =
When you acquire your API Key, we create a dedicated website for you on our WordPress Multisite network. Your templates will be exported there.
= Will my templates be available to anyone else? =
No. Your templates will be saved under own your account and won't be available or visible to anyone else.
= How am I able to categorize all my templates? =
While exporting any template, you can enter your own categories where your template belongs.
= Does it handle images as well? =
Yes — your templates will be exported with all media assets you've in the page. And they will be imported seamlessly on other site when you import the template.
= Can I preview of my templates before importing? =
Yes — you can upload your own screenshots for the template, so you will be able to easily recognize them.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/templator` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress

== Frequently Asked Questions ==

== Screenshots ==

1. Select the demo you want to import.
2. Install and activate the required plugins.
3. Import the demo.

== Changelog ==

v1.0.3.2

* Fix: Showing all categorized and uncategorized templates.
v1.0.3.1

* Enhancement: Implemented `load_textdomain()` to make the plugin translation ready.
v1.0.3
* New: Enable the export support for Elementor library.
v1.0.2
* Enhancement: Removed `raw_meta` argument which contain all template data. Now we have categorize the template data with arguments `page_builder` & `page_builder_meta`.
* Enhancement: Added support for template export support for post.
v1.0.1
* New: Added Templator Screenshot option in export template popup.
* New: Added Rest API endpoint to `update/templator/v1/` change the template import status.
* Enhancement: After template import redirected template to Elementor editor window to edit the template.
* Enhancement: Move the button `Import from Cloud` to the sibling with the button `Add Media`.
* Enhancement: Improve the API key activation/deactivation popup UI.
v0.1.0
* Initial release