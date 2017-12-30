<?php

/*
 *
 *    _______                    _
 *   |__   __|                  (_)
 *      | |_   _ _ __ __ _ _ __  _  ___
 *      | | | | | '__/ _` | '_ \| |/ __|
 *      | | |_| | | | (_| | | | | | (__
 *      |_|\__,_|_|  \__,_|_| |_|_|\___|
 *
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Turanic
 *
 */

declare(strict_types=1);

namespace pocketmine\utils;

class Color {

    const COLOR_DYE_BLACK = 0;//dye colors
    const COLOR_DYE_RED = 1;
    const COLOR_DYE_GREEN = 2;
    const COLOR_DYE_BROWN = 3;
    const COLOR_DYE_BLUE = 4;
    const COLOR_DYE_PURPLE = 5;
    const COLOR_DYE_CYAN = 6;
    const COLOR_DYE_LIGHT_GRAY = 7;
    const COLOR_DYE_GRAY = 8;
    const COLOR_DYE_PINK = 9;
    const COLOR_DYE_LIME = 10;
    const COLOR_DYE_YELLOW = 11;
    const COLOR_DYE_LIGHT_BLUE = 12;
    const COLOR_DYE_MAGENTA = 13;
    const COLOR_DYE_ORANGE = 14;
    const COLOR_DYE_WHITE = 15;

    /** @var int */
    protected $alpha, $red, $green, $blue;

    /** @var \SplFixedArray */
    public static $dyeColors = null;

    public static function init(){
        if(self::$dyeColors === null){
            self::$dyeColors = new \SplFixedArray(16); //What's the point of making a 256-long array for 16 objects?
            self::$dyeColors[self::COLOR_DYE_BLACK] = Color::getRGB(30, 27, 27);
            self::$dyeColors[self::COLOR_DYE_RED] = Color::getRGB(179, 49, 44);
            self::$dyeColors[self::COLOR_DYE_GREEN] = Color::getRGB(61, 81, 26);
            self::$dyeColors[self::COLOR_DYE_BROWN] = Color::getRGB(81, 48, 26);
            self::$dyeColors[self::COLOR_DYE_BLUE] = Color::getRGB(37, 49, 146);
            self::$dyeColors[self::COLOR_DYE_PURPLE] = Color::getRGB(123, 47, 190);
            self::$dyeColors[self::COLOR_DYE_CYAN] = Color::getRGB(40, 118, 151);
            self::$dyeColors[self::COLOR_DYE_LIGHT_GRAY] = Color::getRGB(153, 153, 153);
            self::$dyeColors[self::COLOR_DYE_GRAY] = Color::getRGB(67, 67, 67);
            self::$dyeColors[self::COLOR_DYE_PINK] = Color::getRGB(216, 129, 152);
            self::$dyeColors[self::COLOR_DYE_LIME] = Color::getRGB(65, 205, 52);
            self::$dyeColors[self::COLOR_DYE_YELLOW] = Color::getRGB(222, 207, 42);
            self::$dyeColors[self::COLOR_DYE_LIGHT_BLUE] = Color::getRGB(102, 137, 211);
            self::$dyeColors[self::COLOR_DYE_MAGENTA] = Color::getRGB(195, 84, 205);
            self::$dyeColors[self::COLOR_DYE_ORANGE] = Color::getRGB(235, 136, 68);
            self::$dyeColors[self::COLOR_DYE_WHITE] = Color::getRGB(240, 240, 240);
        }
    }

    /**
     * @param $r
     * @param $g
     * @param $b
     *
     * @return Color
     */
    public static function getRGB(int $r, int $g, int $b){
        return new Color($r, $g, $b);
    }

    /**
     * @param Color[] ...$colors
     *
     * @return Color
     */
    public static function averageColor(Color ...$colors){
        $tr = 0;//total red
        $tg = 0;//green
        $tb = 0;//blue
        $count = 0;
        foreach($colors as $c){
            $tr += $c->getRed();
            $tg += $c->getGreen();
            $tb += $c->getBlue();
            ++$count;
        }
        return new Color((int) $tr / $count, (int) $tg / $count, (int) $tb / $count);
    }

    /**
     * Mixes the supplied list of colours together to produce a result colour.
     *
     * @param Color[] ...$colors
     * @return Color
     * @throws \ArgumentCountError
     */
    public static function mix(Color ...$colors) : Color{
        $count = count($colors);
        if($count < 1){
            throw new \ArgumentCountError("No colors given");
        }

        $a = $r = $g = $b = 0;

        foreach($colors as $color){
            $a += $color->alpha;
            $r += $color->red;
            $g += $color->green;
            $b += $color->blue;
        }

        return new Color((int) ($r / $count), (int) ($g / $count), (int) ($b / $count), (int) ($a / $count));
    }

    /**
     * @param $id
     *
     * @return mixed|Color
     */
    public static function getDyeColor($id){
        if(isset(self::$dyeColors[$id])){
            return clone self::$dyeColors[$id];
        }
        return Color::getRGB(0, 0, 0);
    }

    /**
     * Color constructor.
     *
     * @param $r
     * @param $g
     * @param $b
     * @param int $a
     */
    public function __construct(int $r, int $g, int $b, int $a = 0xff){
        $this->red = $r & 0xff;
        $this->green = $g & 0xff;
        $this->blue = $b & 0xff;
        $this->alpha = $a & 0xff;
    }

    /**
     * @return int
     * @deprecated
     */
    public function getA() : int{
        return $this->getAlpha();
    }

    /**
     * Returns the alpha (transparency) value of this colour.
     * @return int
     */
    public function getAlpha() : int{
        return $this->alpha;
    }

    /**
     * Sets the alpha (opacity) value of this colour, lower = more transparent
     * @param int $a
     */
    public function setAlpha(int $a){
        $this->alpha = $a & 0xff;
    }

    /**
     * @return int
     */
    public function getRed(): int{
        return $this->red;
    }

    /**
     * @return int
     * @deprecated
     */
    public function getR() : int{
        return $this->getRed();
    }

    /**
     * @param int $r
     */
    public function setRed(int $r){
        $this->red = $r & 0xff;
    }

    /**
     * @param int $r
     * @deprecated
     */
    public function setR(int $r){
        $this->setRed($r);
    }

    /**
     * @return int
     */
    public function getGreen() : int{
        return $this->green;
    }

    /**
     * @return int
     * @deprecated
     */
    public function getG() : int{
        return $this->getGreen();
    }

    /**
     * @param int $g
     */
    public function setGreen(int $g){
        $this->green = $g & 0xff;
    }

    /**
     * @param int $g
     * @deprecated
     */
    public function setG(int $g){
        $this->setGreen($g);
    }

    /**
     * @return int
     */
    public function getBlue() : int{
        return $this->blue;
    }

    /**
     * @return int
     * @deprecated
     */
    public function getB() : int{
        return $this->getBlue();
    }

    /**
     * @param int $b
     */
    public function setBlue(int $b){
        $this->blue = $b & 0xff;
    }

    /**
     * @param int $b
     * @deprecated
     */
    public function setB(int $b){
        $this->setBlue($b);
    }

    /**
     * @return int
     */
    public function getColorCode(){
        return ($this->red << 16 | $this->green << 8 | $this->blue) & 0xffffff;
    }

    /**
     * @return int
     */
    public function toRGB() : int{
        return ($this->red << 16) | ($this->green << 8) | $this->blue;
    }

    /**
     * @return string
     */
    public function __toString(){
        return "Color(red:" . $this->red . ", green:" . $this->green . ", blue:" . $this->blue . ")";
    }

    /**
     * Returns a Color from the supplied RGB colour code (24-bit)
     * @param int $code
     *
     * @return Color
     */
    public static function fromRGB(int $code){
        return new Color(($code >> 16) & 0xff, ($code >> 8) & 0xff, $code & 0xff);
    }

    /**
     * Returns a Color from the supplied ARGB colour code (32-bit)
     *
     * @param int $code
     *
     * @return Color
     */
    public static function fromARGB(int $code){
        return new Color(($code >> 16) & 0xff, ($code >> 8) & 0xff, $code & 0xff, ($code >> 24) & 0xff);
    }

    /**
     * Returns an ARGB 32-bit colour value.
     * @return int
     */
    public function toARGB() : int{
        return ($this->alpha << 24) | ($this->red << 16) | ($this->green << 8) | $this->blue;
    }

    /**
     * Returns a little-endian ARGB 32-bit colour value.
     * @return int
     */
    public function toBGRA() : int{
        return ($this->blue << 24) | ($this->green << 16) | ($this->red << 8) | $this->alpha;
    }

    /**
     * Returns an RGBA 32-bit colour value.
     * @return int
     */
    public function toRGBA() : int{
        return ($this->red << 24) | ($this->green << 16) | ($this->blue << 8) | $this->alpha;
    }

    /**
     * Returns a little-endian RGBA colour value.
     * @return int
     */
    public function toABGR() : int{
        return ($this->alpha << 24) | ($this->blue << 16) | ($this->green << 8) | $this->red;
    }

    public static function fromABGR(int $code){
        return new Color($code & 0xff, ($code >> 8) & 0xff, ($code >> 16) & 0xff, ($code >> 24) & 0xff);
    }

    public function toArray() : array {
        return [$this->red, $this->green, $this->blue];
    }
}