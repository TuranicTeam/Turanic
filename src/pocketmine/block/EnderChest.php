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

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\math\AxisAlignedBB;
use pocketmine\Player;
use pocketmine\tile\EnderChest as TileEnderChest;
use pocketmine\tile\Tile;

class EnderChest extends Transparent {

	protected $id = self::ENDER_CHEST;

	/**
	 * EnderChest constructor.
	 *
	 * @param int $meta
	 */
	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	/**
	 * @return bool
	 */
	public function canBeActivated() : bool{
		return true;
	}

	/**
	 * @return float
	 */
	public function getHardness(){
		return 22.5;
	}

	/**
	 * @return int
	 */
	public function getResistance(){
		return 3000;
	}

	/**
	 * @return int
	 */
	public function getLightLevel(){
		return 7;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return "Ender Chest";
	}

	/**
	 * @return int
	 */
	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}

	/**
	 * @return AxisAlignedBB
	 */
	protected function recalculateBoundingBox(){
		return new AxisAlignedBB(
			$this->x + 0.025,
			$this->y,
			$this->z + 0.025,
			$this->x + 0.975,
			$this->y + 0.95,
			$this->z + 0.975
		);
	}

	/**
	 * @param Item        $item
	 * @param Block       $block
	 * @param Block       $target
	 * @param int         $face
	 * @param float       $fx
	 * @param float       $fy
	 * @param float       $fz
	 * @param Player|null $player
	 *
	 * @return bool
	 */
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$faces = [
			0 => 4,
			1 => 2,
			2 => 5,
			3 => 3
		];

		$this->meta = $faces[$player instanceof Player ? $player->getDirection() : 0];

		$this->getLevel()->setBlock($block, $this, true, true);
		Tile::createTile(Tile::ENDER_CHEST, $this->getLevel(), TileEnderChest::createNBT($this, $face, $item, $player));

		return true;
	}

	/**
	 * @param Item        $item
	 * @param Player|null $player
	 *
	 * @return bool
	 */
	public function onActivate(Item $item, Player $player = null){
		if($player instanceof Player){
			$top = $this->getSide(1);
			if($top->isTransparent() !== true){
				return true;
			}

			if(!($this->getLevel()->getTile($this) instanceof TileEnderChest)){
				Tile::createTile(Tile::ENDER_CHEST, $this->getLevel(), TileEnderChest::createNBT($this));
			}

			if($player->isCreative() and $player->getServer()->limitedCreative){
				return true;
			}

			$player->getEnderChestInventory()->openAt($this);
		}

		return true;
	}

	/**
	 * @param Item $item
	 *
	 * @return array
	 */
	public function getDrops(Item $item) : array{
		if($item->hasEnchantment(Enchantment::TYPE_MINING_SILK_TOUCH)){
			return [
				[$this->id, 0, 1],
			];
		}
		return [
			[Item::OBSIDIAN, 0, 8],
		];
	}

    /**
     * @return bool
     */
    public function canHarvestWithHand(): bool{
        return false;
    }
}