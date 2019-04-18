<?php


namespace CortexPE\xMoLang\behaviorpack;


use CortexPE\xMoLang\Main;
use pocketmine\resourcepacks\ResourcePack;
use pocketmine\utils\Config;

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

	public function __construct(Main $loader, string $path, \Logger $logger) {
		$this->loader = $loader;
		$this->path = $path;

		if(!file_exists($this->path)) {
			$logger->debug("Behavior packs path $path does not exist, creating directory");
			mkdir($this->path);
		} elseif(!is_dir($this->path)) {
			throw new \InvalidArgumentException("Behavior packs path $path exists and is not a directory");
		}

		$loader->saveResource("behavior_packs.yml");
		$resourcePacksConfig = new Config($loader->getDataFolder() . "behavior_packs.yml", Config::YAML, []);

		$logger->info("Loading behavior packs...");

		$behaviorStack = $resourcePacksConfig->get("behavior_stack", []);
		if(!is_array($behaviorStack)) {
			throw new \InvalidArgumentException("\"behavior_stack\" key should contain a list of pack names");
		}

		foreach($behaviorStack as $pos => $pack) {
			try {
				$pack = (string)$pack;
			} catch(\ErrorException $e) {
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
				$info = new \SplFileInfo($packPath);
				switch($info->getExtension()) {
					case "zip":
					case "mcpack":
						$newPack = new ZippedBehaviorPack($packPath);
						break;
				}

				if($newPack instanceof ResourcePack) {
					$this->behaviorPacks[] = $newPack;
					$this->uuidList[strtolower($newPack->getPackId())] = $newPack;

					if($newPack->hasClientScripts()){
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