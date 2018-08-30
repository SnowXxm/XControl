<?php

namespace XControl\XMineral;

use XControl\Main;

use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;

use XControl\Xkill\Kill;

use onebone\economyapi\EconomyAPI;

class Mineral implements Listener
{
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        @mkdir($this->plugin->path . "DataBase/MineralBase");
        @mkdir($this->plugin->path . "DataBase/MineralBase/Data");
        $this->conf = (new Config($this->plugin->path . "DataBase/MineralBase/Config.yml", Config::YAML, [
            'CheckWorld' => ['zy'],
            //检查的世界
            'CheckTime' => 600,
            //检查的间隔(s)
            'CheckCount' => [
                'Diamond' => 12,
                'Gold' => 20,
                'Emerald' => 12,
            ],
            //矿物警戒值
            'OverPlan' => 1,
            "#" => "0-不启用，1-限制挖矿，2-放入监狱，3-禁封(不可用)",
            'PunishTime' => 2,
            //禁封时长(h)
        ]))->getAll();

        $this->player = new Config($this->plugin->path . "DataBase/MineralBase/Data/PlayerData.yml", Config::YAML, []);

        $this->daily = new Config($this->plugin->path . "DataBase/MineralBase/Data/DailyData.yml", Config::YAML, [
            "Date" => date("Y-m-j"),
            "Data" => [
                "Diamond" => [],
                "Gold" => [],
                "Emerald" => [],
            ]
        ]);
        $this->plugin->getServer()->getPluginManager()->registerEvents(new MineralEvent($this), $plugin);
        $map = $this->plugin->getServer()->getCommandMap();
        $map->register("Main", new MineralCommand($this));
    }

    //权限检查
    public function checkPermission($player, $block)
    {
        $name = $player->getName();
        if (!in_array($block->getlevel()->getFolderName(), $this->conf["CheckWorld"])) {
            return false;
        }
        if (!in_array($block->getId(), [14,15,16,21,56,73,129])) {
            return false;
        }
        if (
            $player->getGamemode() !== 0 && !$player->isOp()
        ) {
            return false;
        }
        if ($this->player->get($name) === false) {
            $this->setData($name);
        }
        return true;
    }

    //数据记录
    public function setData($name, $type = null)
    {
        if ($type !== null) {
            $data = $this->player->get($name);
            $data['Count'][$type]++;
            $this->player->set($name, $data);
            $this->player->save();
            $data = $this->daily->get('Data');
            if (isset($data[$type][$name])) {
                $data[$type][$name]++;
                $this->daily->set('Data', $data);
                $this->daily->save();
            } else {
                $data[$type][$name] = 1;
            }
            $this->daily->set('Data', $data);
            $this->daily->save();
        } else {
            //新建数据
            $this->player->set($name, [
                'Count' => [
                    'Diamond' => 0,
                    'Gold' => 0,
                    'Emerald' => 0,
                ],
                'BanTime' => 0,
                'LastTime' => 0
            ]);
            $this->player->save();
        }

    }

    public function checkData($event, $type = null)
    {

        $player = $event->getPlayer();
        //$player->sendMessage("§etype $type");
        $name = $player->getName();
        //检测日期清理数据
        if ($this->daily->get('Date') != date("Y-m-j")) {
            $this->daily->remove('Data');
            $this->daily->save();
            $this->daily->set('Date',date("Y-m-j"));
            $this->daily->save();
        }
        //新建玩家数据
        if ($this->player->get($name) === false) {
            $this->setData($name);
        }
        //矿物警戒值
        if ($type !== null) {
            $mineral_max = $this->conf['CheckCount'][$type];
        }
        //惩罚的时间
        $punish_time = $this->conf['PunishTime'] * 3600;
        $data = $this->player->get($name);
        //检测是否达到解除惩罚的条件
        if (($data['LastTime'] + $punish_time) < time() && $data['LastTime'] != 0) {
            $data['Count']['Diamond'] = 0;
            $data['Count']['Emerald'] = 0;
            $data['Count']['Gold'] = 0;
            $data['LastTime'] = 0;
            $this->player->set($name, $data);
            $this->player->save();
            //$player->sendMessage("解除惩罚");
        }
        //检测是否在被惩罚并处理
        if ($data['BanTime'] > time()) {
            //惩罚状
            switch ($this->conf['OverPlan']) {
                case"1"://限制挖矿
                    $freetime = (int)(($data['BanTime'] - time()) / 60);
                    $player->sendMessage("§6[MineralControl] §e你挖的太快，暂时不能挖掘矿石了");
                    $player->sendMessage("§e*-> 距离结束冷却还有 {$freetime} 分钟");
                    $event->setCancelled();
                    break;
                case"2"://丢进监狱
                    kill::setData($name, $punish_time);
                    break;
                case"3"://封号
                    break;
            }
        } else {
        //$player->sendMessage("§e正常状态");
            if ($type !== null) {
                //正常状态
                //$player->sendMessage('§a记录信息成功');
                $this->setData($name, $type);
                //记录数据
                if ($data['Count'][$type] >= $mineral_max && $this->conf['OverPlan'] != 0) {
                    $data['BanTime'] = time() + $punish_time;
                    $data['LastTime'] = time();
                    $this->player->set($name, $data);
                    $this->player->save();
                }
            }
        }
        unset ($player, $name);
    }

}