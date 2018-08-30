<?php

namespace XControl\XUser;

use pocketmine\Player;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

class UserEvent implements Listener
{

    /*
    1 未输入名字
    2 未确认密码
    3 没有登录
    4 已经登陆
    */
    public function __construct(User $plugin)
    {
        $this->plugin = $plugin;
        $this->main = $plugin->plugin;
        $this->msg = $plugin->msg;
        $this->conf = $plugin->conf;

    }

    public function onPreLogin(PlayerPreLoginEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $checkban = $this->plugin->checkBaned($player);
        if (!$checkban === true){
            $freetime = (int)($checkban['bantime'] - time());
            $event->setKickMessage("§c距离解封还有 §a{$freetime} §c天 §b原因：{$checkban['baninfo']}");
            $event->setCancelled();
        }
        if (count($name) > $this->conf['LengthLimit']) {
            $event->setKickMessage($this->msg['PreLogin-NameLimit-false']);
            $event->setCancelled();
        }
        if ($this->main->getServer()->getPlayer($name)) {
            $event->setKickMessage($this->msg['PreLogin-IsOnline-false']);
            $event->setCancelled();
        }
        if ($this->conf['BanAlt'] === true) {
            if ($this->plugin->checkAlt($player) === false){
            $event->setKickMessage($this->msg['PreLogin-BanAlt-false']);
            $event->setCancelled();
         }
        }
        
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $this->plugin->checkPlayer($player);
        $this->plugin->checkPlayer($player, 'checkStatus');
        if ($this->conf['AutoToSpawn'] == true){
            $player->teleport($this->main->getServer()->getDefaultLevel()->getSafeSpawn());
        }
    }

    public function onCmd(PlayerCommandPreprocessEvent $event)
    {
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        $msg = $event->getMessage();
        if ($this->plugin->status[$name] != 4) {
            //判断输入的内容
            switch ($this->plugin->status[$name]) {
                case"1"://未输入名字
                    $event->setCancelled();
                    if ($msg != $player->getName()) {
                        $player->sendMessage($this->msg['Register-Name-false']);
                        break;
                    } else {
                        $this->plugin->status[$name] = 2;
                        $player->sendMessage($this->msg['Register-Name-true']);
                        break;
                    }
                    break;

                case"2"://未确定密码
                    $event->setCancelled();
                    $name = strtolower($name);
                    if (!isset($this->plugin->status_password[$name]) || $this->plugin->status_password[$name] == null) {
                        $this->plugin->status_password[$name] = $msg;
                        $player->sendMessage($this->msg['Register-Password'].$this->plugin->status_password[$name]);
                        $player->sendMessage($this->msg['Register-RePassword']);
                        break;
                    }
                    if ($msg != $this->plugin->status_password[$name]) {
                        $player->sendMessage($this->msg['Register-RePassword-false']);
                        break;
                    } else {
                        $user = $this->plugin->getPlayer($name);
                        $user->set('Password', $this->plugin->status_password[$name]);
                        $user->save();
                        $player->sendMessage($this->msg['Register-Success']);
                        $this->plugin->status[$name] = 4;
                        unset ($this->plugin->status_password[$name], $user);
                        break;
                    }
                    break;

                case"3":
                    $event->setCancelled();
                    $name = strtolower($name);
                    $user = ($this->plugin->getPlayer($name))->getAll();
                    if ($msg != $user['Password']) {
                        $player->sendMessage($this->msg['Login-Password-false']);
                        break;
                    } else {
                        $this->plugin->status[$name] = 4;
                        $user['LastLogin'] = date("Y-m-j-H");
                        $user['LastIp'] = $player->getAddress();
                        $user['LastCid'] = (int)$player->getClientId();
                        $userdata = $this->plugin->getPlayer($name);
                        $userdata->setAll($user);
                        $userdata->save();
                        $player->sendMessage($this->msg['Login-End']);
                        $player->sendMessage($this->msg['Login-End-Message']);
                        if ($this->conf['LoginTitle']){
                        $title = str_replace('{name}',$player->getName(),$this->msg['Login-Title']);
                        $title = explode('%s',$title);
                        $player->sendTitle($title[0],$title[1],20,100,60);
                    }
                        break;
                    }
                    break;
            }
            $event->setCancelled();
        }
    }


    public function onInteract(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        if (!isset($this->plugin->status[$name]) || $this->plugin->status[$name] != 4) {
            $event->setCancelled();
            $player->sendTip("§a[XUser] §e请先按提示注册/登录~");
        }
    }
    
    public function onBreak(BlockBreakEvent $event){
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        if (!isset($this->plugin->status[$name]) || $this->plugin->status[$name] != 4) {
            $event->setCancelled();
            $player->sendTip("§a[XUser] §e请先按提示注册/登录~");
        }
	}
	/*
    public function onOpenPack(InventoryOpenEvent $event)
    {
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        if (!isset($this->plugin->status[$name]) || $this->plugin->status[$name] != 4) {
            $event->setCancelled();
            $player->sendTip("§a[XUser] §e请先按提示注册/登录~");
        }
    }
    */

    public function onFight(EntityDamageEvent $event)
    {
        if ($event instanceof EntityDamageByEntity) {
            $player = $event->getPlayer();
            $name = strtolower($player->getName());
            if (!isset($this->plugin->status[$name]) || $this->plugin->status[$name] != 4) {
                $event->setCancelled();
                $player->sendTip("§a[XUser] §e请先按提示注册/登录~");
            }
        }
    }


    public function onMove(PlayerMoveEvent $event)
    {
        if ($this->conf['NoLoginMove'] == true){return;}
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        if (!isset($this->plugin->status[$name]) || $this->plugin->status[$name] != 4) {
            $event->setCancelled();
            $player->sendTip("§a[XUser] §e请先按提示注册/登录~");
        }
    }

    public function onQuit(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
      /*  if ($this->plugin->status[$name] == 4) {
            $this->plugin->status[$name] = 3;
        } */
        unset ($this->plugin->status[$name]);
    }

}