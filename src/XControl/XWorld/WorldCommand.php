<?php

namespace XControl\XWorld;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\utils\Config;
use pocketmine\Player;


class WorldCommand extends Command
{
	
	public function __construct(World $plugin)
	{
	
		$this->plugin=$plugin;
		$this->main=$plugin->plugin;
	 $this->conf = $this->plugin->conf;
		parent::__construct("xcw","§b[XControl]§e -> 世界控制区块指令");
	}
	public function execute(CommandSender $sender, $label, array $args)
	{

		if(isset($args[0]))
		{
			switch($args[0])
			{
				case"help":
				if ($sender instanceof Player && !$sender->isOp()) {
            $sender->sendMessage("§b[XUser] §7->§e这个操作是非法的~");
            return true;
        }
        if(($sender instanceof Player && $sender->isOp()) || !$sender instanceof Player){
				  $helplist = [
                    "§a======= §f[§bXControl-World§f] §a=======",
                    "§b /xcw admin <world> <player> §e添加/移除一个管理员",
                ];
                foreach ($helplist as $helps) $sender->sendMessage($helps);
                break;
                }
    
    case'admin':
    if ($sender instanceof Player) {
            $sender->sendMessage("§b[XWorld] §7->§c这个指令只允许后台执行");
            return true;
        }
    if(isset($args[1]) && isset($args[2])){
    if($args[1] == 'all'){
    $data = new Config($this->main->path . "DataBase/WorldBase/Config.yml", Config::YAML, []);
    if(!in_array($args[2],$data->get('Admin'))){
    $adminlist = $data->get('Admin');
    $adminlist[] = $args[2];
    $data->set('Admin',$adminlist);
    $data->save();
    $sender->sendMessage("§e[XWorld] §7->§a成功添加 §e总管理员 §b[{$args[2]}]");
    }else{
    $adminlist = $data->get('Admin');
    $admin = array_search($args[2], $adminlist);
    $admin = array_splice($adminlist, $admin, 1); 
    $data->set('Admin',$adminlist);
    $data->save();
    $sender->sendMessage("§e[XWorld] §7->§c成功移除 §a总管理员 §b[{$args[2]}]");
    }
    }else{
    $data = new Config($this->main->path . "DataBase/WorldBase/Data/{$args[1]}.yml", Config::YAML, []);
    if(!in_array($args[2],$data->get('Admin'))){
    $adminlist = $data->get('Admin');
    $adminlist[] = $args[2];
    $data->set('Admin',$adminlist);
    $data->save();
    $sender->sendMessage("§e[XWorld] §7->§a成功添加 §e{$args[1]}管理员 §b[{$args[2]}]");
    }else{
    $adminlist = $data->get('Admin');
    $admin = array_search($args[2], $adminlist);
    $admin = array_splice($adminlist, $admin, 1); 
    $data->set('Admin',$adminlist);
    $data->save();
    $sender->sendMessage("§e[XWorld] §7->§c成功移除 §a总管理员 §b[{$args[2]}]");
    }
    }
   }else{
   $sender->sendMessage("§b[XWorld] §7->§a正确指令 §e/xcw admin <world> <player>");
   }
    break;
	}//command结尾
	}else{
	$sender->sendMessage("§b[XWorld] §7->§a请查看帮助指令 §e/xcw help");
	}
	}
}