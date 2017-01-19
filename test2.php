<?php
	define('PATH', dirname(__FILE__) . '/');

    include PATH . 'class.icothumb.php';

    /**
     * Change this to a local icon
     **/
    $ico = new IcoThumb('http://www.diogoresende.net/news/wp-content/pics.ico');
    $ico->max_size = 128;
    $ico->use_diferent_depths = false;
    $ico->GetThumb(4);
?>