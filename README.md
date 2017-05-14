Elphin IcoFileLoader
====================
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]


This package provides a means to load and convert .ico files in a PHP application. 
It has no dependencies apart from [gd](http://php.net/manual/en/book.image.php) 
for rendering.

The package has unit tests which verify support for 1bit, 4bit, 8bit, 24bit and 32bit
.ico files, and the newer form of .ico files which can included embedded PNG files.

## Installation

IcoFileLoader is available via Composer:

```bash
composer require lordelph/icofileloader
```

The latest version targets currently supported php versions (5.6, 7.0, 7.1). 
If you need to use php5.4 or php5.5, you must install the v1.* branch

```bash
composer require lordelph/icofileloader:1.*
```

## Usage
The [IcoFileService][class-icofileservice] class provides a one-shot method 
`extractIcon`. This should suit most use-cases where you simply want to get one 
image out of a .ico file. 

It returns an image resource, which you can further manipulate with 
[GD functions](http://php.net/gd), e.g. save it to a file with 
[imagepng](http://php.net/imagepng)

For example, here's how you extract a 32x32 transparent image from an ico file:

```php
$loader = new Elphin\IcoFileLoader\IcoFileService;
$im = $loader->extractIcon('/path/to/icon.ico', 32, 32);

//$im is a GD image resource, so we could, for example, save this as a PNG
imagepng($im, '/path/to/output.png');
```

### Render with background color

Instead of retaining the alpha channel from the icon, you can render with a 
background color instead - pass the required color as a renderer option as follows:

```php
$im = $loader->extractIcon('/path/to/icon.ico', 32, 32, ['background'=>'#FFFFFF']);
```

### Extract icon at any size

The `extractIcon` method will try find an image in the icon which is the exact
size you request at highest color depth it can find. If it can't, it will resize the
best quality image in the icon. So, you can request any size you require...

```php
$im = $loader->extractIcon('/path/to/icon.ico', 100, 100);
```

### Extract icon from a URL

As long you have the PHP [fopen wrappers](http://php.net/manual/en/wrappers.php) 
installed, you can pass a URL to `extractIcon`

```php
$im = $loader->extractIcon('https://assets-cdn.github.com/favicon.ico', 16, 16);
```

### Extract icon from binary data

If you already have an ico file held as a binary string, `extractIcon` will cope 
with that just fine too:
```php
$data = file_get_contents('/path/to/icon.ico');
$im = $loader->extractIcon($data, 16, 16);
```

## Lower level methods

If you want to do more than just extract a single image from an icon, you can use 
lower level methods of [IcoFileService][class-icofileservice] to inspect an .ico 
file and perform multiple renderings.

The `fromFile`, `fromString` and `from` methods will parse an `ico` file and return
an [Icon][class-icon] instance representing an icon and the [images][class-image] 
it contains.
 
You can iterate the images in icon, examine them, and render them with `renderImage`

For example, here's how you could extract all the images in an icon and save them
as individual files.

```php
$icon = $loader->fromFile('/path/to/icon.ico');
foreach ($icon as $idx => $image) {
     $im=$loader->renderImage($image);
     
     $filename=sprintf('img%d-%dx%d.png', $idx, $image->width, $image->height);
     imagepng($im, $filename);
     
     printf("rendered %s as %s\n", $image->getDescription(), $filename);
}
```

## Internals

The service is composed of a [parser][class-parser] and a [renderer][class-renderer],
which can be injected into the service at runtime if you wanted to override them.

The current [GdRenderer][class-renderer] works by drawing individual pixels for BMP
based icon images. This isn't going to be terribly fast. PHP 7.2 will have support
for [BMP images](http://php.net/imagecreatefrombmp), and I'll add a renderer which 
takes advantage of that when it is released.


## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](https://github.com/lordelph/icofileloader/blob/master/CONTRIBUTING.md) for details.


## Credits

- [Paul Dixon](http://blog.dixo.net) - 2017 modernization / update
- [Diogo Resende](https://www.phpclasses.org/package/2369-PHP-Extract-graphics-from-ico-files-into-PNG-images.html). Original author of 2005 library this was derived from.

Thanks also to the [PHP League's skeleton project](https://github.com/thephpleague/skeleton) from which this project's structure was derived.

## License

The MIT License (MIT). Please see [License File](https://github.com/lordelph/icofileloader/blob/master/LICENCE) for more information.

*Note: this was based on some classes originally written in 2005 by [Diogo Resende](https://www.phpclasses.org/package/2369-PHP-Extract-graphics-from-ico-files-into-PNG-images.html). 
While these were originally provided on the PHPClasses site under a GPL license,
Diogo kindly agreed to allow them to be licensed under an MIT license.*

[class-icofileservice]: https://github.com/lordelph/icofileloader/blob/master/src/IcoFileService.php
[class-icon]: https://github.com/lordelph/icofileloader/blob/master/src/Icon.php
[class-image]: https://github.com/lordelph/icofileloader/blob/master/src/IconImage.php
[class-parser]: https://github.com/lordelph/icofileloader/blob/master/src/IcoParser.php
[class-renderer]: https://github.com/lordelph/icofileloader/blob/master/src/GdRenderer.php

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
