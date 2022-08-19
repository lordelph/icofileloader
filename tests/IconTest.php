<?php
namespace Elphin\IcoFileLoader;

class IconTest extends IcoTestCase
{
    public function testArrayInterface()
    {
        $icon = new Icon;
        $icon[] = new IconImage([]);
        $this->assertTrue(isset($icon[0]));
        $this->assertFalse(isset($icon[1]));

        unset($icon[0]);
        $this->assertFalse(isset($icon[0]));

        $icon[0] = new IconImage([]);
        $this->assertTrue(isset($icon[0]));

        $this->assertEquals(1, count($icon));
    }

    public function testInvalidAdd()
    {
        $this->expectException(\InvalidArgumentException::class);
        $icon = new Icon;
        $icon[] = "foo";
    }
}
