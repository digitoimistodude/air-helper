# Air helper

[![Packagist](https://img.shields.io/packagist/v/digitoimistodude/air-helper.svg?style=flat-square)](https://packagist.org/packages/digitoimistodude/air-helper) ![GitHub contributors](https://img.shields.io/github/contributors/digitoimistodude/air-helper.svg) [![Build Status for PHP 8.3](https://github.com/digitoimistodude/air-helper/actions/workflows/php8.3.yml/badge.svg)](https://github.com/digitoimistodude/air-helper/actions/workflows/php8.3.yml) [![Build Status for PHP](https://github.com/digitoimistodude/air-helper/actions/workflows/php.yml/badge.svg)](https://github.com/digitoimistodude/air-helper/actions/workflows/php.yml)

Air helper provides helpful functions and modifications for WordPress projects. All modifications are preferences of [Dude](https://www.dude.fi). The plugin is meant to be used with our [Air light](https://github.com/digitoimistodude/air-light) theme, but works just fine also without it.

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
    * [ACF](#acf)
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

## Development workflow

1. Git clone the repository to your local machine
2. Create a fork
3. Create a new branch for each new feature (Height task ID for Dude staff)
4. Push your changes to your fork

### Release cycle workflow

1. Test changes thoroughly in multiple projects by symlinking the dev version of air-helper to the projects:

  ```bash
  # Project 1
  rm -rf /var/www/project1/content/plugins/air-helper
  ln -s /var/www/air-helper /var/www/project1/content/plugins/

  # Project 2
  rm -rf /var/www/project2/content/plugins/air-helper
  ln -s /var/www/air-helper /var/www/project2/content/plugins/

  # Project 3
  rm -rf /var/www/project3/content/plugins/air-helper
  ln -s /var/www/air-helper /var/www/project3/content/plugins/
  ```

2. Run `composer validate` to check if the composer.json is valid
3. After verifying that the changes work as expected, send a pull request to the original repository
4. Wait for review and merge

#### For Dude staff

5. Update versions in air-helper.php, package.json and CHANGELOG.md
6. Create a release on GitHub with the same version number as the one in the `air-helper.php` file
7. The release will automatically be published to Packagist

## Features

### Localization and Polylang support

Air helper adds fallbacks for widely used Polylang functions, so you can use those events if there's no Polylang or multilanguage support needed in project at the time. This saves heck lot of a time when client want's multilanguage support later on.

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

#### REST API support for string translations

By default, the string translation functions like `ask__()` does not work as intended when run inside a REST request, because Polylang does not support it. You can enable support by setting a `lang` parameter to your REST request and enabling the feature with hook:

```php
add_filter( 'air_helper_pll_enable_rest', '__return_true' );
```

### Image lazyloading

Air-helper supports [tuupola/lazyload](https://github.com/tuupola/lazyload) (legacy), [vanilla-lazyload](https://github.com/verlok/vanilla-lazyload) (legacy) and [native-lazyload](https://caniuse.com/?search=lazy) (native, current). [Air-light](https://github.com/digitoimistodude/air-light) version prior 6.1.8 (2020-10-20) had support for lazyload.js provided by [tuupola/lazyload](https://github.com/tuupola/lazyload) which is still legacy-supported by air-helper, but no longer provided by [air-light](https://github.com/digitoimistodude/air-light) theme.

Refer to [functions](#image-lazyloading-1) to find out how to use image lazyloading.

### Disabled views

In most of the client projects there's no need for some views that WordPress creates automatically. Instead of caring about those, show 404 page.

Currently disabled views are:
- archives: tag, category, date, author
- other: search

Enable specific view back with filter `add_filter( 'air_helper_disable_views_{VIEW}', '__return_false' );` or all views with `remove_action( 'template_redirect', 'air_helper_disable_views' )`.

### Functions

#### Archive related
* `get_posts_array( $args, $return_key )` Get posts in key=>title array.
* `get_post_years()` Get years where there are posts published.
* `get_post_months_by_year( $year, $post_type )` Get months where there are posts in a specific year. Defaults to current year.

#### Checks
* `post_exists_id( $post_id )` Check if post exists by ID.
* `has_content( $post_id )` Check if post has main content. Defaults to current post id.
* `has_children( $post_id, $post_type )` Check if post has child pages. Defaults to current post id.

#### Image lazyloading
* `vanilla_lazyload_div( $attachment_id, $fallback )` Echo image in lazyloading div.
* `vanilla_lazyload_tag( $attachment_id, $fallback )` Echo image in lazyloading img tag.
* `native_lazyload_tag( $attachment_id, $args )` Echo image in native lazyloading tag

Fallback is optional. By default fallback is default featured image from theme settings.

Args is optional. Its an array that contains "fallback", "sizes" and "class". Class can be set to give the image tag a specific class, if not set no class will be given.

If you want to get lazyloading div or tag as a string, you may prefix functions with `get_`.

#### Localization
* `ask__( $key, $lang )` Return string by key. Defaults to current language.
* `ask_e( $key, $lang )` Echo string by key. Defaults to current language.
* `asv__( $key, $lang )` Return string by value. Defaults to current language.
* `asv_e( $key, $lang )` Echo string by value. Defaults to current language.

#### Pagination
* `get_next_page_id( $post_id )` Get ID of next page. Defaults to current page.
* `get_prev_page_id( $post_id )` Get ID of previous page. Defaults to current page.

#### Misc
* `get_icons_for_user( $args)` Get list of icons which are available for user. Returns array of icons from defined theme directory (default `svg/foruser/`).
* `wp_parse_args_dimensional( $a, $b )` Similar to wp_parse_args() just extended to work with multidimensional arrays.
* `get_the_sentence_excerpt( $length, $excerpt )` Get excerpt with custom length of sentences. Defaults to three sentences and current post.
* `get_primary_category( $post_id )` Get primary category for defined or current post.

### Modified WordPress functionality

Air helper modifies default behaviour of WordPress and various plugins to make it more suitable for customer projects, forcing our preferences and making sure that all the unnecessary information is hidden or unreachable.

All these modifications can be disabled or altered with hooks. All modifications live under `inc` directory.

To find out how the modification exactly works and how to disable it, search for a comment section from files in `inc` directory with following list item.

#### Admin
* Clean up admin menu from stuff we usually don't need.
* Remove plugins page from admin menu, except for users with specific domain or override in user meta.
* Hide ACF for all users, except for users with specific domain or override in user meta.
* Clean up admin bar.
* Add environment marker to admin bar.
* Remove welcome panel.
* Remove some boxes from dashboard.
* Add our news and support widget to dashboard. Also, make sure that it is always first in order.
* Remove some notices from dashboard.
* Remove Update WP text from admin footer.
* Hide all WP update nags.

#### Security
* Stop user enumeration by ?author=(init) urls.
* Add honeypot to the login form. _NB! This does not replace proper security tools in server, consider using Fail2Ban or similar tool._
* Change login failed message.
* Remove hosting provider specific information from Site Health check.

#### ACF
* Hide ACF for all users, except the ones that have certain domain in their email address or user meta `_airhelper_admin_show_acf` with value of `true`
* Try to activate the pro version automatically if `ACF_PRO_KEY` is defined in .env file

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
* Disable admin bar menu.
* Disable .webp conversion.
* Get Imagify API key from .env
* Resize large images and set maximum width.
* Set optimization level to normal.

#### Email Address Encoder
* Hide always all email address encoder notifications.

#### Mail
* Force essential SendGrid settings.
* Force to address in wp_mail function so that test emails won't go to client.
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

Download [the latest](https://github.com/digitoimistodude/air-helper/releases/latest) version as a zip package and unzip it to your plugins directory.

Or install with composer, running command `composer require digitoimistodude/air-helper` in your project directory or add `"digitoimistodude/air-helper":"dev-master"` to your composer.json require.

### Updates

Updates will be automatically distributed when a new version is released.

## Changelog

Changelog can be found from [releases page](https://github.com/digitoimistodude/air-helper/releases).

## Contributing

If you have ideas about the plugin or spot an issue, please let us know. Before contributing ideas or reporting an issue about "missing" features or things regarding the nature of that matter, please read [Please note](#please-note-before-using) section. Thank you very much.
