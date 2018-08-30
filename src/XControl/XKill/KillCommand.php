<?php

namespace XControl\XKill;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\utils\Config;
use pocketmine\Player;


class KillCommand extends Command
{

    public function __construct(Kill $plugin)
    {

        $this->plugin = $plugin;
        $this->main = $plugin->plugin;
        $this->conf = $this->plugin->conf;
        $this->player = $this->plugin->player;
        parent::__construct("xck", "§b[XControl]§e -> 杀人控制区块指令");
    }

    public function execute(CommandSender $sender, $label, array $args)
    {

        if (isset($args[0])) {
            switch ($args[0]) {
                case"help":
                    if ($sender instanceof Player && !$sender->isOp()) {
                        $sender->sendMessage("§b[XKill] §7->§e这个操作是非法的~");
                        return true;
                    }
                    $helpList = [
                        "§a======= §f[§bXControl-Kill§f] §a=======",
                        "§b /xck pos1 §e设置监狱的起点",
                        "§b /xck pos2 §e设置监狱的终点",
                        "§b /xck center §e设置监狱的中心点",
                        "§b /xck unset §e删除监狱设置",
                        "§b /xck see <player> §e查看一个玩家的状态",
                        "§b /xck add <player> <time> §e把一个玩家送到小黑屋",
                        "§b /xck del <player>§e把一个玩家从小黑屋放出",
                    ];
                    foreach ($helpList as $helps) $sender->sendMessage($helps);
                    break;

                case"pos1":
                    if (!$sender instanceof Player || !$sender->isOp()) {
                        $sender->sendMessage("§b[XKill] §7->§e这个操作是非法的~");
                        return true;
                    }
                    $this->setting[$sender->getName()] = [
                        'Pos1X' => (int)$sender->getX(),
                        'Pos1Z' => (int)$sender->getZ(),
                        'Pos2X' => null,
                        'Pos2Z' => null,
                        'Level' => $sender->getLevel()->getFolderName(),
                    ];
                    $sender->sendMessage("§c[XKill] §a成功设置监狱坐标§e起点§a输入§b/xck pos2 §a进行下一步~");
                    break;

                case"pos2":
                    if (!$sender instanceof Player || !$sender->isOp()) {
                        $sender->sendMessage("§b[XKill] §7->§e这个操作是非法的~");
                        return true;
                    }
                    $name = $sender->getName();
                    if (isset($this->setting[$name]['Level'])) {
                        if ($this->setting[$name]['Level'] != $sender->getLevel()->getFolderName()) {
                            $sender->sendMessage("§c[XKill] §e调皮，请不要更换世界设置点2喔~");
                            break;
                        }
                        if ($this->setting[$name]['Pos1X'] == (int)$sender->getX() || $this->setting[$name]['Pos1Z'] == (int)$sender->getZ()) {
                            $sender->sendMessage("§c[XKill] §e我去，你家的监狱一条线啊？");
                            break;
                        }
                        $this->setting[$name]['Pos2X'] = (int)$sender->getX();
                        $this->setting[$name]['Pos2Z'] = (int)$sender->getZ();
                        $sender->sendMessage("§c[XKill] §a成功设置监狱坐标§e终点§a输入§b/xck  center§a进行下一步~");
                    } else {
                        $sender->sendMessage("§c[XKill] §e请先设置监狱坐标§a起点§a输入§b/xck pos1 §e来完成操作");
                        break;
                    }
                    break;
                case"center":
                    if (!$sender instanceof Player || !$sender->isOp()) {
                        $sender->sendMessage("§b[XKill] §7->§e这个操作是非法的~");
                        return true;
                    }
                    $name = $sender->getName();
                    if (isset($this->setting[$name]['Pos2X'])) {
                        if ($this->setting[$name]['Level'] != $sender->getLevel()->getFolderName()) {
                            $sender->sendMessage("§c[XKill] §e调皮，请不要更换世界设置中心点喔~");
                            break;
                        }
                        $conf = new Config($this->main->path . "DataBase/KillBase/Config.yml", Config::YAML, []);
                        $data = $conf->get('PrisonSet');
                        $data['PrisonRound'] = $this->setting[$name]['Pos1X'] . "|" . $this->setting[$name]['Pos1Z'] . "|" . $this->setting[$name]['Pos2X'] . "|" . $this->setting[$name]['Pos2Z'];
                        $data['PrisonPos'] = (int)$sender->getX() . "|" . (int)$sender->getY() . "|" . (int)$sender->getZ() . "|" . $sender->getLevel()->getFolderName();
                        $conf->set('PrisonSet', $data);
                        $conf->save();
                        $sender->sendMessage("§c[XKill] §a监狱建立完成~");
                    } else {
                        $sender->sendMessage("§c[XKill] §e请先完成之前的操作！");
                        break;
                    }
                    break;
                case"unset":
                    if (!$sender instanceof Player || !$sender->isOp()) {
                        $sender->sendMessage("§b[XKill] §7->§e这个操作是非法的~");
                        return true;
                    }
                    if (isset($this->setting[$sender->getName()])) {
                        unset ($this->setting[$sender->getName()]);
                        $sender->sendMessage("§c[XKill] §e已经清除当前监狱配置");
                        break;
                    }
                    $conf = new Config($this->main->path . "DataBase/KillBase/Config.yml", Config::YAML[]);
                    $data = $conf->get('PrisonSet');
                    if ($data['PrisonPos'] !== null) {
                        $data['PrisonPos'] = null;
                        $data['PrisonRound'] = null;
                        $conf->set('PrisonSet', $data);
                        $conf->save();
                        $sender->sendMessage("§c[XKill] §e已经清除当前监狱配置");
                        break;
                    } else {
                        $sender->sendMessage("§c[XKill] §e并没有监狱被创建");
                        break;
                    }
                    break;
                case"see":
                    if ($sender instanceof Player && !$sender->isOp()) {
                        $sender->sendMessage("§b[XKill] §7->§e这个操作是非法的~");
                        return true;
                    }
                    if (isset($args[1])) {
                        if (!$this->player->get($args[1]) === false) {
                            $data = $this->player->get($args[1]);
                            $killnumber = $data['Kill'];
                            $sender->sendMessage("§a======= §f[§bXKill_Search§f] §a=======");
                            $sender->sendMessage("§b[玩家名]§7>>>§a {$args[1]} ");
                            $sender->sendMessage("§6[详细信息] ：");
                            $sender->sendMessage("§1*-- §c杀人数§d-> §e{$killnumber}");
                            if ($data['BanTime'] > time()) {
                                $freetime = (int)(($data['BanTime'] - time()) / 3600);
                                $sender->sendMessage("§1*-- §9状态§d-> §c关押中§e[距离解封 {$freetime} 小时]");
                            } else {
                                $sender->sendMessage("§1*-- §9状态§d-> §a正常");
                            }
                            $sender->sendMessage("§a——");
                            break;
                        } else {
                            $sender->sendMessage("§c[XKill] §e没有找到这个玩家的数据");
                        }
                    } else {
                        $sender->sendMessage("§c[XKill] §e没有输入目标的名字让我很难办啊~");
                    }
                    break;
                case"add":
                    if ($sender instanceof Player && !$sender->isOp()) {
                        $sender->sendMessage("§b[XKill] §7->§e这个操作是非法的~");
                        return true;
                    }
                    if (isset($args[1])) {
                        if (isset($args[2]) && is_numeric($args[2])) {
                            $this->plugin->setData($args[1], $args[2]);
                            $sender->sendMessage("§c[XKill] §a成功关押玩家 §e[{$args[1]}]");
                        } else {
                            $sender->sendMessage("§c[XKill] §e没有输入时间或者它不是一个数字！");
                        }
                    } else {
                        $sender->sendMessage("§c[XKill] §e没有输入目标的名字让我很难办啊~");
                    }
                    break;
                case"del":
                    if ($sender instanceof Player && !$sender->isOp()) {
                        $sender->sendMessage("§b[XKill] §7->§e这个操作是非法的~");
                        return true;
                    }
                    if (isset($args[1])) {
                        $this->player->remove($args[1]);
                        $this->player->save();
                        $sender->sendMessage("§c[XKill] §a成功移除玩家 §e[{$args[1]}] §a信息~");
                    } else {
                        $sender->sendMessage("§c[XKill] §e没有输入目标的名字让我很难办啊~");
                    }
                    break;
            }//command结尾
        }
    }
}