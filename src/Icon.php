<?php

namespace Elphin\IcoFileLoader;

/**
 * An instance of icon holds the extracted data from a .ico file
 */
class Icon implements \ArrayAccess, \Countable, \Iterator
{
    /**
     * @var IconImage[]
     */
    private $images = [];

    /**
     * @var int iterator position
     */
    private $position = 0;

    /**
     * Returns best icon image with dimensions matching w,h
     * @param $w
     * @param $h
     */
    public function findBestForSize($w, $h): ?IconImage
    {
        $bestBitCount = 0;
        $best = null;
        foreach ($this->images as $image) {
            if ($image->width == $w && $image->height == $h && ($image->bitCount > $bestBitCount)) {
                $bestBitCount = $image->bitCount;
                $best = $image;
            }
        }
        return $best;
    }

    /**
     * Finds the highest quality image in the icon
     * @return IconImage
     */
    public function findBest()
    {
        $bestBitCount = 0;
        $bestWidth = 0;
        $best = null;
        foreach ($this->images as $image) {
            if (($image->width > $bestWidth) ||
                (($image->width == $bestWidth) && ($image->bitCount > $bestBitCount))
            ) {
                $bestWidth = $image->width;
                $bestBitCount = $image->bitCount;
                $best = $image;
            }
        }
        return $best;
    }

    /**
     * Count number of images in the icon
     * As this class implements Countable you can simply use count($icon) if you desire
     */
    public function count(): int
    {
        return count($this->images);
    }

    /**
     * Set an icon
     * This is an implementation of ArrayAccess allowing you to do $icon[$x]=$image
     * @param integer   $offset
     * @param IconImage $value
     */
    public function offsetSet($offset, $value): void
    {
        if (!$value instanceof IconImage) {
            throw new \InvalidArgumentException('Can only add IconImage instances to an Icon');
        }
        if (is_null($offset)) {
            $this->images[] = $value;
        } else {
            $this->images[$offset] = $value;
        }
    }

    /**
     * Check if image with particular index exists
     * This is an implementation of ArrayAccess allowing you to do isset($icon[$x])
     * @param integer $offset
     */
    public function offsetExists($offset): bool
    {
        return isset($this->images[$offset]);
    }

    /**
     * Remove image from icon
     * This is an implementation of ArrayAccess allowing you to do unset($icon[$x])
     * @param integer $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->images[$offset]);
    }

    /**
     * Get image from icon
     * This is an implementation of ArrayAccess allowing you to do $image = $icon[$x]
     * @param integer $offset
     */
    public function offsetGet($offset): ?IconImage
    {
        return isset($this->images[$offset]) ? $this->images[$offset] : null;
    }

    /**
     * Implements \Iterator allowing foreach($icon as $image){}
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Implements \Iterator allowing foreach($icon as $image){}
     */
    public function current(): IconImage
    {
        return $this->images[$this->position];
    }

    /**
     * Implements \Iterator allowing foreach($icon as $image){}
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Implements \Iterator allowing foreach($icon as $image){}
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Implements \Iterator allowing foreach($icon as $image){}
     */
    public function valid(): bool
    {
        return isset($this->images[$this->position]);
    }
}
