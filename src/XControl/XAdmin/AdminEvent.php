<?php

namespace XControl\XWorld;

use pocketmine\Player;
use pocketmine\utils\Config;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;

use pocketmine\entity\PrimedTNT;

use pocketmine\block\Block;
use pocketmine\block\Lava;
use pocketmine\block\Water;

use pocketmine\level\Position;        
use pocketmine\level\Level;
use pocketmine\math\Vector3;

class WorldEvent implements Listener
{

	public function __construct(World $plugin)
	{
		$this->plugin=$plugin;
		$this->main=$plugin->plugin;
		$this->msg = $plugin->msg;
		$this->conf = $this->plugin->conf;
	
	}
 
	
	public function onCmd(PlayerCommandPreprocessEvent $event){
		$player = $event->getPlayer();
		$msg = $event->getMessage();
		$check = $this->plugin->checkPlayer($player,'Cmd',$msg);
		if($check === false){
		$event->setCancelled();
		$player->sendMessage('§e[XWorld] '.$this->msg['BanCmd']);
		}
	}
	
	public function onInteract(PlayerInteractEvent $event){
	$player = $event->getPlayer();
	$action = $event->getAction();
	
	$check = $this->plugin->checkPlayer($player,'Place');
	if($check === false){
		$event->setCancelled();
		return;
	}
	
	if($action == 0){
	$check = $this->plugin->checkPlayer($player,'Break');
	if($check === false){
		$event->setCancelled();
		$player->sendMessage('§e[XWorld] '.$this->msg['Break']);
	}elseif($action == 1){
	$check = $this->plugin->checkPlayer($player,'Place');
	if($check === false){
		$event->setCancelled();
		$player->sendMessage('§e[XWorld] '.$this->msg['Place']);
	}
	}
	}
	
	}
	
	
	public function onPlace(BlockPlaceEvent $event){
	$player = $event->getPlayer();
	$check = $this->plugin->checkPlayer($player,'Place');
	if($check === false){
		$event->setCancelled();
		$player->sendMessage('§e[XWorld] '.$this->msg['Place']);
	}
	
	}
	
	public function onBreak(BlockBreakEvent $event){
	$player = $event->getPlayer();
	$check = $this->plugin->checkPlayer($player,'Break');
	if($check === false){
		$event->setCancelled();
		$player->sendMessage('§e[XWorld] '.$this->msg['Break']);
	}
	
	}
	
	public function onHeld(PlayerItemHeldEvent $event){
   $player = $event->getPlayer();
   $check = $this->plugin->checkPlayer($player,'BanItem',"{$event->getItem()->getId()}:{$event->getItem()->getDamage()}");
	if($check === false){
		$event->setCancelled();
		$player->sendMessage('§e[XWorld] '.$this->msg['BanItem']);
	}
   }
	
	public function onEntityDamage(EntityDamageEvent $event){
  $entity = $event->getEntity();
  if($event instanceof EntityDamageByEntityEvent){
  $player = $event->getDamager();
  if($player instanceof Player){
  $check = $this->plugin->checkPlayer($player,'Pvp');
	if($check === false){
		$event->setCancelled();
		$player->sendMessage('§e[XWorld] '.$this->msg['Pvp']);
  }
  }else{
  $check = $this->plugin->checkPlayer($player,'Pve');
	if($check === false){
		$event->setCancelled();
		$player->sendMessage('§e[XWorld] '.$this->msg['Pve']);
  }
     
   }
   
  }
	}
		
	public function onPlayerDeath(PlayerDeathEvent $event){
	$player = $event->getPlayer();
	$check = $this->plugin->checkPlayer($player,'Drop');
	if($check === false){
		$event->setKeepInventory(true);
	}else{
	$player->getInventory()->clearAll();
  $event->setKeepInventory(false);
	$player->sendMessage('§e[XWorld] '.$this->msg['Drop']);
	}

	}
	
	public function onExplosionPrime(ExplosionPrimeEvent $event){
	if($data['Boom'] == false){
	$event->setCancelled();
	$player->sendMessage('§e[XWorld] '.$this->msg['Boom']);
	}
	}
	
	public function onBlockUpdate(BlockUpdateEvent $event){
  $block = $event->getBlock();
  $level = $block->getLevel()->getFolderName();
  
  $data = (new Config($this->main->path . "DataBase/WorldBase/Data/$level.yml", Config::YAML, []))->getAll();
  if($block instanceof Water || $block instanceof Lava){
	if($data['Flow'] == false){
		$event->setCancelled();
  }
  }else{
	if($data['UpDate'] == false){
		$event->setCancelled();
  }
  }
  
  }
	
	
	public function onPlayerMove(PlayerMoveEvent $event){
	$player = $event->getPlayer();
	$this->plugin->checkPlayer($player,'Status','All');
	}
	
}