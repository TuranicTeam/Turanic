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

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\Tile;
use pocketmine\tile\Banner as TileBanner;

class StandingBanner extends Transparent{

	protected $id = self::STANDING_BANNER;

	protected $itemId = Item::BANNER;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getHardness(){
		return 1;
	}

	public function isSolid(){
		return false;
	}

	public function getName() : string{
		return "Standing Banner";
	}

	protected function recalculateBoundingBox(){
		return null;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
        if ($face !== Vector3::SIDE_DOWN) {
            if($face === Vector3::SIDE_UP and $player !== null){
                $this->meta = floor((($player->yaw + 180) * 16 / 360) + 0.5) & 0x0f;
                $this->getLevel()->setBlock($block, $this, true);
            }else{
                $this->meta = $face;
                $this->getLevel()->setBlock($block, Block::get(Block::WALL_BANNER, $this->meta), true);
            }

            Tile::createTile(Tile::BANNER, $this->getLevel(), TileBanner::createNBT($this, $face, $item, $player));
            return true;
        }

        return false;
    }

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			if($this->getSide(Vector3::SIDE_DOWN)->getId() === self::AIR){
				$this->getLevel()->useBreakOn($this);
				return Level::BLOCK_UPDATE_NORMAL;
			}
		}

		return false;
	}

	public function getToolType(){
		return Tool::TYPE_AXE;
	}

	public function getVariantBitmask() : int{
		return 0;
	}

	public function getDrops(Item $item): array{
		return [];
	}

	public function onBreak(Item $item, Player $player = null){
		if(($tile = $this->level->getTile($this)) !== null) {
			$this->level->dropItem($this, Item::get(Item::BANNER)->setNamedTag($tile->getCleanedNBT()));
		}
		
		return parent::onBreak($item);
	}
}