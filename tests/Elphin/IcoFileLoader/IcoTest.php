<?php
namespace Elphin\IcoFileLoader\Test;

use Elphin\IcoFileLoader\Ico;

class IcoTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $iconFile = './tests/assets/github.ico';

        $ico=new Ico();
        $ok=$ico->loadFile($iconFile);
        $this->assertTrue($ok);

        $count=$ico->getTotalIcons();
        $this->assertEquals(2, $count);

        //get first icon and check info looks ok
        $result=$ico->getIconInfo(0);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('Width', $result);
        $this->assertArrayHasKey('Height', $result);
        $this->assertArrayHasKey('ColorCount', $result);
        $this->assertArrayHasKey('Reserved', $result);
        $this->assertArrayHasKey('Planes', $result);
        $this->assertArrayHasKey('BitCount', $result);
        $this->assertArrayHasKey('SizeInBytes', $result);
        $this->assertArrayHasKey('FileOffset', $result);
        $this->assertArrayHasKey('header', $result);
        $this->assertArrayHasKey('colors', $result);

        $this->assertEquals(16, $result['Width']);
        $this->assertEquals(16, $result['Height']);
        $this->assertEquals(32, $result['BitCount']);

        $this->assertInternalType('array', $ico->getIconInfo(1), "expect a second icon in this file");
        $this->assertFalse($ico->getIconInfo(2), "do not expect a third icon to be found");

        $ico->setBackground('#ff0000');
        $im = $ico->getImage(0);
        $this->assertInternalType('resource', $im);

        //save icon as PNG with no compression
        $this->assertImageLooksLike('github.png', $im);
    }

    public function test4bitIcon()
    {
        $iconFile = './tests/assets/4bit-32px-16px-sample.ico';

        $ico=new Ico();
        $ok=$ico->loadFile($iconFile);
        $this->assertTrue($ok);

        $count=$ico->getTotalIcons();
        $this->assertEquals(2, $count);

        $this->assertIconMetadata($ico, 0, 32, 32, 16, 4);
        $this->assertIconMetadata($ico, 1, 16, 16, 16, 4);

        //we use a bright green background to ensure we spot obvious masking issues
        $ico->setBackground('#00ff00');
        $im = $ico->getImage(0);
        $this->assertInternalType('resource', $im);

        $this->assertImageLooksLike('4bit-32px-expected.png', $im);
    }

    public function test8bitIcon()
    {
        $iconFile = './tests/assets/8bit-48px-32px-16px-sample.ico';

        $ico=new Ico();
        $ok=$ico->loadFile($iconFile);
        $this->assertTrue($ok);

        $count=$ico->getTotalIcons();
        $this->assertEquals(6, $count);

        $this->assertIconMetadata($ico, 0, 32, 32, 16, 4);
        $this->assertIconMetadata($ico, 1, 16, 16, 16, 4);
        $this->assertIconMetadata($ico, 2, 32, 32, 256, 8);
        $this->assertIconMetadata($ico, 3, 16, 16, 256, 8);
        $this->assertIconMetadata($ico, 4, 48, 48, 256, 8);
        $this->assertIconMetadata($ico, 5, 48, 48, 16, 4);


        //we use a bright green background to ensure we spot obvious masking issues
        $ico->setBackground('#00ff00');
        $im = $ico->getImage(2);
        $this->assertInternalType('resource', $im);

        $this->assertImageLooksLike('8bit-32px-expected.png', $im);
    }

    private function assertIconMetadata(Ico $ico, $idx, $w, $h, $c, $b)
    {
        $info=$ico->getIconInfo($idx);
        $this->assertEquals($w, $info['Width'], "Unexpected width for icon $idx");
        $this->assertEquals($h, $info['Height'], "Unexpected height for icon $idx");
        $this->assertEquals($c, $info['ColorCount'], "Unexpected colour count for icon $idx");
        $this->assertEquals($b, $info['BitCount'], "Unexpected bit depth for icon $idx");
    }

    private function assertImageLooksLike($expected, $im)
    {
        $expectedFile='./tests/assets/'.$expected;
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
        $expected=file_get_contents('./tests/assets/'.$expected);
        $this->assertTrue($expected===$imageData, 'generated image did not match expected '.$expected);
    }
}
