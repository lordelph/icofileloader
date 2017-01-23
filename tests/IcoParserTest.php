<?php
namespace Elphin\IcoFileLoader;

class IcoParserTest extends IcoTestCase
{
    public function test32bitIcon()
    {
        $iconFile = './tests/assets/32bit-16px-32px-sample.ico';
        $data = file_get_contents($iconFile);

        $parser = new IcoParser();
        //check the parser can tell an .ico from other garbage
        $this->assertFalse($parser->isSupportedBinaryString('garbage'));
        $this->assertTrue($parser->isSupportedBinaryString($data));

        //and away we go...
        $icon = $parser->parse($data);
        $this->assertInstanceOf(Icon::class, $icon);
        $this->assertTrue($icon[0]->isBmp());

        //we expect 2 images in this sample
        $this->assertEquals(2, count($icon));
        $this->assertEquals('16x16 pixel BMP @ 32 bits/pixel', $icon[0]->getDescription());
        $this->assertEquals('32x32 pixel BMP @ 32 bits/pixel', $icon[1]->getDescription());
        $this->assertNull($icon[2]);
    }

    public function test24bitIcon()
    {
        $parser = new IcoParser();
        $icon = $parser->parse(file_get_contents('./tests/assets/24bit-32px-sample.ico'));
        $this->assertEquals(1, count($icon));
        $this->assertEquals('32x32 pixel BMP @ 24 bits/pixel', $icon[0]->getDescription());
    }

    public function test8bitIcon()
    {
        $parser = new IcoParser();
        $icon = $parser->parse(file_get_contents('./tests/assets/8bit-48px-32px-16px-sample.ico'));
        $this->assertEquals(6, count($icon));
        $this->assertEquals('32x32 pixel BMP @ 4 bits/pixel', $icon[0]->getDescription());
        $this->assertEquals('16x16 pixel BMP @ 4 bits/pixel', $icon[1]->getDescription());
        $this->assertEquals('32x32 pixel BMP @ 8 bits/pixel', $icon[2]->getDescription());
        $this->assertEquals('16x16 pixel BMP @ 8 bits/pixel', $icon[3]->getDescription());
        $this->assertEquals('48x48 pixel BMP @ 8 bits/pixel', $icon[4]->getDescription());
        $this->assertEquals('48x48 pixel BMP @ 4 bits/pixel', $icon[5]->getDescription());
    }

    public function test4bitIcon()
    {
        $parser = new IcoParser();
        $icon = $parser->parse(file_get_contents('./tests/assets/4bit-32px-16px-sample.ico'));
        $this->assertEquals(2, count($icon));
        $this->assertEquals('32x32 pixel BMP @ 4 bits/pixel', $icon[0]->getDescription());
        $this->assertEquals('16x16 pixel BMP @ 4 bits/pixel', $icon[1]->getDescription());
    }

    public function test1bitIcon()
    {
        $parser = new IcoParser();
        $icon = $parser->parse(file_get_contents('./tests/assets/1bit-32px-sample.ico'));
        $this->assertEquals(1, count($icon));
        $this->assertEquals('32x32 pixel BMP @ 1 bits/pixel', $icon[0]->getDescription());
    }

    public function testPngIcon()
    {
        $parser = new IcoParser();
        $icon = $parser->parse(file_get_contents('./tests/assets/32bit-png-sample.ico'));
        $this->assertEquals(12, count($icon));
        $this->assertEquals('16x16 pixel BMP @ 4 bits/pixel', $icon[0]->getDescription());
        $this->assertEquals('16x16 pixel BMP @ 8 bits/pixel', $icon[1]->getDescription());
        $this->assertEquals('32x32 pixel BMP @ 4 bits/pixel', $icon[2]->getDescription());
        $this->assertEquals('32x32 pixel BMP @ 8 bits/pixel', $icon[3]->getDescription());
        $this->assertEquals('48x48 pixel BMP @ 4 bits/pixel', $icon[4]->getDescription());
        $this->assertEquals('48x48 pixel BMP @ 8 bits/pixel', $icon[5]->getDescription());
        $this->assertEquals('256x256 pixel PNG @ 4 bits/pixel', $icon[6]->getDescription());
        $this->assertEquals('256x256 pixel PNG @ 8 bits/pixel', $icon[7]->getDescription());
        $this->assertEquals('16x16 pixel BMP @ 32 bits/pixel', $icon[8]->getDescription());
        $this->assertEquals('32x32 pixel BMP @ 32 bits/pixel', $icon[9]->getDescription());
        $this->assertEquals('48x48 pixel BMP @ 32 bits/pixel', $icon[10]->getDescription());
        $this->assertEquals('256x256 pixel PNG @ 32 bits/pixel', $icon[11]->getDescription());
    }
}
