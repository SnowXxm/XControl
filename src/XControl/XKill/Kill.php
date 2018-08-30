<?php

namespace XControl\XKill;

use XControl\Main;

use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;

use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

use onebone\economyapi\EconomyAPI;

class Kill implements Listener
{
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        @mkdir($this->plugin->path . "DataBase/KillBase");
        @mkdir($this->plugin->path . "DataBase/KillBase/Data");
        $this->conf = (new Config($this->plugin->path . "DataBase/KillBase/Config.yml", Config::YAML, [
            'CheckWorld' => ['zy'],
            //检查的世界
            'KillToPrison' => [
                'Count' => 3,
                'Time' => 12,//小时
                //监狱
            ],
            'KillToBan' => [
                'Count' => 5,
                'Time' => 24,//小时
                //禁封
            ],
            'ClearTime' => 10,
            //多少 天 重置杀人记录
            'PrisonSet' => [
                'PrisonPos' => null,
                'PrisonRound' => null,
            ],
            //监狱的坐标
        ]))->getAll();


        $this->player = new Config($this->plugin->path . "DataBase/KillBase/Data/PlayerData.yml", Config::YAML, []);

        $this->plugin->getServer()->getPluginManager()->registerEvents(new KillEvent($this), $plugin);
        $map = $this->plugin->getServer()->getCommandMap();
        $map->register("Main", new KillCommand($this));
    }

    public function setData($name, $diybantime = null)
    {
        //没有数据，新建数据
        if ($this->player->get($name) === false) {
            $this->player->set($name, [
                'Kill' => 0,
                'BanTime' => 0,
                'LastKill' => 0
            ]);
            $this->player->save();
        }
        //调用关押玩家指定天数
        if ($diybantime !== null) {
            $data = $this->player->get($name);
            $data['BanTime'] = time() + $diybantime;
            $this->player->set($name, $data);
            $this->player->save();
            if ($this->plugin->getServer()->getPlayer($name)) {
                $this->checkData($name);
            }
            return;
        }
        $data = $this->player->get($name);
        $data['Kill']++;
        $data['LastKill'] = time();
        $this->player->set($name, $data);
        $this->player->save;
        $this->checkData($name);
    }

    public function checkData($name)
    {
        //检查监狱是否已经设置好
        if ($this->conf['PrisonSet']['PrisonPos'] !== null) {
            $result = $this->checkPlayer($name);
            return $result;
        }
    }

    public function checkPlayer($name)
    {
        if (!$this->player->get($name) === false) {
            $data = $this->player->get($name);
            $ktp = $this->conf['KillToPrison']['Count'];
            $ktb = $this->conf['KillToBan']['Count'];
            //是否符合惩罚/释放条件
            if ($data['BanTime'] > time()) {
                return false;
            } elseif ($data['BanTime'] < time() && $data['BanTime'] != 0) {
                $data['BanTime'] = 0;
                $this->player->set($name, $data);
                $this->player->save();
                $player = $this->getServer()->getPlayer($name);
                $player->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
                $player->sendMessage("§c[XPrison] §7->§e已经从监狱释放~");
            }
            //符合送监狱
            if ($data['Kill'] >= $ktp && $ktp != 0) {
                //符合禁封
                $punishtime = $ktp['Time'] * 3600;
                $data['BanTime'] = time() + $punishtime;
                if ($data['Kill'] <= $ktb && $ktp != 0) {
                    $punishtime = $ktb['Time'] * 3600;
                    $data['BanTime'] = time() + $punishtime;
                    return false;
                    //未完成
                }
                $this->player->set($name, $data);
                $this->player->save();
                $pos = explode("|", $this->conf['PrisonSet']['PrisonPos']);
                $player->teleport(new Position($pos[0], $pos[1], $pos[2], $this->plugin->getServer()->getLevelByName($pos[3])));
                $freetime = (int)(($data['BanTime'] - time()) / 3600);
                $player->sendMessage("§c=====§f[§eXControl-Prison§f]§c=====");
                $player->sendMessage("§b-> §e杀人数达到警戒值，你已经被看押！");
                $player->sendMessage("§b->§e距离释放还有 §d{$freetime} §e小时");
                return false;
            }
        }
    }


}