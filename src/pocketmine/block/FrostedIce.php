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

namespace pocketmine\block;

use pocketmine\item\Item;

class FrostedIce extends Transparent {

    protected $id = self::FROSTED_ICE;

    /**
     * Ice constructor.
     */
    public function __construct($meta = 0){
        $this->meta = $meta;
    }

    public function getHardness(){
        return 0.5;
    }

    public function getName(){
        return "Frosted Ice";
    }

    public function getDrops(Item $item): array{
        return [];
    }
}