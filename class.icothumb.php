<?php
    /**
     * class.icothumb.php
     *
     * @(#) $Header: /home/jeph/repository/classes/ico/class.icothumb.php,v 0.1 2005/06/09 10:05:41 jeph Exp $
     **/
 
    /**
     * Class IcoThumb
     * Create a thumbnail with an Icon
     *
     * @author Diogo Resende <me@diogoresende.net>
     * @version 0.1
     *
     * @dependency  Class    Ico
     *
     * @method public  IcoThumb($path = '')
     * @method public  LoadIco($path)
     * @method public  GetThumb($total_icons = 0)
     * @method private ImageStringCentered(&$im, $left, $top, $width, $font, $color, $text)
     **/
    class IcoThumb {
        /**
         * IcoThumb::ico
         * Icon resource
         *
         * @type resource
         * @var  public
         **/
        var $ico;
        
        /**
         * IcoThumb::padding
         * Paddings for the thumbnail
         *
         * @type array
         * @var  public
         **/
        var $padding = array(
            'left'     => 10,
            'top'     => 5,
            'right'  => 10,
            'bottom' => 5,
            'middle' => 20
        );
        
        /**
         * IcoThumb::view_info
         * View icon info below each image on thumbnail
         *
         * @type boolean
         * @var  public
         **/
        var $view_info = true;
        
        /**
         * IcoThumb::use_diferent_depths
         * Use always one color depth per size or use all
         *
         * @type boolean
         * @var  public
         **/
        var $use_diferent_depths = false;
        
        /**
         * IcoThumb::font
         * Font for size and depth strings
         *
         * @type array
         * @var  public
         **/
        var $font = array(
            'size'  => 2,
            'depth' => 1
        );
        
        /**
         * IcoThumb::color
         * Color for size and depth strings and also the line
         * between the icon and strings
         *
         * @type array
         * @var  public
         **/
        var $color = array(
            'size'  => array(100, 100, 100),
            'depth' => array(100, 100, 100),
            'line'  => array(180, 180, 180)
        );
        
        /**
         * IcoThumb::max_size
         * Maximum image size inside thumbnail
         *
         * @type integer
         * @var  public
         **/
        var $max_size = 128;

        /**
         * IcoThumb::IcoThumb()
         * Class constructor
         *
         * @param   optional    string   $path   Path to ICO file
         * @return              void
         **/
        function IcoThumb($path = '') {
            if (!class_exists('Ico')) {
                include dirname(__FILE__) . '/class.ico.php';
            }

            if (strlen($path) > 0) {
                $this->LoadIco($path);
            }
        }

        /**
         * IcoThumb::LoadIco()
         * Load an ICO file (don't need to call this is if fill the
         * parameter in the class constructor)
         *
         * @param   string   $path   Path to ICO file
         * @return  void
         **/
        function LoadIco($path) {
            $this->ico = new Ico($path);
            $this->ico->bgcolor = array(255, 255, 255);
        }

        /**
         * IcoThumb::GetThumb()
         * Return an image resource with the thumbnail
         *
         * @param   integer     $total_icons    Total icons in thumbnail
         * @return  resource    Image resource (Thumbnail)
         **/
        function GetThumb($total_icons = 0) {
            if ($total_icons <= 0 || $total_icons > count($this->ico->formats)) {
                $total_icons = count($this->ico->formats);
            }

            /**
             * Get a list ordered by size desc, depth desc
             **/
            $icons = array();
            for ($i = 0; $i < count($this->ico->formats); $i++) {
                if ($this->ico->formats[$i]['Width'] <= $this->max_size) {
                    $icons[] = array(
                        'index' => $i,
                        'size'  => $this->ico->formats[$i]['Width'],
                        'depth' => $this->ico->formats[$i]['BitCount']
                    );
                }
            }
            for ($i = 0; $i < count($icons); $i++) {
                for ($j = $i + 1; $j < count($icons); $j++) {
                    if ($icons[$j]['size'] > $icons[$i]['size']) {
                        $tmp = $icons[$j];
                        $icons[$j] = $icons[$i];
                        $icons[$i] = $tmp;
                    } elseif ($icons[$j]['size'] == $icons[$i]['size'] && $icons[$j]['depth'] > $icons[$i]['depth']) {
                        $tmp = $icons[$j];
                        $icons[$j] = $icons[$i];
                        $icons[$i] = $tmp;
                    }
                }
            }

            /**
             * Chose icon indexes
             **/
            $last_size = 0;
            $chosen_icons = array();
            $p = 0;
            while ($total_icons > 0) {
            	if (!$this->use_diferent_depths) {
	                while (isset($icons[$p + 1]) && $icons[$p]['size'] == $last_size) $p++;
            	}
                if (!isset($icons[$p])) break;
                $chosen_icons[] = $icons[$p]['index'];
                $last_size = $icons[$p++]['size'];
                $total_icons--;
            }

            $width = $this->padding['left'];
            for ($i = 0; $i < count($chosen_icons); $i++) {
                if ($i > 0) {
                    $width += $this->padding['middle'];
                }
                $width += $this->ico->formats[$chosen_icons[$i]]['Width'];
            }
            $width += $this->padding['right'];

            $height = $this->padding['top'] + $this->ico->formats[$chosen_icons[0]]['Height'] + $this->padding['bottom'];

            if ($this->view_info) {
                $height += imagefontheight($this->font['size']) + imagefontheight($this->font['depth']) + 5;
            }

            $im = imagecreatetruecolor($width, $height);
            $c = imagecolorallocate($im, 255, 255, 255);
            imagefilledrectangle($im, 0, 0, $width, $height, $c);

            $x = $this->padding['left'];
            $y = $this->padding['top'] + $this->ico->formats[$chosen_icons[0]]['Height'];
            for ($i = 0; $i < count($chosen_icons); $i++) {
                $im_ico = $this->ico->GetIcon($chosen_icons[$i]);

                $w = $this->ico->formats[$chosen_icons[$i]]['Width'];
                $h = $this->ico->formats[$chosen_icons[$i]]['Height'];
                imagecopyresampled($im, $im_ico, $x, $y - $h, 0, 0, $w, $h, $w, $h);

                $x += $w + $this->padding['middle'];
            }

            if ($this->view_info) {
                $x = $this->padding['left'];
                $y += 2;
                $linecolor = $this->ico->AllocateColor($im, $this->color['line'][0], $this->color['line'][1], $this->color['line'][2]);
                $sizecolor = $this->ico->AllocateColor($im, $this->color['size'][0], $this->color['size'][1], $this->color['size'][2]);
                $depthcolor = $this->ico->AllocateColor($im, $this->color['depth'][0], $this->color['depth'][1], $this->color['depth'][2]);
                for ($i = 0; $i < count($chosen_icons); $i++) {
                    imageline($im, $x, $y, $x + $this->ico->formats[$chosen_icons[$i]]['Width'], $y, $linecolor);
                    $this->ImageStringCentered($im, $x, $y + 2, $this->ico->formats[$chosen_icons[$i]]['Width'], $this->font['size'], $sizecolor, $this->ico->formats[$chosen_icons[$i]]['Width'] . 'x' . $this->ico->formats[$chosen_icons[$i]]['Height']);
                    $this->ImageStringCentered($im, $x, $y + imagefontheight($this->font['size']) + 2, $this->ico->formats[$chosen_icons[$i]]['Width'], $this->font['depth'], $depthcolor, $this->ico->formats[$chosen_icons[$i]]['BitCount'] . ' bits');
                    $x += $this->padding['middle'] + $this->ico->formats[$chosen_icons[$i]]['Width'];
                }
            }

            header('Content-Type: image/png');
            imagepng($im);
            imagedestroy($im);
        }

        /**
         * IcoThumb::ImageStringCentered()
         * Draw a string on the image centered
         *
         * @param   resource   &$im         Image resource
         * @param   integer     $left       X coordinate
         * @param   integer     $top        Y coordinate
         * @param   integer     $width      Width of box to draw string inside
         * @param   integer     $font       Font
         * @param   integer     $color      Color index
         * @param   string      $text       String
         * @return  void
         **/
        function ImageStringCentered(&$im, $left, $top, $width, $font, $color, $text) {
            $text_width = imagefontwidth($font) * strlen($text);
            $left += (($width - $text_width) / 2);

            imagestring($im, $font, $left, $top, $text, $color);
        }
    }
?>