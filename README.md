# **Laravel-Sitemap package**

*Sitemap generator for Laravel.*

## Installation

Run the following command and provide the latest stable version (e.g v8.\*) :

```bash
composer require pelazio/sitemap
```

*or add the following to your `composer.json` file :*


#### For Laravel 9
```json
"pelazio/sitemap": "^9"
```

*Publish needed assets (styles, views, config files) :*

```bash
php artisan vendor:publish --provider="pelazio\Sitemap\SitemapServiceProvider"
```

## Contribution guidelines

Before submiting new merge request or creating new issue, please read [contribution guidelines](https://gitlab.com/pelazio/Sitemap/blob/master/CONTRIBUTING.md).

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).