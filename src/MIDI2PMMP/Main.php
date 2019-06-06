<?php
namespace MIDI2PMMP;


use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;

use pocketmine\Player;

use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;

use pocketmine\scheduler\Task;

use pocketmine\Server;

use pocketmine\network\mcpe\protocol\PlaySoundPacket;



use MIDI2PMMP\commands\M2PCommand;

class Main extends PluginBase implements Listener{

	public $SoundPlayers = [];

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getCommandMap()->registerAll("MIDI2PMMP", [new M2PCommand($this)]);

		$this->soundList = glob($this->getDataFolder()."*.json");
	}

	public function getSoundList(){
		$_list = [];
		foreach ($this->soundList as $key => $sound) {
			$_list[] = [$key, basename($sound, '.json')];
		}
		return $_list;
	}

	public function reloadSoundList(){
		$this->soundList = glob($this->getDataFolder()."*.json");
	}

	public function onLogin(PlayerLoginEvent $event){
		$this->SoundPlayers[$event->getPlayer()->getId()] = null;
	}

	public function stopSound(Player $player){
		if(isset($this->SoundPlayers[$player->getId()])){
			$this->SoundPlayers[$player->getId()]->getHandler()->cancel();
			$this->SoundPlayers[$player->getId()] = null;
		}
	}

	public function startSoundNumber(Player $player, int $num){
		if(!isset($this->soundList[$num])){
			return false;
		}
		$_midi = json_decode(file_get_contents($this->soundList[$num]), true);
		$this->startSound($player, $_midi['data'], ceil($_midi['maxtick']), 20);
		return true;
	}

	private function startSound(Player $player, $notes, $time, $delay = 200){
		if(isset($this->SoundPlayers[$player->getId()])){
			$this->SoundPlayers[$player->getId()]->getHandler()->cancel();
			$this->SoundPlayers[$player->getId()] = null;
		}
		$this->getScheduler()->scheduleDelayedRepeatingTask(new class($this, $player, $notes, $time) extends Task{
			private $_tick = 0;
			protected static $progms = ['note.harp', 'note.bassattack', 'note.bd', 'note.snare', 'note.hat', 'note.pling'];
			public function __construct($_this, $player, $notes, $time){
				$this->_this = $_this;
				$this->player = $player;
				$this->sound = $notes;
				$_this->SoundPlayers[$player->getId()] = $this;
				$this->time = $time;
				$this->endTime = self::SecToMinSec($time/20);
			}
			public static function SecToMinSec($sec){
				$min = floor($sec / 60);
				$sec = floor($sec % 60);
				return ($min>0)?($min."分".$sec."秒"):($sec."秒");
			}
			public function onRun(int $tick){
				if($this->time < $this->_tick){
					$this->getHandler()->cancel();
					$this->_this->SoundPlayers[$this->player->getId()] = null;
					return;
				}
				if(isset($this->sound[$this->_tick])){
					foreach ($this->sound[$this->_tick] as $note){
						$pk = new PlaySoundPacket();
						if(!isset($note[2])){
							$pk->soundName = "note.harp";
						}else{
							$pk->soundName = self::$progms[$note[2]];
						}
						$pk->pitch = $note[0];
						$pk->volume = $note[1];
						$pk->x = $this->player->x;
						$pk->y = $this->player->y;
						$pk->z = $this->player->z;
						$this->player->dataPacket($pk);
					}
					$this->player->sendTip("[M2P] Playing...  ".self::SecToMinSec($this->_tick/20).'/'.$this->endTime." notes:".count($this->sound[$this->_tick]));
				}else{
					$this->player->sendTip("[M2P] Playing...  ".self::SecToMinSec($this->_tick/20).'/'.$this->endTime." notes:0");
				}
				$this->_tick++;
			}
		}, $delay, 1);
	}

}
