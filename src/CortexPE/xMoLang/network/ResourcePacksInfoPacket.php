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


namespace CortexPE\xMoLang\network;


use CortexPE\xMoLang\behaviorpack\BehaviorPack;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\ResourcePacksInfoPacket as PMResourcePacksInfoPacket;
use pocketmine\resourcepacks\ResourcePack;
use pocketmine\utils\Binary;

class ResourcePacksInfoPacket extends PMResourcePacksInfoPacket {
	public const NETWORK_ID = ProtocolInfo::RESOURCE_PACKS_INFO_PACKET;

	/** @var bool */
	public $mustAccept = false; //if true, forces client to use selected resource packs
	/** @var bool */
	public $hasScripts = false; //if true, causes disconnect for any platform that doesn't support scripts yet
	/** @var BehaviorPack[] */
	public $behaviorPackEntries = [];
	/** @var ResourcePack[] */
	public $resourcePackEntries = [];

	public function handle(NetworkSession $session): bool {
		return $session->handleResourcePacksInfo($this);
	}

	protected function decodePayload() {
		$this->mustAccept = (($this->get(1) !== "\x00"));
		$this->hasScripts = (($this->get(1) !== "\x00"));
		$behaviorPackCount = ((unpack("v", $this->get(2))[1]));
		while($behaviorPackCount-- > 0) {
			$this->getString();
			$this->getString();
			(Binary::readLLong($this->get(8)));
			$this->getString();
			$this->getString();
			$this->getString();
			(($this->get(1) !== "\x00"));
		}

		$resourcePackCount = ((unpack("v", $this->get(2))[1]));
		while($resourcePackCount-- > 0) {
			$this->getString();
			$this->getString();
			(Binary::readLLong($this->get(8)));
			$this->getString();
			$this->getString();
			$this->getString();
			(($this->get(1) !== "\x00"));
		}
	}

	protected function encodePayload() {
		($this->buffer .= ($this->mustAccept ? "\x01" : "\x00"));
		($this->buffer .= ($this->hasScripts ? "\x01" : "\x00"));
		($this->buffer .= (pack("v", count($this->behaviorPackEntries))));
		foreach($this->behaviorPackEntries as $entry) {
			$this->putString($entry->getPackId());
			$this->putString($entry->getPackVersion());
			($this->buffer .= (pack("VV", $entry->getPackSize() & 0xFFFFFFFF, $entry->getPackSize() >> 32)));
			$this->putString(""); //TODO: encryption key
			$this->putString(""); //TODO: subpack name
			$this->putString(""); //TODO: content identity
			($this->buffer .= ($entry->hasClientScripts() ? "\x01" : "\x00")); //TODO: has scripts (?)
		}
		($this->buffer .= (pack("v", count($this->resourcePackEntries))));
		foreach($this->resourcePackEntries as $entry) {
			$this->putString($entry->getPackId());
			$this->putString($entry->getPackVersion());
			($this->buffer .= (pack("VV", $entry->getPackSize() & 0xFFFFFFFF, $entry->getPackSize() >> 32)));
			$this->putString(""); //TODO: encryption key
			$this->putString(""); //TODO: subpack name
			$this->putString(""); //TODO: content identity
			($this->buffer .= (false ? "\x01" : "\x00")); //TODO: seems useless for resource packs
		}
	}
}