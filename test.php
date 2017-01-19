<?php
	define('PATH', dirname(__FILE__) . '/');

	include PATH . 'class.ico.php';

    /**
     * Change this to a local icon
     **/
	$ico = new Ico('http://www.diogoresende.net/news/wp-content/pics.ico');
    $ico->SetBackground('#abcdef');
    $im = $ico->GetIcon(9);

    header('Content-Type: image/png');
    imagepng($im);
    imagedestroy($im);
?>