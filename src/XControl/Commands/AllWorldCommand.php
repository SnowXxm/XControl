<?php

namespace XControl\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\Player;

use XControl\Main;

class AllWorldCommand extends Command{
	private $plugin;

	public function __construct(Main $plugin){
		parent::__construct("wl", "§b[XWorld]§e -> 查看所有世界");

		$this->plugin = $plugin;
	}

	public function execute(CommandSender $sender, $label, array $args)
    {
		 	  $levels = $this->plugin->getServer()->getLevels();
				$sender->sendMessage("§b|======§e-世界列表-§b======|");
     foreach ($levels as $level){
	   $sender->sendMessage(" §6§l- ".$level->getFolderName());
	   }
	   $sender->sendMessage("§e——\n§9小贴士: 使用指令 §b/w 世界名 §9进行传送");
		   	return true;
     
     }
}
