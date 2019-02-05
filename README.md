# Air helper

[![Packagist](https://img.shields.io/packagist/v/digitoimistodude/air-helper.svg?style=flat-square)](https://packagist.org/packages/digitoimistodude/air-helper) ![Tested_up_to WordPress_5.0.3](https://img.shields.io/badge/Tested_up_to-WordPress_5.0.3-blue.svg?style=flat-square) ![Compatible_with PHP_7.2](https://img.shields.io/badge/Compatible_with-PHP_7.2-green.svg?style=flat-square) [![Build Status](https://img.shields.io/travis/digitoimistodude/air-helper.svg?style=flat-square)](https://travis-ci.org/digitoimistodude/air-helper)

Air helper brings useful functions and modifications to WordPress projects, from where many of those are preferences of Digitoimisto Dude. Plugin is meant to be used with our [Air](https://github.com/digitoimistodude/air) theme.

[Digitoimisto Dude Oy](https://www.dude.fi) is a Finnish boutique digital agency in the center of Jyväskylä.

## Table of contents

1. [Please note before using](#please-note-before-using)
2. [License](#license)
3. [Features](#features)
    1. [Functions](#functions)
    2. [Modified WordPress functionality](#modified-wordpress-functionality)
    3. [Localization and Polylang support](#localization-and-polylang-support)
    4. [Post meta revisions](#post-meta-revisions)
    5. [WooCommerce support](#woocommerce-support)
4. [Installing](#installing)
    1. [Updates](#updates)
6. [Changelog](#hangelog)
7. [Contributing](#contributing)

### Please note before using

Air helper and Air is used for **development**, so those update very often. By using these code bases, you agree that the anything can change to a different direction without a warning.

### License

Air helper is licensed with [The MIT License (MIT)](http://choosealicense.com/licenses/mit/) which means you can freely use this plugin commercially or privately, modify it, or distribute it, but you are forbidden to hold Dude liable for anything, or claim that what you do with this is made by us.

### Features

#### Functions

Air helper introduces few helper functions to make your life easier.

* Check if post exists, `post_exists_id( $post_id )`
* Check if post has content, `has_content( $post_id )`
* Check if post has childs, `has_children( $post_id, $post_type )`
* Get array of svg icons available for user in `svg/foruser` directory with `get_icons_for_user()`
* Get key=>value list of pages, `get_posts_array( $args_for_get_posts, $field_to_use_as_key )`
* Use [Carbon Fields](https://carbonfields.net) conditional check with [Polylang](https://polylang.pro/) with `dude_get_crb_pll_id( $condition_from_cfb, $post_id = 0, $condition_operator = '=' )`
* Use post meta preview with [Carbon Fields](https://carbonfields.net) field types not saving to one row with `dude_get_post_meta( $post_id, $key, $single )` 
* Get previous page ID `get_prev_page_id( $id = 0)`
* Get next page ID `get_next_page_id( $id = 0)`
* Get years where are posts `get_post_years( $post_type = 'post' )`
* Get months by year where are posts `get_post_months_by_year( $year = date( 'Y' ), $post_type = 'post' )`

#### Modified WordPress functionality

Air helper also modifies default WordPress behavior to make it more suitable for customer projects, forcing our personal preferences and making sure that all the un-neccesary information is hidden or unreachable.

All of these modifications can be altered and/or disabled with hooks. Please see [`inc/hooks.php`](https://github.com/digitoimistodude/air-helper/blob/master/inc/hooks.php) for references.

* In development and staging envarioments allow outgoing emails only if there's administrator, editor or author with recipients address
* Remove archive title prefix
* Disable emojicons
* Clean up admin bar and menu from unused and/or non client friendly things
* Remove plugins page from admin menu, execpt for users with spesific domain or user meta row
* Hide WordPress core, plugins and themes updates nag
* Add a pingback url auto-discovery header for singularly identifiable articles
* Disable REST API users endpoint
* Remove admin bar in front-end when not on development envarioment
* Add envarioment marker to admin bar
* Remove the additional CSS section from customizer
* Show TinyMCE second editor tools row by default
* Strip unwanted html tags from titles and menu items
* Allow Gravity Forms to hide labels to add placeholders
* Set Yoast SEO plugin metabox priority to low
* Remove update WordPress text from admin footer
* Change default uploads folder to `media`
* Force disablation of month- and year-based folders
* Get SendGrid credentials from `.env`
* Disable some views by default.
    - archives: tag, category, date, author
    - other: search

#### Localization and Polylang support

Air helper adds fallbacks for widely used Polylang functions, so you can use those event if there's no Polylang or multilanguage support needed in project at the time. This saves heck lot of a time when client want's multilanguage support later on.

These functionalities are quite direct copies from [Aucor starter](https://github.com/aucor/aucor-starter) because why invent the wheel again ;) Please see their [documentation](https://github.com/aucor/aucor-starter#71-localization-polylang) about localization helpers.

#### Post meta revisions

WordPress does not revision post meta by default and Air helper makes this possible if needed. It also adds possibility to preview meta changes before publishing or updating posts.

At the moment post meta isn't revisioned automatically, you need to register your choice of meta fields for revisioning manually.

```
function air_helper_example_meta_revisions( $keys ) {
    $keys['_subtitle'] = true; // key is the name of your meta field
    $keys['_location'] = 'map'; // if you use CRB and field is map, tell it
    $keys['_persons'] = 'complex'; // and same goes if fiels type is complex
    return $keys;
}
add_filter( 'wp_post_revision_meta_keys', 'air_helper_example_meta_revisions' );
```

Meta revisions are served only when using `get_post_meta` for simple key=>value fields or `dude_get_post_meta` for Carbon Fields complexed fields.

#### WooCommerce support

Plugin will detect if current theme support WooCommerce and if so, loads the most basic overrides and makes Air based theme compatible with WooCommerce. This feature is built for starting point only.

### Installing

Download [latest](https://github.com/digitoimistodude/air-helper/releases/latest) version as a zip package and unzip it to your plugins directiry. 

Or install with composer, running command `composer require digitoimistodude/air-helper` in your project directory or add `"digitoimistodude/air-helper":"dev-master"` to your composer.json require.

#### Updates

Updates will be automatically distributed when new version is released.

### Changelog

Changelog can be found from [releases page](https://github.com/digitoimistodude/air-helper/releases).

### Contributing

If you have ideas about the plugin or spot an issue, please let us know. Before contributing ideas or reporting an issue about "missing" features or things regarding to the nature of that matter, please read [Please note](#please-note-before-using) section. Thank you very much.
