# BigBox Template Loader Component

Add sanity to WordPress templating systems.

## Table Of Contents

* [Installation](#installation)
* [Basic Usage](#basic-usage)
* [Contributing](#contributing)

## Installation

The best way to use this component is through Composer:

```BASH
composer require bigboxwc/wp-template-loader
```

## Basic Usage

Initializing the core template filtering should happen during runtime. This will route WordPress's core template files ([template hierarchy](https://developer.wordpress.org/themes/basics/template-hierarchy/) to your chosen layout directory (`/resources/views/layout`) by default.

```PHP
( new \BigBoxWC\WP_Template_Loader\Loader() )::watch();
```

Instead of loading `/single.php`, `/resources/views/layout/single.php` is loaded instead.

### Defining Custom Directories

The default view directory is `/resources/views` with a `layout` and `partials` directory relative to that. To change these pass them to the `Loader` instantiation.

```PHP
( new \BigBoxWC\WP_Template_Loader\Loader( [
	'base_path'    => 'resources/templates',
	'layout_dir'   => 'wp-pages',
	'partials_dir' => 'parts',
] ) )::watch();
```

### Loading Views Manually

The `Loader` class also offers the ability to load views without using the WordPress' template loading system. These can easily be plugged in to any existing template helpers your theme may already be using.

#### Render a View

```PHP
\BigBoxWC\WP_Template_Loader\Loader::view( 'my-view' );
```

Will output the contents of `resources/views/my-view.php`

#### Get a View

```PHP
$view = \BigBoxWC\WP_Template_Loader\Loader::get_view( 'my-view' );
```

Will assign the contents of `resources/views/my-view.php` to a variable.

#### Render a View in a Custom Directory

```PHP
\BigBoxWC\WP_Template_Loader\Loader::view( 'global/header' );
```

Will output the contents of `resources/views/global/header.php`

#### Render a View with Passed Variables

```PHP
\BigBoxWC\WP_Template_Loader\Loader::view( 'global/header', [
	'min' => true,
] );
```

Will output the contents of `resources/views/global/header.php` with the variable `$min` available in the global scope.

#### Render a Partial

The above methods can be repeated with the `partial()` method instead of `view()` to automatically look in the set `$partial_dir` location.

## Contributing

All feedback / bug reports / pull requests are welcome.

## License

This code is released under the GPL license.

For the full copyright and license information, please view the [`LICENSE`](LICENSE) file distributed with this source code.