<?php

namespace Elphin\IcoFileLoader;

/**
 * Class Ico
 * Open ICO files and extract any size/depth to PNG format.
 *
 * @author Diogo Resende <me@diogoresende.net>
 *
 * @version 0.1
 **/
class Ico
{
    /**
     * Ico::bgcolor
     * Background color on icon extraction.
     *
     * @var array(R, G, B) = array(255, 255, 255)
     **/
    public $bgcolor = [255, 255, 255];

    /**
     * Ico::bgcolor_transparent
     * Is background color transparent?
     *
     * @var bool = false
     **/
    public $bgcolorTransparent = false;

    private $filename;
    private $ico;
    private $formats;

    /**
     * Ico constructor.
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
     **/
    public function loadFile($path)
    {
        $this->filename = $path;
        if (($fp = @fopen($path, 'rb')) !== false) {
            $data = '';
            while (!feof($fp)) {
                $data .= fread($fp, 4096);
            }
            fclose($fp);

            return $this->loadData($data);
        }

        return false;
    }

    /**
     * Load an ICO data. If you prefer to open the file
     * and return the binary data you can use this function
     * directly. Otherwise use loadFile() instead.
     *
     * @param string $data Binary data of ICO file
     *
     * @return bool Success
     **/
    private function loadData($data)
    {
        $this->formats = [];

        /**
         * ICO header.
         **/
        $icodata = unpack('SReserved/SType/SCount', $data);
        $this->ico = $icodata;
        $data = substr($data, 6);

        /*
         * Extract each icon header
         **/
        for ($i = 0; $i < $this->ico['Count']; ++$i) {
            $icodata = unpack('CWidth/CHeight/CColorCount/CReserved/SPlanes/SBitCount/LSizeInBytes/LFileOffset', $data);
            $icodata['FileOffset'] -= ($this->ico['Count'] * 16) + 6;
            if ($icodata['ColorCount'] == 0) {
                $icodata['ColorCount'] = 256;
            }
            $this->formats[] = $icodata;

            $data = substr($data, 16);
        }

        /*
         * Extract aditional headers for each extracted icon header
         **/
        $formatCount=count($this->formats);
        for ($i = 0; $i < $formatCount; ++$i) {
            $icodata = unpack(
                'LSize/LWidth/LHeight/SPlanes/SBitCount/LCompression/LImageSize/'.
                'LXpixelsPerM/LYpixelsPerM/LColorsUsed/LColorsImportant',
                substr($data, $this->formats[$i]['FileOffset'])
            );

            $this->formats[$i]['header'] = $icodata;
            $this->formats[$i]['colors'] = [];

            $this->formats[$i]['BitCount'] = $this->formats[$i]['header']['BitCount'];

            switch ($this->formats[$i]['BitCount']) {
                case 32:
                case 24:
                    $length = $this->formats[$i]['header']['Width'] *
                        $this->formats[$i]['header']['Height'] *
                        ($this->formats[$i]['BitCount'] / 8);
                    $this->formats[$i]['data'] = substr(
                        $data,
                        $this->formats[$i]['FileOffset'] + $this->formats[$i]['header']['Size'],
                        $length
                    );
                    break;
                case 8:
                case 4:
                    $icodata = substr(
                        $data,
                        $this->formats[$i]['FileOffset'] + $icodata['Size'],
                        $this->formats[$i]['ColorCount'] * 4
                    );
                    $offset = 0;
                    for ($j = 0; $j < $this->formats[$i]['ColorCount']; ++$j) {
                        $this->formats[$i]['colors'][] = [
                            'red' => ord($icodata[$offset]),
                            'green' => ord($icodata[$offset + 1]),
                            'blue' => ord($icodata[$offset + 2]),
                            'reserved' => ord($icodata[$offset + 3]),
                        ];
                        $offset += 4;
                    }
                    $length = $this->formats[$i]['header']['Width'] *
                        $this->formats[$i]['header']['Height'] *
                        (1 + $this->formats[$i]['BitCount']) / $this->formats[$i]['BitCount'];
                    $this->formats[$i]['data'] = substr(
                        $data,
                        $this->formats[$i]['FileOffset'] +
                            ($this->formats[$i]['ColorCount'] * 4) +
                            $this->formats[$i]['header']['Size'],
                        $length
                    );
                    break;
                case 1:
                    $icodata = substr(
                        $data,
                        $this->formats[$i]['FileOffset'] + $icodata['Size'],
                        $this->formats[$i]['ColorCount'] * 4
                    );

                    $this->formats[$i]['colors'][] = [
                        'blue' => ord($icodata[0]),
                        'green' => ord($icodata[1]),
                        'red' => ord($icodata[2]),
                        'reserved' => ord($icodata[3]),
                    ];
                    $this->formats[$i]['colors'][] = [
                        'blue' => ord($icodata[4]),
                        'green' => ord($icodata[5]),
                        'red' => ord($icodata[6]),
                        'reserved' => ord($icodata[7]),
                    ];

                    $length = $this->formats[$i]['header']['Width'] * $this->formats[$i]['header']['Height'] / 8;
                    $this->formats[$i]['data'] = substr(
                        $data,
                        $this->formats[$i]['FileOffset'] + $this->formats[$i]['header']['Size'] + 8,
                        $length
                    );
                    break;
            }
            $this->formats[$i]['data_length'] = strlen($this->formats[$i]['data']);
        }

        return true;
    }

    /**
     * Return the total icons extracted at the moment.
     *
     * @return int Total icons
     **/
    public function getTotalIcons()
    {
        return count($this->formats);
    }

    /**
     * Ico::GetIconInfo()
     * Return the icon header corresponding to that index.
     *
     * @param int $index Icon index
     *
     * @return resource|bool Icon header or false
     **/
    public function getIconInfo($index)
    {
        if (isset($this->formats[$index])) {
            return $this->formats[$index];
        }

        return false;
    }

    /**
     * Changes background color of extraction. You can set
     * the 3 color components or set $red = '#xxxxxx' (HTML format)
     * and leave all other blanks.
     *
     * @param int $red   Red component
     * @param int $green Green component
     * @param int $blue  Blue component
     **/
    public function setBackground($red = 255, $green = 255, $blue = 255)
    {
        if (is_string($red) && preg_match('/^\#[0-9a-f]{6}$/', $red)) {
            $green = hexdec($red[3].$red[4]);
            $blue = hexdec($red[5].$red[6]);
            $red = hexdec($red[1].$red[2]);
        }

        $this->bgcolor = [$red, $green, $blue];
    }

    /**
     * Set background color to be saved as transparent.
     *
     * @param bool $is_transparent Is Transparent or not
     *
     * @return bool Is Transparent or not
     **/
    public function setBackgroundTransparent($is_transparent = true)
    {
        return $this->bgcolorTransparent = $is_transparent;
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
        if (!isset($this->formats[$index])) {
            return false;
        }

        // create image filled with desired background color
        $im = imagecreatetruecolor($this->formats[$index]['Width'], $this->formats[$index]['Height']);
        $bgcolor = $this->allocateColor($im, $this->bgcolor[0], $this->bgcolor[1], $this->bgcolor[2]);
        imagefilledrectangle($im, 0, 0, $this->formats[$index]['Width'], $this->formats[$index]['Height'], $bgcolor);

        if ($this->bgcolorTransparent) {
            imagecolortransparent($im, $bgcolor);
        }

        //we may build a string of 1/0 to represent the XOR mask
        $maskBits='';
        //we may build a palette for 8 bit images
        $palette=[];

        // allocate palette and get XOR image
        if (in_array($this->formats[$index]['BitCount'], [1, 4, 8, 24])) {
            if ($this->formats[$index]['BitCount'] != 24) {
                $palette = [];
                for ($i = 0; $i < $this->formats[$index]['ColorCount']; ++$i) {
                    $palette[$i] = $this->allocateColor(
                        $im,
                        $this->formats[$index]['colors'][$i]['red'],
                        $this->formats[$index]['colors'][$i]['green'],
                        $this->formats[$index]['colors'][$i]['blue'],
                        round($this->formats[$index]['colors'][$i]['reserved'] / 255 * 127)
                    );
                }
            }

            // build XOR mask bits
            $width = $this->formats[$index]['Width'];
            if (($width % 32) > 0) {
                $width += (32 - ($this->formats[$index]['Width'] % 32));
            }
            $offset = $this->formats[$index]['Width'] *
                $this->formats[$index]['Height'] *
                $this->formats[$index]['BitCount'] / 8;
            $total_bytes = ($width * $this->formats[$index]['Height']) / 8;
            $maskBits = '';
            $bytes = 0;
            $bytes_per_line = ($this->formats[$index]['Width'] / 8);
            $bytes_to_remove = (($width - $this->formats[$index]['Width']) / 8);
            for ($i = 0; $i < $total_bytes; ++$i) {
                $maskBits .= str_pad(decbin(ord($this->formats[$index]['data'][$offset + $i])), 8, '0', STR_PAD_LEFT);
                ++$bytes;
                if ($bytes == $bytes_per_line) {
                    $i += $bytes_to_remove;
                    $bytes = 0;
                }
            }
        }

        // now paint pixels based on bit count
        switch ($this->formats[$index]['BitCount']) {
            case 32:
                /**
                 * 32 bits: 4 bytes per pixel [ B | G | R | ALPHA ].
                 **/
                $offset = 0;
                for ($i = $this->formats[$index]['Height'] - 1; $i >= 0; --$i) {
                    for ($j = 0; $j < $this->formats[$index]['Width']; ++$j) {
                        $color = substr($this->formats[$index]['data'], $offset, 4);
                        if (ord($color[3]) > 0) {
                            $palette = $this->allocateColor(
                                $im,
                                ord($color[2]),
                                ord($color[1]),
                                ord($color[0]),
                                127 - round(ord($color[3]) / 255 * 127)
                            );
                            imagesetpixel($im, $j, $i, $palette);
                        }
                        $offset += 4;
                    }
                }
                break;
            case 24:
                /**
                 * 24 bits: 3 bytes per pixel [ B | G | R ].
                 **/
                $offset = 0;
                $bitoffset = 0;
                for ($i = $this->formats[$index]['Height'] - 1; $i >= 0; --$i) {
                    for ($j = 0; $j < $this->formats[$index]['Width']; ++$j) {
                        if ($maskBits[$bitoffset] == 0) {
                            $color = substr($this->formats[$index]['data'], $offset, 3);
                            $palette = $this->allocateColor($im, ord($color[2]), ord($color[1]), ord($color[0]));
                            imagesetpixel($im, $j, $i, $palette);
                        }
                        $offset += 3;
                        ++$bitoffset;
                    }
                }
                break;
            case 8:
                /**
                 * 8 bits: 1 byte per pixel [ COLOR INDEX ].
                 **/
                $offset = 0;
                for ($i = $this->formats[$index]['Height'] - 1; $i >= 0; --$i) {
                    for ($j = 0; $j < $this->formats[$index]['Width']; ++$j) {
                        if ($maskBits[$offset] == 0) {
                            $color = ord(substr($this->formats[$index]['data'], $offset, 1));
                            imagesetpixel($im, $j, $i, $palette[$color]);
                        }
                        ++$offset;
                    }
                }
                break;
            case 4:
                /**
                 * 4 bits: half byte/nibble per pixel [ COLOR INDEX ].
                 **/
                $offset = 0;
                $maskoffset = 0;
                $leftbits = true;
                for ($i = $this->formats[$index]['Height'] - 1; $i >= 0; --$i) {
                    for ($j = 0; $j < $this->formats[$index]['Width']; ++$j) {
                        $colorByte = substr($this->formats[$index]['data'], $offset, 1);
                        $color = [
                            'High' => bindec(substr(decbin(ord($colorByte)), 0, 4)),
                            'Low' => bindec(substr(decbin(ord($colorByte)), 4)),
                        ];

                        if ($leftbits) {
                            if ($maskBits[$maskoffset++] == 0) {
                                imagesetpixel($im, $j, $i, $palette[$color['High']]);
                            }
                            $leftbits = false;
                        } else {
                            if ($maskBits[$maskoffset++] == 0) {
                                imagesetpixel($im, $j, $i, $palette[$color['Low']]);
                            }
                            ++$offset;
                            $leftbits = true;
                        }
                    }
                }
                break;
            case 1:
                /**
                 * 1 bit: 1 bit per pixel (2 colors, usually black&white) [ COLOR INDEX ].
                 **/
                $colorbits = '';
                $total = strlen($this->formats[$index]['data']);
                for ($i = 0; $i < $total; ++$i) {
                    $colorbits .= str_pad(decbin(ord($this->formats[$index]['data'][$i])), 8, '0', STR_PAD_LEFT);
                }

                $offset = 0;
                for ($i = $this->formats[$index]['Height'] - 1; $i >= 0; --$i) {
                    for ($j = 0; $j < $this->formats[$index]['Width']; ++$j) {
                        if ($maskBits[$offset] == 0) {
                            imagesetpixel($im, $j, $i, $palette[$colorbits[$offset]]);
                        }
                        ++$offset;
                    }
                }
                break;
        }

        return $im;
    }

    /**
     * Ico::AllocateColor()
     * Allocate a color on $im resource. This function prevents
     * from allocating same colors on the same pallete. Instead
     * if it finds that the color is already allocated, it only
     * returns the index to that color.
     * It supports alpha channel.
     *
     * @param resource $im     Image resource
     * @param int      $red    Red component
     * @param int      $green  Green component
     * @param int      $blue   Blue component
     * @param int      $alpha Alpha channel
     *
     * @return int Color index
     **/
    private function allocateColor(&$im, $red, $green, $blue, $alpha = 0)
    {
        $c = imagecolorexactalpha($im, $red, $green, $blue, $alpha);
        if ($c >= 0) {
            return $c;
        }

        return imagecolorallocatealpha($im, $red, $green, $blue, $alpha);
    }
}
