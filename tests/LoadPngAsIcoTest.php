<?php
namespace Elphin\IcoFileLoader;

/**
 * Test for https://github.com/lordelph/icofileloader/issues/9
 * @package Elphin\IcoFileLoader
 */
class LoadPngAsIcoTest extends IcoTestCase
{
    public function testLoadPngAsIcoTest()
    {
        //first, let's be sure our test file is a PNG
        $signature = unpack('LFourCC', file_get_contents('./tests/assets/png-as-ico-sample.ico'));
        $this->assertEquals(0x474e5089, $signature['FourCC']);

        //run it through the parser and verify looks sane...
        $icon = $this->parseIcon('png-as-ico-sample.ico');
        $image = $icon[0];
        $this->assertEquals('16x16 pixel PNG @ 8 bits/pixel', $image->getDescription());

        //render on green background to better show where transparency should be
        $renderer = new GdRenderer;
        $im = $renderer->render($image, ['background' => '#00ff00']);
        $this->assertImageLooksLike('png-as-ico-expected.png', $im);
    }
}
