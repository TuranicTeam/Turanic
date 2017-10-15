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

/**
 * All the Tile classes and related classes
 */
namespace pocketmine\tile;

use pocketmine\event\Timings;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;

abstract class Tile extends Position {

	const BREWING_STAND = "BrewingStand";
	const CHEST = "Chest";
	const DL_DETECTOR = "DayLightDetector";
	const ENCHANT_TABLE = "EnchantTable";
	const FLOWER_POT = "FlowerPot";
	const FURNACE = "Furnace";
	const MOB_SPAWNER = "MobSpawner";
	const SIGN = "Sign";
	const SKULL = "Skull";
	const ITEM_FRAME = "ItemFrame";
	const BED = "Bed";
	const DAY_LIGHT_DETECTOR = "DLDetector";
	const DISPENSER = "Dispenser";
	const CAULDRON = "Cauldron";
	const HOPPER = "Hopper";
	const ENDER_CHEST = "EnderChest";
	const DROPPER = "Dropper";
	const BEACON = "Beacon";
	const VIRTUAL_HOLDER = "VirtualHolder";
	const JUKEBOX = "Jukebox";
	const BANNER = "Banner";

	public static $tileCount = 1;

	private static $knownTiles = [];
	private static $shortNames = [];

	/** @var Chunk */
	public $chunk;
	public $name;
	public $id;
	public $x;
	public $y;
	public $z;
	public $attach;
	public $metadata;
	public $closed = false;
	public $namedtag;
	
	protected $lastUpdate;
	protected $server;
	protected $timings;

	/** @var \pocketmine\event\TimingsHandler */
	public $tickTimer;

	public static function init(){
		self::registerTile(Banner::class);
		self::registerTile(Bed::class);
		self::registerTile(BrewingStand::class);
		self::registerTile(Cauldron::class);
		self::registerTile(Chest::class);
		self::registerTile(Dispenser::class);
		self::registerTile(DLDetector::class);
		self::registerTile(Dropper::class);
		self::registerTile(EnchantTable::class);
		self::registerTile(FlowerPot::class);
		self::registerTile(Furnace::class);
		self::registerTile(ItemFrame::class);
		self::registerTile(MobSpawner::class);
		self::registerTile(Sign::class);
		self::registerTile(Skull::class);
		self::registerTile(VirtualHolder::class);
		self::registerTile(Jukebox::class);
	}

	/**
	 * @param string      $type
	 * @param Level       $level
	 * @param CompoundTag $nbt
	 * @param array       $args
	 *
	 * @return Tile
	 */
	public static function createTile($type, Level $level, CompoundTag $nbt, ...$args){
		if(isset(self::$knownTiles[$type])){
			$class = self::$knownTiles[$type];
			return new $class($level, $nbt, ...$args);
		}

		return null;
	}
	
	public static function createTileFromPosition(string $type, Position $pos, ...$args){
		$nbt = new CompoundTag("", [
			new StringTag("id", $type),
			new IntTag("x", (int) $pos->x),
			new IntTag("y", (int) $pos->y),
			new IntTag("z", (int) $pos->z),
		]);
		
		return self::createTile($type, $pos->level, $nbt, ...$args);
	}

	/**
	 * @param $className
	 *
	 * @return bool
	 */
	public static function registerTile($className){
		$class = new \ReflectionClass($className);
		if(is_a($className, Tile::class, true) and !$class->isAbstract()){
			self::$knownTiles[$class->getShortName()] = $className;
			self::$shortNames[$className] = $class->getShortName();
			return true;
		}

		return false;
	}

	/**
	 * Returns the short save name
	 *
	 * @return string
	 */
	public function getSaveId(){
		return self::$shortNames[static::class];
	}

	/**
	 * Tile constructor.
	 *
	 * @param Level       $level
	 * @param CompoundTag $nbt
	 */
	public function __construct(Level $level, CompoundTag $nbt){
	    parent::__construct($level, $nbt);
		$this->timings = Timings::getTileEntityTimings($this);

		$this->namedtag = $nbt;
		$this->server = $level->getServer();
		$this->setLevel($level);
		$this->chunk = $level->getChunk($this->namedtag["x"] >> 4, $this->namedtag["z"] >> 4, false);
		assert($this->chunk !== null);

		$this->name = "";
		$this->lastUpdate = microtime(true);
		$this->id = Tile::$tileCount++;
		$this->x = (int) $this->namedtag["x"];
		$this->y = (int) $this->namedtag["y"];
		$this->z = (int) $this->namedtag["z"];

		$this->chunk->addTile($this);
		$this->getLevel()->addTile($this);
		$this->tickTimer = Timings::getTileEntityTimings($this);
	}

	/**
	 * @return int
	 */
	public function getId(){
		return $this->id;
	}

	public function saveNBT(){
		$this->namedtag->id = new StringTag("id", $this->getSaveId());
		$this->namedtag->x = new IntTag("x", $this->x);
		$this->namedtag->y = new IntTag("y", $this->y);
		$this->namedtag->z = new IntTag("z", $this->z);
	}

	/**
	 * @return \pocketmine\block\Block
	 */
	public function getBlock(){
		return $this->level->getBlock($this);
	}

	/**
	 * @return bool
	 */
	public function onUpdate(){
		return false;
	}

	public final function scheduleUpdate(){
		$this->level->updateTiles[$this->id] = $this;
	}

	public function __destruct(){
		$this->close();
	}

	public function close(){
		if(!$this->closed){
			$this->closed = true;
			unset($this->level->updateTiles[$this->id]);
			if($this->chunk instanceof Chunk){
				$this->chunk->removeTile($this);
			}
			if(($level = $this->getLevel()) instanceof Level){
				$level->removeTile($this);
			}
			$this->level = null;
		}
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return $this->name;
	}

    public function getCleanedNBT(){
        $this->saveNBT();
        $tag = clone $this->namedtag;
        unset($tag->x, $tag->y, $tag->z, $tag->id);
        if($tag->getCount() > 0){
            return $tag;
        }else{
            return null;
        }
    }
    
    public function isClosed() : bool{
    	return $this->closed;
    }
}