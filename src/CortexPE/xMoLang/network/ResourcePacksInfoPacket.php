<?php


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

	protected function decodePayload(){
		$this->mustAccept = (($this->get(1) !== "\x00"));
		$this->hasScripts = (($this->get(1) !== "\x00"));
		$behaviorPackCount = ((unpack("v", $this->get(2))[1]));
		while($behaviorPackCount-- > 0){
			$this->getString();
			$this->getString();
			(Binary::readLLong($this->get(8)));
			$this->getString();
			$this->getString();
			$this->getString();
			(($this->get(1) !== "\x00"));
		}

		$resourcePackCount = ((unpack("v", $this->get(2))[1]));
		while($resourcePackCount-- > 0){
			$this->getString();
			$this->getString();
			(Binary::readLLong($this->get(8)));
			$this->getString();
			$this->getString();
			$this->getString();
			(($this->get(1) !== "\x00"));
		}
	}

	protected function encodePayload(){
		var_dump("yo");
		($this->buffer .= ($this->mustAccept ? "\x01" : "\x00"));
		($this->buffer .= ($this->hasScripts ? "\x01" : "\x00"));
		($this->buffer .= (pack("v", count($this->behaviorPackEntries))));
		foreach($this->behaviorPackEntries as $entry){
			$this->putString($entry->getPackId());
			$this->putString($entry->getPackVersion());
			($this->buffer .= (pack("VV", $entry->getPackSize() & 0xFFFFFFFF, $entry->getPackSize() >> 32)));
			$this->putString(""); //TODO: encryption key
			$this->putString(""); //TODO: subpack name
			$this->putString(""); //TODO: content identity
			($this->buffer .= ($entry->hasClientScripts() ? "\x01" : "\x00")); //TODO: has scripts (?)
			var_dump($entry->getPackId());
		}
		($this->buffer .= (pack("v", count($this->resourcePackEntries))));
		foreach($this->resourcePackEntries as $entry){
			$this->putString($entry->getPackId());
			$this->putString($entry->getPackVersion());
			($this->buffer .= (pack("VV", $entry->getPackSize() & 0xFFFFFFFF, $entry->getPackSize() >> 32)));
			$this->putString(""); //TODO: encryption key
			$this->putString(""); //TODO: subpack name
			$this->putString(""); //TODO: content identity
			($this->buffer .= (false ? "\x01" : "\x00")); //TODO: seems useless for resource packs
		}
	}

	public function handle(NetworkSession $session) : bool{
		return $session->handleResourcePacksInfo($this);
	}
}