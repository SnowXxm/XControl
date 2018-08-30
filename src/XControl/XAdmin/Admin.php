<?php

namespace XControl\XAdmin;

use XControl\Main;

use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;

use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

use onebone\economyapi\EconomyAPI;

class Admin implements Listener
{

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        @mkdir($this->plugin->path . "DataBase/AdminBase");
        @mkdir($this->plugin->path . "DataBase/AdminBase/Data");

       $this->conf = (new Config($this->plugin->path . "DataBase/AdminBase/Config.yml", Config::YAML, [
       'LastClear' => 0,
       'ClearAdmin' => ['AdminName'],
       'ClearTime' => 5,//天
       'Admin' =>[]
        ]))->getAll();
        
        $this->msg = (new Config($this->plugin->path . "DataBase/AdminBase/Message.yml", Config::YAML, [
      'Place' => '§e本世界禁止放置方块',
      'Break' => '§e本世界禁止破坏方块',
      'BanCmd' => '§e这个指令在这个世界是不被允许的',
      'BanItem' => '§e本世界禁止使用此物品',
      'Pvp' => '§e本世界禁止PVP',
      'Pve' => '§e本世界禁止PVE',
      'Drop' => '§e由于本世界死亡掉落，你丢掉了所有物品',
        ]))->getAll();
        $this->LoadAllAdmin();
        foreach($this->plugin->getServer()->getLevels() as $level){
        $LevelName = $level->getFolderName();
        new Config($this->plugin->path . "DataBase/AdminBase/Data/$LevelName.yml", Config::YAML, [
      "AdminMode"=>false,
      "AdminMessage"=>false,
      "LockTime"=>false,
      "MaxPlayer"=>false,
      "Place"=>true,
      "Break"=>true,
      "Boom"=>false,
      "Flow"=>false,
      "UpDate"=>true,
      "Fly"=>true,
      "Pvp"=>true,
      "Pve"=>true,
      "Drop"=>false,
      "BanItem"=>false,
      "BanAllCommand"=>false,
      "BanCommand"=>[
      '/test'
      ],
      "Admin"=>[]
        ]);
        }
        
        $this->plugin->getServer()->getPluginManager()->registerEvents(new AdminEvent($this), $plugin);
        $map = $this->plugin->getServer()->getCommandMap();
        $map->register("Main", new AdminCommand($this));
    }
 
 
	public function loadAllAdmin() {
    $level = $this->plugin->getServer()->getDefaultLevel();
    $path = $level->getFolderName();
    $pathh = $this->plugin->getServer()->getDataPath() . "Admins/";
   		$dirnowfile = scandir($pathh, 1);
   		foreach ($dirnowfile as $dirfile){
	    	if($dirfile != '.' && $dirfile != '..' && $dirfile != $path && is_dir($pathh.$dirfile)) {
				if (!$this->plugin->getServer()->isLevelLoaded($dirfile)) {  
					$this->plugin->getLogger()->info( "§d正在加载世界 ->> | $dirfile |");
					$this->plugin->getServer()->generateLevel($dirfile);
					$this->plugin->getServer()->loadLevel($dirfile);
					$level = $this->plugin->getServer()->getLevelbyName($dirfile);
					}
	}
	}
 $this->plugin->getLogger()->info( "§b------§a所有世界加载完成§7------");
 $this->checkClear();
 }
 
 public function checkClear(){
 if((time()-$this->conf['LastClear']) > ($this->conf['ClearTime']*86400)){
 foreach($this->plugin->getServer()->getLevels() as $level){
 $LevelName = $level->getFolderName();
 if(in_array($LevelName,$this->conf['ClearAdmin'])){
 $dir = $this->plugin->getServer()->getDataPath(). "Admins/$LevelName";
 $this->onClear($dir);
 @mkdir($this->plugin->getServer()->getDataPath()."Admins/$LevelName/");
 $this->plugin->getServer()->unloadLevel($this->plugin->getServer()->getLevelbyName($LevelName));
 $this->plugin->getServer()->generateLevel($LevelName);
 $this->plugin->getServer()->loadLevel($LevelName);
 }
 $conf = new Config($this->plugin->path . "DataBase/AdminBase/Config.yml", Config::YAML, []);
 $conf->set('LastClear',time());
 $conf->save();
 }
 $this->plugin->getLogger()->info( "§b////指定世界已经刷新////");
 }
 }
 
 private function onClear($dir){
 $dh = opendir($dir);
 while ($file=readdir($dh)) {
 if($file!="." && $file!="..") {
 $fullpath = $dir."/".$file;
 if(!is_dir($fullpath)){
 @unlink($fullpath);
 }else{
 $this->onClear($fullpath);
 }
 }
 }
 closedir($dh);
 if(@rmdir($dir)) {
 return true;
 } else {
 return false;
 }
 }
	
	public function checkPlayer($player,$type = null,$info = null){
	$name  = $player->getName();
	$level = $player->getLevel()->getFolderName();
	$data = (new Config($this->plugin->path . "DataBase/AdminBase/Data/$level.yml", Config::YAML, []))->getAll();
	if(in_array($name,$this->conf['Admin']) || in_array($name,$data['Admin'])){
	return true;
	}
	if(!$type == null){
	switch($type){
	case'Place':
	if($data['Place'] == false){
	return false;
	}
	break;
	
	case'Break':
	if($data['Break'] == false){
	return false;
	}
	break;
	
	case'Pvp':
	if($data['Pvp'] == false){
	return false;
	}
	break;
	
	case'Pve':
	if($data['Pve'] == false){
	return false;
	}
	break;
	
	case'Fly':
	if($data['Fly'] == false){
	return false;
	}
	break;
	
	case'Flow':
	if($data['Flow'] == false){
	return false;
	}
	break;
	
	case'Drop':
	if($data['Drop'] == false){
	return false;
	}
	break;
	
	case'BanItem':
	if(!$data['BanItem'] == false){
	if(in_array($info,explode(',',$data['BanItem'])))
	return false;
	}
	break;
	
	case'Cmd':
	if($data['BanAllCommand'] == true || in_array(strtolower($info),$data['BanCommand'])){
	return false;
	}
	break;
	//状态检查
	case'Status':
	
	switch($info){
	case'All':
	if($data['Fly'] == false){
	if($player->getAllowFlight()==true){
   $player->setAllowFlight(false);
   }
	if(!$data['AdminMode'] == false){
	if($player->getGamemode() != $data['AdminMode']){
   $player->setGamemode($data['AdminMode']);
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