# Air helper

[![Build Status](https://img.shields.io/travis/digitoimistodude/air-helper.svg?style=flat-square)](https://travis-ci.org/digitoimistodude/air-helper)

Air helper brings personal preferences of Digitoimisto Dude to Air based themes and extends it with basic WooCommerce layout modifications.

[Digitoimisto Dude Oy](https://www.dude.fi) is a Finnish boutique digital agency in the center of Jyväskylä.

## Table of contents

1. [Please note before using](#please-note-before-using)
2. [License](#license)
3. [Features](#features)
    1. [WordPress & functions](#wordpress--functions)
    2. [Disabled features](#disabled-features)
    3. [WooCommerce support](#woocommerce-support)
4. [Contributing](#contributing)

### Please note before using

Air helper an Air is used for **development**, so those update very often. By using these code bases, you agree that the anything can change to a different direction without a warning.

### License

Air helper is licensed with [The MIT License (MIT)](http://choosealicense.com/licenses/mit/) which means you can freely use this plugin commercially or privately, modify it, or distribute it, but you are forbidden to hold Dude liable for anything, or claim that what you do with this is made by us.

### Features

#### WordPress & functions

* Automatic feed links
* WordPress managed title tag
* WP updates nag hidden
* All times and local units in Finnish
* Custom uploads folder `media/` instead of default `content/uploads/`
* Force to-address in `wp_mail` at development and staging environments
* Get SendGrid credentials from `.env`

#### Disabled features

* WordPress admin bar for logged in users
* Emojicons
* REST API users endpoint

#### WooCommerce support

Plugin will detect if current theme support WooCommerce and if so, loads the most basic overrides and makes Air based theme compatible with WooCommerce. This feature is built for starting point only.

### Contributing

If you have ideas about the plugin or spot an issue, please let us know. Before contributing ideas or reporting an issue about "missing" features or things regarding to the nature of that matter, please read [Please note](#please-note-before-using) section. Thank you very much.
