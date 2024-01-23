# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 2.19.6
### Added
- Security: Prevent access to plugins

### Changed
- Improve plugin blacklist feature

## 2.19.5
### Added
- Security: Add plugin blacklist feature
- Add .editorconfig

## 2.19.4
### Fixed
- `air_helper_helper_remove_admin_menu_links` function causing errors if on admin page where menu does not exist. admin-post.php for example.


## 2.19.3
### Fixed
- Fixed translation override strings not working if not logged in

## 2.19.2
### Added
- Filter `air_helper_acf_groups_to_warn_about` to allow plugins disable local josn warning for specific field groups

## 2.19.1
### Fixed
- Fix release being stuck with a mismatch in composer.json
- Bump tested version to 6.2.2

## 2.19.0
### Added
- Show warning if ACF has field groups that are not saved in the local json
- If Polylang is not active, add settings page to allow overriding localization strings
- Added filter `air_helper_site_has_care_plan` to allow turning care plan off if needed

### Fixed
- After `edit_user_created_user` action do not add `air_helper_helper_force_mail_to` filter if in production
- Replace only whole word in media library, see #51 props @ronilaukkarinen 

## 2.18.0
### Changed
- Way of forcing email in development and staging. Instead of role based allowance, new allowances are domain based. Modify the array of allowed domains with new `air_helper_mail_to_allowed_domains` filter.

### Removed
- `air_helper_helper_mail_to_allowed_roles` filter

### Fixed
- `get_native_lazyload_tag` function calling fallback with correct `$args`, props @jennitahva

## 2.17.0
### Added
- Dismiss Filebird version 5.0.8 YayMail plugin upsell
- If core/list block is allowed, allow also core/list-item. This fixes change that WP 6.1 introduced, without it you cannot add new list item to list
- Disable global svg filter rendering
- Force anti-spam honeypot on all Gravity Forms forms
- Menu edit link added to top level of dashboard sidebar. This does not affect users that have activated plugin before version 2.17.0
- Themes link removed for users without meta override or email from specific domain. This does not affect users that have activated plugin before version 2.17.0
- Add native lazy loading for fallback imgs

### Changed
- Disable autoload for notification dismissal options
- Unified the way to check if current user should see some functionalities

### Fixed
- On editor view, add padding-bottom to sidebar in order to make space for HS beacon

## 2.16.2
### Changed
- PUC plugin json details changes to Github proxy

## 2.16.0
### Fixed
- Fix warning when overriding Hide ACF for all users with user meta. Props @villekujansuu

### Changed
- Priority of `air_helper_login_honeypot_check` on `authenticate` hook to 29 in order it be runned before Simple History runs with priority 30. This prevents some login errors from flooding the Simple History.

### Added
- Way to write into centralized log when login fails
- On `air_helper_login_honeypot_check` do centralized login logging if honeypot fails
- Redirect Simple History user_unknown_login_failed messages to centralized login log

## 2.15.0
### Added
- Disble cache for Relevanssi related posts output on development environment for easier development

### Fixed
- Change the priority for correct UTF8 orderby for term name for better compatibility with other plugins

## 2.14.2
### Fixed
- Fixed get_the_sentence_excerpt function to work as intended.

## 2.14.1
### Added
* Forced Mailgun's tracking options to false and disabled the selects

## 2.14.0
### Added
* New server "slash" to care plan allowed hostnames
* `native_lazyload_tag` funtion to get a img tag that uses browser native lazyloading.

### Removed
* Removed custom settings related files. Legacy support can be found in air-helper-legacy-custom-settings repository. All new custom setting related stuff can be found in air-setting-groups repository

## 2.13.0
### Added
* Removed general YITH plugin widgets from dashboard
* `get_first_page_id` function to get first page id.

### Fixed
* Sorting in pagination.php functions.

### Removed
* Removed caching from `get_prev_page_id` and `get_next_page_id` functions.

## 2.12.1
### Changed
- Allow disabling post type check on lazyload, this is needed sometimes on MU installations

## 2.12.0: 2021-11-18
### Updated
- Updated ACF Pro license key fetching, to use new ACF_PRO_LICENSE constant.

### Fixed
- Fixed the_block_content function when using a string as post_id.

### Changed
- Changed instantpage script to load from plugin instead of outside source.

### Added
- Remove written by from Yoast enhanced data if author email cointains specific domain.

## 2.11.0 & 2.11.1: 2021-09-23
### Added
- Remove plugins page from multisite admin menu
- Email delivery support for MailHog when in development environment
- If ACF Pro key is defined in .env file, try to activate the license automatically

### Updated
- Email delivery `is_plugin_active` checks with more robust checks that do not fail in frontend

### Fixed
- Regular expression for archive prefix removal

## 2.10.3: 2021-08-19
### Added
- Support for REST requests to string translation functions

## 2.10.2: 2021-08-04
### Fixed
- Polylang support for custom setting group block editor checks

## 2.10.1: 2021-08-03
### Added
- Before forcing Mailgun, test that it's API usage is set

## 2.10.0: 2021-08-03
### Added
- Support for official Mailgun plugin on email delivery settings forcing

### Changed
- Priority loaded file forcing email delivery settings renamed from sendgrid.php to mail-delivery.php
- Started using own dedicated API key in dashboard help widget data requests, falls back to using sending key if new one is not defined

## 2.9.0: 2021-08-02
### Added
- Allow user to select "No icon" when ACF icon select field is set to "Allow null"
- Add custom settings functionality
- Increase body padding for the HS widget not to override paging controls
- Add helper function to output block content
- Use block editor in custom setting groups

## 2.8.0: 2021-05-18
### Added
- Disabled Imagify backup by default

### Changed
- Removed Site Health widget from dashboard
- Removed Redis Object Cache widget from dashboard
- Remove PHP nag widget from dashboard

## 2.7.1: 2021-02-22
### Fixed
- Prefixed `remove_recent_comments_style` function properly
- Do not remove comment widget styles if air helped activated before version 2.6.0

## 2.7.0: 2021-02-22
### Added
- Notice if The SEO Framework options have not been reseted to our standards

### Fixed
- Hiding some The SEO Framework features

## 2.6.0: 2021-02-19
### Changed
- Allow REST API users endpoints if user is logged in and can edit_posts
- Get icons with previews from any theme path
- Changed `WP_ENV` checks to new core function `wp_get_environment_type` introduced in 5.5

### Fixed
- Escape outputting localization functions ask_e, asv_e and pll_e return
- On `widgets_init` remove recent comments style
- Removal of widgets.php from admin menu

## 2.5.0: 2020-11-23
### Added
- `get_primary_category` supports now custom taxonomies

### Fixed
- Vanilla lazyload image fallbacks

## 2.4.0: 2020-10-21
### Added
- Hook to add custom styles for vanilla lazyload

### Fixed
- Vanilla lazyload fallbacks
- Semantic versioning for plugin version (new features)

## 2.3.1: 2020-10-20
### Added
- Introducing support for vanilla-lazyload

### Fixed
- Lazyload img accessibility

## 2.3.0: 2020-10-02
### Added
- Image lazyload try to get fallback from theme settings if not defined
- Remove unnecessary type attributes to suppress HTML validator messages

### Changed
- Do not show Helpscout notice if not configured
- Move emoji disable to priority fly

### Removed
- noscript fallback from lazyloading images

## 2.2.1
### Fixed
- `get_primary_category` function declaration

## 2.2.0
### Added
- Option to inject styles to lazyloaded images
- `get_primary_category` function
- Alt tag for lazyloaded img tag if alternative text exists

## 2.1.5
### Fixed
- Support for correct UTF8 orderby for post_title and term name (äöå) hooks now always in, before in some rare occasion
- Bump instant.page script to version 5.1.0

## 2.1.3-2.1.4
### Added
- Fix aria-hidden in pre-loaded divs, update PHP Code Sniffer excludes

## 2.1.2
### Added
- Fix accessibility issues, add missing alt tags and aria-hidden for loading image

## 2.1.0
### Added
- Add fallback support for lazyloaded images

## 2.0.1
### Added
- Allow filtering air_helper_activated_at_version for MU support

### Fixed
- Remove double declaration of EAE_DISABLE_NOTICES
- Update upload options always unless in production and already updated

## 2.0.0
2.0.0 release is a rewrite of the plugin.

Functions and hooks are now all separated into smaller files containing things related to the same specific functionalities. Internal hooks are added to provide more ways to customize how Air helper works. Also caching to especially expensive functions are added.

Version 2.0.0 breaks backward compatibility as it drops support for WooCommerce, Carbon Fields and Post Meta Revisions. Other changes do not break backward compatibility. Sites using WooCommerce or Carbon Fields should install legacy support plugins that do contain the same functionalities than previous versions of Air helper.

For upkeep customers, Helpscout beacon is added and it requires HS_BEACON_ID in .env file.

## 1.12.2
### Added
- Hide ExactMetrics version 6.0.0 onboarding

## 1.12.1
### Fixed
- Registration of our lazyload preload image size
- Lazyload imagesize get

## 1.12.0
### Added
- Hook `get_{$post_type}_years_result` for function get_post_years
- Hook `get_{$post_type}_years_result_key` for function get_post_years

## 1.11.1
### Fixed
- fix img lazyload data attributes

## 1.11.0
### Added
- Lazyload
- If dev env, show database host

### Fixed
- Hide ACF for all users, execpt for users with spesific domain or override in user meta

## 1.10.1
### Fixed
- Load order for Carbon Field related things, `air_helper_fly` prio changed to 998

## 1.10.0
### Added
- `get_the_sentence_excerpt` function
- Remove hosting provider spesific details from site health check

### Fixed
- Honeypot on WooCommerce login

## 1.9.0: 2019-03-25
### Added
- Simple honeypot to login form

## 1.8.1: 2019-03-13
### Added
- Force from address in staging

## 1.8.0: 2019-03-01
### Added
- Priority hooks file loaded in `init` hook with priority `5`
- User enumeration stop
- Change login failed message to more generic one

### Removed
- PHP 5.6 Travis check

## 1.7.3: 2019-01-08
### Fixed
- Function dude_get_post_meta set $single default as false

## 1.7.2: 2019-01-04
### Fixed
- Tiny MCE (classic editor) `tiny_mce_before_init` hook which caused white broken classic editor with WP 5.0 in some situations

## 1.7.1: 2018-12-21
### Added
- Remove SendGrid, GADWP and Email Address Encored notifications from dashboard

### Changed
- Do not trust deactivation hook on air_helper_deactivated_without_version nag save, maybe do it in admin_init

## 1.7.0: 2018-12-05
### Added
- Introduce new dashboard widget to show sheculed maintenances, news/updates from vendor and for sending new support requests. Only visible if site is hosted on Dude's servers
- Remove some dashboard widgets for having more simpler dashboard
- Do not show welcome message after core update
- Introcude `wp_parse_args_dimensional` function which is similar to wp_parse_args() just extended to work with multidimensional arrays
- At admin menu env, show when latest deploy was made to staging
- Remove some unused Tiny MCE formats from editor

### Fixed
- Improved `get_icons_for_user` function icon name parsing

### Changed
- Force mail to address from hook with koodarit@dude.fi default, not from admin email option

## 1.6.0: 2018-09-13
### Added
* Allow overriding plugins page admin menu removal from user meta with meta key `_airhelper_admin_show_plugins`
* Save plugin version where it was activated first time, this allows us to do some tricks that does not affect old projects
* Disable tag, category, date, author archives and search by default
* Introduced function `get_post_years` to get years where there are posts
* Intriduced function ` get_post_months_by_year` to get months where there are posts in spesific year
* Disable "Try Gutenberg" notification
* Hide ACF admin menu item if also plugins item is hidden

### Fixed
* `post_exists_id` function when passing zero or empty string as a value

## 1.5.6: 2018-05-03

See the [GitHub](https://github.com/digitoimistodude/air-helper/releases) releases for changelog for this and previous versions. Work in progress to merge the previous versiosn to this changelog.
