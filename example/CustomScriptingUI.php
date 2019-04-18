<?php

/**
 * @name CustomScriptingUI
 * @main CortexPE\xMoLang\test\CustomScriptingUI
 * @version 1.0.0
 * @api 3.0.0
 * @description Sample plugin to test xMolang
 * @author Spajker7, CortexPE
 */
 
declare(strict_types=1);

namespace CortexPE\xMoLang\test {
	use CortexPE\xMoLang\event\PlayerScriptEvent;
	use CortexPE\xMoLang\Main;
	use pocketmine\plugin\PluginBase;
	use pocketmine\event\Listener;
	
	class CustomScriptingUI extends PluginBase implements Listener {
		public function onEnable() : void {
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
		}

		public function onPlayerScriptEvent(PlayerScriptEvent $ev) : void {
			$p = $ev->getPlayer();
			if($ev->getScriptEventName() == "mod:client_entered_world"){
				Main::sendScriptCustomEvent(
					$p,
					"mod:show_message",
					$p->getName() . " to a PocketMine server!"
				);
			} elseif($ev->getScriptEventName() == "mod:click") {
				$this->getServer()->broadcastMessage(
					$p->getName() . " is a " . $ev->getScriptEventData() . "!"
				);
			}
		}
	}
}