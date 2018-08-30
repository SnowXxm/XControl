<?php

namespace XControl\XKill;

use pocketmine\Player;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

class KillEvent implements Listener
{

    public function __construct(Kill $plugin)
    {
        $this->plugin = $plugin;
        $this->main = $plugin->plugin;

        $this->conf = $this->plugin->conf;
        $this->player = $this->plugin->player;
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $return = $this->plugin->checkData($name);
        if ($return === false) {
            //在监狱的处罚
            $pos = explode('|', $this->conf['PrisonSet']['PrisonPos']);
            $player->teleport(new Position($pos[0], $pos[1], $pos[2], $this->main->getServer()->getLevelByName($pos[3])));
            $data = $this->player->get($name);
            $freetime = (int)(($data['BanTime'] - time()) / 3600);
            $player->sendMessage("§b[XPrison] §7->§e距离释放还有 §d{$freetime} §e小时");
        }
        unset ($pos, $data, $freetime);
    }


    public function getCase($player)
    {
        $lastDamage = $player->getLastDamageCause();
        if ($lastDamage instanceof EntityDamageByEntityEvent) {
            if (!in_array($player->getlevel()->getFolderName(), $this->conf["CheckWorld"])) {
                return false;
            }
            $killer = $lastDamage->getDamager();
            if ($killer->isOp()) {
                return false;
            }
            return $killer;
        } else {
            return false;
        }
    }

    public function onDeath(PlayerDeathEvent $event)
    {
        /*
        dp = 死者
        kp = 杀手
        */
        $dp = $event->getEntity();
        $kp = $this->getCase($dp);
        if (!$kp === false && $kp instanceof Player) {
            $kpn = $kp->getName();
            $this->plugin->setData($kpn);
            $player->sendMessage("§c[XKill] §e杀人信息已经记录，达到限制将会受到惩罚");
        }
    }

    public function onBreak(BlockBreakEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $result = $this->plugin->checkData($name);
        if ($result === false) {
            $event->setCancelled();
            $player->sendTip("§c[XPrison] §e什么？你竟然想拆监狱？");
        }
    }

    public function onPlace(BlockPlaceEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $result = $this->plugin->checkData($name);
        if ($result === false) {
            $event->setCancelled();
            $player->sendTip("§c[XPrison] §e哎哎哎，老实点~");
        }
    }

    public function onOpenPack(InventoryOpenEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $result = $this->plugin->checkData($name);
        if ($result === false) {
            $event->setCancelled();
            $player->sendTip("§c[XPrison] §e打开背包干嘛！");
        }
    }

    public function onFight(EntityDamageEvent $event)
    {
        if ($event instanceof EntityDamageByEntity) {
            $player = $event->getDamager();
            $name = $kp->getName();
            $result = $this->plugin->checkData($name);
            if ($result === false) {
                $event->setCancelled();
                $player->sendTip("§c[XPrison] §e监狱里你还想搞事情？");
            }
        }
    }

    public function onCmd(PlayerCommandPreprocessEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $result = $this->plugin->checkData($name);
        if ($result === false) {
            $event->setCancelled();
            $player->sendTip("§c[XPrison] §e想输入指令逃跑吗？");
        }
    }

    public function onMove(PlayerMoveEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $x = (int)$player->getX();
        $z = (int)$player->getZ();
        $result = $this->plugin->checkData($name);
        if ($result === false) {
            $pos = explode("|", $this->conf['PrisonSet']['PrisonRound']);
            $inaround = $this->inAround($x, $z, $pos[0], $pos[1], $pos[2], $pos[3]);
            if ($inaround === false) {
                $event->setCancelled();
                unset ($pos);
                $pos = explode('|', $this->conf['PrisonSet']['PrisonPos']);
                $player->teleport(new Position($pos[0], $pos[1], $pos[2], $this->main->getServer()->getLevelByName($pos[3])));
                $player->sendTip("§c[XPrison] §e打算去哪里啊？");
            }
        }
    }

    //判断是否在监狱范围
    public function inAround($x, $z, $x1, $z1, $x2, $z2)
    {
        if ($z1 > $z2) {
            $pz1 = $z2;
            $pz2 = $z1;
        } else {
            $pz2 = $z2;
            $pz1 = $z1;
        }
        if ($x1 > $x2) {
            $px1 = $x2;
            $px2 = $x1;
        } else {
            $px2 = $x2;
            $px1 = $x1;
        }
        if ($x > $px1 and $x < $px2 and $z > $pz1 and $z < $pz2) {
            return true;
        } else {
            return false;
        }
    }

}