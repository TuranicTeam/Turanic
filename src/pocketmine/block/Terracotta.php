<?php

/*
 *
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
 *
*/

namespace pocketmine\block;


use pocketmine\item\Tool;
use pocketmine\item\Item;

class Terracotta extends Solid {

	protected $id = self::TERRACOTTA;

    /**
     * Terracotta constructor.
     * @param int $meta
     */
	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return "Terracotta";
	}

	/**
	 * @return int
	 */
	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}

	/**
	 * @return float
	 */
	public function getHardness(){
		return 1.25;
	}

    /**
     * @param Item $item
     * @return array
     */
    public function getDrops(Item $item): array{
        if($item->isPickaxe() >= 1){
            return [
                [Item::TERRACOTTA, $this->meta, 1],
            ];
        }
        return [];
    }
}