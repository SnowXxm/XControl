<?php

namespace XControl\XWorld;

use XControl\Main;

use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;

use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

use onebone\economyapi\EconomyAPI;

class World implements Listener
{

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        @mkdir($this->plugin->path . "DataBase/WorldBase");
        @mkdir($this->plugin->path . "DataBase/WorldBase/Data");

        $this->conf = (new Config($this->plugin->path . "DataBase/WorldBase/Config.yml", Config::YAML, [
            'LastClear' => time(),
            'ClearWorld' => ['WorldName'],
            'ClearTime' => 5,//天
            'Admin' => []
        ]))->getAll();

        $this->msg = (new Config($this->plugin->path . "DataBase/WorldBase/Message.yml", Config::YAML, [
            'Place' => '§e本世界禁止放置方块',
            'Break' => '§e本世界禁止破坏方块',
            'BanCmd' => '§e这个指令在这个世界是不被允许的',
            'BanItem' => '§e本世界禁止使用此物品',
            'Pvp' => '§e本世界禁止PVP',
            'Pve' => '§e本世界禁止PVE',
            'Drop' => '§e由于本世界死亡掉落，你丢掉了所有物品',
        ]))->getAll();
        $this->LoadAllWorld();
        foreach ($this->plugin->getServer()->getLevels() as $level) {
            $LevelName = $level->getFolderName();
            $data = new Config($this->plugin->path . "DataBase/WorldBase/Data/$LevelName.yml", Config::YAML, [
                'WorldMode' => false,
                'WorldMessage' => false,
                'LockTime' => false,
                'MaxPlayer' => false,
                'Place' => true,
                'Break' => true,
                'Boom' => false,
                'Flow' => false,
                'UpDate' => true,
                'Fly' => true,
                'Pvp' => true,
                'Pve' => true,
                'Drop' => false,
                'BanItem' => false,
                'BanAllCommand' => false,
                'BanCommand' => [
                    '/test'
                ],
                'Admin' => []
            ]);
            if (!$data->get('LockTime') == false){
                $level->setTime($data->get('LockTime'));
                $level->stopTime();
            }
        }

        $this->plugin->getServer()->getPluginManager()->registerEvents(new WorldEvent($this), $plugin);
        $map = $this->plugin->getServer()->getCommandMap();
        $map->register("Main", new WorldCommand($this));
    }


    public function loadAllWorld()
    {
        $level = $this->plugin->getServer()->getDefaultLevel();
        $path = $level->getFolderName();
        $pathh = $this->plugin->getServer()->getDataPath() . "worlds/";
        $dirnowfile = scandir($pathh, 1);
        foreach ($dirnowfile as $dirfile) {
            if ($dirfile != '.' && $dirfile != '..' && $dirfile != $path && is_dir($pathh . $dirfile)) {
                if (!$this->plugin->getServer()->isLevelLoaded($dirfile)) {
                    $this->plugin->getLogger()->info("§d正在加载世界 ->> | $dirfile |");
                    $this->plugin->getServer()->generateLevel($dirfile);
                    $this->plugin->getServer()->loadLevel($dirfile);
                    $level = $this->plugin->getServer()->getLevelbyName($dirfile);
                }
            }
        }
        $this->plugin->getLogger()->info("§b------§a所有世界加载完成§7------");
        $this->checkClear();
    }

    public function checkClear()
    {
        if ((time() - $this->conf['LastClear']) > ($this->conf['ClearTime'] * 86400)) {
            foreach ($this->plugin->getServer()->getLevels() as $level) {
                $LevelName = $level->getFolderName();
                if (in_array($LevelName, $this->conf['ClearWorld'])) {
                    $dir = $this->plugin->getServer()->getDataPath() . "worlds/$LevelName";
                    $this->onClear($dir);
                    @mkdir($this->plugin->getServer()->getDataPath() . "worlds/$LevelName/");
                    $this->plugin->getServer()->unloadLevel($this->plugin->getServer()->getLevelbyName($LevelName));
                    $this->plugin->getServer()->generateLevel($LevelName);
                    $this->plugin->getServer()->loadLevel($LevelName);
                }
                $conf = new Config($this->plugin->path . "DataBase/WorldBase/Config.yml", Config::YAML, []);
                $conf->set('LastClear', time());
                $conf->save();
            }
            $this->plugin->getLogger()->info("§b////指定世界已经刷新////");
        }
    }

    private function onClear($dir)
    {
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir . "/" . $file;
                if (!is_dir($fullpath)) {
                    @unlink($fullpath);
                } else {
                    $this->onClear($fullpath);
                }
            }
        }
        closedir($dh);
        if (@rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }

    public function checkPlayer($player, $type = null, $info = null)
    {
        $name = $player->getName();
        $level = $player->getLevel()->getFolderName();
        $data = (new Config($this->plugin->path . "DataBase/WorldBase/Data/$level.yml", Config::YAML, []))->getAll();
        if (in_array($name, $this->conf['Admin']) || in_array($name, $data['Admin'])) {
            return true;
        }
        if (!$type == null) {
            switch ($type) {
                case'Place':
                    if ($data['Place'] == false) {
                        return false;
                    }
                    break;

                case'Break':
                    if ($data['Break'] == false) {
                        return false;
                    }
                    break;

                case'Pvp':
                    if ($data['Pvp'] == false) {
                        return false;
                    }
                    break;

                case'Pve':
                    if ($data['Pve'] == false) {
                        return false;
                    }
                    break;

                case'Fly':
                    if ($data['Fly'] == false) {
                        return false;
                    }
                    break;

                case'Flow':
                    if ($data['Flow'] == false) {
                        return false;
                    }
                    break;

                case'Drop':
                    if ($data['Drop'] == false) {
                        return true;
                    }else{
                        return false;
                    }
                    break;

                case'BanItem':
                    if (!$data['BanItem'] == false) {
                        if (in_array($info, explode(',', $data['BanItem'])))
                            return false;
                    }
                    break;

                case'Cmd':
                    if ($data['BanAllCommand'] == true && ($info != '/w' && $info != '/wl')) {
                        return false;
                    } else {
                    $info = strtolower($info);
                        $info = explode(' ', $info);
                      
                        if(in_array($info[0], $data['BanCommand']))
                        {
                            return false;
                        }
                    }
                    break;
                //状态检查
                case'Status':

                    switch ($info) {
                        case'All':
                            if ($data['Fly'] == false) {
                                if ($player->getAllowFlight() == true) {
                                    $player->setAllowFlight(false);
                                }
                                if (!$data['WorldMode'] == false) {
                                    if (!$player->getGamemode() == $data['WorldMode']) {
                                        $player->setGamemode($data['WorldMode']);
                                        if($player->getGamemode() == 0){
                              $player->getInventory()->clearAll();          
                    }
                                    }
                                }

                                break;
                            }
                        case'Clear':
                            $this->checkClear();
                            break;
                    }
            }
        }
    }

}