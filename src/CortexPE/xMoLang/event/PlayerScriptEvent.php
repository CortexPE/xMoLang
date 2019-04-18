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


namespace CortexPE\xMoLang\event;


use pocketmine\event\player\PlayerEvent;
use pocketmine\Player;

class PlayerScriptEvent extends PlayerEvent {
	/** @var string */
	protected $scriptEventName;
	/** @var mixed */
	protected $scriptEventData;

	/**
	 * PlayerScriptEvent constructor.
	 *
	 * @param Player $player
	 * @param string $scriptEventName
	 * @param mixed  $scriptEventData
	 */
	public function __construct(Player $player, string $scriptEventName, $scriptEventData) {
		$this->player = $player;
		$this->scriptEventName = $scriptEventName;
		$this->scriptEventData = $scriptEventData;
	}

	/**
	 * @return string
	 */
	public function getScriptEventName(): string {
		return $this->scriptEventName;
	}

	/**
	 * @return mixed
	 */
	public function getScriptEventData() {
		return $this->scriptEventData;
	}
}