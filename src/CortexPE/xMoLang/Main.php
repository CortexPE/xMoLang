<?php

declare(strict_types=1);

namespace CortexPE\xMoLang;

use CortexPE\xMoLang\behaviorpack\BehaviorPackManager;
use CortexPE\xMoLang\network\ResourcePacksInfoPacket;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\ScriptCustomEventPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;

class Main extends PluginBase {
	/** @var BehaviorPackManager */
	protected $behaviorPackManager;

	public function onEnable(): void {
		$this->behaviorPackManager = new BehaviorPackManager($this,
			$this->getDataFolder() . "behavior_packs" . DIRECTORY_SEPARATOR, $this->getLogger());
		$this->getServer()->getPluginManager()->registerEvents(new PacketInjector($this), $this);
		$this->getScheduler()->scheduleTask(new ClosureTask(function (int $_):void{
			PacketPool::registerPacket(new ResourcePacksInfoPacket());
		}));
	}

	public static function sendScriptCustomEvent(Player $player, string $scriptEventName, $scriptEventData): void {
		$pk = new ScriptCustomEventPacket();
		$pk->eventName = $scriptEventName;
		$pk->eventData = json_encode($scriptEventData);
		$player->sendDataPacket($pk);
	}

	/**
	 * @return BehaviorPackManager
	 */
	public function getBehaviorPackManager(): BehaviorPackManager {
		return $this->behaviorPackManager;
	}
}
