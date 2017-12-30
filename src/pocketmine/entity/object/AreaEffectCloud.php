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

namespace pocketmine\entity\object;

use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\item\Potion;
use pocketmine\level\particle\Particle;
use pocketmine\math\AxisAlignedBB;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

class AreaEffectCloud extends Entity {
    const NETWORK_ID = self::AREA_EFFECT_CLOUD;

    public $width = 5;
    public $height = 1;

    private $PotionId = 0;
    private $Radius = 3;
    private $RadiusOnUse = -0.5;
    private $RadiusPerTick = -0.005;
    private $WaitTime = 10;
    private $Duration = 600;
    private $DurationOnUse = 0;

    public function initEntity(){
        parent::initEntity();

        if(!$this->namedtag->hasTag("PotionId", ShortTag::class)){
            $this->namedtag->setShort("PotionId", $this->PotionId);
        }
        $this->PotionId = $this->namedtag->getShort("PotionId");

        if(!$this->namedtag->hasTag("Radius", FloatTag::class)){
            $this->namedtag->setFloat("Radius", $this->Radius);
        }
        $this->Radius = $this->namedtag->getFloat("Radius");

        if(!$this->namedtag->hasTag("RadiusOnUse", FloatTag::class)){
            $this->namedtag->setFloat("RadiusOnUse", $this->RadiusOnUse);
        }
        $this->RadiusOnUse = $this->namedtag->getFloat("RadiusOnUse");

        if(!$this->namedtag->hasTag("RadiusPerTick", FloatTag::class)){
            $this->namedtag->setFloat("RadiusPerTick", $this->RadiusPerTick);
        }
        $this->RadiusPerTick = $this->namedtag->getFloat("RadiusPerTick");

        if(!$this->namedtag->hasTag("RadiusPerTick", IntTag::class)){
            $this->namedtag->setInt("WaitTime", $this->WaitTime);
        }
        $this->WaitTime = $this->namedtag->getInt("WaitTime");

        if(!$this->namedtag->hasTag("Duration", IntTag::class)){
            $this->namedtag->setInt("Duration", $this->Duration);
        }
        $this->Duration = $this->namedtag->getInt("Duration");

        if(!$this->namedtag->hasTag("DurationOnUse", IntTag::class)){
            $this->namedtag->setInt("DurationOnUse", $this->DurationOnUse);
        }
        $this->DurationOnUse = $this->namedtag->getInt("DurationOnUse");

        $this->setDataProperty(self::DATA_AREA_EFFECT_CLOUD_PARTICLE_ID, self::DATA_TYPE_INT, Particle::TYPE_MOB_SPELL);//todo
        $this->setDataProperty(self::DATA_AREA_EFFECT_CLOUD_RADIUS, self::DATA_TYPE_FLOAT, $this->Radius);
        $this->setDataProperty(self::DATA_AREA_EFFECT_CLOUD_WAITING, self::DATA_TYPE_INT, $this->WaitTime);
        $this->setDataProperty(self::DATA_BOUNDING_BOX_HEIGHT, self::DATA_TYPE_FLOAT, 1);
        $this->setDataProperty(self::DATA_BOUNDING_BOX_WIDTH, self::DATA_TYPE_FLOAT, $this->Radius * 2);
        $this->setDataProperty(self::DATA_POTION_AMBIENT, self::DATA_TYPE_BYTE, 1);
    }

    public function onUpdate(int $tick){
        if($this->closed){
            return false;
        }

        $this->timings->startTiming();

        $hasUpdate = parent::onUpdate($tick);

        if($this->age > $this->Duration || $this->PotionId == 0 || $this->Radius <= 0){
            $this->close();
            $hasUpdate = true;
        }else{
            $effects = Potion::getEffectsById($this->PotionId);
            if(count($effects) <= 0){
                $this->close();
                $this->timings->stopTiming();

                return true;
            }
            /** @var Effect[] $effects */
            $firsteffect = $effects[0]; //Todo multiple effects
            $this->setDataProperty(self::DATA_POTION_COLOR, self::DATA_TYPE_INT, $firsteffect->getColor()->toARGB());
            $this->Radius += $this->RadiusPerTick;
            $this->setDataProperty(self::DATA_BOUNDING_BOX_WIDTH, self::DATA_TYPE_FLOAT, $this->Radius * 2);
            if($this->WaitTime > 0){
                $this->WaitTime--;
                $this->timings->stopTiming();

                return true;
            }
            $bb = new AxisAlignedBB($this->x - $this->Radius, $this->y, $this->z - $this->Radius, $this->x + $this->Radius, $this->y + $this->height, $this->z + $this->Radius);
            $used = false;
            foreach($this->getLevel()->getCollidingEntities($bb, $this) as $collidingEntity){
                if($collidingEntity instanceof Living && $collidingEntity->distanceSquared($this) <= $this->Radius ** 2){
                    $used = true;
                    foreach($effects as $eff){
                        $collidingEntity->addEffect($eff);
                    }
                }
            }
            if($used){
                $this->Duration -= $this->DurationOnUse;
                $this->Radius += $this->RadiusOnUse;
                $this->WaitTime = 10;
            }
        }

        $this->setDataProperty(self::DATA_AREA_EFFECT_CLOUD_RADIUS, self::DATA_TYPE_FLOAT, $this->Radius);
        $this->setDataProperty(self::DATA_AREA_EFFECT_CLOUD_WAITING, self::DATA_TYPE_INT, $this->WaitTime);

        $this->timings->stopTiming();

        return $hasUpdate;
    }

    public function spawnTo(Player $player){
        $pk = new AddEntityPacket();
        $pk->type = AreaEffectCloud::NETWORK_ID;
        $pk->entityRuntimeId = $this->getId();
        $pk->position = $this;
        $pk->motion = $this->getMotion();
        $pk->metadata = $this->dataProperties;
        $player->dataPacket($pk);

        parent::spawnTo($player);
    }

    public function getName(){
        return "Area Effect Cloud";
    }

    public function canCollideWith(Entity $entity){
        return $entity instanceof Living;
    }
}