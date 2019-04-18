<?php


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
	 * @param        $scriptEventData
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