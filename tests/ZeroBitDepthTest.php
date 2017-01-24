<?php
namespace Elphin\IcoFileLoader;

/**
 * Test for https://github.com/lordelph/icofileloader/issues/8
 * @package Elphin\IcoFileLoader
 */
class ZeroBitDepthTest extends IcoTestCase
{
    public function testZeroBitDepthIcon()
    {
        //first, let's be sure our test file has got a zero for the bitDepth in the ICONDIRENTRY
        $data = file_get_contents('./tests/assets/zero-bit-depth-sample.ico');
        $hdr = unpack(
            'SReserved/SType/SCount/' .
            'Cwidth/Cheight/CcolorCount/Creserved/Splanes/SbitCount/LsizeInBytes/LfileOffset',
            $data
        );
        $this->assertEquals(0, $hdr['bitCount']);
        $this->assertEquals(1384, $hdr['sizeInBytes']);

        //run it through the parser and verify looks sane...
        $icon = $this->parseIcon('zero-bit-depth-sample.ico');
        $image = $icon[0];
        $this->assertEquals(8, $image->bitCount);
        $this->assertEquals('16x16 pixel BMP @ 8 bits/pixel', $image->getDescription());

        //render on green background to better show where transparency should be
        $renderer = new GdRenderer;
        $im = $renderer->render($image, ['background' => '#00ff00']);
        $this->assertImageLooksLike('zero-bit-depth-expected.png', $im);
    }
}
