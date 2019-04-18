<?php


namespace CortexPE\xMoLang;


use CortexPE\xMoLang\behaviorpack\BehaviorPack;
use CortexPE\xMoLang\event\PlayerScriptEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\ResourcePackChunkDataPacket;
use pocketmine\network\mcpe\protocol\ResourcePackChunkRequestPacket;
use pocketmine\network\mcpe\protocol\ResourcePackClientResponsePacket;
use pocketmine\network\mcpe\protocol\ResourcePackDataInfoPacket;
use pocketmine\network\mcpe\protocol\ResourcePacksInfoPacket;
use pocketmine\network\mcpe\protocol\ScriptCustomEventPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;

class PacketInjector implements Listener {
	/** @var Main */
	protected $loader;

	public function __construct(Main $loader) {
		$this->loader = $loader;
	}

	/**
	 * @param DataPacketSendEvent $ev
	 *
	 * @priority LOWEST
	 */
	public function onPacketSend(DataPacketSendEvent $ev): void {
		$pk = $ev->getPacket();
		if($pk instanceof StartGamePacket) {
			var_dump("inject game rules");
			$pk->gameRules["experimentalgameplay"] = [1, true];
		} elseif($pk instanceof ResourcePacksInfoPacket) {
			var_dump("inject resource pack info");
			$mgr = $this->loader->getBehaviorPackManager();
			$pk->behaviorPackEntries = $mgr->getBehaviorPacks();
			$pk->hasScripts = $mgr->hasClientScripts();
		}
	}

	/**
	 * @param DataPacketReceiveEvent $ev
	 *
	 * @priority LOWEST
	 * @throws \ReflectionException
	 */
	public function onPacketReceive(DataPacketReceiveEvent $ev): void {
		$pk = $ev->getPacket();
		if($pk instanceof ScriptCustomEventPacket) {
			var_dump("got script custom event");
			$eventName = $pk->eventName;
			$eventData = json_decode($pk->eventData);
			$ev = new PlayerScriptEvent($ev->getPlayer(), $eventName, $eventData);
			$ev->call();
		}elseif($pk instanceof ResourcePackClientResponsePacket && $pk->status == ResourcePackClientResponsePacket::STATUS_SEND_PACKS){
			var_dump("got resource pack client response");
			$player = $ev->getPlayer();
			$manager = $this->loader->getBehaviorPackManager();
			$provided = [];
			var_dump($pk->packIds);
			foreach($pk->packIds as  $uuid) {
				$pack = $manager->getPackById(substr($uuid, 0,
					strpos($uuid, "_"))); //dirty hack for mojang's dirty hack for versions
				if($pack instanceof BehaviorPack) {
					$resPk = new ResourcePackDataInfoPacket();
					$resPk->packId = $pack->getPackId();
					$resPk->maxChunkSize = 1048576; //1MB
					$resPk->chunkCount = (int)ceil($pack->getPackSize() / $resPk->maxChunkSize);
					$resPk->compressedPackSize = $pack->getPackSize();
					$resPk->sha256 = $pack->getSha256();
					$player->sendDataPacket($resPk);
					$provided[] = $uuid;
				}
			}
			$pk->packIds = array_diff($pk->packIds, $provided);
			var_dump($pk->packIds, $provided);
		}elseif($pk instanceof ResourcePackChunkRequestPacket){
			var_dump("got resource pack chunk request");
			$player = $ev->getPlayer();
			$manager = $this->loader->getBehaviorPackManager();
			$pack = $manager->getPackById($pk->packId);
			if($pack instanceof BehaviorPack) {
				$resPk = new ResourcePackChunkDataPacket();
				$resPk->packId = $pack->getPackId();
				$resPk->chunkIndex = $pk->chunkIndex;
				$resPk->data = $pack->getPackChunk(1048576 * $pk->chunkIndex, 1048576);
				$resPk->progress = (1048576 * $pk->chunkIndex);
				$player->sendDataPacket($resPk);
				$ev->setCancelled(); // lets not let PM know about this
			}
		}
	}

	public function onPlayerScriptEvent(PlayerScriptEvent $event){
		if($event->getScriptEventName() === "mod:client_entered_world"){
			$message = "Welcome " . $event->getPlayer()->getName() . " to a PocketMine server!";

			Main::sendScriptCustomEvent($event->getPlayer(),"mod:show_message", $message);
		}
		elseif($event->getScriptEventName() === "mod:click"){
			$event->getPlayer()->getServer()->broadcastMessage($event->getPlayer()->getName() . " is a " . $event->getScriptEventData() . "!");
		}
	}
}