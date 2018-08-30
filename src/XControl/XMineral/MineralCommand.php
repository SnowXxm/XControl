<?php

namespace XControl\XMineral;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;


class MineralCommand extends Command
{

    public function __construct(Mineral $plugin)
    {
        $this->plugin = $plugin;
        $this->main = $plugin->plugin;
        parent::__construct("xcm", "§b[XControl]§e -> 矿物监控指令区块");
    }

    public function execute(CommandSender $sender, $label, array $args)
    {
        /*
                if(isset($args[0]))
                {
                if (!$sender instanceof Player || !$sender->isOp()) {
                    $sender->sendMessage("§b[XMineral] §7->§e这个操作是非法的~");
                    return true;
                }
                    switch($args[0])
                    {
                        case"help":
                          $helpList = [
                            "§a======= §f[§bXControl-Mineral§f] §a=======",
                            "§b /xcm see <第一行> <第二行> <第三行> <第四行>",
                            "§b /xcm set <行数(0-3)> <内容>",
                            "§9--",
                            "§6* 请使用@代替空格~",
                        ];
                        foreach ($helpList as $helps) $sender->sendMessage($helps);
                        break;
            }//command结尾
            }*/
    }
}