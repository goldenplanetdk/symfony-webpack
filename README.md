<img align="right" src="https://cloud.githubusercontent.com/assets/3078595/22329543/5f1dae0c-e3ca-11e6-82d1-2e64e8b94703.png">

[![Build Status](https://travis-ci.org/goldenplanetdk/symfony-webpack.svg?branch=master)](https://travis-ci.org/goldenplanetdk/symfony-webpack)
[![Coverage Status](https://coveralls.io/repos/github/goldenplanetdk/symfony-webpack/badge.svg)](https://coveralls.io/github/goldenplanetdk/symfony-webpack)

<br>

[Installation](#installation)

[Usage](#usage)

[Run](#compile)

[Documentation](https://github.com/goldenplanetdk/symfony-webpack/wiki)

<img type="clear-floated-image" src="https://cloud.githubusercontent.com/assets/3078595/22329385/b18b9218-e3c9-11e6-99ce-db83b05480aa.png" src-origin="http://placehold.it/2000x1/fff/fff">

Installation
============

```shell
composer require goldenplanetdk/symfony-webpack
```

Add this bundle to your array of instantiated bundles in `AppKernel` `registerBundles()`:

```php
new GoldenPlanet\WebpackBundle\GoldenPlanetWebpackBundle(),
```

Generate a basic `symfony-webpack.config.js`:

```
app/console webpack:setup
```

Usually you will want to write your own webpack config. The one that is created with this command is mainly used for testing purposes of this repository.

<br>

Usage
===

Requiring scripts and stylesheets in `twig`
----

Single entry point:

```twig
<link rel="stylesheet" href="{{ webpack_asset('@acme_hello/script.js', 'css') }}">
<script defer src="{{ webpack('@acme_hello/script.js') }}"></script>
```

*Note: here `@acme_hello` is equal to `@AcmeHelloBundle/Resources/assets`*

Multiple entry points:

```twig
{% webpack_javascripts
	'@acme_hello/main.js'
	'@acme_hello/another-entry-point.js'
%}
	<script defer src="{{ asset_url }}"><script>
{% end_webpack_javascripts %}
```

```twig
{% webpack_stylesheets
	'@acme_hello/main.js'
	'@acme_hello/another-entry-point.js'
%}
	<link rel="stylesheet" href="{{ asset_url }}"><script>
{% end_webpack_stylesheets %}
```

To avoid having a `link` element with an empty `href` in the DOM when the script may possibly not emit a stylesheet, test the value returned from `webpack_asset` before inserting the `link` element:

```twig
{% set cssUrl = webpack_asset('@acme_hello/script.js', 'css') %}
{% if cssUrl %}
	<link rel="stylesheet" href="{{ cssUrl }}">
{% endif %}
```

Requiring other resource types in `twig`
---

You can pass any kind of resources to webpack with `webpack_asset` function for single entry point:

```twig
<img src="{{ webpack_asset('@AcmeHelloBundle/Resources/public/images/background.jpg') }}">
```

Or with `webpack_assets` tag for multiple entry points:

```
<ul class="nav nav-pills social-icons">
	{% webpack_assets
		'@AcmeHelloBundle/Resources/public/images/facebook.jpg'
		'@AcmeHelloBundle/Resources/public/images/twitter.jpg'
		'@AcmeHelloBundle/Resources/public/images/youtube.jpg'
	%}
		<li>
			<img src="{{ asset_url }}">
		</li>
	{% end_webpack_assets %}
</ul>
```

Requiring within scripts and stylesheets
---

Inside `script.js`:

```js
import {Person} from './models/person.ts';

require('./other-script.js');
```

Inside `stylesheet.css`, `less`, `sass` or `stylus`:

```css
body {
    background: url('~@AcmeBundle/Resources/images/bg.jpg');
}
```

Requiring with custom aliases
----

Assuming that you've [configured an `images` alias](#aliases) in `config.yml`: 

```twig
<img src="{{ webpack_asset('@acme_images/lion.png') }}"/>
```

```css
body{ background: url('~@acme_images/lion.png') }
```

<br>

Compile
===

Using Symfony `app/console` to run webpack commands
---

Compile for dev environment:

```bash
app/console webpack:compile
```

Watch for changes and compile

```bash
app/console webpack:watch
```

Watch for changes, compile and automatically reload browser tab(s)

```bash
app/console webpack:dev-server
```

Compile as part of deployment into production environment:

```bash
app/console webpack:compile --env=prod
```

<br>

Documentation
===

Full documentation is available at [Wiki pages of this repository](https://github.com/goldenplanetdk/symfony-webpack/wiki)
