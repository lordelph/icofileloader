<?php
namespace Elphin\IcoFileLoader;

abstract class IcoTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $expected leafname of asset file we expect image to look like
     * @param resource $im generated image you want to check
     */
    protected function assertImageLooksLike($expected, $im)
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

    /**
     * @param string $asset leafname of asset file
     * @return Icon
     */
    protected function parseIcon($asset)
    {
        $parser=new IcoParser;
        return $parser->parse(file_get_contents('./tests/assets/'.$asset));
    }
}
