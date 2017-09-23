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

namespace pocketmine;

use pocketmine\block\Air;
use pocketmine\block\Fire;
use pocketmine\entity\Projectile;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerToggleGlideEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\win10\Win10InvLogic;
use pocketmine\item\Potion;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\block\Block;
use pocketmine\block\PressurePlate;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Arrow;
use pocketmine\entity\Attribute;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\entity\FishingHook;
use pocketmine\entity\Human;
use pocketmine\entity\Item as DroppedItem;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryPickupArrowEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\player\cheat\PlayerIllegalMoveEvent;
use pocketmine\event\player\PlayerAchievementAwardedEvent;
use pocketmine\event\player\PlayerAnimationEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerBedLeaveEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerTextPreSendEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\TextContainer;
use pocketmine\event\Timings;
use pocketmine\event\TranslationContainer;
use pocketmine\inventory\BaseTransaction;
use pocketmine\inventory\BigShapedRecipe;
use pocketmine\inventory\BigShapelessRecipe;
use pocketmine\inventory\EnchantInventory;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\inventory\ShapedRecipe;
use pocketmine\inventory\ShapelessRecipe;
use pocketmine\item\Item;
use pocketmine\level\ChunkLoader;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\level\WeakPosition;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\metadata\MetadataValue;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\protocol\ChangeDimensionPacket;
use pocketmine\network\mcpe\protocol\ChunkRadiusUpdatedPacket;
use pocketmine\network\mcpe\protocol\ContainerSetContentPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\DisconnectPacket;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\FullChunkDataPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\ResourcePacksInfoPacket;
use pocketmine\network\mcpe\protocol\SetDifficultyPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlayStatusPacket;
use pocketmine\network\mcpe\protocol\ResourcePackClientResponsePacket;
use pocketmine\network\mcpe\protocol\ResourcePackStackPacket;
use pocketmine\network\mcpe\protocol\RespawnPacket;
use pocketmine\network\mcpe\protocol\SetEntityMotionPacket;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;
use pocketmine\network\mcpe\protocol\SetSpawnPositionPacket;
use pocketmine\network\mcpe\protocol\SetTimePacket;
use pocketmine\network\mcpe\protocol\SetTitlePacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\TakeItemEntityPacket;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\SourceInterface;
use pocketmine\permission\PermissibleBase;
use pocketmine\permission\PermissionAttachment;
use pocketmine\plugin\Plugin;
use pocketmine\tile\Sign;
use pocketmine\tile\Spawnable;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat;

class Player extends Human implements CommandSender, InventoryHolder, ChunkLoader, IPlayer{

    const SURVIVAL = 0;
    const CREATIVE = 1;
    const ADVENTURE = 2;
    const SPECTATOR = 3;
    const VIEW = Player::SPECTATOR;

    const CRAFTING_SMALL = 0;
    const CRAFTING_BIG = 1;
    const CRAFTING_ANVIL = 2;
    const CRAFTING_ENCHANT = 3;

    /** @var SourceInterface */
    protected $interface;

    /** @var bool */
    public $playedBefore = false;
    public $spawned = false;
    public $loggedIn = false;
    public $gamemode;
    public $lastBreak;

    protected $windowCnt = 2;
    /** @var \SplObjectStorage<Inventory> */
    protected $windows;
    /** @var Inventory[] */
    protected $windowIndex = [];

    protected $messageCounter = 2;

    private $clientSecret;

    /** @var Vector3 */
    public $speed = null;

    public $achievements = [];

    public $craftingType = self::CRAFTING_SMALL; //0 = 2x2 crafting, 1 = 3x3 crafting, 2 = anvil, 3 = enchanting

    public $creationTime = 0;

    protected $randomClientId;

    protected $protocol;

    /** @var Vector3 */
    protected $forceMovement = null;
    /** @var Vector3 */
    protected $teleportPosition = null;
    protected $connected = true;
    protected $ip;
    protected $removeFormat = false;
    protected $port;
    protected $username;
    protected $iusername;
    protected $displayName;
    protected $startAction = -1;
    /** @var Vector3 */
    protected $sleeping = null;
    protected $clientID = null;

    protected $deviceModel;
    protected $deviceOS;

    private $loaderId = null;

    protected $stepHeight = 0.6;

    public $usedChunks = [];
    protected $chunkLoadCount = 0;
    protected $loadQueue = [];
    protected $nextChunkOrderRun = 5;

    /** @var Player[] */
    protected $hiddenPlayers = [];

    /** @var Vector3 */
    protected $newPosition;

    protected $viewDistance = -1;
    protected $chunksPerTick;
    protected $spawnThreshold;
    /** @var null|WeakPosition */
    private $spawnPosition = null;

    protected $inAirTicks = 0;
    protected $startAirTicks = 5;

    protected $autoJump = true;
    protected $allowFlight = false;
    protected $flying = false;
    protected $muted = false;

    protected $allowMovementCheats = false;
    protected $allowInstaBreak = false;

    private $needACK = [];

    private $batchedPackets = [];

    /** @var PermissibleBase */
    private $perm = null;

    public $weatherData = [0, 0, 0];

    /** @var Vector3 */
    public $fromPos = null;
    private $portalTime = 0;
    protected $shouldSendStatus = false;
    /** @var  Position */
    private $shouldResPos;

    /** @var FishingHook */
    public $fishingHook = null;

    /** @var Position[] */
    public $selectedPos = [];
    /** @var Level[] */
    public $selectedLev = [];

    /** @var Item[] */
    protected $personalCreativeItems = [];

    /** @var int */
    protected $lastEnderPearlUse = 0;

    /** @var null|string */
    protected $butonText = null;
    protected $inventoryType;
    protected $languageCode;
    protected $currentWindow = null;
    protected $currentWindowId = -1;

    public function getCurrentWindowId() {
        return $this->currentWindowId;
    }

    public function getCurrentWindow() {
        return $this->currentWindow;
    }

    /**
     * @param FishingHook $entity
     *
     * @return bool
     */
    public function linkHookToPlayer(FishingHook $entity)
    {
        if ($entity->isAlive()) {
            $this->setFishingHook($entity);
            $pk = new EntityEventPacket();
            $pk->eid = $this->getFishingHook()->getId();
            $pk->event = EntityEventPacket::FISH_HOOK_POSITION;
            $this->server->broadcastPacket($this->level->getPlayers(), $pk);
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function unlinkHookFromPlayer()
    {
        if ($this->fishingHook instanceof FishingHook) {
            $pk = new EntityEventPacket();
            $pk->eid = $this->fishingHook->getId();
            $pk->event = EntityEventPacket::FISH_HOOK_TEASE;
            $this->server->broadcastPacket($this->level->getPlayers(), $pk);
            $this->setFishingHook();
            return true;
        }
        return false;
    }

    /**
     * @param string $text
     */
    public function setButonText(string $text)
    {
        $this->setDataProperty(self::DATA_INTERACTIVE_TAG, self::DATA_TYPE_STRING, $text);
    }

    /**
     * @return bool
     */
    public function isFishing()
    {
        return ($this->fishingHook instanceof FishingHook);
    }

    /**
     * @return FishingHook
     */
    public function getFishingHook()
    {
        return $this->fishingHook;
    }

    /**
     * @param FishingHook|null $entity
     */
    public function setFishingHook(FishingHook $entity = null)
    {
        if ($entity == null and $this->fishingHook instanceof FishingHook) {
            $this->fishingHook->close();
        }
        $this->fishingHook = $entity;
    }

    /**
     * @return mixed
     */
    public function getDeviceModel()
    {
        return $this->deviceModel;
    }

    /**
     * @return mixed
     */
    public function getDeviceOS()
    {
        return $this->deviceOS;
    }

    /**
     * @return Item
     */
    public function getItemInHand()
    {
        return $this->inventory->getItemInHand();
    }

    /**
     * @return TranslationContainer
     */
    public function getLeaveMessage()
    {
        return new TranslationContainer(TextFormat::YELLOW . "%multiplayer.player.left", [
            $this->getDisplayName()
        ]);
    }

    /**
     * This might disappear in the future.
     * Please use getUniqueId() instead (IP + clientId + name combo, in the future it'll change to real UUID for online
     * auth)
     */
    public function getClientId()
    {
        return $this->randomClientId;
    }

    /**
     * @return mixed
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @return bool
     */
    public function isBanned()
    {
        return $this->server->getNameBans()->isBanned(strtolower($this->getName()));
    }

    /**
     * @param bool $value
     */
    public function setBanned($value)
    {
        if ($value === true) {
            $this->server->getNameBans()->addBan($this->getName(), null, null, null);
            $this->kick(TextFormat::RED . "You have been banned");
        } else {
            $this->server->getNameBans()->remove($this->getName());
        }
    }

    /**
     * @return bool
     */
    public function isWhitelisted(): bool
    {
        return $this->server->isWhitelisted(strtolower($this->getName()));
    }

    /**
     * @param bool $value
     */
    public function setWhitelisted($value)
    {
        if ($value === true) {
            $this->server->addWhitelist(strtolower($this->getName()));
        } else {
            $this->server->removeWhitelist(strtolower($this->getName()));
        }
    }

    /**
     * @return $this
     */
    public function getPlayer()
    {
        return $this;
    }

    /**
     * @return null
     */
    public function getFirstPlayed()
    {
        return $this->namedtag instanceof CompoundTag ? $this->namedtag["firstPlayed"] : null;
    }

    /**
     * @return null
     */
    public function getLastPlayed()
    {
        return $this->namedtag instanceof CompoundTag ? $this->namedtag["lastPlayed"] : null;
    }

    /**
     * @return bool
     */
    public function hasPlayedBefore()
    {
        return $this->playedBefore;
    }

    /**
     * @param $value
     */
    public function setAllowFlight($value)
    {
        $this->allowFlight = (bool)$value;
        $this->sendSettings();
    }

    /**
     * @return bool
     */
    public function getAllowFlight(): bool
    {
        return $this->allowFlight;
    }

    /**
     * @param bool $value
     */
    public function setMuted(bool $value){
        $this->muted = $value;
        $this->sendSettings();
    }

    /**
     * @return bool
     */
    public function isMuted() : bool{
        return $this->muted;
    }

    /**
     * @param bool $value
     */
    public function setFlying(bool $value)
    {
        $this->flying = $value;
        $this->sendSettings();
    }

    /**
     * @return bool
     */
    public function isFlying(): bool
    {
        return $this->flying;
    }

    /**
     * @param $value
     */
    public function setAutoJump($value)
    {
        $this->autoJump = $value;
        $this->sendSettings();
    }

    /**
     * @return bool
     */
    public function hasAutoJump(): bool
    {
        return $this->autoJump;
    }

    /**
     * @return bool
     */
    public function allowMovementCheats(): bool
    {
        return $this->allowMovementCheats;
    }

    /**
     * @param bool $value
     */
    public function setAllowMovementCheats(bool $value = false)
    {
        $this->allowMovementCheats = $value;
    }

    /**
     * @return bool
     */
    public function allowInstaBreak(): bool
    {
        return $this->allowInstaBreak;
    }

    /**
     * @param bool $value
     */
    public function setAllowInstaBreak(bool $value = false)
    {
        $this->allowInstaBreak = $value;
    }

    /**
     * @param Player $player
     */
    public function spawnTo(Player $player)
    {
        if ($this->spawned and $player->spawned and $this->isAlive() and $player->isAlive() and $player->getLevel() === $this->level and $player->canSee($this) and !$this->isSpectator()) {
            parent::spawnTo($player);
        }
    }

    /**
     * @return Server
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @return bool
     */
    public function getRemoveFormat()
    {
        return $this->removeFormat;
    }

    /**
     * @param bool $remove
     */
    public function setRemoveFormat($remove = true)
    {
        $this->removeFormat = (bool)$remove;
    }

    /**
     * @param Player $player
     *
     * @return bool
     */
    public function canSee(Player $player): bool
    {
        return !isset($this->hiddenPlayers[$player->getRawUniqueId()]);
    }

    /**
     * @param Player $player
     */
    public function hidePlayer(Player $player)
    {
        if ($player === $this) {
            return;
        }
        $this->hiddenPlayers[$player->getRawUniqueId()] = $player;
        $player->despawnFrom($this);
    }

    /**
     * @param Player $player
     */
    public function showPlayer(Player $player)
    {
        if ($player === $this) {
            return;
        }
        unset($this->hiddenPlayers[$player->getRawUniqueId()]);
        if ($player->isOnline()) {
            $player->spawnTo($this);
        }
    }

    /**
     * @param Entity $entity
     *
     * @return bool
     */
    public function canCollideWith(Entity $entity): bool
    {
        return false;
    }

    public function resetFallDistance()
    {
        parent::resetFallDistance();
        if ($this->inAirTicks !== 0) {
            $this->startAirTicks = 5;
        }
        $this->inAirTicks = 0;
    }

    /**
     * @return int
     */
    public function getViewDistance(): int
    {
        return $this->viewDistance;
    }

    /**
     * @param int $distance
     */
    public function setViewDistance(int $distance)
    {
        $this->viewDistance = $this->server->getAllowedViewDistance($distance);

        $this->spawnThreshold = (int)(min($this->viewDistance, $this->server->getProperty("chunk-sending.spawn-radius", 4)) ** 2 * M_PI);

        $pk = new ChunkRadiusUpdatedPacket();
        $pk->radius = $this->viewDistance;
        $this->dataPacket($pk);
    }

    /**
     * @return bool
     */
    public function isOnline(): bool
    {
        return $this->connected === true and $this->loggedIn === true;
    }

    /**
     * @return bool
     */
    public function isOp(): bool
    {
        return $this->server->isOp($this->getName());
    }

    /**
     * @param bool $value
     */
    public function setOp($value)
    {
        if ($value === $this->isOp()) {
            return;
        }

        if ($value === true) {
            $this->server->addOp($this->getName());
        } else {
            $this->server->removeOp($this->getName());
        }

        $this->recalculatePermissions();
        $this->sendSettings();
    }

    /**
     * @param permission\Permission|string $name
     *
     * @return bool
     */
    public function isPermissionSet($name)
    {
        return $this->perm->isPermissionSet($name);
    }

    /**
     * @param permission\Permission|string $name
     *
     * @return bool
     *
     * @throws \InvalidStateException if the player is closed
     */
    public function hasPermission($name)
    {
        if ($this->closed) {
            throw new \InvalidStateException("Trying to get permissions of closed player");
        }
        return $this->perm->hasPermission($name);
    }

    /**
     * @param Plugin $plugin
     * @param string $name
     * @param bool $value
     *
     * @return permission\PermissionAttachment|null
     */
    public function addAttachment(Plugin $plugin, $name = null, $value = null)
    {
        if ($this->perm == null) return null;
        return $this->perm->addAttachment($plugin, $name, $value);
    }


    /**
     * @param PermissionAttachment $attachment
     *
     * @return bool
     */
    public function removeAttachment(PermissionAttachment $attachment)
    {
        if ($this->perm == null) {
            return false;
        }
        $this->perm->removeAttachment($attachment);
        return true;
    }

    public function recalculatePermissions()
    {
        $this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_USERS, $this);
        $this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);

        if ($this->perm === null) {
            return;
        }

        $this->perm->recalculatePermissions();

        if ($this->hasPermission(Server::BROADCAST_CHANNEL_USERS)) {
            $this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_USERS, $this);
        }
        if ($this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE)) {
            $this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);
        }

        $this->sendCommandData();
    }

    /**
     * @return permission\PermissionAttachmentInfo[]
     */
    public function getEffectivePermissions()
    {
        return $this->perm->getEffectivePermissions();
    }

    public function sendCommandData()
    {
        $data = new \stdClass();
        $count = 0;
        foreach ($this->server->getCommandMap()->getCommands() as $command) {
            if ($this->hasPermission($command->getPermission()) or $command->getPermission() == null) {
                if (($cmdData = $command->generateCustomCommandData($this)) !== null) {
                    ++$count;
                    $data->{$command->getName()}->versions[0] = $cmdData;
                }
            }
        }

        if ($count > 0) {
            $pk = new AvailableCommandsPacket();
            $pk->commands = json_encode($data);
            $this->dataPacket($pk);
        }
    }

    /**
     * @param SourceInterface $interface
     * @param null $clientID
     * @param string $ip
     * @param int $port
     */
    public function __construct(SourceInterface $interface, $clientID, $ip, $port){
        $this->interface = $interface;
        $this->windows = new \SplObjectStorage();
        $this->perm = new PermissibleBase($this);
        $this->namedtag = new CompoundTag();
        $this->server = Server::getInstance();
        $this->lastBreak = PHP_INT_MAX;
        $this->ip = $ip;
        $this->port = $port;
        $this->clientID = $clientID;
        $this->loaderId = Level::generateChunkLoaderId($this);
        $this->chunksPerTick = (int)$this->server->getProperty("chunk-sending.per-tick", 4);
        $this->spawnThreshold = (int)(($this->server->getProperty("chunk-sending.spawn-radius", 4) ** 2) * M_PI);
        $this->spawnPosition = null;
        $this->gamemode = $this->server->getGamemode();
        $this->setLevel($this->server->getDefaultLevel());
        $this->newPosition = new Vector3(0, 0, 0);
        $this->boundingBox = new AxisAlignedBB(0, 0, 0, 0, 0, 0);

        $this->uuid = null;
        $this->rawUUID = null;

        $this->creationTime = microtime(true);

        $this->allowMovementCheats = (bool)$this->server->getProperty("player.anti-cheat.allow-movement-cheats", false);
        $this->allowInstaBreak = (bool)$this->server->getProperty("player.anti-cheat.allow-instabreak", false);
    }

    /**
     * @param string $achievementId
     */
    public function removeAchievement($achievementId)
    {
        if ($this->hasAchievement($achievementId)) {
            $this->achievements[$achievementId] = false;
        }
    }

    /**
     * @param string $achievementId
     *
     * @return bool
     */
    public function hasAchievement($achievementId): bool
    {
        if (!isset(Achievement::$list[$achievementId]) or !isset($this->achievements)) {
            $this->achievements = [];

            return false;
        }

        return isset($this->achievements[$achievementId]) and $this->achievements[$achievementId] != false;
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->connected === true;
    }

    /**
     * Gets the "friendly" name to display of this player to use in the chat.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param string $name
     */
    public function setDisplayName($name)
    {
        $this->displayName = $name;
        if ($this->spawned) {
            $this->server->updatePlayerListData($this->getUniqueId(), $this->getId(), $this->getDisplayName(), $this->getSkinId(), $this->getSkinData());
        }
    }

    public function setSkin($str, $skinId, $skinGeometryName = "", $skinGeometryData = "", $capeData = ""){
        parent::setSkin($str, $skinId, $skinGeometryName, $skinGeometryData, $capeData);
        if ($this->spawned) {
            $this->server->updatePlayerListData($this->getUniqueId(), $this->getId(), $this->getDisplayName(), $skinId, $str);
        }
    }

    /**
     * Gets the player IP address
     *
     * @return string
     */
    public function getAddress(): string
    {
        return $this->ip;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return Position
     */
    public function getNextPosition()
    {
        return $this->newPosition !== null ? new Position($this->newPosition->x, $this->newPosition->y, $this->newPosition->z, $this->level) : $this->getPosition();
    }

    /**
     * @return bool
     */
    public function isSleeping(): bool
    {
        return $this->sleeping !== null;
    }

    /**
     * @return int
     */
    public function getInAirTicks()
    {
        return $this->inAirTicks;
    }

    /**
     * @param Level $targetLevel
     *
     * @return bool|void
     */
    protected function switchLevel(Level $targetLevel)
    {
        $oldLevel = $this->level;
        if (parent::switchLevel($targetLevel)) {
            foreach ($this->usedChunks as $index => $d) {
                Level::getXZ($index, $X, $Z);
                $this->unloadChunk($X, $Z, $oldLevel);
            }

            $this->usedChunks = [];
            $pk = new SetTimePacket();
            $pk->time = $this->level->getTime();
            $pk->started = $this->level->stopTime == false;
            $this->dataPacket($pk);

            if ($targetLevel->getDimension() != $oldLevel->getDimension()) {
                $pk = new ChangeDimensionPacket();
                $pk->dimension = $targetLevel->getDimension();
                $pk->x = $this->x;
                $pk->y = $this->y;
                $pk->z = $this->z;
                $this->dataPacket($pk);
                //$this->shouldSendStatus = true;
                $pk1 = new PlayStatusPacket();
                $pk1->status = PlayStatusPacket::PLAYER_SPAWN;
                $this->dataPacket($pk1);
            }
            $targetLevel->getWeather()->sendWeather($this);

            if ($this->spawned) {
                $this->spawnToAll();
            }
        }
    }

    /**
     * @param            $x
     * @param            $z
     * @param Level|null $level
     */
    private function unloadChunk($x, $z, Level $level = null)
    {
        $level = $level === null ? $this->level : $level;
        $index = Level::chunkHash($x, $z);
        if (isset($this->usedChunks[$index])) {
            foreach ($level->getChunkEntities($x, $z) as $entity) {
                if ($entity !== $this) {
                    $entity->despawnFrom($this);
                }
            }

            unset($this->usedChunks[$index]);
        }
        $level->unregisterChunkLoader($this, $x, $z);
        unset($this->loadQueue[$index]);
    }

    /**
     * @return Position
     */
    public function getSpawn()
    {
        if ($this->hasValidSpawnPosition()) {
            return $this->spawnPosition;
        } else {
            $level = $this->server->getDefaultLevel();

            return $level->getSafeSpawn();
        }
    }

    /**
     * @return bool
     */
    public function hasValidSpawnPosition(): bool
    {
        return $this->spawnPosition instanceof WeakPosition and $this->spawnPosition->isValid();
    }

    /**
     * @param $x
     * @param $z
     * @param $payload
     */
    public function sendChunk($x, $z, $payload)
    {
        if ($this->connected === false) {
            return;
        }

        $this->usedChunks[Level::chunkHash($x, $z)] = true;
        $this->chunkLoadCount++;

        if ($payload instanceof DataPacket) {
            $this->dataPacket($payload);
        } else {
            $pk = new FullChunkDataPacket();
            $pk->chunkX = $x;
            $pk->chunkZ = $z;
            $pk->data = $payload;
            $this->batchDataPacket($pk);
        }

        if ($this->spawned) {
            foreach ($this->level->getChunkEntities($x, $z) as $entity) {
                if ($entity !== $this and !$entity->closed and $entity->isAlive()) {
                    $entity->spawnTo($this);
                }
            }
        }
    }

    protected function sendNextChunk()
    {
        if ($this->connected === false) {
            return;
        }

        Timings::$playerChunkSendTimer->startTiming();

        $count = 0;
        foreach ($this->loadQueue as $index => $distance) {
            if ($count >= $this->chunksPerTick) {
                break;
            }

            $X = null;
            $Z = null;
            Level::getXZ($index, $X, $Z);

            ++$count;

            $this->usedChunks[$index] = false;
            $this->level->registerChunkLoader($this, $X, $Z, false);

            if (!$this->level->populateChunk($X, $Z)) {
                continue;
            }

            unset($this->loadQueue[$index]);
            $this->level->requestChunk($X, $Z, $this);
        }

        if ($this->chunkLoadCount >= $this->spawnThreshold and $this->spawned === false and $this->teleportPosition === null) {
            $this->doFirstSpawn();
        }

        Timings::$playerChunkSendTimer->stopTiming();
    }

    protected function doFirstSpawn()
    {
        $this->spawned = true;

        $this->sendPotionEffects($this);
        $this->sendData($this);

        $pk = new SetTimePacket();
        $pk->time = $this->level->getTime();
        $pk->started = $this->level->stopTime == false;
        $this->dataPacket($pk);

        $pos = $this->level->getSafeSpawn($this);

        $this->server->getPluginManager()->callEvent($ev = new PlayerRespawnEvent($this, $pos));

        $pos = $ev->getRespawnPosition();
        if ($pos->getY() < 127) $pos = $pos->add(0, 0.2, 0);

        /*$pk = new RespawnPacket();
		$pk->x = $pos->x;
		$pk->y = $pos->y;
		$pk->z = $pos->z;
		$this->dataPacket($pk);*/

        $pk = new PlayStatusPacket();
        $pk->status = PlayStatusPacket::PLAYER_SPAWN;
        $this->dataPacket($pk);

        $this->noDamageTicks = 60;

        foreach ($this->usedChunks as $index => $c) {
            Level::getXZ($index, $chunkX, $chunkZ);
            foreach ($this->level->getChunkEntities($chunkX, $chunkZ) as $entity) {
                if ($entity !== $this and !$entity->closed and $entity->isAlive()) {
                    $entity->spawnTo($this);
                }
            }
        }

        $this->teleport($pos);

        $this->allowFlight = (($this->gamemode == 3) or ($this->gamemode == 1));
        $this->setHealth($this->getHealth());

        $this->server->getPluginManager()->callEvent($ev = new PlayerJoinEvent($this, new TranslationContainer(TextFormat::YELLOW . "%multiplayer.player.joined", [
            $this->getDisplayName()
        ])));

        $this->sendSettings();

        if (strlen(trim($msg = $ev->getJoinMessage())) > 0) {
            if ($this->server->playerMsgType === Server:: PLAYER_MSG_TYPE_MESSAGE) $this->server->broadcastMessage($msg);
            elseif ($this->server->playerMsgType === Server::PLAYER_MSG_TYPE_TIP) $this->server->broadcastTip(str_replace("@player", $this->getName(), $this->server->playerLoginMsg));
            elseif ($this->server->playerMsgType === Server::PLAYER_MSG_TYPE_POPUP) $this->server->broadcastPopup(str_replace("@player", $this->getName(), $this->server->playerLoginMsg));
        }

        $this->server->onPlayerLogin($this);
        $this->spawnToAll();

        $this->level->getWeather()->sendWeather($this);

        if ($this->server->dserverConfig["enable"] and $this->server->dserverConfig["queryAutoUpdate"]) {
            $this->server->updateQuery();
        }

        /*if($this->server->getUpdater()->hasUpdate() and $this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE)){
			$this->server->getUpdater()->showPlayerUpdate($this);
		}*/

        if ($this->getHealth() <= 0) {
            $pk = new RespawnPacket();
            $pos = $this->getSpawn();
            $pk->x = $pos->x;
            $pk->y = $pos->y;
            $pk->z = $pos->z;
            $this->dataPacket($pk);
        }

        $this->inventory->sendContents($this);
        $this->inventory->sendArmorContents($this);

    }

    /**
     * @return bool
     */
    protected function orderChunks()
    {
        if ($this->connected === false or $this->viewDistance === -1) {
            return false;
        }

        Timings::$playerChunkOrderTimer->startTiming();

        $this->nextChunkOrderRun = 200;

        $radius = $this->server->getAllowedViewDistance($this->viewDistance);
        $radiusSquared = $radius ** 2;

        $newOrder = [];
        $unloadChunks = $this->usedChunks;

        $centerX = $this->x >> 4;
        $centerZ = $this->z >> 4;

        for ($x = 0; $x < $radius; ++$x) {
            for ($z = 0; $z <= $x; ++$z) {
                if (($x ** 2 + $z ** 2) > $radiusSquared) {
                    break; //skip to next band
                }

                //If the chunk is in the radius, others at the same offsets in different quadrants are also guaranteed to be.

                /* Top right quadrant */
                if (!isset($this->usedChunks[$index = Level::chunkHash($centerX + $x, $centerZ + $z)]) or $this->usedChunks[$index] === false) {
                    $newOrder[$index] = true;
                }
                unset($unloadChunks[$index]);

                /* Top left quadrant */
                if (!isset($this->usedChunks[$index = Level::chunkHash($centerX - $x - 1, $centerZ + $z)]) or $this->usedChunks[$index] === false) {
                    $newOrder[$index] = true;
                }
                unset($unloadChunks[$index]);

                /* Bottom right quadrant */
                if (!isset($this->usedChunks[$index = Level::chunkHash($centerX + $x, $centerZ - $z - 1)]) or $this->usedChunks[$index] === false) {
                    $newOrder[$index] = true;
                }
                unset($unloadChunks[$index]);


                /* Bottom left quadrant */
                if (!isset($this->usedChunks[$index = Level::chunkHash($centerX - $x - 1, $centerZ - $z - 1)]) or $this->usedChunks[$index] === false) {
                    $newOrder[$index] = true;
                }
                unset($unloadChunks[$index]);

                if ($x !== $z) {
                    /* Top right quadrant mirror */
                    if (!isset($this->usedChunks[$index = Level::chunkHash($centerX + $z, $centerZ + $x)]) or $this->usedChunks[$index] === false) {
                        $newOrder[$index] = true;
                    }
                    unset($unloadChunks[$index]);

                    /* Top left quadrant mirror */
                    if (!isset($this->usedChunks[$index = Level::chunkHash($centerX - $z - 1, $centerZ + $x)]) or $this->usedChunks[$index] === false) {
                        $newOrder[$index] = true;
                    }
                    unset($unloadChunks[$index]);

                    /* Bottom right quadrant mirror */
                    if (!isset($this->usedChunks[$index = Level::chunkHash($centerX + $z, $centerZ - $x - 1)]) or $this->usedChunks[$index] === false) {
                        $newOrder[$index] = true;
                    }
                    unset($unloadChunks[$index]);

                    /* Bottom left quadrant mirror */
                    if (!isset($this->usedChunks[$index = Level::chunkHash($centerX - $z - 1, $centerZ - $x - 1)]) or $this->usedChunks[$index] === false) {
                        $newOrder[$index] = true;
                    }
                    unset($unloadChunks[$index]);
                }
            }
        }

        foreach ($unloadChunks as $index => $bool) {
            Level::getXZ($index, $X, $Z);
            $this->unloadChunk($X, $Z);
        }

        $this->loadQueue = $newOrder;


        Timings::$playerChunkOrderTimer->stopTiming();

        return true;
    }

    /**
     * Batch a Data packet into the channel list to send at the end of the tick
     *
     * @param DataPacket $packet
     *
     * @return bool
     */
    public function batchDataPacket(DataPacket $packet)
    {
        if ($this->connected === false) {
            return false;
        }

        $timings = Timings::getSendDataPacketTimings($packet);
        $timings->startTiming();
        $this->server->getPluginManager()->callEvent($ev = new DataPacketSendEvent($this, $packet));
        if ($ev->isCancelled()) {
            $timings->stopTiming();
            return false;
        }

        if (!isset($this->batchedPackets)) {
            $this->batchedPackets = [];
        }

        $this->batchedPackets[] = clone $packet;
        $timings->stopTiming();
        return true;
    }

    /**
     * Sends an ordered DataPacket to the send buffer
     *
     * @param DataPacket $packet
     * @param bool $needACK
     *
     * @return int|bool
     */
    public function dataPacket(DataPacket $packet, $needACK = false)
    {
        if (!$this->connected) {
            return false;
        }

        $timings = Timings::getSendDataPacketTimings($packet);
        $timings->startTiming();

        $this->server->getPluginManager()->callEvent($ev = new DataPacketSendEvent($this, $packet));
        if ($ev->isCancelled()) {
            $timings->stopTiming();
            return false;
        }

        $identifier = $this->interface->putPacket($this, $packet, $needACK, false);

        if ($needACK and $identifier !== null) {
            $this->needACK[$identifier] = false;

            $timings->stopTiming();
            return $identifier;
        }

        $timings->stopTiming();
        return true;
    }

    /**
     * @param DataPacket $packet
     * @param bool $needACK
     *
     * @return bool|int
     */
    public function directDataPacket(DataPacket $packet, $needACK = false)
    {
        if ($this->connected === false) {
            return false;
        }

        $timings = Timings::getSendDataPacketTimings($packet);
        $timings->startTiming();
        $this->server->getPluginManager()->callEvent($ev = new DataPacketSendEvent($this, $packet));
        if ($ev->isCancelled()) {
            $timings->stopTiming();
            return false;
        }

        $identifier = $this->interface->putPacket($this, $packet, $needACK, true);

        if ($needACK and $identifier !== null) {
            $this->needACK[$identifier] = false;

            $timings->stopTiming();
            return $identifier;
        }

        $timings->stopTiming();
        return true;
    }

    /**
     * @param Vector3 $pos
     *
     * @return boolean
     */
    public function sleepOn(Vector3 $pos)
    {
        if (!$this->isOnline()) {
            return false;
        }

        foreach ($this->level->getNearbyEntities($this->boundingBox->grow(2, 1, 2), $this) as $p) {
            if ($p instanceof Player) {
                if ($p->sleeping !== null and $pos->distance($p->sleeping) <= 0.1) {
                    return false;
                }
            }
        }

        $this->server->getPluginManager()->callEvent($ev = new PlayerBedEnterEvent($this, $this->level->getBlock($pos)));
        if ($ev->isCancelled()) {
            return false;
        }

        $this->sleeping = clone $pos;

        $this->setDataProperty(self::DATA_PLAYER_BED_POSITION, self::DATA_TYPE_POS, [$pos->x, $pos->y, $pos->z]);
        $this->setDataFlag(self::DATA_PLAYER_FLAGS, self::DATA_PLAYER_FLAG_SLEEP, true, self::DATA_TYPE_BYTE);

        $this->setSpawn($pos);

        $this->level->sleepTicks = 60;


        return true;
    }

    /**
     * Sets the spawnpoint of the player (and the compass direction) to a Vector3, or set it on another world with a
     * Position object
     *
     * @param Vector3|Position $pos
     */
    public function setSpawn(Vector3 $pos)
    {
        if (!($pos instanceof Position)) {
            $level = $this->level;
        } else {
            $level = $pos->getLevel();
        }
        $this->spawnPosition = new WeakPosition($pos->x, $pos->y, $pos->z, $level);
        $pk = new SetSpawnPositionPacket();
        $pk->x = (int)$this->spawnPosition->x;
        $pk->y = (int)$this->spawnPosition->y;
        $pk->z = (int)$this->spawnPosition->z;
        $this->dataPacket($pk);
    }

    public function stopSleep()
    {
        if ($this->sleeping instanceof Vector3) {
            $this->server->getPluginManager()->callEvent($ev = new PlayerBedLeaveEvent($this, $this->level->getBlock($this->sleeping)));

            $this->sleeping = null;
            $this->setDataProperty(self::DATA_PLAYER_BED_POSITION, self::DATA_TYPE_POS, [0, 0, 0]);
            $this->setDataFlag(self::DATA_PLAYER_FLAGS, self::DATA_PLAYER_FLAG_SLEEP, false, self::DATA_TYPE_BYTE);


            $this->level->sleepTicks = 0;

            $pk = new AnimatePacket();
            $pk->eid = $this->id;
            $pk->action = PlayerAnimationEvent::WAKE_UP;
            $this->dataPacket($pk);
        }

    }

    /**
     * @param string $achievementId
     *
     * @return bool
     */
    public function awardAchievement($achievementId)
    {
        if (isset(Achievement::$list[$achievementId]) and !$this->hasAchievement($achievementId)) {
            foreach (Achievement::$list[$achievementId]["requires"] as $requirementId) {
                if (!$this->hasAchievement($requirementId)) {
                    return false;
                }
            }
            $this->server->getPluginManager()->callEvent($ev = new PlayerAchievementAwardedEvent($this, $achievementId));
            if (!$ev->isCancelled()) {
                $this->achievements[$achievementId] = true;
                Achievement::broadcast($this, $achievementId);

                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * @return int
     */
    public function getGamemode(): int
    {
        return $this->gamemode;
    }

    /**
     * @internal
     *
     * Returns a client-friendly gamemode of the specified real gamemode
     * This function takes care of handling gamemodes known to MCPE (as of 1.1.0.3, that includes Survival, Creative and Adventure)
     *
     * TODO: remove this when Spectator Mode gets added properly to MCPE
     *
     * @param int $gamemode
     *
     * @return int
     */
    public static function getClientFriendlyGamemode(int $gamemode): int
    {
        $gamemode &= 0x03;

        return $gamemode;
    }

    /**
     * Sets the gamemode, and if needed, kicks the Player.
     *
     * @param int $gm
     * @param bool $client if the client made this change in their GUI
     *
     * @return bool
     */
    public function setGamemode(int $gm, bool $client = false)
    {
        if ($gm < 0 or $gm > 3 or $this->gamemode === $gm) {
            return false;
        }

        $this->server->getPluginManager()->callEvent($ev = new PlayerGameModeChangeEvent($this, $gm));
        if ($ev->isCancelled()) {
            if ($client) { //gamemode change by client in the GUI
                $this->sendGamemode();
            }
            return false;
        }

        if ($this->server->autoClearInv) {
            $this->inventory->clearAll();
        }

        $this->gamemode = $gm;

        $this->allowFlight = $this->isCreative();
        if ($this->isSpectator()) {
            $this->flying = true;
            $this->despawnFromAll();

            // Client automatically turns off flight controls when on the ground.
            // A combination of this hack and a new AdventureSettings flag FINALLY
            // fixes spectator flight controls. Thank @robske110 for this hack.
            $this->teleport($this->temporalVector->setComponents($this->x, $this->y + 0.1, $this->z));
        } else {
            if ($this->isSurvival()) {
                $this->flying = false;
            }
            $this->spawnToAll();
        }

        $this->resetFallDistance();

        $this->namedtag->playerGameType = new IntTag("playerGameType", $this->gamemode);

        if (!$client) { //Gamemode changed by server, do not send for client changes
            $this->sendGamemode();
        } else {
            Command::broadcastCommandMessage($this, new TranslationContainer("commands.gamemode.success.self", [Server::getGamemodeString($gm)]));
        }

        if ($this->gamemode === Player::SPECTATOR) {
            $pk = new ContainerSetContentPacket();
            $pk->windowid = ContainerSetContentPacket::SPECIAL_CREATIVE;
            $this->dataPacket($pk);
        } else {
            $pk = new ContainerSetContentPacket();
            $pk->windowid = ContainerSetContentPacket::SPECIAL_CREATIVE;
            $pk->slots = array_merge(Item::getCreativeItems(), $this->personalCreativeItems);
            $this->dataPacket($pk);
        }

        $this->sendSettings();

        $this->inventory->sendContents($this);
        $this->inventory->sendContents($this->getViewers());
        $this->inventory->sendHeldItem($this->hasSpawned);
        return true;
    }

    /**
     * @internal
     * Sends the player's gamemode to the client.
     */
    public function sendGamemode()
    {
        $pk = new SetPlayerGameTypePacket();
        $pk->gamemode = Player::getClientFriendlyGamemode($this->gamemode);
        $this->dataPacket($pk);
    }

    /**
     * Sends all the option flags
     */
    public function sendSettings()
    {
        $pk = new AdventureSettingsPacket();
        $pk->flags = 0;
        $pk->worldImmutable = $this->isAdventure();
        $pk->autoJump = $this->autoJump;
        $pk->allowFlight = $this->allowFlight;
        $pk->noClip = $this->isSpectator();
        $pk->worldBuilder = !($this->isAdventure());
        $pk->isFlying = $this->flying;
        $pk->muted = $this->muted;
        $pk->userPermission = ($this->isOp() ? AdventureSettingsPacket::PERMISSION_OPERATOR : AdventureSettingsPacket::PERMISSION_NORMAL);
        $this->dataPacket($pk);
    }

    /**
     * @return bool
     */
    public function isSurvival(): bool
    {
        return ($this->gamemode & 0x01) === 0;
    }

    /**
     * @return bool
     */
    public function isCreative(): bool
    {
        return ($this->gamemode & 0x01) > 0;
    }

    /**
     * @return bool
     */
    public function isSpectator(): bool
    {
        return $this->gamemode === 3;
    }

    /**
     * @return bool
     */
    public function isAdventure(): bool
    {
        return ($this->gamemode & 0x02) > 0;
    }

    /**
     * @return bool
     */
    public function isFireProof(): bool
    {
        return $this->isCreative();
    }

    /**
     * @return array
     */
    public function getDrops()
    {
        if (!$this->isCreative()) {
            return parent::getDrops();
        }

        return [];
    }

    /**
     * @param int $id
     * @param int $type
     * @param mixed $value
     *
     * @return bool
     */
    public function setDataProperty($id, $type, $value)
    {
        if (parent::setDataProperty($id, $type, $value)) {
            $this->sendData($this, [$id => $this->dataProperties[$id]]);
            return true;
        }

        return false;
    }

    /**
     * @param $movX
     * @param $movY
     * @param $movZ
     * @param $dx
     * @param $dy
     * @param $dz
     */
    protected function checkGroundState($movX, $movY, $movZ, $dx, $dy, $dz)
    {
        if (!$this->onGround or $movY != 0) {
            $bb = clone $this->boundingBox;
            $bb->maxY = $bb->minY + 0.5;
            $bb->minY -= 1;
            if (count($this->level->getCollisionBlocks($bb, true)) > 0) {
                $this->onGround = true;
            } else {
                $this->onGround = false;
            }
        }
        $this->isCollided = $this->onGround;
    }

    protected function checkBlockCollision()
    {
        foreach ($blocksaround = $this->getBlocksAround() as $block) {
            $block->onEntityCollide($this);
            if ($this->getServer()->redstoneEnabled) {
                if ($block instanceof PressurePlate) {
                    $this->activatedPressurePlates[Level::blockHash($block->x, $block->y, $block->z)] = $block;
                }
            }
        }

        if ($this->getServer()->redstoneEnabled) {
            /** @var \pocketmine\block\PressurePlate $block * */
            foreach ($this->activatedPressurePlates as $key => $block) {
                if (!isset($blocksaround[$key])) $block->checkActivation();
            }
        }
    }

    /**
     * @param $tickDiff
     */
    protected function checkNearEntities($tickDiff)
    {
        foreach ($this->level->getNearbyEntities($this->boundingBox->grow(0.5, 0.5, 0.5), $this) as $entity) {
            $entity->scheduleUpdate();

            if (!$entity->isAlive()) {
                continue;
            }

            if ($entity instanceof Arrow and $entity->hadCollision) {
                $item = Item::get(Item::ARROW, $entity->getPotionId(), 1);

                $add = false;
                if (!$this->server->allowInventoryCheats and !$this->isCreative()) {
                    if (!$this->getFloatingInventory()->canAddItem($item) or !$this->inventory->canAddItem($item)) {
                        //The item is added to the floating inventory to allow client to handle the pickup
                        //We have to also check if it can be added to the real inventory before sending packets.
                        continue;
                    }
                    $add = true;
                }

                $this->server->getPluginManager()->callEvent($ev = new InventoryPickupArrowEvent($this->inventory, $entity));
                if ($ev->isCancelled()) {
                    continue;
                }

                $pk = new TakeItemEntityPacket();
                $pk->eid = $this->id;
                $pk->target = $entity->getId();
                $this->server->broadcastPacket($entity->getViewers(), $pk);

                if ($add) {
                    $this->getFloatingInventory()->addItem(clone $item);
                }
                $entity->kill();
            } elseif ($entity instanceof DroppedItem) {
                if ($entity->getPickupDelay() <= 0) {
                    $item = $entity->getItem();

                    if ($item instanceof Item) {
                        $add = false;
                        if (!$this->server->allowInventoryCheats and !$this->isCreative()) {
                            if (!$this->getFloatingInventory()->canAddItem($item) or !$this->inventory->canAddItem($item)) {
                                continue;
                            }
                            $add = true;
                        }

                        $this->server->getPluginManager()->callEvent($ev = new InventoryPickupItemEvent($this->inventory, $entity));
                        if ($ev->isCancelled()) {
                            continue;
                        }

                        switch ($item->getId()) {
                            case Item::WOOD:
                                $this->awardAchievement("mineWood");

                                break;
                            case Item::DIAMOND:
                                $this->awardAchievement("diamond");
                                break;
                        }

                        $pk = new TakeItemEntityPacket();
                        $pk->eid = $this->id;
                        $pk->target = $entity->getId();
                        $this->server->broadcastPacket($entity->getViewers(), $pk);

                        if ($add) {
                            $this->getFloatingInventory()->addItem(clone $item);
                        }
                        $entity->kill();
                    }
                }
            }
        }
    }

    /**
     * @param $tickDiff
     */
    protected function processMovement($tickDiff)
    {
        if (!$this->isAlive() or !$this->spawned or $this->newPosition === null or $this->teleportPosition !== null or $this->isSleeping()) {
            return;
        }

        $newPos = $this->newPosition;
        $distanceSquared = $newPos->distanceSquared($this);

        $revert = false;

        if (($distanceSquared / ($tickDiff ** 2)) > 115 and !$this->allowMovementCheats) {
            $this->server->getLogger()->warning($this->getName() . " moved too fast, reverting movement");
            $revert = true;
        } else {
            if ($this->chunk === null or !$this->chunk->isGenerated()) {
                $chunk = $this->level->getChunk($newPos->x >> 4, $newPos->z >> 4, false);
                if ($chunk === null or !$chunk->isGenerated()) {
                    $revert = true;
                    $this->nextChunkOrderRun = 0;
                } else {
                    if ($this->chunk !== null) {
                        $this->chunk->removeEntity($this);
                    }
                    $this->chunk = $chunk;
                }
            }
        }

        if (!$revert and $distanceSquared != 0) {
            $dx = $newPos->x - $this->x;
            $dy = $newPos->y - $this->y;
            $dz = $newPos->z - $this->z;

            $this->move($dx, $dy, $dz);

            $diffX = $this->x - $newPos->x;
            $diffY = $this->y - $newPos->y;
            $diffZ = $this->z - $newPos->z;

            $diff = ($diffX ** 2 + $diffY ** 2 + $diffZ ** 2) / ($tickDiff ** 2);

            if ($this->isSurvival() and !$revert and $diff > 0.0625) {
                $ev = new PlayerIllegalMoveEvent($this, $newPos);
                $ev->setCancelled($this->allowMovementCheats);

                $this->server->getPluginManager()->callEvent($ev);

                if (!$ev->isCancelled()) {
                    $revert = true;
                    $this->server->getLogger()->warning($this->getServer()->getLanguage()->translateString("pocketmine.player.invalidMove", [$this->getName()]));
                }
            }

            if ($diff > 0) {
                $this->x = $newPos->x;
                $this->y = $newPos->y;
                $this->z = $newPos->z;
                $radius = $this->width / 2;
                $this->boundingBox->setBounds($this->x - $radius, $this->y, $this->z - $radius, $this->x + $radius, $this->y + $this->height, $this->z + $radius);
            }
        }

        $from = new Location($this->lastX, $this->lastY, $this->lastZ, $this->lastYaw, $this->lastPitch, $this->level);
        $to = $this->getLocation();

        $delta = pow($this->lastX - $to->x, 2) + pow($this->lastY - $to->y, 2) + pow($this->lastZ - $to->z, 2);
        $deltaAngle = abs($this->lastYaw - $to->yaw) + abs($this->lastPitch - $to->pitch);

        if (!$revert and ($delta > 0.0001 or $deltaAngle > 1.0)) {

            $isFirst = ($this->lastX === null or $this->lastY === null or $this->lastZ === null);

            $this->lastX = $to->x;
            $this->lastY = $to->y;
            $this->lastZ = $to->z;

            $this->lastYaw = $to->yaw;
            $this->lastPitch = $to->pitch;

            if (!$isFirst) {
                $ev = new PlayerMoveEvent($this, $from, $to);
                $this->setMoving(true);

                $this->server->getPluginManager()->callEvent($ev);

                if (!($revert = $ev->isCancelled())) { //Yes, this is intended
                    if ($this->server->netherEnabled) {
                        if ($this->isInsideOfPortal()) {
                            if ($this->portalTime == 0) {
                                $this->portalTime = $this->server->getTick();
                            }
                        } else {
                            $this->portalTime = 0;
                        }
                    }

                    if ($to->distanceSquared($ev->getTo()) > 0.01) { //If plugins modify the destination
                        $this->teleport($ev->getTo());
                    } else {
                        $this->level->addEntityMovement($this->x >> 4, $this->z >> 4, $this->getId(), $this->x, $this->y + $this->getEyeHeight(), $this->z, $this->yaw, $this->pitch, $this->yaw);
                    }

                    if ($this->fishingHook instanceof FishingHook) {
                        if ($this->distance($this->fishingHook) > 33 or $this->inventory->getItemInHand()->getId() !== Item::FISHING_ROD) {
                            $this->setFishingHook();
                        }
                    }
                }
            }

            if (!$this->isSpectator()) {
                $this->checkNearEntities($tickDiff);
            }

            $this->speed = ($to->subtract($from))->divide($tickDiff);
        } elseif ($distanceSquared == 0) {
            $this->speed = new Vector3(0, 0, 0);
            $this->setMoving(false);
        }

        if ($revert) {

            $this->lastX = $from->x;
            $this->lastY = $from->y;
            $this->lastZ = $from->z;

            $this->lastYaw = $from->yaw;
            $this->lastPitch = $from->pitch;

            $this->sendPosition($from, $from->yaw, $from->pitch, MovePlayerPacket::MODE_RESET);
            $this->forceMovement = new Vector3($from->x, $from->y, $from->z);
        } else {
            $this->forceMovement = null;
            if ($distanceSquared != 0 and $this->nextChunkOrderRun > 20) {
                $this->nextChunkOrderRun = 20;
            }
        }

        $this->newPosition = null;
    }

    /**
     * @param Vector3 $mot
     *
     * @return bool
     */
    public function setMotion(Vector3 $mot)
    {
        if (parent::setMotion($mot)) {
            if ($this->chunk !== null) {
                $this->level->addEntityMotion($this->chunk->getX(), $this->chunk->getZ(), $this->getId(), $this->motionX, $this->motionY, $this->motionZ);
                $pk = new SetEntityMotionPacket();
                $pk->eid = $this->id;
                $pk->motionX = $mot->x;
                $pk->motionY = $mot->y;
                $pk->motionZ = $mot->z;
                $this->dataPacket($pk);
            }

            if ($this->motionY > 0) {
                $this->startAirTicks = (-(log($this->gravity / ($this->gravity + $this->drag * $this->motionY))) / $this->drag) * 2 + 5;
            }

            return true;
        }
        return false;
    }


    protected function updateMovement()
    {

    }

    public $foodTick = 0;

    public $starvationTick = 0;

    public $foodUsageTime = 0;

    protected $moving = false;

    /**
     * @param $moving
     */
    public function setMoving($moving)
    {
        $this->moving = $moving;
    }

    /**
     * @return bool
     */
    public function isMoving(): bool
    {
        return $this->moving;
    }

    /**
     * @param bool $sendAll
     */
    public function sendAttributes(bool $sendAll = false)
    {
        $entries = $sendAll ? $this->attributeMap->getAll() : $this->attributeMap->needSend();
        if (count($entries) > 0) {
            $pk = new UpdateAttributesPacket();
            $pk->entityId = $this->id;
            $pk->entries = $entries;
            $this->dataPacket($pk);
            foreach ($entries as $entry) {
                $entry->markSynchronized();
            }
        }
    }

    /**
     * @param $currentTick
     *
     * @return bool
     */
    public function onUpdate($currentTick)
    {
        if (!$this->loggedIn) {
            return false;
        }

        $tickDiff = $currentTick - $this->lastUpdate;

        if ($tickDiff <= 0) {
            return true;
        }

        $this->messageCounter = 2;

        $this->lastUpdate = $currentTick;

        $this->sendAttributes();

        if (!$this->isAlive() and $this->spawned) {
            ++$this->deadTicks;
            if ($this->deadTicks >= 10) {
                $this->despawnFromAll();
            }
            return true;
        }

        $this->timings->startTiming();

        if ($this->spawned) {
            if ($this->server->netherEnabled) {
                if (($this->isCreative() or $this->isSurvival() and $this->server->getTick() - $this->portalTime >= 80) and $this->portalTime > 0) {
                    $netherLevel = null;
                    if ($this->server->isLevelLoaded($this->server->netherName) or $this->server->loadLevel($this->server->netherName)) {
                        $netherLevel = $this->server->getLevelByName($this->server->netherName);
                    }

                    if ($netherLevel instanceof Level) {
                        if ($this->getLevel() !== $netherLevel) {
                            $this->fromPos = $this->getPosition();
                            $this->fromPos->x = ((int)$this->fromPos->x) + 0.5;
                            $this->fromPos->z = ((int)$this->fromPos->z) + 0.5;
                            $this->teleport($this->shouldResPos = $netherLevel->getSafeSpawn());
                        } elseif ($this->fromPos instanceof Position) {
                            if (!($this->getLevel()->isChunkLoaded($this->fromPos->x, $this->fromPos->z))) {
                                $this->getLevel()->loadChunk($this->fromPos->x, $this->fromPos->z);
                            }
                            $add = [1, 0, -1, 0, 0, 1, 0, -1];
                            $tempos = null;
                            for ($j = 2; $j < 5; $j++) {
                                for ($i = 0; $i < 4; $i++) {
                                    if ($this->fromPos->getLevel()->getBlock($this->temporalVector->fromObjectAdd($this->fromPos, $add[$i] * $j, 0, $add[$i + 4] * $j))->getId() === Block::AIR) {
                                        if ($this->fromPos->getLevel()->getBlock($this->temporalVector->fromObjectAdd($this->fromPos, $add[$i] * $j, 1, $add[$i + 4] * $j))->getId() === Block::AIR) {
                                            $tempos = $this->fromPos->add($add[$i] * $j, 0, $add[$i + 4] * $j);
                                            //$this->getLevel()->getServer()->getLogger()->debug($tempos);
                                            break;
                                        }
                                    }
                                }
                                if ($tempos != null) {
                                    break;
                                }
                            }
                            if ($tempos === null) {
                                $tempos = $this->fromPos->add(mt_rand(-2, 2), 0, mt_rand(-2, 2));
                            }
                            $this->teleport($this->shouldResPos = $tempos);
                            $add = null;
                            $tempos = null;
                            $this->fromPos = null;
                        } else {
                            $this->teleport($this->shouldResPos = $this->server->getDefaultLevel()->getSafeSpawn());
                        }
                        $this->portalTime = 0;
                    }
                }
            }

            $this->processMovement($tickDiff);
            $this->entityBaseTick($tickDiff);

            if ($this->isOnFire() or $this->lastUpdate % 10 == 0) {
                if ($this->isCreative() and !$this->isInsideOfFire()) {
                    $this->extinguish();
                } elseif ($this->getLevel()->getWeather()->isRainy()) {
                    if ($this->getLevel()->canBlockSeeSky($this)) {
                        $this->extinguish();
                    }
                }
            }

            if (!$this->isSpectator() and $this->speed !== null) {
                if ($this->hasEffect(Effect::LEVITATION)) {
                    $this->inAirTicks = 0;
                }
                if ($this->onGround) {
                    if ($this->inAirTicks !== 0) {
                        $this->startAirTicks = 5;
                    }
                    $this->inAirTicks = 0;
                } else {
                    if ($this->getInventory()->getItem($this->getInventory()->getSize() + 1)->getId() == "444") {
                        #enable use of elytra. todo: check if it is open
                        $this->inAirTicks = 0;
                    }
                    if (!$this->allowFlight and $this->inAirTicks > 10 and !$this->isSleeping() and !$this->isImmobile()) {
                        $expectedVelocity = (-$this->gravity) / $this->drag - ((-$this->gravity) / $this->drag) * exp(-$this->drag * ($this->inAirTicks - $this->startAirTicks));
                        $diff = ($this->speed->y - $expectedVelocity) ** 2;

                        if (!$this->hasEffect(Effect::JUMP) and $diff > 0.6 and $expectedVelocity < $this->speed->y and !$this->server->getAllowFlight()) {
                            if ($this->inAirTicks < 1000) {
                                $this->setMotion(new Vector3(0, $expectedVelocity, 0));
                            } elseif ($this->kick("Flying is not enabled on this server", false)) {
                                $this->timings->stopTiming();

                                return false;
                            }
                        }
                    }

                    ++$this->inAirTicks;
                }
            }

            if ($this->getTransactionQueue() !== null) {
                $this->getTransactionQueue()->execute();
            }
        }

        $this->checkTeleportPosition();

        $this->timings->stopTiming();

        if (count($this->messageQueue) > 0) {
            $pk = new TextPacket();
            $pk->type = TextPacket::TYPE_RAW;
            $pk->message = implode("\n", $this->messageQueue);
            $this->dataPacket($pk);
            $this->messageQueue = [];
        }

        return true;
    }

    public function checkNetwork()
    {
        if (!$this->isOnline()) {
            return;
        }

        if ($this->nextChunkOrderRun-- <= 0 or $this->chunk === null) {
            $this->orderChunks();
        }

        if (count($this->loadQueue) > 0 or !$this->spawned) {
            $this->sendNextChunk();
        }

        if (count($this->batchedPackets) > 0) {
            $this->server->batchPackets([$this], $this->batchedPackets, false);
            $this->batchedPackets = [];
        }

    }

    /**
     * @param Vector3 $pos
     * @param         $maxDistance
     * @param float $maxDiff
     *
     * @return bool
     */
    public function canInteract(Vector3 $pos, $maxDistance, $maxDiff = 0.5)
    {
        $eyePos = $this->getPosition()->add(0, $this->getEyeHeight(), 0);
        if ($eyePos->distanceSquared($pos) > $maxDistance ** 2) {
            return false;
        }

        $dV = $this->getDirectionPlane();
        $dot = $dV->dot(new Vector2($eyePos->x, $eyePos->z));
        $dot1 = $dV->dot(new Vector2($pos->x, $pos->z));
        return ($dot1 - $dot) >= -$maxDiff;
    }

    public function onPlayerPreLogin()
    {
        $pk = new PlayStatusPacket();
        $pk->status = PlayStatusPacket::LOGIN_SUCCESS;
        $this->dataPacket($pk);

        $this->processLogin();
    }

    public function clearCreativeItems()
    {
        $this->personalCreativeItems = [];
    }

    /**
     * @return array
     */
    public function getCreativeItems(): array
    {
        return $this->personalCreativeItems;
    }

    /**
     * @param Item $item
     */
    public function addCreativeItem(Item $item)
    {
        $this->personalCreativeItems[] = Item::get($item->getId(), $item->getDamage());
    }

    /**
     * @param Item $item
     */
    public function removeCreativeItem(Item $item)
    {
        $index = $this->getCreativeItemIndex($item);
        if ($index !== -1) {
            unset($this->personalCreativeItems[$index]);
        }
    }

    /**
     * @param Item $item
     *
     * @return int
     */
    public function getCreativeItemIndex(Item $item): int
    {
        foreach ($this->personalCreativeItems as $i => $d) {
            if ($item->equals($d, !$item->isTool())) {
                return $i;
            }
        }

        return -1;
    }

    protected function processLogin(){
        if (!$this->server->isWhitelisted(strtolower($this->getName()))) {
            $this->close($this->getLeaveMessage(), "Server is white-listed");
            return;
        } elseif ($this->server->getNameBans()->isBanned(strtolower($this->getName())) or $this->server->getIPBans()->isBanned($this->getAddress()) or $this->server->getCIDBans()->isBanned($this->randomClientId)) {
            $this->close($this->getLeaveMessage(), TextFormat::RED . "You are banned");
            return;
        }

        if ($this->hasPermission(Server::BROADCAST_CHANNEL_USERS)) {
            $this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_USERS, $this);
        }
        if ($this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE)) {
            $this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);
        }

        foreach ($this->server->getOnlinePlayers() as $p) {
            if ($p !== $this and strtolower($p->getName()) === strtolower($this->getName())) {
                if ($p->kick("logged in from another location") === false) {
                    $this->close($this->getLeaveMessage(), "Logged in from another location");
                    return;
                }
            } elseif ($p->loggedIn and $this->getUniqueId()->equals($p->getUniqueId())) {
                if ($p->kick("logged in from another location") === false) {
                    $this->close($this->getLeaveMessage(), "Logged in from another location");
                    return;
                }
            }
        }
        $this->setNameTag($this->getDisplayName());

        $nbt = $this->server->getOfflinePlayerData($this->username);
        if ($nbt == null) {
            $this->close($this->getLeaveMessage(), "Invalid data");
            return;
        }
        $this->playedBefore = ($nbt["lastPlayed"] - $nbt["firstPlayed"]) > 1;
        $nbt->NameTag = new StringTag("NameTag", $this->username);

        $this->gamemode = $nbt["playerGameType"] & 0x03;
        if ($this->server->getForceGamemode()) {
            $this->gamemode = $this->server->getGamemode();
            $nbt->playerGameType = new IntTag("playerGameType", $this->gamemode);
        }

        $this->allowFlight = $this->isCreative();
        $this->flying = $this->isCreative();

        if (($level = $this->server->getLevelByName($nbt["Level"])) === null) {
            $this->setLevel($this->server->getDefaultLevel());
            $nbt["Level"] = $this->level->getName();
            $nbt["Pos"][0] = $this->level->getSpawnLocation()->x;
            $nbt["Pos"][1] = $this->level->getSpawnLocation()->y;
            $nbt["Pos"][2] = $this->level->getSpawnLocation()->z;
        } else {
            $this->setLevel($level);
        }

        $this->achievements = [];

        /** @var ByteTag $achievement */
        foreach ($nbt->Achievements as $achievement) {
            $this->achievements[$achievement->getName()] = $achievement->getValue() > 0 ? true : false;
        }

        $nbt->lastPlayed = new LongTag("lastPlayed", floor(microtime(true) * 1000));
        if ($this->server->getAutoSave()) {
            $this->server->saveOfflinePlayerData($this->username, $nbt, true);
        }

        parent::initEntity();
        $this->server->getPluginManager()->callEvent($ev = new PlayerLoginEvent($this, "Plugin reason"));
        if ($ev->isCancelled()) {
            $this->close($this->getLeaveMessage(), $ev->getKickMessage());
            return;
        }

        $this->server->addOnlinePlayer($this);
        $this->loggedIn = true;

        if ($this->isCreative()) {
            $this->inventory->setHeldItemIndex(0);
        } else {
            $this->inventory->setHeldItemIndex($this->inventory->getHotbarSlotIndex(0));
        }

        if ($this->isSpectator()) $this->keepMovement = true;

        if (!$this->hasValidSpawnPosition() and isset($this->namedtag->SpawnLevel) and ($level = $this->server->getLevelByName($this->namedtag["SpawnLevel"])) instanceof Level) {
            $this->spawnPosition = new WeakPosition($this->namedtag["SpawnX"], $this->namedtag["SpawnY"], $this->namedtag["SpawnZ"], $level);
        }
        $spawnPosition = $this->getSpawn();

        $pk = new StartGamePacket();
        $pk->entityUniqueId = $this->id;
        $pk->entityRuntimeId = $this->id;
        $pk->playerGamemode = Player::getClientFriendlyGamemode($this->gamemode);
        $pk->x = $this->x;
        $pk->y = $this->y;
        $pk->z = $this->z;
        $pk->pitch = $this->pitch;
        $pk->yaw = $this->yaw;
        $pk->seed = -1;
        $pk->dimension = $this->level->getDimension();
        $pk->worldGamemode = Player::getClientFriendlyGamemode($this->server->getGamemode());
        $pk->difficulty = $this->server->getDifficulty();
        $pk->spawnX = $spawnPosition->getFloorX();
        $pk->spawnY = $spawnPosition->getFloorY();
        $pk->spawnZ = $spawnPosition->getFloorZ();
        $pk->hasAchievementsDisabled = 1;
        $pk->dayCycleStopTime = -1;
        $pk->eduMode = 0;
        $pk->rainLevel = 0;
        $pk->lightningLevel = 0;
        $pk->commandsEnabled = 1;
        $pk->levelId = "";
        $pk->worldName = $this->server->getMotd();
        $pk->generator = 1;
        $this->dataPacket($pk);

        $pk = new SetTimePacket();
        $pk->time = $this->level->getTime();
        $pk->started = $this->level->stopTime == false;
        $this->dataPacket($pk);

        $this->setMovementSpeed(0.1);
        $this->sendAttributes();
        $this->setNameTagVisible(true);
        $this->setNameTagAlwaysVisible(true);
        $this->setCanClimb(true);

        $this->server->getLogger()->info($this->getServer()->getLanguage()->translateString("pocketmine.player.logIn", [
            TextFormat::AQUA . $this->username . TextFormat::WHITE,
            $this->ip,
            $this->port,
            TextFormat::GREEN . $this->randomClientId . TextFormat::WHITE,
            $this->id,
            $this->level->getName(),
            round($this->x, 4),
            round($this->y, 4),
            round($this->z, 4)
        ]));

        if ($this->isOp()) {
            $this->setRemoveFormat(false);
        }

        if ($this->gamemode === Player::SPECTATOR) {
            $pk = new InventoryContentPacket();
            $pk->inventoryID = ProtocolInfo::CONTAINER_ID_CREATIVE;
            $this->dataPacket($pk);
        } else {
            $pk = new InventoryContentPacket();
            $pk->inventoryID = ProtocolInfo::CONTAINER_ID_CREATIVE;
            $pk->items = array_merge(Item::getCreativeItems(), $this->personalCreativeItems);
            $this->dataPacket($pk);
        }

        $this->sendCommandData();

        $this->level->getWeather()->sendWeather($this);
        $this->forceMovement = $this->teleportPosition = $this->getPosition();
        $this->server->onPlayerLogin($this);
    }

    /**
     * @return mixed
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    public function handleDataPacket(DataPacket $packet){
        if($this->connected === false){
            return;
        }

        if($packet->pid() === 0xfe){
            /** @var BatchPacket $packet */
            $this->server->getNetwork()->processBatch($packet, $this);
            return;
        }

        switch($packet->pid()){
            case ProtocolInfo::ADVENTURE_SETTINGS_PACKET:
                $isCheater = ($this->allowFlight === false && ($packet->flags >> 9) & 0x01 === 1) || (!$this->isSpectator() && ($packet->flags >> 7) & 0x01 === 1);
                if (($packet->isFlying and !$this->allowFlight and !$this->server->getAllowFlight()) or $isCheater) {
                    $this->kick($this->server->getProperty("settings.fly-kick-message", "Flying is not enabled on this server"));
                    break;
                } else {
                    $this->server->getPluginManager()->callEvent($ev = new PlayerToggleFlightEvent($this, $packet->isFlying));
                    if ($ev->isCancelled()) {
                        $this->sendSettings();
                    } else {
                        $this->flying = $ev->isFlying();
                    }
                    break;
                }
                break;
            case ProtocolInfo::LOGIN_PACKET:
                if($this->loggedIn){
                    break;
                }

                /*if (!in_array($packet->protocol, ProtocolInfo::ACCEPTED_PROTOCOLS)) {
                    if ($packet->protocol < ProtocolInfo::CURRENT_PROTOCOL) {
                        $message = "disconnectionScreen.outdatedClient";
                        $pk = new PlayStatusPacket();
                        $pk->status = PlayStatusPacket::LOGIN_FAILED_CLIENT;
                        $this->directDataPacket($pk);
                    } else {
                        $message = "disconnectionScreen.outdatedServer";
                        $pk = new PlayStatusPacket();
                        $pk->status = PlayStatusPacket::LOGIN_FAILED_SERVER;
                        $this->directDataPacket($pk);
                    }
                    $this->close("", $message, false);
                    break;
                }*/

                $this->protocol = $packet->protocol;
                if($packet->isValidProtocol === false) {
                    // TODO new message
                    $this->close("", "Not valid protocol");
                    break;
                }

                $this->username = TextFormat::clean($packet->username);
                $this->displayName = $this->username;
                $this->setNameTag($this->username);
                $this->iusername = strtolower($this->username);
                $this->randomClientId = $packet->clientId;
                $this->deviceModel = $packet->deviceModel;
                $this->deviceOS = $packet->deviceOS;
                $this->uuid = $packet->clientUUID;
                $this->rawUUID = $this->uuid->toBinary();

                $this->clientSecret = $packet->clientSecret;
                $this->setSkin($packet->skin, $packet->skinId, $packet->skinGeometryName, $packet->skinGeometryData, $packet->capeData);
                if ($packet->inventoryType >= 0) {
                    $this->inventoryType = $packet->inventoryType;
                }
                $this->languageCode = $packet->languageCode;
                $this->ip = $packet->serverAddress;

                $this->server->getPluginManager()->callEvent($ev = new PlayerPreLoginEvent($this, "Plugin reason"));
                if ($ev->isCancelled()) {
                    $this->close("", $ev->getKickMessage());
                    break;
                }

                /*$pk = new PlayStatusPacket();
                $pk->status = PlayStatusPacket::LOGIN_SUCCESS;
                $this->directDataPacket($pk);

                $infoPacket = new ResourcePacksInfoPacket();
                $infoPacket->resourcePackEntries = $this->server->getResourcePackManager()->getResourceStack();
                $infoPacket->mustAccept = $this->server->getResourcePackManager()->resourcePacksRequired();
                $this->directDataPacket($infoPacket);*/

                $this->processLogin();
                break;
            case ProtocolInfo::MOVE_PLAYER_PACKET:
                $newPos = new Vector3($packet->x, $packet->y - $this->getEyeHeight(), $packet->z);
                if ($newPos->distanceSquared($this) == 0 and ($packet->yaw % 360) === $this->yaw and ($packet->pitch % 360) === $this->pitch) { //player hasn't moved, just client spamming packets
                    break;
                }
                $revert = false;
                if (!$this->isAlive() or $this->spawned !== true) {
                    $revert = true;
                    $this->forceMovement = new Vector3($this->x, $this->y, $this->z);
                }
                if ($this->teleportPosition !== null or ($this->forceMovement instanceof Vector3 and ($newPos->distanceSquared($this->forceMovement) > 0.1 or $revert))) {
                    $this->sendPosition($this->forceMovement, $packet->yaw, $packet->pitch, MovePlayerPacket::MODE_RESET);
                } else {
                    $packet->yaw %= 360;
                    $packet->pitch %= 360;
                    if ($packet->yaw < 0) {
                        $packet->yaw += 360;
                    }
                    $this->setRotation($packet->yaw, $packet->pitch);
                    $this->newPosition = $newPos;
                    $this->forceMovement = null;
                }
                break;
            case ProtocolInfo::MOB_EQUIPMENT_PACKET:
                if ($this->spawned === false or !$this->isAlive()) {
                    break;
                }
                /** @var Item $item */
                $item = null;
                if($this->isCreative() && !$this->isSpectator()){ //Creative mode match
                    $item = $packet->item;
                    $slot = Item::getCreativeItemIndex($item);
                }else{
                    $item = $this->inventory->getItem($packet->slot);
                    $slot = $packet->slot;
                }

                if($packet->slot === -1){ //Air
                    if($this->isCreative()){
                        $found = false;
                        for($i = 0; $i < $this->inventory->getHotbarSize(); ++$i){
                            if($this->inventory->getHotbarSlotIndex($i) === -1){
                                $this->inventory->setHeldItemIndex($i);
                                $found = true;
                                break;
                            }
                        }
                        if(!$found){
                            $this->inventory->sendContents($this);
                            break;
                        }
                    }else{
                        if ($packet->selectedSlot >= 0 and $packet->selectedSlot < 9) {
                            /** @var Item $hotbarItem */
                            $hotbarItem = $this->inventory->getHotbar()[$packet->selectedSlot];
                            $isNeedSendToHolder = !($hotbarItem->equals($packet->item));
                            $this->inventory->setHeldItemIndex($packet->selectedSlot, $isNeedSendToHolder);
                            $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, false);
                            break;
                        } else {
                            $this->inventory->sendContents($this);
                            break;
                        }
                    }
                }elseif($this->isCreative() && !$this->isSpectator()){
                    $this->inventory->setHeldItemIndex($packet->selectedSlot);
                    $this->inventory->setItem($packet->selectedSlot, $item);
                }elseif($item === null or $slot === -1 or !$item->equals($packet->item)){ // packet error or not implemented
                    $this->inventory->sendContents($this);
                    break;
                }else{
                    if ($packet->selectedSlot >= 0 and $packet->selectedSlot < 9) {
                        $hotbarItem = $this->inventory->getItem($packet->selectedSlot);
                        $isNeedSendToHolder = !($hotbarItem->equals($packet->item));
                        $this->inventory->setHeldItemIndex($packet->selectedSlot, $isNeedSendToHolder);
                        $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, false);
                        break;
                    } else {
                        $this->inventory->sendContents($this);
                        break;
                    }
                }

                $this->inventory->setHeldItemIndex($packet->selectedSlot, false, $packet->slot);
                $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, false);
                break;
            case ProtocolInfo::LEVEL_SOUND_EVENT_PACKET:
                $viewers = $this->getViewers();
                foreach ($viewers as $viewer) {
                    $viewer->dataPacket($packet);
                }
                $this->dataPacket($packet);
                break;
            case ProtocolInfo::PLAYER_ACTION_PACKET:
                if ($this->spawned === false or !$this->isAlive()) {
                    break;
                }

                $pos = new Vector3($packet->x, $packet->y, $packet->z);

                switch ($packet->action) {
                    case PlayerActionPacket::ACTION_JUMP:
                        break 2;
                    case PlayerActionPacket::ACTION_START_BREAK:
                        if ($this->lastBreak !== PHP_INT_MAX or $pos->distanceSquared($this) > 10000) {
                            break;
                        }
                        $target = $this->level->getBlock($pos);
                        $ev = new PlayerInteractEvent($this, $this->inventory->getItemInHand(), $target, $packet->face, $target->getId() === 0 ? PlayerInteractEvent::LEFT_CLICK_AIR : PlayerInteractEvent::LEFT_CLICK_BLOCK);
                        $this->getServer()->getPluginManager()->callEvent($ev);
                        if (!$ev->isCancelled()) {
                            $side = $target->getSide($packet->face);
                            if ($side instanceof Fire) {
                                $side->getLevel()->setBlock($side, new Air());
                                break;
                            }
                            if (!$this->isCreative()) {
                                $breakTime = ceil($target->getBreakTime($this->inventory->getItemInHand()) * 20);
                                if ($breakTime > 0) {
                                    $this->level->broadcastLevelEvent($pos, LevelEventPacket::EVENT_BLOCK_START_BREAK, (int)(65535 / $breakTime));
                                }
                            }
                            $this->lastBreak = microtime(true);
                        } else {
                            $this->inventory->sendHeldItem($this);
                        }
                        break;
                    case PlayerActionPacket::ACTION_ABORT_BREAK:
                    case PlayerActionPacket::ACTION_STOP_BREAK:
                        if($packet->action == PlayerActionPacket::ACTION_ABORT_BREAK) $this->lastBreak = PHP_INT_MAX;
                        $this->level->broadcastLevelEvent($pos, LevelEventPacket::EVENT_BLOCK_STOP_BREAK);
                        break;
                    case PlayerActionPacket::ACTION_RELEASE_ITEM:
                        if ($this->startAction > -1 and $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION)) {
                            if ($this->inventory->getItemInHand()->getId() === Item::BOW) {
                                $bow = $this->inventory->getItemInHand();
                                if ($this->isSurvival() and !$this->inventory->contains(Item::get(Item::ARROW, -1))) {
                                    $this->inventory->sendContents($this);
                                    break;
                                }
                                $arrow = null;
                                $index = $this->inventory->first(Item::get(Item::ARROW, -1));
                                if ($index !== -1) {
                                    $arrow = $this->inventory->getItem($index);
                                    $arrow->setCount(1);
                                } elseif ($this->isCreative()) {
                                    $arrow = Item::get(Item::ARROW, 0, 1);
                                } else {
                                    $this->inventory->sendContents($this);
                                    break;
                                }
                                $nbt = new CompoundTag("", [
                                    "Pos" => new ListTag("Pos", [
                                        new DoubleTag("", $this->x),
                                        new DoubleTag("", $this->y + $this->getEyeHeight()),
                                        new DoubleTag("", $this->z)
                                    ]),
                                    "Motion" => new ListTag("Motion", [
                                        new DoubleTag("", -sin($this->yaw / 180 * M_PI) * cos($this->pitch / 180 * M_PI)),
                                        new DoubleTag("", -sin($this->pitch / 180 * M_PI)),
                                        new DoubleTag("", cos($this->yaw / 180 * M_PI) * cos($this->pitch / 180 * M_PI))
                                    ]),
                                    "Rotation" => new ListTag("Rotation", [
                                        new FloatTag("", $this->yaw),
                                        new FloatTag("", $this->pitch)
                                    ]),
                                    "Fire" => new ShortTag("Fire", $this->isOnFire() ? 45 * 60 : 0),
                                    "Potion" => new ShortTag("Potion", $arrow->getDamage())
                                ]);
                                $diff = ($this->server->getTick() - $this->startAction);
                                $p = $diff / 20;
                                $f = min((($p ** 2) + $p * 2) / 3, 1) * 2;
                                $ev = new EntityShootBowEvent($this, $bow, Entity::createEntity("Arrow", $this->getLevel(), $nbt, $this, $f == 2 ? true : false), $f);
                                if ($f < 0.1 or $diff < 5) {
                                    $ev->setCancelled();
                                }
                                $this->server->getPluginManager()->callEvent($ev);
                                if ($ev->isCancelled()) {
                                    $ev->getProjectile()->kill();
                                    $this->inventory->sendContents($this);
                                } else {
                                    $ev->getProjectile()->setMotion($ev->getProjectile()->getMotion()->multiply($ev->getForce()));
                                    if ($this->isSurvival()) {
                                        $this->inventory->removeItem($arrow);
                                        $bow->setDamage($bow->getDamage() + 1);
                                        if ($bow->getDamage() >= 385) {
                                            $this->inventory->setItemInHand(Item::get(Item::AIR, 0, 0));
                                        } else {
                                            $this->inventory->setItemInHand($bow);
                                        }
                                    }
                                    if ($ev->getProjectile() instanceof Projectile) {
                                        $this->server->getPluginManager()->callEvent($projectileEv = new ProjectileLaunchEvent($ev->getProjectile()));
                                        if ($projectileEv->isCancelled()) {
                                            $ev->getProjectile()->kill();
                                        } else {
                                            $ev->getProjectile()->spawnToAll();
                                            $this->level->broadcastLevelSoundEvent($this->add(0, 2, 0), LevelSoundEventPacket::SOUND_BOW);
                                        }
                                    } else {
                                        $ev->getProjectile()->spawnToAll();
                                    }
                                }
                            }
                        } elseif ($this->inventory->getItemInHand()->getId() === Item::BUCKET and $this->inventory->getItemInHand()->getDamage() === 1) { //Milk!
                            $this->server->getPluginManager()->callEvent($ev = new PlayerItemConsumeEvent($this, $this->inventory->getItemInHand()));
                            if ($ev->isCancelled()) {
                                $this->inventory->sendContents($this);
                                break;
                            }
                            $pk = new EntityEventPacket();
                            $pk->eid = $this->getId();
                            $pk->event = EntityEventPacket::USE_ITEM;
                            $this->dataPacket($pk);
                            $this->server->broadcastPacket($this->getViewers(), $pk);
                            if ($this->isSurvival()) {
                                $slot = $this->inventory->getItemInHand();
                                --$slot->count;
                                $this->inventory->setItemInHand($slot);
                                $this->inventory->addItem(Item::get(Item::BUCKET, 0, 1));
                            }
                            $this->removeAllEffects();
                        } else {
                            $this->inventory->sendContents($this);
                        }
                        break;
                    case PlayerActionPacket::ACTION_STOP_SLEEPING:
                        $this->stopSleep();
                        break;
                    case PlayerActionPacket::ACTION_SPAWN_NETHER:
                        break;
                    case PlayerActionPacket::ACTION_SPAWN_SAME_DIMENSION:
                    case PlayerActionPacket::ACTION_SPAWN_OVERWORLD:
                        if ($this->isAlive() or !$this->isOnline()) {
                            break;
                        }
                        if ($this->server->isHardcore()) {
                            $this->setBanned(true);
                            break;
                        }
                        $this->craftingType = self::CRAFTING_SMALL;
                        if ($this->server->netherEnabled) {
                            if ($this->level === $this->server->getLevelByName($this->server->netherName)) {
                                $this->teleport($pos = $this->server->getDefaultLevel()->getSafeSpawn());
                            }
                        }
                        $this->server->getPluginManager()->callEvent($ev = new PlayerRespawnEvent($this, $this->getSpawn()));
                        $this->teleport($ev->getRespawnPosition());
                        $this->setSprinting(false);
                        $this->setSneaking(false);
                        $this->extinguish();
                        $this->setDataProperty(self::DATA_AIR, self::DATA_TYPE_SHORT, 400);
                        $this->deadTicks = 0;
                        $this->noDamageTicks = 60;
                        $this->removeAllEffects();
                        $this->setHealth($this->getMaxHealth());
                        $this->setFood(20);
                        $this->starvationTick = 0;
                        $this->foodTick = 0;
                        $this->foodUsageTime = 0;
                        $this->sendData($this);
                        $this->sendSettings();
                        $this->inventory->sendContents($this);
                        $this->inventory->sendArmorContents($this);
                        $this->spawnToAll();
                        $this->scheduleUpdate();
                        break;
                    case PlayerActionPacket::ACTION_START_SPRINT:
                    case PlayerActionPacket::ACTION_STOP_SPRINT:
                        $value = $packet->action == PlayerActionPacket::ACTION_START_SPRINT;
                        $ev = new PlayerToggleSprintEvent($this, $value);
                        $this->server->getPluginManager()->callEvent($ev);
                        if($ev->isCancelled()){
                            $this->sendData($this);
                        }else{
                            $this->setSprinting($value);
                        }
                        break;
                    case PlayerActionPacket::ACTION_START_SNEAK:
                    case PlayerActionPacket::ACTION_STOP_SNEAK:
                        $value = $packet->action == PlayerActionPacket::ACTION_START_SNEAK;
                        $ev = new PlayerToggleSneakEvent($this, $value);
                        $this->server->getPluginManager()->callEvent($ev);
                        if($ev->isCancelled()){
                            $this->sendData($this);
                        }else{
                            $this->setSneaking($value);
                        }
                        break;
                    case PlayerActionPacket::ACTION_START_GLIDE:
                    case PlayerActionPacket::ACTION_STOP_GLIDE:
                        $value = $packet->action == PlayerActionPacket::ACTION_START_GLIDE;
                        $ev = new PlayerToggleGlideEvent($this, $value);
                        $this->server->getPluginManager()->callEvent($ev);
                        if ($ev->isCancelled()) {
                            $this->sendData($this);
                        } else {
                            $this->setGliding($value);
                        }
                        break;
                    case PlayerActionPacket::ACTION_CONTINUE_BREAK:
                        $block = $this->level->getBlock($pos);
                        $this->level->broadcastLevelEvent($pos, LevelEventPacket::EVENT_PARTICLE_PUNCH_BLOCK, $block->getId() | ($block->getDamage() << 8) | ($packet->face << 16));
                        break;
                }

                $this->startAction = -1;
                $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, false);
                break;
            case ProtocolInfo::MOB_ARMOR_EQUIPMENT_PACKET:
                break;
            case ProtocolInfo::ANIMATE_PACKET:
                if ($this->spawned === false or !$this->isAlive()) {
                    break;
                }

                $this->server->getPluginManager()->callEvent($ev = new PlayerAnimationEvent($this, $packet->action));
                if($ev->isCancelled()){
                    break;
                }

                $pk = new AnimatePacket();
                $pk->eid = $this->id;
                $pk->action = $ev->getAnimationType();
                $this->server->broadcastPacket($this->getViewers(), $pk);
                break;
            case ProtocolInfo::SET_HEALTH_PACKET: //Not used
                break;
            case ProtocolInfo::ENTITY_EVENT_PACKET:
                if ($this->spawned === false or !$this->isAlive()) {
                    break;
                }
                $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, false); //TODO: check if this should be true
                switch($packet->event){
                    case EntityEventPacket::USE_ITEM: //Eating
                        $slot = $this->inventory->getItemInHand();
                        if($slot instanceof Potion && $slot->canBeConsumed()){
                            $ev = new PlayerItemConsumeEvent($this, $slot);
                            $this->server->getPluginManager()->callEvent($ev);
                            if(!$ev->isCancelled()){
                                $slot->onConsume($this);
                            }else{
                                $this->inventory->sendContents($this);
                            }
                        }
                        $this->level->broadcastLevelSoundEvent($this->add(0, 2, 0), LevelSoundEventPacket::SOUND_BURP);
                        break;
                    case EntityEventPacket::ENCHANT:
                        if ($this->currentWindow instanceof EnchantInventory) {
                            $this->currentWindow->setItem(0, Item::get(Item::AIR));
                            $this->currentWindow->sendContents($this);
                            $this->inventory->sendContents($this);
                        }
                        break;
                    case EntityEventPacket::FEED:
                        $this->level->broadcastLevelSoundEvent($this->add(0, 2, 0), LevelSoundEventPacket::SOUND_EAT, 63);
                        break;
                }
                break;
            case ProtocolInfo::TEXT_PACKET:
                if ($this->spawned === false or !$this->isAlive()) {
                    break;
                }
                $this->craftingType = self::CRAFTING_SMALL;
                if($packet->type === TextPacket::TYPE_CHAT){
                    $packet->message = TextFormat::clean($packet->message, $this->removeFormat);
                    foreach(explode("\n", $packet->message) as $message){
                        if(trim($message) != "" and strlen($message) <= 255 and $this->messageCounter-- > 0){
                            $this->server->getPluginManager()->callEvent($ev = new PlayerChatEvent($this, $message));
                            if(!$ev->isCancelled()){
                                $this->server->broadcastMessage($ev->getPlayer()->getDisplayName() . ": " . $ev->getMessage(), $ev->getRecipients());
                            }
                        }
                    }
                }
                break;
            case ProtocolInfo::CONTAINER_CLOSE_PACKET:
                if ($this->spawned === false or $packet->windowid === 0) {
                    break;
                }
                $this->craftingType = self::CRAFTING_SMALL;
                $this->transactionQueue = null;
                if ($packet->windowid === $this->currentWindowId) {
                    $this->server->getPluginManager()->callEvent(new InventoryCloseEvent($this->currentWindow, $this));
                    $this->removeWindow($this->currentWindow);
                }
                break;
            case ProtocolInfo::CRAFTING_EVENT_PACKET:
                if (!$this->spawned || !$this->isAlive()) {
                    return false;
                }
                if ($packet->windowId > 0 && $packet->windowId !== $this->currentWindowId) {
                    $this->inventory->sendContents($this);
                    $pk = new ContainerClosePacket();
                    $pk->windowid = $packet->windowId;
                    $this->dataPacket($pk);
                    break;
                }

                $recipe = $this->server->getCraftingManager()->getRecipe($packet->id);
                $result = $packet->output[0];

                if (!($result instanceof Item)) {
                    $this->inventory->sendContents($this);
                    break;
                }

                if (is_null($recipe) || !$result->equals($recipe->getResult(), true, false) ) { //hack for win10
                    $newRecipe = $this->server->getCraftingManager()->getRecipeByHash($result->getId() . ":" . $result->getDamage());
                    if (!is_null($newRecipe)) {
                        $recipe = $newRecipe;
                    }
                }

                $craftSlots = $this->inventory->getCraftContents();
                try {
                    $ingredients = [];
                    if ($recipe instanceof ShapedRecipe) {
                        $itemGrid = $recipe->getIngredientMap();
                        foreach ($itemGrid as $line) {
                            foreach ($line as $item) {
                                $ingredients[] = $item;
                            }
                        }
                    } else if ($recipe instanceof ShapelessRecipe) {
                        $ingredients = $recipe->getIngredientList();
                    }
                    $ingredientsCount = count($ingredients);
                    $firstIndex = 0;
                    /** @var Item &$item */
                    foreach ($craftSlots as &$item) {
                        if ($item == null || $item->getId() == Item::AIR) {
                            continue;
                        }
                        for ($i = $firstIndex; $i < $ingredientsCount; $i++) {
                            $ingredient = $ingredients[$i];
                            if ($ingredient->getId() == Item::AIR) {
                                continue;
                            }
                            $isItemsNotEquals = $item->getId() != $ingredient->getId() ||
                                ($item->getDamage() != $ingredient->getDamage() && $ingredient->getDamage() != 32767) ||
                                $item->count < $ingredient->count;
                            if ($isItemsNotEquals) {
                                throw new \Exception('Recive bad recipe');
                            }
                            $firstIndex = $i + 1;
                            $item->count -= $ingredient->count;
                            if ($item->count == 0) {
                                /** @important count = 0 is important */
                                $item = Item::get(Item::AIR, 0, 0);
                            }
                            break;
                        }
                    }

                    $this->inventory->setItem(PlayerInventory::CRAFT_RESULT_INDEX, $recipe->getResult());
                    foreach ($craftSlots as $slot => $item) {
                        if ($item == null) {
                            continue;
                        }
                        $this->inventory->setItem(PlayerInventory::CRAFT_INDEX_0 - $slot, $item);
                    }
                } catch (\Exception $e) {
                    var_dump($e->getMessage());
                }

                break;
            case ProtocolInfo::TILE_ENTITY_DATA_PACKET:
                if ($this->spawned === false or !$this->isAlive()) {
                    break;
                }
                $this->craftingType = self::CRAFTING_SMALL;

                $pos = new Vector3($packet->x, $packet->y, $packet->z);
                if($pos->distanceSquared($this) > 10000){
                    break;
                }

                $t = $this->level->getTile($pos);
                if ($t instanceof Sign) {
                    $nbt = new NBT(NBT::LITTLE_ENDIAN);
                    $nbt->read($packet->namedtag, false, true);
                    $nbtData = $nbt->getData();
                    $isNotCreator = !isset($t->namedtag->Creator) || $t->namedtag["Creator"] !== $this->username;
                    if ($nbtData["id"] !== Tile::SIGN || $isNotCreator) {
                        $t->spawnTo($this);
                        break;
                    }
                    $signText = explode("\n", $nbtData['Text']);
                    for ($i = 0; $i < 4; $i++) {
                        $signText[$i] = isset($signText[$i]) ? TextFormat::clean($signText[$i], $this->removeFormat) : '';
                    }
                    unset($nbtData['Text']);
                    // event part
                    $ev = new SignChangeEvent($t->getBlock(), $this, $signText);
                    $this->server->getPluginManager()->callEvent($ev);
                    if ($ev->isCancelled()) {
                        $t->spawnTo($this);
                    } else {
                        $t->setText($ev->getLine(0), $ev->getLine(1), $ev->getLine(2), $ev->getLine(3));
                    }
                }
                break;
            case ProtocolInfo::REQUEST_CHUNK_RADIUS_PACKET:
                if ($packet->radius > 12) {
                    $packet->radius = 12;
                } elseif ($packet->radius < 4) {
                    $packet->radius = 4;
                }
                $this->setViewDistance($packet->radius);
                $pk = new ChunkRadiusUpdatedPacket();
                $pk->radius = $packet->radius;
                $this->dataPacket($pk);
                $this->loggedIn = true;
                $this->scheduleUpdate();
                $this->justCreated = false;
                break;
            case ProtocolInfo::RESOURCE_PACK_CLIENT_RESPONSE_PACKET:
                switch ($packet->status) {
                    case ResourcePackClientResponsePacket::STATUS_REFUSED:
                    case ResourcePackClientResponsePacket::STATUS_SEND_PACKS:
                    case ResourcePackClientResponsePacket::STATUS_HAVE_ALL_PACKS:
                        $pk = new ResourcePackStackPacket();
                        $this->dataPacket($pk);
                        break;
                    case ResourcePackClientResponsePacket::STATUS_COMPLETED:
                        $this->processLogin();
                        break;
                    default:
                        return false;
                }
                break;
            case ProtocolInfo::INVENTORY_TRANSACTION_PACKET:
                switch ($packet->transactionType) {
                    case InventoryTransactionPacket::TRANSACTION_TYPE_INVENTORY_MISMATCH:
                        break;
                    case InventoryTransactionPacket::TRANSACTION_TYPE_NORMAL:
                        $this->normalTransactionLogic($packet);
                        break;
                    case InventoryTransactionPacket::TRANSACTION_TYPE_ITEM_USE_ON_ENTITY:
                        if ($packet->actionType == InventoryTransactionPacket::ITEM_USE_ON_ENTITY_ACTION_ATTACK) {
                            $this->attackByTargetId($packet->entityId);
                        }
                        break;
                    case InventoryTransactionPacket::TRANSACTION_TYPE_ITEM_USE:
                        switch ($packet->actionType) {
                            case InventoryTransactionPacket::ITEM_USE_ACTION_PLACE:
                            case InventoryTransactionPacket::ITEM_USE_ACTION_USE:
                                $this->useItem($packet->item, $packet->slot, $packet->face, $packet->position, $packet->clickPosition);
                                break;
                            case InventoryTransactionPacket::ITEM_USE_ACTION_DESTROY:
                                $this->breakBlock($packet->position);
                                break;
                            default:
                                error_log('Wrong actionType ' . $packet->actionType);
                                break;
                        }
                        break;
                    case InventoryTransactionPacket::TRANSACTION_TYPE_ITEM_RELEASE:
                        switch ($packet->actionType) {
                            case InventoryTransactionPacket::ITEM_RELEASE_ACTION_RELEASE:
                                $this->releaseUseItem();
                                break;
                        }
                        break;
                    default:
                        error_log('Wrong transactionType ' . $packet->transactionType);
                        break;
                }
                break;
            /** @minProtocol 120 */
            case 'COMMAND_REQUEST_PACKET':
                if ($packet->command[0] != '/') {
                    $this->sendMessage('Invalid command data.');
                    break;
                }
                $commandLine = substr($packet->command, 1);
                $commandPreprocessEvent = new PlayerCommandPreprocessEvent($this, $commandLine);
                $this->server->getPluginManager()->callEvent($commandPreprocessEvent);
                if ($commandPreprocessEvent->isCancelled()) {
                    break;
                }

                $this->server->dispatchCommand($this, $commandLine);

                $commandPostprocessEvent = new PlayerCommandPostprocessEvent($this, $commandLine);
                $this->server->getPluginManager()->callEvent($commandPostprocessEvent);
                break;
            /** @minProtocol 120 */
            case 'PLAYER_SKIN_PACKET':
                $this->setSkin($packet->newSkinByteData, $packet->newSkinId, $packet->newSkinGeometryName, $packet->newSkinGeometryData, $packet->newCapeByteData);
                // Send new skin to viewers and to self
                $this->updatePlayerSkin($packet->oldSkinName, $packet->newSkinName);
                break;
            /** @minProtocol 120 */
            case 'MODAL_FORM_RESPONSE_PACKET':
                $this->checkModal($packet->formId, json_decode($packet->data, true));
                break;
            /** @minProtocol 120 */
            case 'PURCHASE_RECEIPT_PACKET':
                $event = new PlayerReceiptsReceivedEvent($this, $packet->receipts);
                $this->server->getPluginManager()->callEvent($event);
                break;
            case 'SERVER_SETTINGS_REQUEST_PACKET':
                $this->sendServerSettings();
                break;
            case 'CLIENT_TO_SERVER_HANDSHAKE_PACKET':
                $this->continueLoginProcess();
                break;
            case 'SUB_CLIENT_LOGIN_PACKET':
                $subPlayer = new static($this->interface, null, $this->ip, $this->port);
                if ($subPlayer->subAuth($packet, $this)) {
                    $this->subClients[$packet->targetSubClientID] = $subPlayer;
                }
                //$this->kick("COOP play is not allowed");
                break;
            case 'DISCONNECT_PACKET':
                if ($this->subClientId > 0) {
                    $this->close('', 'client disconnect');
                }
                break;
            default:
                break;
        }
    }

    /**
     * Kicks a player from the server
     *
     * @param string $reason
     * @param bool $isAdmin
     *
     * @return bool
     */
    public function kick($reason = "", $isAdmin = true)
    {
        $this->server->getPluginManager()->callEvent($ev = new PlayerKickEvent($this, $reason, $this->getLeaveMessage()));
        if (!$ev->isCancelled()) {
            if ($isAdmin) {
                $message = "Kicked by admin." . ($reason !== "" ? " Reason: " . $reason : "");
            } else {
                if ($reason === "") {
                    $message = "disconnectionScreen.noReason";
                } else {
                    $message = $reason;
                }
            }
            $this->close($ev->getQuitMessage(), $message);

            return true;
        }

        return false;
    }

    /** @var string[] */
    private $messageQueue = [];

    /**
     * @param Item $item
     *
     * Drops the specified item in front of the player.
     */
    public function dropItem(Item $item)
    {
        if ($this->spawned === false or !$this->isAlive()) {
            return;
        }

        if (($this->isCreative() and $this->server->limitedCreative) or $this->isSpectator()) {
            //Ignore for limited creative
            return;
        }

        if ($item->getId() === Item::AIR or $item->getCount() < 1) {
            //Ignore dropping air or items with bad counts
            return;
        }

        $ev = new PlayerDropItemEvent($this, $item);
        $this->server->getPluginManager()->callEvent($ev);
        if ($ev->isCancelled()) {
            $this->getFloatingInventory()->removeItem($item);
            $this->getInventory()->addItem($item);
            return;
        }

        $motion = $this->getDirectionVector()->multiply(0.4);

        $this->level->dropItem($this->add(0, 1.3, 0), $item, $motion, 40);

        $this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, false);
    }

    /**
     * Adds a title text to the user's screen, with an optional subtitle.
     *
     * @param string $title
     * @param string $subtitle
     * @param int $fadeIn Duration in ticks for fade-in. If -1 is given, client-sided defaults will be used.
     * @param int $stay Duration in ticks to stay on screen for
     * @param int $fadeOut Duration in ticks for fade-out.
     */
    public function sendActionBar(string $title, string $subtitle = "", int $fadeIn = -1, int $stay = -1, int $fadeOut = -1)
    {
        $this->setTitleDuration($fadeIn, $stay, $fadeOut);
        if ($subtitle !== "") {
            $this->sendTitleText($subtitle, SetTitlePacket::TYPE_SUB_TITLE);
        }
        $this->sendTitleText($title, SetTitlePacket::TYPE_TITLE);
    }

    /*********/
    /**
     * @param string $title
     * @param string $subtitle
     * @param int $fadeIn
     * @param int $stay
     * @param int $fadeOut
     */
    public function addTitle(string $title, string $subtitle = "", int $fadeIn = -1, int $stay = -1, int $fadeOut = -1)
    {
        $this->setTitleDuration($fadeIn, $stay, $fadeOut);
        if ($subtitle !== "") {
            $this->sendTitleText($subtitle, SetTitlePacket::TYPE_SUB_TITLE);
        }
        $this->sendTitleText($title, SetTitlePacket::TYPE_TITLE);
    }

    /**
     * Adds small text to the user's screen.
     *
     * @param string $message
     */
    public function addActionBarMessage(string $message)
    {
        $this->sendTitleText($message, SetTitlePacket::TYPE_ACTION_BAR);
    }

    /**
     * Removes the title from the client's screen.
     */
    public function removeTitles()
    {
        $pk = new SetTitlePacket();
        $pk->type = SetTitlePacket::TYPE_CLEAR;
        $this->dataPacket($pk);
    }

    /**
     * Sets the title duration.
     *
     * @param int $fadeIn Title fade-in time in ticks.
     * @param int $stay Title stay time in ticks.
     * @param int $fadeOut Title fade-out time in ticks.
     */
    public function setTitleDuration(int $fadeIn, int $stay, int $fadeOut)
    {
        if ($fadeIn >= 0 and $stay >= 0 and $fadeOut >= 0) {
            $pk = new SetTitlePacket();
            $pk->type = SetTitlePacket::TYPE_TIMES;
            $pk->fadeInDuration = $fadeIn;
            $pk->duration = $stay;
            $pk->fadeOutDuration = $fadeOut;
            $this->dataPacket($pk);
        }
    }

    /**
     * Internal function used for sending titles.
     *
     * @param string $title
     * @param int $type
     */
    protected function sendTitleText(string $title, int $type)
    {
        $pk = new SetTitlePacket();
        $pk->type = $type;
        $pk->title = $title;
        $this->dataPacket($pk);
    }

    /**
     * @param string $address
     * @param        $port
     */
    public function transfer(string $address, $port)
    {
        $pk = new TransferPacket();
        $pk->address = $address;
        $pk->port = $port;
        $this->dataPacket($pk);
    }

    /**
     * Change Player Movement Speed without effects
     *
     * @param $amount
     */
    public function setMovementSpeed($amount){
        if($this->spawned === true){
            $this->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->setValue($amount, true);
        }
    }

    /**
     * Sends a direct chat message to a player
     *
     * @param string|TextContainer $message
     *
     * @return bool
     */
    public function sendMessage($message){
        if ($message instanceof TextContainer) {
            if ($message instanceof TranslationContainer) {
                $this->sendTranslation($message->getText(), $message->getParameters());
                return false;
            }
            $message = $message->getText();
        }
        //TODO: Remove this workaround (broken client MCPE 1.0.0)
        $this->messageQueue[] = $this->server->getLanguage()->translateString($message);
        return true;
    }

    /**
     * @param       $message
     * @param array $parameters
     *
     * @return bool
     */
    public function sendTranslation($message, array $parameters = [])
    {
        $pk = new TextPacket();
        if (!$this->server->isLanguageForced()) {
            $pk->type = TextPacket::TYPE_TRANSLATION;
            $pk->message = $this->server->getLanguage()->translateString($message, $parameters, "pocketmine.");
            foreach ($parameters as $i => $p) {
                $parameters[$i] = $this->server->getLanguage()->translateString($p, $parameters, "pocketmine.");
            }
            $pk->parameters = $parameters;
        } else {
            $pk->type = TextPacket::TYPE_RAW;
            $pk->message = $this->server->getLanguage()->translateString($message, $parameters);
        }

        $ev = new PlayerTextPreSendEvent($this, $pk->message, PlayerTextPreSendEvent::TRANSLATED_MESSAGE);
        $this->server->getPluginManager()->callEvent($ev);
        if (!$ev->isCancelled()) {
            $this->dataPacket($pk);
            return true;
        }
        return false;
    }

    /**
     * @param        $message
     * @param string $subtitle
     *
     * @return bool
     */
    public function sendPopup($message, $subtitle = "")
    {
        $ev = new PlayerTextPreSendEvent($this, $message, PlayerTextPreSendEvent::POPUP);
        $this->server->getPluginManager()->callEvent($ev);
        if (!$ev->isCancelled()) {
            $pk = new TextPacket();
            $pk->type = TextPacket::TYPE_POPUP;
            $pk->source = $ev->getMessage();
            $pk->message = $subtitle;
            $this->dataPacket($pk);
            return true;
        }
        return false;
    }

    /**
     * @param $message
     *
     * @return bool
     */
    public function sendTip($message)
    {
        $ev = new PlayerTextPreSendEvent($this, $message, PlayerTextPreSendEvent::TIP);
        $this->server->getPluginManager()->callEvent($ev);
        if (!$ev->isCancelled()) {
            $pk = new TextPacket();
            $pk->type = TextPacket::TYPE_TIP;
            $pk->message = $ev->getMessage();
            $this->dataPacket($pk);
            return true;
        }
        return false;
    }

    /**
     * Send a title text or/and with/without a sub title text to a player
     *
     * @param        $title
     * @param string $subtitle
     * @param int $fadein
     * @param int $fadeout
     * @param int $duration
     *
     * @return bool
     */
    public function sendTitle($title, $subtitle = "", $fadein = 20, $fadeout = 20, $duration = 5)
    {
        return $this->addTitle($title, $subtitle, $fadein, $duration, $fadeout);
    }

    /**
     * Note for plugin developers: use kick() with the isAdmin
     * flag set to kick without the "Kicked by admin" part instead of this method.
     *
     * @param string $message Message to be broadcasted
     * @param string $reason Reason showed in console
     * @param bool $notify
     */
    public final function close($message = "", $reason = "generic reason", $notify = true)
    {
        if ($this->connected and !$this->closed) {
            if ($notify and strlen((string)$reason) > 0) {
                $pk = new DisconnectPacket();
                $pk->hideDisconnectionScreen = null;
                $pk->message = $reason;
                $this->dataPacket($pk);
            }

            //$this->setLinked();

            if ($this->fishingHook instanceof FishingHook) {
                $this->fishingHook->close();
                $this->fishingHook = null;
            }

            $this->removeEffect(Effect::HEALTH_BOOST);

            $this->connected = false;
            if (strlen($this->getName()) > 0) {
                $this->server->getPluginManager()->callEvent($ev = new PlayerQuitEvent($this, $message, true));
                if ($this->loggedIn === true and $ev->getAutoSave()) {
                    $this->save();
                }
            }

            foreach ($this->server->getOnlinePlayers() as $player) {
                if (!$player->canSee($this)) {
                    $player->showPlayer($this);
                }
            }
            $this->hiddenPlayers = [];

            foreach ($this->windowIndex as $window) {
                $this->removeWindow($window);
            }

            foreach ($this->usedChunks as $index => $d) {
                Level::getXZ($index, $chunkX, $chunkZ);
                $this->level->unregisterChunkLoader($this, $chunkX, $chunkZ);
                foreach ($this->level->getChunkEntities($chunkX, $chunkZ) as $entity) {
                    $entity->despawnFrom($this, false);
                }
                unset($this->usedChunks[$index]);
            }

            parent::close();

            $this->interface->close($this, $notify ? $reason : "");

            if ($this->loggedIn) {
                $this->server->removeOnlinePlayer($this);
            }

            $this->loggedIn = false;

            $this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_USERS, $this);
            $this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);

            if (isset($ev) and $this->username != "" and $this->spawned !== false and $ev->getQuitMessage() != "") {
                if ($this->server->playerMsgType === Server::PLAYER_MSG_TYPE_MESSAGE) $this->server->broadcastMessage($ev->getQuitMessage());
                elseif ($this->server->playerMsgType === Server::PLAYER_MSG_TYPE_TIP) $this->server->broadcastTip(str_replace("@player", $this->getName(), $this->server->playerLogoutMsg));
                elseif ($this->server->playerMsgType === Server::PLAYER_MSG_TYPE_POPUP) $this->server->broadcastPopup(str_replace("@player", $this->getName(), $this->server->playerLogoutMsg));
            }

            $this->spawned = false;
            $this->server->getLogger()->info($this->getServer()->getLanguage()->translateString("pocketmine.player.logOut", [
                TextFormat::AQUA . $this->getName() . TextFormat::WHITE,
                $this->ip,
                $this->port,
                $this->getServer()->getLanguage()->translateString($reason)
            ]));
            $this->windows = new \SplObjectStorage();
            $this->windowIndex = [];
            $this->usedChunks = [];
            $this->loadQueue = [];
            $this->hasSpawned = [];
            $this->spawnPosition = null;

            if ($this->server->dserverConfig["enable"] and $this->server->dserverConfig["queryAutoUpdate"]) $this->server->updateQuery();
        }

        if ($this->perm !== null) {
            $this->perm->clearPermissions();
            $this->perm = null;
        }

        $this->inventory = null;
        $this->floatingInventory = null;
        $this->enderChestInventory = null;
        $this->transactionQueue = null;

        $this->chunk = null;

        $this->server->removePlayer($this);
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [];
    }

    /**
     * Handles player data saving
     *
     * @param bool $async
     */
    public function save($async = false)
    {
        if ($this->closed) {
            throw new \InvalidStateException("Tried to save closed player");
        }

        parent::saveNBT();
        if ($this->level instanceof Level) {
            $this->namedtag->Level = new StringTag("Level", $this->level->getName());
            if ($this->hasValidSpawnPosition()) {
                $this->namedtag["SpawnLevel"] = $this->spawnPosition->getLevel()->getName();
                $this->namedtag["SpawnX"] = (int)$this->spawnPosition->x;
                $this->namedtag["SpawnY"] = (int)$this->spawnPosition->y;
                $this->namedtag["SpawnZ"] = (int)$this->spawnPosition->z;
            }

            foreach ($this->achievements as $achievement => $status) {
                $this->namedtag->Achievements[$achievement] = new ByteTag($achievement, $status === true ? 1 : 0);
            }

            $this->namedtag["playerGameType"] = $this->gamemode;
            $this->namedtag["lastPlayed"] = new LongTag("lastPlayed", floor(microtime(true) * 1000));
            $this->namedtag["Health"] = new ShortTag("Health", $this->getHealth());
            $this->namedtag["MaxHealth"] = new ShortTag("MaxHealth", $this->getMaxHealth());

            if ($this->username != "" and $this->namedtag instanceof CompoundTag) {
                $this->server->saveOfflinePlayerData($this->username, $this->namedtag, $async);
            }
        }
    }

    /**
     * Gets the username
     *
     * @return string
     */
    public function getName()
    {
        return $this->username;
    }

    public function kill()
    {
        if (!$this->spawned) {
            return;
        }

        $message = "death.attack.generic";

        $params = [
            $this->getDisplayName()
        ];

        $cause = $this->getLastDamageCause();

        switch ($cause === null ? EntityDamageEvent::CAUSE_CUSTOM : $cause->getCause()) {
            case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
                if ($cause instanceof EntityDamageByEntityEvent) {
                    $e = $cause->getDamager();
                    if ($e instanceof Player) {
                        $message = "death.attack.player";
                        $params[] = $e->getDisplayName();
                        break;
                    } elseif ($e instanceof Living) {
                        $message = "death.attack.mob";
                        $params[] = $e->getNameTag() !== "" ? $e->getNameTag() : $e->getName();
                        break;
                    } else {
                        $params[] = "Unknown";
                    }
                }
                break;
            case EntityDamageEvent::CAUSE_PROJECTILE:
                if ($cause instanceof EntityDamageByEntityEvent) {
                    $e = $cause->getDamager();
                    if ($e instanceof Player) {
                        $message = "death.attack.arrow";
                        $params[] = $e->getDisplayName();
                    } elseif ($e instanceof Living) {
                        $message = "death.attack.arrow";
                        $params[] = $e->getNameTag() !== "" ? $e->getNameTag() : $e->getName();
                        break;
                    } else {
                        $params[] = "Unknown";
                    }
                }
                break;
            case EntityDamageEvent::CAUSE_SUICIDE:
                $message = "death.attack.generic";
                break;
            case EntityDamageEvent::CAUSE_VOID:
                $message = "death.attack.outOfWorld";
                break;
            case EntityDamageEvent::CAUSE_FALL:
                if ($cause instanceof EntityDamageEvent) {
                    if ($cause->getFinalDamage() > 2) {
                        $message = "death.fell.accident.generic";
                        break;
                    }
                }
                $message = "death.attack.fall";
                break;

            case EntityDamageEvent::CAUSE_SUFFOCATION:
                $message = "death.attack.inWall";
                break;

            case EntityDamageEvent::CAUSE_LAVA:
                $message = "death.attack.lava";
                break;

            case EntityDamageEvent::CAUSE_FIRE:
                $message = "death.attack.onFire";
                break;

            case EntityDamageEvent::CAUSE_FIRE_TICK:
                $message = "death.attack.inFire";
                break;

            case EntityDamageEvent::CAUSE_DROWNING:
                $message = "death.attack.drown";
                break;

            case EntityDamageEvent::CAUSE_CONTACT:
                if ($cause instanceof EntityDamageByBlockEvent) {
                    if ($cause->getDamager()->getId() === Block::CACTUS) {
                        $message = "death.attack.cactus";
                    }
                }
                break;

            case EntityDamageEvent::CAUSE_BLOCK_EXPLOSION:
            case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
                if ($cause instanceof EntityDamageByEntityEvent) {
                    $e = $cause->getDamager();
                    if ($e instanceof Player) {
                        $message = "death.attack.explosion.player";
                        $params[] = $e->getDisplayName();
                    } elseif ($e instanceof Living) {
                        $message = "death.attack.explosion.player";
                        $params[] = $e->getNameTag() !== "" ? $e->getNameTag() : $e->getName();
                        break;
                    }
                } else {
                    $message = "death.attack.explosion";
                }
                break;

            case EntityDamageEvent::CAUSE_MAGIC:
                $message = "death.attack.magic";
                break;

            case EntityDamageEvent::CAUSE_CUSTOM:
                break;

            default:

        }

        Entity::kill();

        $ev = new PlayerDeathEvent($this, $this->getDrops(), new TranslationContainer($message, $params));
        $ev->setKeepInventory($this->server->keepInventory);
        $ev->setKeepExperience($this->server->keepExperience);
        $this->server->getPluginManager()->callEvent($ev);

        if (!$ev->getKeepInventory()) {
            foreach ($ev->getDrops() as $item) {
                $this->level->dropItem($this, $item);
            }

            if ($this->inventory !== null) {
                $this->inventory->clearAll();
            }
        }

        if ($this->server->expEnabled and !$ev->getKeepExperience()) {
            $exp = min(91, $this->getTotalXp()); //Max 7 levels of exp dropped
            $this->getLevel()->spawnXPOrb($this->add(0, 0.2, 0), $exp);
            $this->setTotalXp(0, true);
        }

        if ($ev->getDeathMessage() != "") {
            $this->server->broadcast($ev->getDeathMessage(), Server::BROADCAST_CHANNEL_USERS);
        }

        $pos = $this->getSpawn();

        $this->setHealth(0);

        $pk = new RespawnPacket();
        $pk->x = $pos->x;
        $pk->y = $pos->y;
        $pk->z = $pos->z;
        $this->dataPacket($pk);
    }

    /**
     * @param int $amount
     */
    public function setHealth($amount)
    {
        parent::setHealth($amount);
        if ($this->spawned === true) {
            $this->foodTick = 0;
            $this->getAttributeMap()->getAttribute(Attribute::HEALTH)->setMaxValue($this->getMaxHealth())->setValue($amount, true);
        }
    }

    /**
     * @param float $damage
     * @param EntityDamageEvent $source
     *
     * @return bool
     */
    public function attack($damage, EntityDamageEvent $source)
    {
        if (!$this->isAlive()) {
            return false;
        }

        if ($this->isCreative()
            and $source->getCause() !== EntityDamageEvent::CAUSE_MAGIC
            and $source->getCause() !== EntityDamageEvent::CAUSE_SUICIDE
            and $source->getCause() !== EntityDamageEvent::CAUSE_VOID
        ) {
            $source->setCancelled();
        } elseif ($this->allowFlight and $source->getCause() === EntityDamageEvent::CAUSE_FALL) {
            $source->setCancelled();
        }

        parent::attack($damage, $source);

        if ($source->isCancelled()) {
            return false;
        } elseif ($this->getLastDamageCause() === $source and $this->spawned) {
            $pk = new EntityEventPacket();
            $pk->eid = $this->id;
            $pk->event = EntityEventPacket::HURT_ANIMATION;
            $this->dataPacket($pk);

            if ($this->isSurvival()) {
                $this->exhaust(0.3, PlayerExhaustEvent::CAUSE_DAMAGE);
            }
        }
        return true;
    }

    /**
     * @param Vector3 $pos
     * @param null $yaw
     * @param null $pitch
     * @param int $mode
     * @param array|null $targets
     */
    public function sendPosition(Vector3 $pos, $yaw = null, $pitch = null, $mode = MovePlayerPacket::MODE_NORMAL, array $targets = null)
    {
        $yaw = $yaw === null ? $this->yaw : $yaw;
        $pitch = $pitch === null ? $this->pitch : $pitch;

        $pk = new MovePlayerPacket();
        $pk->eid = $this->getId();
        $pk->x = $pos->x;
        $pk->y = $pos->y + $this->getEyeHeight();
        $pk->z = $pos->z;
        $pk->bodyYaw = $yaw;
        $pk->pitch = $pitch;
        $pk->yaw = $yaw;
        $pk->mode = $mode;

        if($targets !== null){
        	$this->server->broadcastPacket($targets, $pk);
        }else{
        	$this->dataPacket($pk);
        }

        $this->newPosition = null;
    }

    protected function checkChunks()
    {
        if ($this->chunk === null or ($this->chunk->getX() !== ($this->x >> 4) or $this->chunk->getZ() !== ($this->z >> 4))) {
            if ($this->chunk !== null) {
                $this->chunk->removeEntity($this);
            }
            $this->chunk = $this->level->getChunk($this->x >> 4, $this->z >> 4, true);

            if (!$this->justCreated) {
                $newChunk = $this->level->getChunkPlayers($this->x >> 4, $this->z >> 4);
                unset($newChunk[$this->getLoaderId()]);

                /** @var Player[] $reload */
                $reload = [];
                foreach ($this->hasSpawned as $player) {
                    if (!isset($newChunk[$player->getLoaderId()])) {
                        $this->despawnFrom($player);
                    } else {
                        unset($newChunk[$player->getLoaderId()]);
                        $reload[] = $player;
                    }
                }

                foreach ($newChunk as $player) {
                    $this->spawnTo($player);
                }
            }

            if ($this->chunk === null) {
                return;
            }

            $this->chunk->addEntity($this);
        }
    }

    /**
     * @return bool
     */
    protected function checkTeleportPosition()
    {
        if ($this->teleportPosition !== null) {
            $chunkX = $this->teleportPosition->x >> 4;
            $chunkZ = $this->teleportPosition->z >> 4;

            for ($X = -1; $X <= 1; ++$X) {
                for ($Z = -1; $Z <= 1; ++$Z) {
                    if (!isset($this->usedChunks[$index = Level::chunkHash($chunkX + $X, $chunkZ + $Z)]) or $this->usedChunks[$index] === false) {
                        return false;
                    }
                }
            }

            $this->sendPosition($this, null, null, MovePlayerPacket::MODE_RESET);
            $this->spawnToAll();
            $this->forceMovement = $this->teleportPosition;
            $this->teleportPosition = null;

            return true;
        }

        return true;
    }

    /**
     * @param Vector3|Position|Location $pos
     * @param float $yaw
     * @param float $pitch
     *
     * @return bool
     */
    public function teleport(Vector3 $pos, $yaw = null, $pitch = null)
    {
        if (!$this->isOnline()) {
            return false;
        }

        $oldPos = $this->getPosition();
        if (parent::teleport($pos, $yaw, $pitch)) {

            foreach ($this->windowIndex as $window) {
                if ($window === $this->inventory) {
                    continue;
                }
                $this->removeWindow($window);
            }

            $this->teleportPosition = new Vector3($this->x, $this->y, $this->z);

            if (!$this->checkTeleportPosition()) {
                $this->forceMovement = $oldPos;
            } else {
                $this->spawnToAll();
            }


            $this->resetFallDistance();
            $this->nextChunkOrderRun = 0;
            $this->newPosition = null;
            $this->stopSleep();
            return true;
        }
        return false;
    }

    /**
     * This method may not be reliable. Clients don't like to be moved into unloaded chunks.
     * Use teleport() for a delayed teleport after chunks have been sent.
     *
     * @param Vector3 $pos
     * @param float $yaw
     * @param float $pitch
     */
    public function teleportImmediate(Vector3 $pos, $yaw = null, $pitch = null)
    {
        if (parent::teleport($pos, $yaw, $pitch)) {

            foreach ($this->windowIndex as $window) {
                if ($window === $this->inventory) {
                    continue;
                }
                $this->removeWindow($window);
            }

            $this->forceMovement = new Vector3($this->x, $this->y, $this->z);
            $this->sendPosition($this, $this->yaw, $this->pitch, MovePlayerPacket::MODE_RESET);


            $this->resetFallDistance();
            $this->orderChunks();
            $this->nextChunkOrderRun = 0;
            $this->newPosition = null;
        }
    }


    /**
     * @param Inventory $inventory
     *
     * @return int
     */
    public function getWindowId(Inventory $inventory): int
    {
        if ($this->windows->contains($inventory)) {
            return $this->windows[$inventory];
        }

        return -1;
    }

    /**
     * Returns the created/existing window id
     *
     * @param Inventory $inventory
     * @param int $forceId
     *
     * @return int
     */
    public function addWindow(Inventory $inventory, $forceId = null): int{
        if ($this->windows->contains($inventory)) {
            return $this->windows[$inventory];
        }

        if ($forceId === null) {
            $this->windowCnt = $cnt = max(2, ++$this->windowCnt % 99);
        } else {
            $cnt = (int)$forceId;
        }

        $this->currentWindow = $inventory;
        $this->currentWindowId = $cnt;
        $this->windowIndex[$cnt] = $inventory;
        $this->windows->attach($inventory, $cnt);
        if ($inventory->open($this)) {
            return $cnt;
        } else {
            $this->removeWindow($inventory);

            return -1;
        }
    }

    /**
     * @param Inventory $inventory
     */
    public function removeWindow(Inventory $inventory)
    {
        $inventory->close($this);
        if ($this->windows->contains($inventory)) {
            $id = $this->windows[$inventory];
            $this->windows->detach($this->windowIndex[$id]);
            unset($this->windowIndex[$id]);
            $this->currentWindow = null;
            $this->currentWindowId = -1;
        }
    }

    /**
     * @param string $metadataKey
     * @param MetadataValue $metadataValue
     */
    public function setMetadata($metadataKey, MetadataValue $metadataValue)
    {
        $this->server->getPlayerMetadata()->setMetadata($this, $metadataKey, $metadataValue);
    }

    /**
     * @param string $metadataKey
     *
     * @return MetadataValue[]
     */
    public function getMetadata($metadataKey)
    {
        return $this->server->getPlayerMetadata()->getMetadata($this, $metadataKey);
    }

    /**
     * @param string $metadataKey
     *
     * @return bool
     */
    public function hasMetadata($metadataKey)
    {
        return $this->server->getPlayerMetadata()->hasMetadata($this, $metadataKey);
    }

    /**
     * @param string $metadataKey
     * @param Plugin $plugin
     */
    public function removeMetadata($metadataKey, Plugin $plugin)
    {
        $this->server->getPlayerMetadata()->removeMetadata($this, $metadataKey, $plugin);
    }

    /**
     * @param Chunk $chunk
     */
    public function onChunkChanged(Chunk $chunk)
    {
        if (isset($this->usedChunks[$hash = Level::chunkHash($chunk->getX(), $chunk->getZ())])) {
            $this->usedChunks[$hash] = false;
        }
        if (!$this->spawned) {
            $this->nextChunkOrderRun = 0;
        }
    }

    /**
     * @param Chunk $chunk
     */
    public function onChunkLoaded(Chunk $chunk)
    {

    }

    /**
     * @param Chunk $chunk
     */
    public function onChunkPopulated(Chunk $chunk)
    {

    }

    /**
     * @param Chunk $chunk
     */
    public function onChunkUnloaded(Chunk $chunk)
    {

    }

    /**
     * @param Vector3 $block
     */
    public function onBlockChanged(Vector3 $block)
    {

    }

    /**
     * @return int|null
     */
    public function getLoaderId()
    {
        return $this->loaderId;
    }

    /**
     * @return bool
     */
    public function isLoaderActive()
    {
        return $this->isConnected();
    }

    /**
     * @param Effect $effect
     *
     * @return bool|void
     * @internal param $Effect
     */
    public function addEffect(Effect $effect){//Overwrite
        if ($effect->isBad() && $this->isCreative()) {
            return;
        }

        parent::addEffect($effect);
    }
}