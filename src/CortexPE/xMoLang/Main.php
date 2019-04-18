<?php

/**
 *                      __
 * __  __ /\/\   ___   / /  __ _ _ __   __ _
 * \ \/ //    \ / _ \ / /  / _` | '_ \ / _` |
 *  >  </ /\/\ \ (_) / /__| (_| | | | | (_| |
 * /_/\_\/    \/\___/\____/\__,_|_| |_|\__, |
 *                                     |___/
 *
 * Copyright (C) CortexPE 2019
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

declare(strict_types=1);

namespace CortexPE\xMoLang;

use CortexPE\xMoLang\behaviorpack\BehaviorPackManager;
use CortexPE\xMoLang\network\ResourcePacksInfoPacket;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\ScriptCustomEventPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase {
	/** @var BehaviorPackManager */
	protected $behaviorPackManager;

	public static function sendScriptCustomEvent(Player $player, string $scriptEventName, $scriptEventData): void {
		$pk = new ScriptCustomEventPacket();
		$pk->eventName = $scriptEventName;
		$pk->eventData = json_encode($scriptEventData);
		$player->sendDataPacket($pk);
	}

	public function onEnable(): void {
		$this->behaviorPackManager = new BehaviorPackManager($this,
			$this->getDataFolder() . "behavior_packs" . DIRECTORY_SEPARATOR, $this->getLogger());
		$this->getServer()->getPluginManager()->registerEvents(new PacketInjector($this), $this);
		PacketPool::registerPacket(new ResourcePacksInfoPacket());
	}

	/**
	 * @return BehaviorPackManager
	 */
	public function getBehaviorPackManager(): BehaviorPackManager {
		return $this->behaviorPackManager;
	}
}
