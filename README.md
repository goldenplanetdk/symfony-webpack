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

Add to `AppKernel`:

```php
new GoldenPlanet\WebpackBundle\GoldenPlanetWebpackBundle(),
```

Generate `symfony-webpack.config.js` and install dependencies:

```
app/console webpack:setup
```

<br>

Usage
===

Scripts and Stylesheets
----

Single entry point (`.js`, `.ts`, `.coffee` etc.) in `twig` templates:

```twig
<link rel="stylesheet" href="{{ webpack_asset('@acmeHello/script.js', 'css') }}">
<script defer src="{{ webpack_asset('@acmeHello/script.js') }}"></script>
```

*Note: here `@acmeHello` is equal to `@AcmeHelloBundle/Resources/assets`*

Multiple entry points:

```twig
{% webpack js
	'@acmeHello/main.js'
	'@acmeHello/another-entry-point.js'
%}
	<script defer src="{{ asset_url }}"><script>
{% end_webpack %}
```

```twig
{% webpack css
	'@acmeHello/main.js'
	'@acmeHello/another-entry-point.js'
%}
	<link rel="stylesheet" href="{{ asset_url }}"><script>
{% end_webpack %}
```

To avoid having a `link` element with an empty `href` in the DOM when the script may possibly not emit a stylesheet, test the value returned from `webpack_asset` before inserting the `link` element:

```twig
{% set cssUrl = webpack_asset('@acmeHello/script.js', 'css') %}
{% if cssUrl %}
	<link rel="stylesheet" href="{{ cssUrl }}">
{% endif %}
```

Named commons chunk
---

In webpack configuration it is allowed to put commonly used libraries (shared dependencies) in a separate file, while still having reference to the same singleton library when using `require`. For example, to put `jquery` and `lodash` to a separate file (a commons chunk) add following to your `symfony-webpack.config.js`:

```js
module.exports = function makeWebpackConfig(symfonyOptions) {

	config.entry = symfonyOptions.entry;
	config.entry['jquery-and-lodash'] = ['jquery', 'lodash'];
	
	// ...
		
	config.plugins.push(
		new webpack.optimize.CommonsChunkPlugin({
			names: [
				'jquery-and-lodash', // match entry point name(s)
			],
		}),		
	)
}
```

Then add the script that will load the common libs before any other script that may depend on it. 
Use the `webpack_named_asset` function to inject the actual compiled asset path:

```twig
<script defer src="{{ webpack_named_asset('jquery-and-lodash') }}"><script>
```

Commons chunk may contain other type of assets:

```twig
<link rel="stylesheet" href="{{ webpack_named_asset('shared', 'css') }}">
```

The rendered output of above in production mode will be something like:

```html
<script src="/compiled/jquery-and-lodash.64ff80bf.c95f999344d5b2777843.js"></script>
<link rel="stylesheet" href="/compiled/shared.0a8efeb2b0832928e773.css">
```

Webpack can also be configured to determine the commonly used libraries in multiple entry points automatically. Support for these is planned. 

Other resource types
---

You can pass any kind of resources to webpack with `webpack_asset` function for single entry point:

```twig
<img src="{{ webpack_asset('@AcmeHelloBundle/Resources/public/images/background.jpg') }}">
```

Or with `webpack` tag for multiple entry points:

```
<ul class="nav nav-pills social-icons">
	{% webpack
		'@AcmeHelloBundle/Resources/public/images/facebook.jpg'
		'@AcmeHelloBundle/Resources/public/images/twitter.jpg'
		'@AcmeHelloBundle/Resources/public/images/youtube.jpg'
	%}
		<li>
			<img src="{{ asset_url }}">
		</li>
	{% end_webpack %}
</ul>
```

Requiring within scripts and stylesheets
---

Inside `script.js`:

```js
import URI from 'urijs';
import {Person} from './models/person';

require('./other-script.ts');
```

Inside `stylesheet.css`, `less`, `sass` or `stylus`:

```css
body {
    background: url('~@AcmeBundle/Resources/images/bg.jpg');
}
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

Compile as part of deployment in production environment:

```bash
app/console webpack:compile --env=prod
```

<br>

Documentation
===

Full documentation is available at [Wiki pages of this repository](https://github.com/goldenplanetdk/symfony-webpack/wiki)
