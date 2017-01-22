Elphin IcoFileLoader
====================
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]


This class provides a means to load .ico files into a PHP application. It has no 
dependancies apart from gd for rendering.

The class has unit tests which verify support for 1bit, 4bit, 8bit, 24bit and 32bit
.ico files, and the newer form of .ico files which can included embedded PNG files.

## Installation

IcoFileLoader is available via Composer:

```
composer require lordelph/icofileloader
```

## Quick start
The `IcoFileService` class provides a one-shot method for extracting an icon of a particular
size from a `.ico` file. Here's how you extract a 32x32 transparent image from an ico file:

```
$loader=new Elphin\IcoFileLoader\IcoFileService;
$im = $loader->extractIcon('/page/to/icon.ico', 32, 32);
```

### Render with background color

Instead of retaining the alpha channel from the icon, you can render with a background
color instead - pass the required color as a renderer option as follows:

```
$loader=new Elphin\IcoFileLoader\IcoFileService;
$im = $loader->extractIcon('/page/to/icon.ico', 32, 32, ['background'=>'#FFFFFF']);
```

### Extract icon at any size

The `extractIcon` method will try find an image in the icon which is the exact
size you request at highest color depth it can find. If it can't it will resize the
best quality image in the icon. So, you can request any size you require...

```
$loader=new Elphin\IcoFileLoader\IcoFileService;
$im = $loader->extractIcon('/page/to/icon.ico', 100, 100);
```

### Extract icon from a URL

As long you have the PHP fopen wrappers installed, you can pass a URL to `extractIcon`

```
$loader=new Elphin\IcoFileLoader\IcoFileService;
$im = $loader->extractIcon('https://assets-cdn.github.com/favicon.ico', 16, 16);
```

### Extract icon from binary data

If you already have an ico file held as a binary string, `extractIcon` will cope with
that just fine too:
```
$loader=new Elphin\IcoFileLoader\IcoFileService;
$data=file_get_contents('/page/to/icon.ico');
$im = $loader->extractIcon($data, 16, 16);
```

## Lower level methods

The service is largely made up of a parser, which can provide an `Icon` instance representing
an icon and the images it contains, and a renderer. The current renderer uses gd functions to 
provide a true color image resource with an alpha channel.

Lower level methods in the service and constituent classes let you load and inspect an 
icon if your needs are more complex.


## Contributing

Please see [CONTRIBUTING](https://github.com/lordelph/icofileloader/blob/master/CONTRIBUTING.md) for details.


## Credits

- [Paul Dixon](http://blog.dixo.net) - 2017 modernization / update
- [Diogo Resende](https://www.phpclasses.org/package/2369-PHP-Extract-graphics-from-ico-files-into-PNG-images.html). Original author of 2005 library this was derived from.

Thanks also to the [PHP League's skeleton project](https://github.com/thephpleague/skeleton) from which this project's structure was derived.

## License

The MIT License (MIT). Please see [License File](https://github.com/thephpleague/color-extractor/blob/master/LICENSE) for more information.

*Note: this was based on some classes originally written in 2005 by [Diogo Resende](https://www.phpclasses.org/package/2369-PHP-Extract-graphics-from-ico-files-into-PNG-images.html). 
While these were originally provided on the PHPClasses site under a GPL license,
Diogo kindly agreed to allow them to be licensed under an MIT license.*

[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/lordelph/icofileloader/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/lordelph/icofileloader.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/lordelph/icofileloader.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/lordelph/icofileloader.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/lordelph/icofileloader
[link-travis]: https://travis-ci.org/lordelph/icofileloader
[link-scrutinizer]: https://scrutinizer-ci.com/g/lordelph/icofileloader/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/lordelph/icofileloader
[link-downloads]: https://packagist.org/packages/lordelph/icofileloader
[link-author]: https://github.com/lordelph
[link-contributors]: ../../contributors