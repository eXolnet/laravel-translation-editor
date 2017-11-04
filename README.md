# Laravel Instruments

[![Latest Stable Version](https://poser.pugx.org/eXolnet/laravel-translation-editor/v/stable?format=flat-square)](https://packagist.org/packages/eXolnet/laravel-translation-editor)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/eXolnet/laravel-translation-editor/master.svg?style=flat-square)](https://travis-ci.org/eXolnet/laravel-translation-editor)
[![Total Downloads](https://img.shields.io/packagist/dt/eXolnet/laravel-translation-editor.svg?style=flat-square)](https://packagist.org/packages/eXolnet/laravel-translation-editor)

This project allow you to edit your translations directly through the browser.

## Installation

Require this package with composer:

```
composer require eXolnet/laravel-translation-editor
```

After updating composer, add the ServiceProvider to the providers array in `config/app.php`:

```
Exolnet\Translation\Editor\TranslationEditorServiceProvider::class
```

## Testing

To run the phpUnit tests, please use:

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE OF CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email security@exolnet.com instead of using the issue tracker.

## Credits

- [Alexandre D'Eschambeault](https://github.com/xel1045)
- [All Contributors](../../contributors)

## License

This code is licensed under the [MIT license](http://choosealicense.com/licenses/mit/). Please see the [license file](LICENSE) for more information.
