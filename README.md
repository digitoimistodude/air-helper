# Air helper

[![Packagist](https://img.shields.io/packagist/v/digitoimistodude/air-helper.svg?style=flat-square)](https://packagist.org/packages/digitoimistodude/air-helper) ![Tested_up_to WordPress_5.3](https://img.shields.io/badge/Tested_up_to-WordPress_5.3-blue.svg?style=flat-square) ![Compatible_with PHP_7.2](https://img.shields.io/badge/Compatible_with-PHP_7.2-green.svg?style=flat-square) [![Build Status](https://img.shields.io/travis/digitoimistodude/air-helper.svg?style=flat-square)](https://travis-ci.org/digitoimistodude/air-helper)

Air helper provides helpful functions and modifications for WordPress projects. All modifications are preferences of [Dude](https://www.dude.fi). Plugin is meant to be used with our [Air light](https://github.com/digitoimistodude/air-light) theme, but works just fine also without it.

## Table of contents

+ [Features](#features)
  - [Localization and Polylang support](#localization-and-polylang-support)
    * [Registering your strings](#registering-your-strings)
  - [Image lazyloading](#image-lazyloading)
  - [Disabled views](#disabled-views)
  - [Functions](#functions)
    * [Archive related](#archive-related)
    * [Checks](#checks)
    * [Image lazyloading](#image-lazyloading-1)
    * [Localization](#localization)
    * [Pagination](#pagination)
    * [Misc](#misc)
  - [Modified WordPress functionality](#modified-wordpress-functionality)
    * [Admin](#admin)
    * [Security](#security)
    * [Archives](#archives)
    * [The SEO Framework](#the-seo-framework)
    * [Yoast](#yoast)
    * [Commenting](#commenting)
    * [Customizer](#customizer)
    * [Gravity Forms](#gravity-forms)
    * [Imagify](#imagify)
    * [Email Address Encoder](#email-address-encoder)
    * [Mail](#mail)
    * [Media](#media)
    * [Rest API](#rest-api)
    * [TinyMCE](#tinymce)
    * [Misc](#misc-1)
+ [Installing](#installing)
  - [Updates](#updates)
+ [Changelog](#changelog)
+ [Contributing](#contributing)

## Please note before using

Air helper and Air light are used for **development**, so those update very often. By using these code bases, you agree that the anything can change to a different direction without a prior warning.

## Features

### Localization and Polylang support

Air helper adds fallbacks for widely used Polylang functions, so you can use those event if there's no Polylang or multilanguage support needed in project at the time. This saves heck lot of a time when client want's multilanguage support later on.

Refer to section below and [functions](#localization) to find out how to use translated strings.

#### Registering your strings

All strings needs to be registered in one `localization.php` file and passed to `air_helper_pll_register_strings` an an array.

Like this.
```php
add_filter( 'air_helper_pll_register_strings', function() {
  return [
    // General
    'General: Read more' => 'Read more',

    // Footer
    'Footer: Back to top' => 'Back to top',
  ]
} );
```

### Image lazyloading

Air helper adds few additional helpers to work with image lazyloading, but requires also support from the theme in use. In there, there needs to be [js](https://github.com/digitoimistodude/air-light/blob/master/js/src/lazyload.js) and [style](https://github.com/digitoimistodude/air-light/blob/master/sass/features/_lazyload.scss) files.

If plugin is activated after images have been already uploaded, regenerate the thumbnails to get 20x20px image for preview purposes. Regerenation can be done using WP-CLI media regenerate or Regenerate Thumbnails plugin.

Refer to [functions](#image-lazyloading) to find out how to use image lazyloading.

### Disabled views

In most of the client projects there's no need for some views that WordPress creates automatically. Insted of caring about those, show 404 page.

Currently disabled views are:
- archives: tag, category, date, author
- other: search

Enable spesific view back with filter `add_filter( 'air_helper_disable_views_{VIEW}', '__return_false' );` or all views with `remove_action( 'template_redirect', 'air_helper_disable_views' )`.

### Functions

#### Archive related
* `get_posts_array( $args, $return_key )` Get posts in key=>title array.
* `get_post_years()` Get years where there are posts published.
* `get_post_months_by_year( $year, $post_type )` Get months where there are posts in spesific year. Defaults to current year.

#### Checks
* `post_exists_id( $post_id )` Check if post exists by ID.
* `has_content( $post_id )` Check if post has main content. Defaults to current post id.
* `has_children( $post_id, $post_type )` Check if post has child pages. Defaults to current post id.

#### Image lazyloading
* `image_lazyload_div( $attachment_id )` Echo image in lazyloading divs.
* `image_lazyload_tag( $attachment_id )` Echo image in lazyloading tag.

#### Localization
* `ask__( $key, $lang )` Return string by key. Defaults to current language.
* `ask_e( $key, $lang )` Echo string by key. Defaults to current language.
* `asv__( $key, $lang )` Return string by value. Defaults to current language.
* `asv_e( $key, $lang )` Echo string by value. Defaults to current language.

#### Pagination
* `get_next_page_id( $post_id )` Get ID of next page. Defaults to current page.
* `get_prev_page_id( $post_id )` Get ID of previous page. Defaults to current page.

#### Misc
* `get_icons_for_user()` Get list of icons which are available for user. Returns array of icons inside theme's `svg/foruser` directory.
* `wp_parse_args_dimensional( $a, $b )` Similar to wp_parse_args() just extended to work with multidimensional arrays.
* `get_the_sentence_excerpt( $length, $excerpt )` Get excerpt with custom length of sentences. Defaults to three sentences and current post.

### Modified WordPress functionality

Air helper modifies default behaviour of WordPress and various plugins to make it more suitable for customer projects, forcing our preferences and making sure that all the un-neccesary information is hidden or unreachable.

All these modifications can be disabled or altered with hooks. All modifications live under `inc` directory.

To find out how the modification exactly works and how to disable it, search for a comment section from files in `inc` directory with following list item.

#### Admin
* Clean up admin menu from stuff we usually don't need.
* Remove plugins page from admin menu, execpt for users with spesific domain or override in user meta.
* Hide ACF for all users, execpt for users with spesific domain or override in user meta.
* Clean up admin bar.
* Add envarioment marker to adminbar.
* Remove welcome panel.
* Remove some boxes from dashboard.
* Add our news and support widget to dashboard. Also make sure that it is always first in order.
* Remove some notices from dashboard.
* Remove Update WP text from admin footer.
* Hide all WP update nags.

#### Security
* Stop user enumeraton by ?author=(init) urls.
* Add honeypot to the login form. _NB! This does not replace proper security tools in server, consider using Fail2Ban or similar tool._
* Change login failed message.
* Remove hosting provider spesific information from Site Health check.

#### Archives
* Remove archive title prefix. Turn off by using `remove_filter( 'get_the_archive_title', 'air_helper_helper_remove_archive_title_prefix' )`

#### The SEO Framework
* Set default setting values.

#### Yoast
* Set Yoast SEO plugin metabox priority to low.

#### Commenting
* Add a pingback url auto-discovery header for singularly identifiable articles.

#### Customizer
* Remove custom CSS.

#### Gravity Forms
* Allow Gravity Forms to hide labels to add placeholders.

#### Imagify
* Disable adminbar menu.
* Disable .webp conversion.
* Get Imagify API key from .env
* Resize large images and set maximum width.
* Set optimization level to normal.

#### Email Address Encoder
* Hide always all email address encoder notifications.

#### Mail
* Force essential SendGrid settings.
* Force to address in wp_mail function so that test emails wont go to client.
* Show notice if SendGrid is not active or configured.

#### Media
* Custom uploads folder media/ instead of default content/uploads/.

#### Rest API
* Disable REST API users endpoint.

#### TinyMCE
* Show TinyMCE second editor tools row by default.
* Remove some Tiny MCE formats from editor.

#### Misc
* Disable emojicons.
* Strip unwanted html tags from titles.
* Add support for correct UTF8 orderby for post_title and term name (äöå).
* Add instant.page just-in-time preloading script to footer.

## Installing

Download [latest](https://github.com/digitoimistodude/air-helper/releases/latest) version as a zip package and unzip it to your plugins directiry.

Or install with composer, running command `composer require digitoimistodude/air-helper` in your project directory or add `"digitoimistodude/air-helper":"dev-master"` to your composer.json require.

### Updates

Updates will be automatically distributed when new version is released.

## Changelog

Changelog can be found from [releases page](https://github.com/digitoimistodude/air-helper/releases).

## Contributing

If you have ideas about the plugin or spot an issue, please let us know. Before contributing ideas or reporting an issue about "missing" features or things regarding to the nature of that matter, please read [Please note](#please-note-before-using) section. Thank you very much.
