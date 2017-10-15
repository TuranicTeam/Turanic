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

/**
 * Command handling related classes
 */

namespace pocketmine\command;

use pocketmine\event\TextContainer;
use pocketmine\event\TimingsHandler;
use pocketmine\event\TranslationContainer;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\command\overload\{CommandOverload, CommandParameter, CommandEnum};

abstract class Command {

	/** @var string */
	private $name;

	/** @var string */
	private $nextLabel;

	/** @var string */
	private $label;

	/**
	 * @var string[]
	 */
	private $aliases = [];

	/**
	 * @var string[]
	 */
	private $activeAliases = [];

	/** @var CommandMap */
	private $commandMap = null;

	/** @var string */
	protected $description = "";

	/** @var string */
	protected $usageMessage;

	/** @var string */
	private $permissionMessage = null;
	
	protected $pocketminePermission = null;
	
	private $permissionLevel = 0;

	/** @var TimingsHandler */
	public $timings;
	
	/** @var CommandOverload[] */
	public $overloads = [];

	/**
	 * @param string   $name
	 * @param string   $description
	 * @param string   $usageMessage
	 * @param string[] $aliases
	 */
	public function __construct($name, $description = "", $usageMessage = null, array $aliases = [], array $overloads = []){
		$this->name = $this->nextLabel = $this->label = $name;
		$this->setDescription($description);
		$this->usageMessage = $usageMessage === null ? "/" . $name : $usageMessage;
		$this->setAliases($aliases);
		$this->timings = new TimingsHandler("** Command: " . $name);
		
		if(count($overloads) == 0){
			self::applyDefaultSettings($this);
		}else{
			$this->overloads = $overloads;
		}
	}
	
	/**
	 * @return array
	 */
	public function getOverloads() : array{
		return $this->overloads;
	}
	
	public function addOverload(CommandOverload $overload){
		$this->overloads[$overload->getName()] = $overload;
	}
	
	public function getOverload(string $name){
		return $this->overloads[$name] ?? null;
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $commandLabel
	 * @param string[]      $args
	 *
	 * @return mixed
	 */
	public abstract function execute(CommandSender $sender, $commandLabel, array $args);

	/**
	 * @return string
	 */
	public function getName() : string{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getPermission(){
		return $this->pocketminePermission ?? null;
	}


	/**
	 * @param string|null $permission
	 */
	public function setPermission($permission){
		if($permission !== null){
			$this->pocketminePermission = $permission;
		}else{
			unset($this->pocketminePermission);
		}
	}
	
	public function getPermissionLevel() : int{
		return $this->permissionLevel;
	}
	
	public function setPermissionLevel(int $level){
		$this->permissionLevel = $level;
	}

	/**
	 * @param CommandSender $target
	 *
	 * @return bool
	 */
	public function canExecute(CommandSender $target) : bool{
		if($this->scanPermission($target)){
			return true;
		}

		if($this->permissionMessage === null){
			$target->sendMessage(TextFormat::RED . new TranslationContainer("%commands.generic.permission"));
		}elseif($this->permissionMessage !== ""){
			$target->sendMessage(str_replace("<permission>", $this->getPermission(), $this->permissionMessage));
		}

		return false;
	}

	/**
	 * @param CommandSender $target
	 *
	 * @return bool
	 */
	public function scanPermission(CommandSender $target) : bool{
		if(($perm = $this->getPermission()) === null or $perm === ""){
			return true;
		}

		foreach(explode(";", $perm) as $permission){
			if($target->hasPermission($permission)){
				return true;
			}
		}

		return false;
	}
		
	/**
	 * @deprecated
	 */
	public function testPermission(CommandSender $sender) : bool{
		return $this->canExecute($sender);
	}
	
	/**
	 * @deprecated
	 */
	public function testPermissionSilent(CommandSender $sender) : bool{
		return $this->canExecute($sender);
	}

	/**
	 * @return string
	 */
	public function getLabel(){
		return $this->label;
	}

	/**
	 * @param $name
	 *
	 * @return bool
	 */
	public function setLabel($name){
		$this->nextLabel = $name;
		if(!$this->isRegistered()){
			$this->timings = new TimingsHandler("** Command: " . $name);
			$this->label = $name;

			return true;
		}

		return false;
	}

	/**
	 * Registers the command into a Command map
	 *
	 * @param CommandMap $commandMap
	 *
	 * @return bool
	 */
	public function register(CommandMap $commandMap){
		if($this->allowChangesFrom($commandMap)){
			$this->commandMap = $commandMap;

			return true;
		}

		return false;
	}

	/**
	 * @param CommandMap $commandMap
	 *
	 * @return bool
	 */
	public function unregister(CommandMap $commandMap){
		if($this->allowChangesFrom($commandMap)){
			$this->commandMap = null;
			$this->activeAliases = $this->aliases;
			$this->label = $this->nextLabel;

			return true;
		}

		return false;
	}

	/**
	 * @param CommandMap $commandMap
	 *
	 * @return bool
	 */
	private function allowChangesFrom(CommandMap $commandMap){
		return $this->commandMap === null or $this->commandMap === $commandMap;
	}

	/**
	 * @return bool
	 */
	public function isRegistered(){
		return $this->commandMap !== null;
	}

	/**
	 * @return string[]
	 */
	public function getAliases(){
		return $this->activeAliases;
	}

	/**
	 * @return string
	 */
	public function getPermissionMessage(){
		return $this->permissionMessage;
	}

	/**
	 * @return string
	 */
	public function getDescription(){
		return $this->description;
	}

	/**
	 * @return string
	 */
	public function getUsage(){
		return $this->usageMessage;
	}

	/**
	 * @param string[] $aliases
	 */
	public function setAliases(array $aliases){
		$this->aliases = $aliases;
		if(!$this->isRegistered()){
			$this->activeAliases = (array) $aliases;
		}
	}
	
	public function getAliasesEnum() : CommandEnum{
		$enum = new CommandEnum("aliases", $this->aliases);
		return $enum;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description){
		if(strlen($description) > 0 and $description[0] == '%'){
			$description = Server::getInstance()->getLanguage()->translateString($description);
		}
		$this->description = $description;
	}

	/**
	 * @param string $permissionMessage
	 */
	public function setPermissionMessage($permissionMessage){
		if(strlen($permissionMessage) > 0 and $permissionMessage[0] == '%'){
			$permissionMessage = Server::getInstance()->getLanguage()->translateString($permissionMessage);
		}
		$this->permissionMessage = $permissionMessage;
	}

	/**
	 * @param string $usage
	 */
	public function setUsage($usage){
		if(strlen($usage) > 0 and $usage[0] == '%'){
			$usage = Server::getInstance()->getLanguage()->translateString($usage);
		}
		$this->usageMessage = $usage;
	}

	public static function applyDefaultSettings(Command $cmd){
		$defParam = new CommandParameter("args");
		$overload = new CommandOverload("default", [$defParam]);
		$cmd->addOverload($overload);
	}

	/**
	 * @param CommandSender $source
	 * @param string        $message
	 * @param bool          $sendToSource
	 */
	public static function broadcastCommandMessage(CommandSender $source, $message, $sendToSource = true){
		if($message instanceof TextContainer){
			$m = clone $message;
			$result = "[" . $source->getName() . ": " . ($source->getServer()->getLanguage()->get($m->getText()) !== $m->getText() ? "%" : "") . $m->getText() . "]";

			$users = $source->getServer()->getPluginManager()->getPermissionSubscriptions(Server::BROADCAST_CHANNEL_ADMINISTRATIVE);
			$colored = TextFormat::GRAY . TextFormat::ITALIC . $result;

			$m->setText($result);
			$result = clone $m;
			$m->setText($colored);
			$colored = clone $m;
		}else{
			$users = $source->getServer()->getPluginManager()->getPermissionSubscriptions(Server::BROADCAST_CHANNEL_ADMINISTRATIVE);
			$result = new TranslationContainer("chat.type.admin", [$source->getName(), $message]);
			$colored = TextFormat::GRAY . TextFormat::ITALIC . new TranslationContainer("%chat.type.admin", [$source->getName(), $message]);
		}

		if($sendToSource === true and !($source instanceof ConsoleCommandSender)){
			$source->sendMessage($message);
		}

		foreach($users as $user){
			if($user instanceof CommandSender){
				if($user instanceof ConsoleCommandSender){
					$user->sendMessage($result);
				}elseif($user !== $source){
					$user->sendMessage($colored);
				}
			}
		}
	}

	/**
	 * @return string
	 */
	public function __toString(){
		return $this->name;
	}
}
