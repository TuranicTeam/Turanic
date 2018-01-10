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

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;

class Glowstone extends Transparent implements SolidLight {

	protected $id = self::GLOWSTONE_BLOCK;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Glowstone";
	}

	public function getHardness() : float{
		return 0.3;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getLightLevel() : int{
		return 15;
	}

	public function getDrops(Item $item) : array{
		if($item->getEnchantmentLevel(Enchantment::TYPE_MINING_SILK_TOUCH) > 0){
			return parent::getDrops($item);
		}else{
			$fortuneL = $item->getEnchantmentLevel(Enchantment::TYPE_MINING_FORTUNE);
			$fortuneL = $fortuneL > 3 ? 3 : $fortuneL;
			$times = [1, 1, 2, 3, 4];
			$time = $times[mt_rand(0, $fortuneL + 1)];
			$num = mt_rand(2, 4) * $time;
			$num = $num > 4 ? 4 : $num;
			return [
				Item::get(Item::GLOWSTONE_DUST, 0, $num)
			];
		}
	}
}
