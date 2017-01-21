<?php
namespace Elphin\IcoFileLoader\Test;

use Elphin\IcoFileLoader\Ico;

class IcoTest extends \PHPUnit_Framework_TestCase
{
    public function test32bitIcon()
    {
        $iconFile = './tests/assets/32bit-16px-32px-sample.ico';

        $ico = new Ico();
        $ok = $ico->loadFile($iconFile);
        $this->assertTrue($ok);

        $count = $ico->getTotalIcons();
        $this->assertEquals(2, $count);

        $this->assertIconMetadata($ico, 0, 16, 16, 256, 32);
        $this->assertIconMetadata($ico, 1, 32, 32, 256, 32);
        $this->assertFalse($ico->getIconInfo(2), "do not expect a third icon to be found");

        $ico->setBackground('#00ff00');
        $im = $ico->getImage(1);

        //save icon as PNG with no compression
        $this->assertImageLooksLike('32bit-32px-expected.png', $im);
    }

    public function test24bitIcon()
    {
        $ico = new Ico('./tests/assets/24bit-32px-sample.ico');
        $this->assertEquals(1, $ico->getTotalIcons());
        $this->assertIconMetadata($ico, 0, 32, 32, 256, 24);

        //we use a bright green background to ensure we spot obvious masking issues
        $ico->setBackground('#00ff00');
        $im = $ico->getImage(0);
        $this->assertImageLooksLike('24bit-32px-expected.png', $im);
    }

    public function test8bitIcon()
    {
        $ico = new Ico('./tests/assets/8bit-48px-32px-16px-sample.ico');
        $this->assertEquals(6, $ico->getTotalIcons());

        $this->assertIconMetadata($ico, 0, 32, 32, 16, 4);
        $this->assertIconMetadata($ico, 1, 16, 16, 16, 4);
        $this->assertIconMetadata($ico, 2, 32, 32, 256, 8);
        $this->assertIconMetadata($ico, 3, 16, 16, 256, 8);
        $this->assertIconMetadata($ico, 4, 48, 48, 256, 8);
        $this->assertIconMetadata($ico, 5, 48, 48, 16, 4);


        //we use a bright green background to ensure we spot obvious masking issues
        $ico->setBackground('#00ff00');
        $im = $ico->getImage(2);

        $this->assertImageLooksLike('8bit-32px-expected.png', $im);
    }

    public function test4bitIcon()
    {
        $ico = new Ico('./tests/assets/4bit-32px-16px-sample.ico');
        $this->assertEquals(2, $ico->getTotalIcons());

        $this->assertIconMetadata($ico, 0, 32, 32, 16, 4);
        $this->assertIconMetadata($ico, 1, 16, 16, 16, 4);

        //we use a bright green background to ensure we spot obvious masking issues
        $ico->setBackground('#00ff00');

        $im = $ico->getImage(0);
        $this->assertImageLooksLike('4bit-32px-expected.png', $im);
    }

    public function test1bitIcon()
    {
        $ico = new Ico('./tests/assets/1bit-32px-sample.ico');
        $this->assertEquals(1, $ico->getTotalIcons());
        $this->assertIconMetadata($ico, 0, 32, 32, 2, 1);

        //we use a bright green background to ensure we spot obvious masking issues - however,
        //our 2 bit sample doesn't have a mask
        $ico->setBackground('#00ff00');
        $im = $ico->getImage(0);
        $this->assertImageLooksLike('1bit-32px-expected.png', $im);
    }

    public function testPngIcon()
    {
        $ico = new Ico('./tests/assets/32bit-png-sample.ico');
        $this->assertEquals(12, $ico->getTotalIcons());
        $this->assertIconMetadata($ico, 0, 16, 16, 16, 4);
        $this->assertIconMetadata($ico, 1, 16, 16, 256, 8);
        $this->assertIconMetadata($ico, 2, 32, 32, 16, 4);
        $this->assertIconMetadata($ico, 3, 32, 32, 256, 8);
        $this->assertIconMetadata($ico, 4, 48, 48, 16, 4);
        $this->assertIconMetadata($ico, 5, 48, 48, 256, 8);
        $this->assertIconMetadata($ico, 6, 256, 256, 16, 4);
        $this->assertIconMetadata($ico, 7, 256, 256, 256, 8);
        $this->assertIconMetadata($ico, 8, 16, 16, 256, 32);
        $this->assertIconMetadata($ico, 9, 32, 32, 256, 32);
        $this->assertIconMetadata($ico, 10, 48, 48, 256, 32);
        $this->assertIconMetadata($ico, 11, 256, 256, 256, 32);

        $ico->setBackgroundTransparent(true);
        $im = $ico->getImage(9);
        $this->assertImageLooksLike('32bit-png-expected.png', $im);
    }

    //most of our tests have used a green background, but this tests we can generate
    //an image with an alpha channel successfully
    public function testTransparency()
    {
        $ico = new Ico('./tests/assets/32bit-16px-32px-sample.ico');
        $this->assertEquals(2, $ico->getTotalIcons());
        $this->assertIconMetadata($ico, 1, 32, 32, 256, 32);

        $ico->setBackgroundTransparent(true);
        $im = $ico->getImage(1);

        //save icon as PNG with no compression
        $this->assertImageLooksLike('32bit-32px-alpha-expected.png', $im);
    }

    //most of our tests have used a green background, but this tests we can generate
    //an image with an alpha channel successfully
    public function testBackgroundColor()
    {
        //with this particular 8 bit image, the image contains the colour white
        //in both the masked transparent area and the icon itself. If we ask for a transparent
        //icon and set the the background as white, we should NOT get transparent
        //pixels in the opaque are of the mask. That's what we're testing here!
        $ico = new Ico('./tests/assets/32bit-png-sample.ico');
        $this->assertIconMetadata($ico, 3, 32, 32, 256, 8);

        $ico->setBackgroundTransparent(true);
        $ico->setBackground('#ffffff');
        $im = $ico->getImage(3);


        //this sample has got white pixels in the downward arrow as we expect
        $this->assertImageLooksLike('32bit-bgtest-expected.png', $im);
    }


    /**
     * useful during test development, this can spit out some assertions for regression tests
     * @param Ico $ico
     */
    protected function generateTest(Ico $ico)
    {
        for ($x = 0; $x < $ico->getTotalIcons(); $x++) {
            $info = $ico->getIconInfo($x);
            echo '$this->assertIconMetadata($ico, ', $x, ', ',
            $info['Width'], ', ', $info['Height'], ', ', $info['ColorCount'], ', ', $info['BitCount'], ");\n";
        }
    }

    private function assertIconMetadata(Ico $ico, $idx, $w, $h, $c, $b)
    {
        $info = $ico->getIconInfo($idx);

        //check structure is expected
        $this->assertInternalType('array', $info);
        $this->assertArrayHasKey('Width', $info);
        $this->assertArrayHasKey('Height', $info);
        $this->assertArrayHasKey('ColorCount', $info);
        $this->assertArrayHasKey('Reserved', $info);
        $this->assertArrayHasKey('Planes', $info);
        $this->assertArrayHasKey('BitCount', $info);
        $this->assertArrayHasKey('SizeInBytes', $info);
        $this->assertArrayHasKey('FileOffset', $info);
        if (!isset($info['png'])) {
            $this->assertArrayHasKey('header', $info);
            $this->assertArrayHasKey('colors', $info);
        }
        //check image is of form expected
        $this->assertEquals($w, $info['Width'], "Unexpected width for icon $idx");
        $this->assertEquals($h, $info['Height'], "Unexpected height for icon $idx");
        $this->assertEquals($c, $info['ColorCount'], "Unexpected colour count for icon $idx");
        $this->assertEquals($b, $info['BitCount'], "Unexpected bit depth for icon $idx");
    }

    private function assertImageLooksLike($expected, $im)
    {
        $this->assertInternalType('resource', $im);

        $expectedFile = './tests/assets/' . $expected;
        //can regenerate expected results by deleting and re-running test
        if (!file_exists($expectedFile)) {
            imagepng($im, $expectedFile, 0);
            $this->markTestSkipped('Regenerated $expected  - skipping test');
        }

        //save icon as PNG with no compression
        ob_start();
        imagepng($im, null, 0);
        $imageData = ob_get_contents();
        ob_end_clean();

        //it's possible this might break if the gd results change anything in their png encoding
        //but that should be rare - the aim here to catch everyday problems in library maintenance
        $expectedData = file_get_contents('./tests/assets/' . $expected);
        if ($expectedData !== $imageData) {
            $observedFile=str_replace('.png', '-OBSERVED.png', $expectedFile);
            file_put_contents($observedFile, $imageData);
        }
        $this->assertTrue($expectedData === $imageData, 'generated image did not match expected ' . $expected);
    }
}
