<?php
namespace Elphin\IcoFileLoader\Test;

use Elphin\IcoFileLoader\Ico;

class IcoTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $iconFile = './tests/assets/github.ico';

        $ico=new Ico();
        $ok=$ico->LoadFile($iconFile);
        $this->assertTrue($ok);

        $count=$ico->TotalIcons();
        $this->assertEquals(2, $count);

        //get first icon and check info looks ok
        $result=$ico->GetIconInfo(0);
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

        $this->assertInternalType('array', $ico->GetIconInfo(1), "expect a second icon in this file");
        $this->assertFalse($ico->GetIconInfo(2), "do not expect a third icon to be found");

        $ico->SetBackground('#ff0000');
        $im = $ico->GetIcon(0);
        $this->assertInternalType('resource', $im);

        //save icon as PNG with no compression
        ob_start();
        imagepng($im, null, 0);
        $imageData = ob_get_contents();
        ob_end_clean();

        //it's possible this might break if the gd results change anything in their png encoding
        //but that should be rare - the aim here to catch everyday problems in library maintenance
        $expected=file_get_contents('./tests/assets/github.png');
        $this->assertTrue($expected===$imageData, 'Extracted icon did not match expected PNG');
    }
}
