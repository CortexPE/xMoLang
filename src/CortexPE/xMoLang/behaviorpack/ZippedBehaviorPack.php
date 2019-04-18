<?php


namespace CortexPE\xMoLang\behaviorpack;


use pocketmine\resourcepacks\ZippedResourcePack;

class ZippedBehaviorPack extends ZippedResourcePack implements BehaviorPack {
	/** @var bool */
	private $hasClientScripts = false;
	/**
	 * @param string $zipPath Path to the behavior pack zip
	 */
	public function __construct(string $zipPath){
		parent::__construct($zipPath);
		if(is_array($this->manifest->modules)){
			foreach($this->manifest->modules as $module){
				if(isset($module->type) && $module->type === "client_data"){
					$this->hasClientScripts = true;
				}
			}
		}
	}
	/**
	 * Returns whether the behavior pack contains a client-side script.
	 * @return bool
	 */
	public function hasClientScripts() : bool{
		return $this->hasClientScripts;
	}
}