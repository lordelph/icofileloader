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

        $info32=$ico->getIconInfo(0);
        $this->assertEquals(32, $info32['Width']);
        $this->assertEquals(32, $info32['Height']);
        $this->assertEquals(16, $info32['ColorCount']);
        $this->assertEquals(4, $info32['BitCount']);

        $info16=$ico->getIconInfo(1);
        $this->assertEquals(16, $info16['Width']);
        $this->assertEquals(16, $info16['Height']);
        $this->assertEquals(16, $info16['ColorCount']);
        $this->assertEquals(4, $info16['BitCount']);

        //we use a bright green background to ensure we spot obvious masking issues
        $ico->setBackground('#00ff00');
        $im = $ico->getImage(0);
        $this->assertInternalType('resource', $im);

        $this->assertImageLooksLike('4bit-32px-expected.png', $im);
    }

    private function assertImageLooksLike($expected, $im)
    {
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
