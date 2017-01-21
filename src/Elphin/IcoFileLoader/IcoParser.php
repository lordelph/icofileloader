<?php

namespace Elphin\IcoFileLoader;

/**
 * IcoParser provides the means to read an ico file and produce an Icon object
 * containing many IconImage objects
 *
 * @package Elphin\IcoFileLoader
 */
class IcoParser implements ParserInterface
{
    /**
     * @inheritdoc
     */
    public function isSupportedBinaryString($data)
    {
        return !is_null($this->parseIconDir($data));
    }

    /**
     * Reads the ICONDIR header and verifies it looks sane
     * @param string $data
     * @return array|null - null is returned if the file doesn't look like an .ico file
     */
    private function parseIconDir($data)
    {
        $icondir = unpack('SReserved/SType/SCount', $data);
        if ($icondir['Reserved'] == 0 && $icondir['Type'] == 1) {
            return $icondir;
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function parse($data)
    {
        $icondir = $this->parseIconDir($data);
        if (!$icondir) {
            throw new \InvalidArgumentException('Invalid ICO file format');
        }

        //nibble the header off our data
        $data = substr($data, 6);

        //parse the ICONDIRENTRY headers
        $icon = new Icon();
        $data = $this->parseIconDirEntries($icon, $data, $icondir['Count']);

        // Extract additional headers for each extracted ICONDIRENTRY
        for ($i = 0; $i < count($icon); ++$i) {
            $signature = unpack('LFourCC', substr($data, $icon[$i]->fileOffset, 4));
            if ($signature['FourCC'] == 0x474e5089) {
                $this->parsePng($icon[$i], $data);
            } else {
                $this->parseBmp($icon[$i], $data);
            }
        }

        return $icon;
    }

    private function parseIconDirEntries(Icon $icon, $data, $count)
    {
        for ($i = 0; $i < $count; ++$i) {
            $icoDirEntry = unpack(
                'Cwidth/Cheight/CcolorCount/Creserved/Splanes/SbitCount/LsizeInBytes/LfileOffset',
                $data
            );
            $icoDirEntry['fileOffset'] -= ($count * 16) + 6;
            if ($icoDirEntry['colorCount'] == 0) {
                $icoDirEntry['colorCount'] = 256;
            }
            if ($icoDirEntry['width'] == 0) {
                $icoDirEntry['width'] = 256;
            }
            if ($icoDirEntry['height'] == 0) {
                $icoDirEntry['height'] = 256;
            }

            $entry = new IconImage($icoDirEntry);
            $icon[] = $entry;

            $data = substr($data, 16);
        }

        return $data;
    }

    private function parsePng(IconImage $entry, $data)
    {
        //a png icon contains a complete png image at the file offset
        $png = substr($data, $entry->fileOffset, $entry->sizeInBytes);
        $entry->setPngFile($png);
    }

    private function parseBmp(IconImage $entry, $data)
    {
        $bitmapInfoHeader = unpack(
            'LSize/LWidth/LHeight/SPlanes/SBitCount/LCompression/LImageSize/' .
            'LXpixelsPerM/LYpixelsPerM/LColorsUsed/LColorsImportant',
            substr($data, $entry->fileOffset, 40)
        );

        $entry->setBitmapInfoHeader($bitmapInfoHeader);

        switch ($entry->bitCount) {
            case 32:
            case 24:
                $this->parseTrueColorImageData($entry, $data);
                break;
            case 8:
            case 4:
                $this->parsePaletteImageData($entry, $data);
                break;
            case 1:
                $this->parseMonoImageData($entry, $data);
                break;
        }
    }

    private function parseTrueColorImageData(IconImage $entry, $data)
    {
        $length = $entry->bmpHeaderWidth * $entry->bmpHeaderHeight * ($entry->bitCount / 8);
        $bmpData = substr($data, $entry->fileOffset + $entry->bmpHeaderSize, $length);
        $entry->setBitmapData($bmpData);
    }

    private function parsePaletteImageData(IconImage $entry, $data)
    {
        //var_dump($entry->colorCount);

        $pal = substr($data, $entry->fileOffset + $entry->bmpHeaderSize, $entry->colorCount * 4);
        $idx = 0;
        for ($j = 0; $j < $entry->colorCount; ++$j) {
            $entry->addToBmpPalette(ord($pal[$idx + 2]), ord($pal[$idx + 1]), ord($pal[$idx]), ord($pal[$idx + 3]));
            $idx += 4;
        }

        $length = $entry->bmpHeaderWidth * $entry->bmpHeaderHeight * (1 + $entry->bitCount) / $entry->bitCount;
        $bmpData = substr($data, $entry->fileOffset + $entry->bmpHeaderSize + $entry->colorCount * 4, $length);
        $entry->setBitmapData($bmpData);
    }

    private function parseMonoImageData(IconImage $entry, $data)
    {
        $pal = substr($data, $entry->fileOffset + $entry->bmpHeaderSize, $entry->colorCount * 4);

        $idx = 0;
        for ($j = 0; $j < $entry->colorCount; ++$j) {
            $entry->addToBmpPalette(ord($pal[$idx + 2]), ord($pal[$idx + 1]), ord($pal[$idx]), ord($pal[$idx + 3]));
            $idx += 4;
        }

        $length = $entry->bmpHeaderWidth * $entry->bmpHeaderHeight / 8;
        $bmpData = substr($data, $entry->fileOffset + $entry->bmpHeaderSize + $entry->colorCount * 4, $length);
        $entry->setBitmapData($bmpData);
    }
}
