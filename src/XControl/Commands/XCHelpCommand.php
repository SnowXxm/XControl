<?php

namespace XControl\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

use XControl\Main;

class XCHelpCommand extends Command{
	private $plugin;

	public function __construct(Main $plugin){
		parent::__construct("xc", "§b[XControl]§e -> 指令指南");

		$this->plugin = $plugin;
	}

	public function execute(CommandSender $sender, $label, array $args)
    {
		 		 if ($sender instanceof Player && !$sender->isOp()) {

                 }
		 	  if(isset($args[0]) && $args[0] == 'help'){
                  $helplist = [
                      "§7======= §f[§bXControl-Help§f] §7=======",
                      "§a矿物监控模块帮助 §7>>> §b/xcm help",
                      "§a杀人控制模块帮助 §7>>> §b/xck help",
                      "§a用户管理模块帮助 §7>>> §b/xcu help",
                      "§a世界管理模块帮助 §7>>> §b/xcw help",
                      "§a权限节点模块帮助 §7>>> §b/xca help",
                      "§e---",
                      "§d##没写为无指令或者正在制作~",
                  ];
                  foreach ($helplist as $helps) $sender->sendMessage($helps);
		 	  }else{
		 	  $sender->sendMessage("§b[XControl] §7->§a请正确输入指令 §e/xc help");
		 	  return true;
		 	  }
     }
}
