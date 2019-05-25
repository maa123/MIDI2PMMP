<?php
namespace MIDI2PMMP\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\Player;

use pocketmine\plugin\Plugin;


use MIDI2PMMP;

class M2PCommand extends Command {
	public function __construct(Plugin $_this){
		parent::__construct("midi", "MIDIを再生します", "/midi [list|play number|stop|reload]");
		$this->_this = $_this;
	}
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!isset($args[0])){
			return false;
		}
		if($args[0] === 'list'){
			$list = $this->_this->getSoundList();
			foreach ($list as $value) {
				$sender->sendMessage('[M2P]'.$value[0].'. '.$value[1]);
			}
		}else if($args[0] === 'play' && isset($args[1])){
			if($this->_this->startSoundNumber($sender->getPlayer(), intval($args[1]))){
				$sender->sendMessage('[M2P] 再生を開始します。');
				return true;
			}
			return false;
		}else if($args[0] === 'reload'){
			$list = $this->_this->reloadSoundList();
			$sender->sendMessage('[M2P] Reload');
			return true;
		}else if($args[0] === 'stop'){
			$list = $this->_this->stopSound($sender->getPlayer());
			$sender->sendMessage('[M2P] Request Stop...');
			return true;
		}
		return false;
	}
}