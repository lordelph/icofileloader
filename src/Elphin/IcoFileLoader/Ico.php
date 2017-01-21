<?php

namespace Elphin\IcoFileLoader;

/**
 * Open ICO files and extract any size/depth to PNG format.
 */
class Ico
{
    /**
     * Background color on icon extraction.
     * @var array(R, G, B) = array(255, 255, 255)
     */
    public $bgcolor = [255, 255, 255];

    /**
     * @var bool Is background color transparent?
     */
    public $bgcolorTransparent = false;

    private $filename;
    private $ico;
    private $iconDirEntry;

    /**
     * Constructor
     *
     * @param string $path optional path to ICO file
     */
    public function __construct($path = '')
    {
        if (strlen($path) > 0) {
            $this->loadFile($path);
        }
    }

    /**
     * Load an ICO file (don't need to call this is if fill the
     * parameter in the class constructor).
     *
     * @param string $path Path to ICO file
     *
     * @return bool Success
     */
    public function loadFile($path)
    {
        $this->filename = $path;
        return $this->loadData(file_get_contents($path));
    }

    /**
     * Load an ICO data. If you prefer to open the file
     * and return the binary data you can use this function
     * directly. Otherwise use loadFile() instead.
     *
     * @param string $data Binary data of ICO file
     *
     * @return bool Success
     */
    public function loadData($data)
    {
        $this->iconDirEntry = [];

        //extract ICONDIR header
        $icodata = unpack('SReserved/SType/SCount', $data);
        $this->ico = $icodata;
        $data = substr($data, 6);

        //extract ICONDIRENTRY structures
        $data = $this->extractIconDirEntries($data);

        // Extract additional headers for each extracted ICONDIRENTRY
        $iconCount = count($this->iconDirEntry);
        for ($i = 0; $i < $iconCount; ++$i) {
            $signature = unpack('LFourCC', substr($data, $this->iconDirEntry[$i]['FileOffset'], 4));

            if ($signature['FourCC'] == 0x474e5089) {
                $this->extractPng($i, $data);
            } else {
                $this->extractBmp($i, $data);
            }
        }
        return true;
    }

    private function extractIconDirEntries($data)
    {
        for ($i = 0; $i < $this->ico['Count']; ++$i) {
            $icodata = unpack('CWidth/CHeight/CColorCount/CReserved/SPlanes/SBitCount/LSizeInBytes/LFileOffset', $data);
            $icodata['FileOffset'] -= ($this->ico['Count'] * 16) + 6;
            if ($icodata['ColorCount'] == 0) {
                $icodata['ColorCount'] = 256;
            }
            if ($icodata['Width'] == 0) {
                $icodata['Width'] = 256;
            }
            if ($icodata['Height'] == 0) {
                $icodata['Height'] = 256;
            }
            $this->iconDirEntry[] = $icodata;

            $data = substr($data, 16);
        }

        return $data;
    }

    private function extractPng($i, $data)
    {
        //a png icon contains a complete png image at the file offset
        $this->iconDirEntry[$i]['png'] =
            substr($data, $this->iconDirEntry[$i]['FileOffset'], $this->iconDirEntry[$i]['SizeInBytes']);
    }

    private function extractBmp($i, $data)
    {
        $bitmapInfoHeader = unpack(
            'LSize/LWidth/LHeight/SPlanes/SBitCount/LCompression/LImageSize/' .
            'LXpixelsPerM/LYpixelsPerM/LColorsUsed/LColorsImportant',
            substr($data, $this->iconDirEntry[$i]['FileOffset'])
        );

        $this->iconDirEntry[$i]['header'] = $bitmapInfoHeader;
        $this->iconDirEntry[$i]['colors'] = [];
        $this->iconDirEntry[$i]['BitCount'] = $this->iconDirEntry[$i]['header']['BitCount'];

        switch ($this->iconDirEntry[$i]['BitCount']) {
            case 32:
            case 24:
                $this->extractTrueColorImageData($i, $data);
                break;
            case 8:
            case 4:
                $this->extractPaletteImageData($i, $data);
                break;
            case 1:
                $this->extractMonoImageData($i, $data);
                break;
        }
        $this->iconDirEntry[$i]['data_length'] = strlen($this->iconDirEntry[$i]['data']);
    }

    private function extractTrueColorImageData($i, $data)
    {
        $length = $this->iconDirEntry[$i]['header']['Width'] *
            $this->iconDirEntry[$i]['header']['Height'] *
            ($this->iconDirEntry[$i]['BitCount'] / 8);
        $this->iconDirEntry[$i]['data'] = substr(
            $data,
            $this->iconDirEntry[$i]['FileOffset'] + $this->iconDirEntry[$i]['header']['Size'],
            $length
        );
    }

    private function extractPaletteImageData($i, $data)
    {
        $icodata = substr(
            $data,
            $this->iconDirEntry[$i]['FileOffset'] + $this->iconDirEntry[$i]['header']['Size'],
            $this->iconDirEntry[$i]['ColorCount'] * 4
        );
        $offset = 0;
        for ($j = 0; $j < $this->iconDirEntry[$i]['ColorCount']; ++$j) {
            $this->iconDirEntry[$i]['colors'][] = [
                'blue' => ord($icodata[$offset]),
                'green' => ord($icodata[$offset + 1]),
                'red' => ord($icodata[$offset + 2]),
                'reserved' => ord($icodata[$offset + 3]),
            ];
            $offset += 4;
        }
        $length = $this->iconDirEntry[$i]['header']['Width'] *
            $this->iconDirEntry[$i]['header']['Height'] *
            (1 + $this->iconDirEntry[$i]['BitCount']) / $this->iconDirEntry[$i]['BitCount'];
        $this->iconDirEntry[$i]['data'] = substr(
            $data,
            $this->iconDirEntry[$i]['FileOffset'] +
            ($this->iconDirEntry[$i]['ColorCount'] * 4) +
            $this->iconDirEntry[$i]['header']['Size'],
            $length
        );
    }

    private function extractMonoImageData($i, $data)
    {
        $icodata = substr(
            $data,
            $this->iconDirEntry[$i]['FileOffset'] + $this->iconDirEntry[$i]['header']['Size'],
            $this->iconDirEntry[$i]['ColorCount'] * 4
        );

        $this->iconDirEntry[$i]['colors'][] = [
            'blue' => ord($icodata[0]),
            'green' => ord($icodata[1]),
            'red' => ord($icodata[2]),
            'reserved' => ord($icodata[3]),
        ];
        $this->iconDirEntry[$i]['colors'][] = [
            'blue' => ord($icodata[4]),
            'green' => ord($icodata[5]),
            'red' => ord($icodata[6]),
            'reserved' => ord($icodata[7]),
        ];

        $length = $this->iconDirEntry[$i]['header']['Width'] * $this->iconDirEntry[$i]['header']['Height'] / 8;
        $this->iconDirEntry[$i]['data'] = substr(
            $data,
            $this->iconDirEntry[$i]['FileOffset'] + $this->iconDirEntry[$i]['header']['Size'] + 8,
            $length
        );
    }

    /**
     * Return the total icons extracted at the moment.
     *
     * @return int Total icons
     */
    public function getTotalIcons()
    {
        return count($this->iconDirEntry);
    }

    /**
     * Return the icon header corresponding to that index.
     *
     * @param int $index Icon index
     *
     * @return resource|bool Icon header or false
     */
    public function getIconInfo($index)
    {
        if (isset($this->iconDirEntry[$index])) {
            return $this->iconDirEntry[$index];
        }

        return false;
    }

    /**
     * Changes background color of extraction. You can set
     * the 3 color components or set $red = '#xxxxxx' (HTML format)
     * and leave all other blanks.
     *
     * @param int $red Red component
     * @param int $green Green component
     * @param int $blue Blue component
     */
    public function setBackground($red = 255, $green = 255, $blue = 255)
    {
        if (is_string($red) && preg_match('/^\#[0-9a-f]{6}$/', $red)) {
            $green = hexdec($red[3] . $red[4]);
            $blue = hexdec($red[5] . $red[6]);
            $red = hexdec($red[1] . $red[2]);
        }

        $this->bgcolor = [$red, $green, $blue];
    }

    /**
     * Set background color to be saved as transparent.
     *
     * @param bool $transparent Is Transparent or not
     *
     * @return bool Is Transparent or not
     */
    public function setBackgroundTransparent($transparent = true)
    {
        return $this->bgcolorTransparent = $transparent;
    }

    /**
     * Return an image resource with the icon stored
     * on the $index position of the ICO file.
     *
     * @param int $index Position of the icon inside ICO
     *
     * @return resource|bool Image resource
     **/
    public function getImage($index)
    {
        if (!isset($this->iconDirEntry[$index])) {
            return false;
        }

        if (isset($this->iconDirEntry[$index]['png'])) {
            return $this->getPngImage($index);
        } else {
            return $this->getBmpImage($index);
        }
    }

    private function getPngImage($index)
    {
        $im = imagecreatefromstring($this->iconDirEntry[$index]['png']);
        return $im;
    }

    private function getBmpImage($index)
    {
        // create image filled with desired background color
        $w=$this->iconDirEntry[$index]['Width'];
        $h=$this->iconDirEntry[$index]['Height'];
        $im = imagecreatetruecolor($w, $h);

        if ($this->bgcolorTransparent) {
            imagealphablending($im, false);
            $bgcolor=$this->allocateColor($im, $this->bgcolor[0], $this->bgcolor[1], $this->bgcolor[2], 127);
            imagefilledrectangle($im, 0, 0, $w, $h, $bgcolor);
            imagesavealpha($im, true);
        } else {
            $bgcolor = $this->allocateColor($im, $this->bgcolor[0], $this->bgcolor[1], $this->bgcolor[2]);
            imagefilledrectangle($im, 0, 0, $w, $h, $bgcolor);
        }

        // now paint pixels based on bit count
        switch ($this->iconDirEntry[$index]['BitCount']) {
            case 32:
                $this->render32bit($this->iconDirEntry[$index], $im);
                break;
            case 24:
                $this->render24bit($this->iconDirEntry[$index], $im);
                break;
            case 8:
                $this->render8bit($this->iconDirEntry[$index], $im);
                break;
            case 4:
                $this->render4bit($this->iconDirEntry[$index], $im);
                break;
            case 1:
                $this->render1bit($this->iconDirEntry[$index], $im);
                break;
        }

        return $im;
    }

    /**
     * Allocate a color on $im resource. This function prevents
     * from allocating same colors on the same pallete. Instead
     * if it finds that the color is already allocated, it only
     * returns the index to that color.
     * It supports alpha channel.
     *
     * @param resource $im Image resource
     * @param int $red Red component
     * @param int $green Green component
     * @param int $blue Blue component
     * @param int $alpha Alpha channel
     *
     * @return int Color index
     */
    private function allocateColor($im, $red, $green, $blue, $alpha = 0)
    {
        $c = imagecolorexactalpha($im, $red, $green, $blue, $alpha);
        if ($c >= 0) {
            return $c;
        }

        return imagecolorallocatealpha($im, $red, $green, $blue, $alpha);
    }

    private function render32bit($metadata, $im)
    {
        /**
         * 32 bits: 4 bytes per pixel [ B | G | R | ALPHA ].
         **/
        $offset = 0;
        $binary = $metadata['data'];

        for ($i = $metadata['Height'] - 1; $i >= 0; --$i) {
            for ($j = 0; $j < $metadata['Width']; ++$j) {
                //we translate the BGRA to aRGB ourselves, which is twice as fast
                //as calling imagecolorallocatealpha
                $alpha7 = ((~ord($binary[$offset + 3])) & 0xff) >> 1;
                if ($alpha7 < 127) {
                    $col = ($alpha7 << 24) |
                        (ord($binary[$offset + 2]) << 16) |
                        (ord($binary[$offset + 1]) << 8) |
                        (ord($binary[$offset]));
                    imagesetpixel($im, $j, $i, $col);
                }
                $offset += 4;
            }
        }
    }

    private function render24bit($metadata, $im)
    {
        $maskBits = $this->buildMaskBits($metadata);

        /**
         * 24 bits: 3 bytes per pixel [ B | G | R ].
         **/
        $offset = 0;
        $bitoffset = 0;
        $binary = $metadata['data'];

        for ($i = $metadata['Height'] - 1; $i >= 0; --$i) {
            for ($j = 0; $j < $metadata['Width']; ++$j) {
                if ($maskBits[$bitoffset] == 0) {
                    //translate BGR to RGB
                    $col = (ord($binary[$offset + 2]) << 16) |
                        (ord($binary[$offset + 1]) << 8) |
                        (ord($binary[$offset]));
                    imagesetpixel($im, $j, $i, $col);
                }
                $offset += 3;
                ++$bitoffset;
            }
        }
    }

    private function buildMaskBits($metadata)
    {
        $width = $metadata['Width'];
        if (($width % 32) > 0) {
            $width += (32 - ($metadata['Width'] % 32));
        }
        $offset = $metadata['Width'] *
            $metadata['Height'] *
            $metadata['BitCount'] / 8;
        $total_bytes = ($width * $metadata['Height']) / 8;
        $maskBits = '';
        $bytes = 0;
        $bytes_per_line = ($metadata['Width'] / 8);
        $bytes_to_remove = (($width - $metadata['Width']) / 8);
        for ($i = 0; $i < $total_bytes; ++$i) {
            $maskBits .= str_pad(decbin(ord($metadata['data'][$offset + $i])), 8, '0', STR_PAD_LEFT);
            ++$bytes;
            if ($bytes == $bytes_per_line) {
                $i += $bytes_to_remove;
                $bytes = 0;
            }
        }
        return $maskBits;
    }

    private function render8bit($metadata, $im)
    {
        $palette = $this->buildPalette($metadata, $im);
        $maskBits = $this->buildMaskBits($metadata);

        /**
         * 8 bits: 1 byte per pixel [ COLOR INDEX ].
         **/
        $offset = 0;
        for ($i = $metadata['Height'] - 1; $i >= 0; --$i) {
            for ($j = 0; $j < $metadata['Width']; ++$j) {
                if ($maskBits[$offset] == 0) {
                    $color = ord($metadata['data'][$offset]);
                    imagesetpixel($im, $j, $i, $palette[$color]);
                }
                ++$offset;
            }
        }
    }

    private function buildPalette($metadata, $im)
    {
        $palette = [];
        if ($metadata['BitCount'] != 24) {
            $palette = [];
            for ($i = 0; $i < $metadata['ColorCount']; ++$i) {
                $palette[$i] = $this->allocateColor(
                    $im,
                    $metadata['colors'][$i]['red'],
                    $metadata['colors'][$i]['green'],
                    $metadata['colors'][$i]['blue'],
                    round($metadata['colors'][$i]['reserved'] / 255 * 127)
                );
            }
        }
        return $palette;
    }

    private function render4bit($metadata, $im)
    {
        $palette = $this->buildPalette($metadata, $im);
        $maskBits = $this->buildMaskBits($metadata);

        /**
         * 4 bits: half byte/nibble per pixel [ COLOR INDEX ].
         **/
        $offset = 0;
        $maskoffset = 0;
        for ($i = $metadata['Height'] - 1; $i >= 0; --$i) {
            for ($j = 0; $j < $metadata['Width']; $j += 2) {
                $colorByte = ord($metadata['data'][$offset]);
                $lowNibble = $colorByte & 0x0f;
                $highNibble = ($colorByte & 0xf0) >> 4;

                if ($maskBits[$maskoffset++] == 0) {
                    imagesetpixel($im, $j, $i, $palette[$highNibble]);
                }

                if ($maskBits[$maskoffset++] == 0) {
                    imagesetpixel($im, $j + 1, $i, $palette[$lowNibble]);
                }
                $offset++;
            }
        }
    }

    private function render1bit($metadata, $im)
    {
        $palette = $this->buildPalette($metadata, $im);
        $maskBits = $this->buildMaskBits($metadata);

        /**
         * 1 bit: 1 bit per pixel (2 colors, usually black&white) [ COLOR INDEX ].
         **/
        $colorbits = '';
        $total = strlen($metadata['data']);
        for ($i = 0; $i < $total; ++$i) {
            $colorbits .= str_pad(decbin(ord($metadata['data'][$i])), 8, '0', STR_PAD_LEFT);
        }

        $offset = 0;
        for ($i = $metadata['Height'] - 1; $i >= 0; --$i) {
            for ($j = 0; $j < $metadata['Width']; ++$j) {
                if ($maskBits[$offset] == 0) {
                    imagesetpixel($im, $j, $i, $palette[$colorbits[$offset]]);
                }
                ++$offset;
            }
        }
    }
}
