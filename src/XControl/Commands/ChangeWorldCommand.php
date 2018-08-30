<?php

namespace XControl\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\utils\Config;

use XControl\Main;

class ChangeWorldCommand extends Command{
	private $plugin;

	public function __construct(Main $plugin){
		parent::__construct("w", "§b[XWorld]§e -> 切换世界");

		$this->plugin = $plugin;
	}

	public function execute(CommandSender $sender, $label, array $args)
    {
		 		 if ($sender instanceof Player){
		 	  if(isset($args[0])){
		 	  if($this->plugin->getServer()->isLevelLoaded($args[0])){
		    $level = $this->plugin->getServer()->getLevelByName($args[0]);
 	    $sender->teleport($level->getSafeSpawn());
 	    $data = new Config($this->plugin->path . "DataBase/WorldBase/Data/{$args[0]}.yml", Config::YAML, []);
 	    $sender->sendMessage("§b[XWorld] §7->§a成功空降至 {$args[0]}");
 	    if(!$data->get('WorldMessage') == false)
 	    $sender->sendMessage("§e§l世界信息 --- {$data->get('WorldMessage')}");
		    }else{
		 	  $sender->sendMessage("§b[XWorld] §7->§e世界 §6{$args[1]} §e不存在或者未加载");
		 	  $sender->sendMessage("§e——\n§9小贴士: 使用指令 §b/wl §9查看所有世界");
		 	  }
		 	  }else{
		 	  $sender->sendMessage("§b[XWorld] §7->§a请正确输入指令 §e/w <世界名>");
		 	  }
		   	return true;
		   	}
     
     }
}
