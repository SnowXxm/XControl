<?php

namespace XControl\XAdmin;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\utils\Config;
use pocketmine\Player;


class AdminCommand extends Command
{
	
	public function __construct(Admin $plugin)
	{
	
		$this->plugin=$plugin;
		$this->main=$plugin->plugin;
	 $this->conf = $this->plugin->conf;
		parent::__construct("xca","§b[XControl]§e -> 权限节点区块指令");
	}
	public function execute(CommandSender $sender, $label, array $args)
    {

        if (isset($args[0])) {
            switch ($args[0]) {
                case"help":
                    if ($sender instanceof Player) {
                        $sender->sendMessage("§b[XAdmin] §7->§e这个操作是非法的~");
                        return true;
                    }
                    $helplist = [
                        "§a======= §f[§bXControl-Admin§f] §a=======",
                        "§b /xca new <class> §e添加一个权限节点",
                        "§b /xca see <class> §e查看当前节点所有成员",
                        "§b /xca add <class> <player>§e在一个权限节点添加一个玩家",
                        "§b /xca del <class> <player>§e从一个权限节点移除一个玩家",
                    ];
                    foreach ($helplist as $helps) $sender->sendMessage($helps);
                    break;

                case 'admin':
                    if ($sender instanceof Player) {
                        $sender->sendMessage("§b[XAdmin] §7->§c这个指令只允许后台执行~");
                        return true;
                    }
                    if (isset($args[1])) {
                        switch ($args[1]) {
                            case'new':
                                if(isset($args[2])){
                                    if (!file_exists($this->plugin->path . 'DataBase/AdminBase/Data/' . "$args[2].yml")) {
                                        $data = new Config($this->main->path . "DataBase/AdminBase/{$args[2]}.yml", Config::YAML, [
                                            'Member' => [],
                                            'Commands' => [],
                                            'Control' => [
                                                'Command' => true,
                                                'Chat' => true,
                                            ],
                                        ]);
                                        $sender->sendMessage('§b[XAdmin] §a成功创建权限节点');
                                    }else{
                                        $sender->sendMessage('§b[XAdmin] §e已经存在相同的权限节点');
                                        break;
                                    }
                                }else{
                                    $sender->sendMessage("§b[XAdmin] §7->§a请输入一个权限组名称");
                                    break;
                                }
                            case'see':
                                if(isset($args[2])){
                                    if (file_exists($this->plugin->path . 'DataBase/AdminBase/Data/' . "$args[2].yml")) {
                                        $data = (new Config($this->main->path . "DataBase/AdminBase/{$args[2]}.yml", Config::YAML, []))->getAll();
                                        $sender->sendMessage("§f[§bXControl-Admin§f] §a 权限组成员如下");
                                        foreach ($data[Member] as $players) {
                                            $sender->sendMessage("§7 -》 §b{$players}");
                                        }
                                    }else{
                                        $sender->sendMessage('§b[XAdmin] §e这个权限组未创建');
                                        break;
                                    }
                                }else{
                                    $sender->sendMessage("§b[XAdmin] §7->§a请输入查询的权限组名称");
                                    break;
                                }
                                break;
                            case'add':
                                if(isset($args[2]) && isset($args[3])){
                                    if (file_exists($this->plugin->path . 'DataBase/AdminBase/Data/' . "$args[2].yml")) {
                                        $data = (new Config($this->main->path . "DataBase/AdminBase/{$args[2]}.yml", Config::YAML, []))->getAll();
                                        /*
                                         * 2017.6.25
                                         * 太累了，不写了
                                         */
                                        foreach ($data[Member] as $players) {
                                            $sender->sendMessage("§7 -》 §b{$players}");
                                        }
                                    }else{
                                        $sender->sendMessage('§b[XAdmin] §e这个权限组未创建');
                                        break;
                                    }
                                }else{
                                    $sender->sendMessage("§b[XAdmin] §7->§a请正确输入指令 /xca add <class> <player>");
                                    break;
                                }
                                break;

                        }
                            }else {
                        $sender->sendMessage("§b[XAdmin] §7->§a请查看帮助 §e/xca help");
                        break;
                    }
                    break;
            }
        }//command结尾
    }
}