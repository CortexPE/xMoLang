<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace CortexPE\xMoLang\behaviorpack;


use CortexPE\xMoLang\Main;
use ErrorException;
use InvalidArgumentException;
use Logger;
use pocketmine\resourcepacks\ResourcePack;
use pocketmine\utils\Config;
use SplFileInfo;

class BehaviorPackManager {
	/** @var Main */
	protected $loader;
	/** @var string */
	protected $path;
	/** @var array */
	protected $behaviorPacks = [];
	/** @var array */
	protected $uuidList = [];
	/** @var bool */
	protected $hasClientScripts = false;

	public function __construct(Main $loader, string $path, Logger $logger) {
		$this->loader = $loader;
		$this->path = $path;

		if(!file_exists($this->path)) {
			$logger->debug("Behavior packs path $path does not exist, creating directory");
			mkdir($this->path);
		} elseif(!is_dir($this->path)) {
			throw new InvalidArgumentException("Behavior packs path $path exists and is not a directory");
		}

		$loader->saveResource("behavior_packs.yml");
		$resourcePacksConfig = new Config($loader->getDataFolder() . "behavior_packs.yml", Config::YAML, []);

		$logger->info("Loading behavior packs...");

		$behaviorStack = $resourcePacksConfig->get("behavior_stack", []);
		if(!is_array($behaviorStack)) {
			throw new InvalidArgumentException("\"behavior_stack\" key should contain a list of pack names");
		}

		foreach($behaviorStack as $pos => $pack) {
			try {
				$pack = (string)$pack;
			} catch(ErrorException $e) {
				$logger->critical("Found invalid entry in behavior pack list at offset $pos of type " . gettype($pack));
				continue;
			}
			try {
				/** @var string $pack */
				$packPath = $this->path . DIRECTORY_SEPARATOR . $pack;
				if(!file_exists($packPath)) {
					throw new BehaviorPackException("File or directory not found");
				}
				if(is_dir($packPath)) {
					throw new BehaviorPackException("Directory behavior packs are unsupported");
				}

				$newPack = null;
				//Detect the type of resource pack.
				$info = new SplFileInfo($packPath);
				switch($info->getExtension()) {
					case "zip":
					case "mcpack":
						$newPack = new ZippedBehaviorPack($packPath);
						break;
				}

				if($newPack instanceof ResourcePack) {
					$this->behaviorPacks[] = $newPack;
					$this->uuidList[strtolower($newPack->getPackId())] = $newPack;

					if($newPack->hasClientScripts()) {
						$this->hasClientScripts = true;
					}
				} else {
					throw new BehaviorPackException("Format not recognized");
				}
			} catch(BehaviorPackException $e) {
				$logger->critical("Could not load behavior pack \"$pack\": " . $e->getMessage());
			}
		}

		$logger->debug("Successfully loaded " . count($this->behaviorPacks) . " behavior packs");
	}

	/**
	 * @return bool
	 */
	public function hasClientScripts(): bool {
		return $this->hasClientScripts;
	}

	/**
	 * @return array
	 */
	public function getBehaviorPacks(): array {
		return $this->behaviorPacks;
	}

	public function getPackById(string $id) {
		return $this->uuidList[strtolower($id)] ?? null;
	}
}