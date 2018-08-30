<?php

namespace XControl\XMineral;

use pocketmine\Player;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

use XControl\Main;
use XControl\XMineral\Mineral;

class MineralEvent implements Listener
{

    public function __construct(Mineral $plugin)
    {
        $this->plugin = $plugin;

        $this->conf = $this->plugin->conf;
        $this->player = $this->plugin->player;
        $this->daily = $this->plugin->daily;
    }

    public function onBreak(BlockBreakEvent $event)
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $block = $event->getBlock();
        /**
         * 快速检查玩家信息
         **/
        //$player->sendMessage('§a准备快速检查');
        if ($this->plugin->checkPermission($player, $block)) {
            $this->plugin->checkData($event);
            //$player->sendMessage('§a第一次检查完成');
            if (!$event->isCancelled()) {
                //$player->sendMessage('§a传递处理信息成功');
                switch ($block->getId()) {
                    case"56"://钻石
                        //$player->sendMessage("§e传递钻石");
                        $this->plugin->checkData($event, 'Diamond');
                        break;
                    case"129"://绿宝石
                        $this->plugin->checkData($event, 'Emerald');
                        break;
                    case"15"://金矿
                        $this->plugin->checkData($event, 'Gold');
                        break;

                }

            }
        }
    }


}