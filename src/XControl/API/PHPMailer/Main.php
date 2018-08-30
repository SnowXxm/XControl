<?php

namespace MyBanAlt;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\Config;

use pocketmine\event\player\PlayerPreLoginEvent;


class Main extends PluginBase implements Listener{

	public function onEnable(){
	 $this->getInfo();
	$this->getServer()->getPluginManager()->registerEvents($this, $this);
		@mkdir($this->getDataFolder());
		$this->players = (new Config($this->getDataFolder()."player.yml",Config::YAML,array()))->getAll();
		$this->player = new Config($this->getDataFolder()."player.yml",Config::YAML,array());
		$this->conf = new Config($this->getDataFolder()."config.yml",Config::YAML,array(
		"Max" => 2,
		"Admin" => array("你的名字"),
		"MadeBy" => "SnowXxm"
		)); 
		}
	
	public function onLogin(PlayerPreLoginEvent $event){
  $player = $event->getPlayer();
  $name = $player->getName();
  $cid = (int)$player->getClientId();
  $ip = $player->getAddress();
  $cont_max = $this->conf->get("Max");
 
  if(in_array($name,$this->conf->get("Admin"))){return true;}
 
   foreach($this->players as $name => $data){
   static $cont = 0;
   if($data["Ip"] == $ip){
    $cont++;
    $owner = $name;
   }
   if($data["Cid"] == $cid && isset($owner) && !$owner == $name){
    $cont++;
   }
   if($cont > $cont_max){
   $event->setKickMessage("§c§l[MyBanAlt]§e你的小号太多了，本服只允许 §b{$cont_max} §e个小号");
				$event->setCancelled(true);
				unset ($cont,$owner,$ip,$cid);
				return;
   }
   }
   if(!isset($this->players[$name])){
   $info = [
   "Ip" => $ip,
   "Cid" => $cid,
   ];
   $this->player->set($name,$info);
   $this->player->save();
   }
 }
 /*
 public function save(){
 $cfg = new Config($this->getDataFolder()."player.yml",Config::YAML,array());
 $cfg->setAll($this->conf);
 $cfg->save();
 $this->player = (new Config($this->getDataFolder()."player.yml",Config::YAML,array()))->getAll();
 unset($cfg);
}
*/

 private function getInfo(){
	  $this->getLogger()->info("§e[MyBanAlt] §6-> §a完成加载!");
   $this->getLogger()->info("§b作者 §9SnowXxm(qq16769334) §c倒卖必究！");
	}
 
}