<?php

namespace XControl;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\level\Level;

use XControl\XMineral\Mineral;
use XControl\XKill\Kill;
use XControl\XUser\User;
use XControl\XWorld\World;

use XControl\Commands\ChangeWorldCommand;
use XControl\Commands\AllWorldCommand;
use XControl\Commands\XCHelpCommand;

class Main extends PluginBase
{
	public function onLoad()
	{
	$this->path = $this->getDataFolder();
	@mkdir($this->path);
	@mkdir($this->path."DataBase");
	}
	public function onEnable(){
  $this->checkData();
  $this->checkUpdata();
  $this->registerEvents();
  $this->registerCommands();
  $this->sendInfo();
  
 
 
 }
	
	private function registerEvents()
	{
	//注册事件
	if($this->conf['Mods']['XMineral'] === true){
		$this->mineralClass=new Mineral($this);
		$this->getLogger()->info("§e* §7->> §b[矿物监控模块] §a已加载");
		}
		if($this->conf['Mods']['XKill'] === true){
		$this->killClass=new Kill($this);
		$this->getLogger()->info("§e* §7->> §b[杀人控制模块] §a已加载");
		}
		if($this->conf['Mods']['XUser'] === true){
		$this->userClass=new User($this);
		$this->getLogger()->info("§e* §7->> §b[用户管理模块] §a已加载");
		}
		if($this->conf['Mods']['XWorld'] === true){
		$this->worldClass=new World($this);
		$this->getLogger()->info("§e* §7->> §b[世界管理模块] §a已加载");
		}
        /*
		if($this->conf['Mods']['XAdmin'] === true){
            $this->worldClass=new World($this);
            $this->getLogger()->info("§e* §7->> §b[权限节点模块] §a已加载");
        }
        */
	}
	
	private function registerCommands(){
		$map = $this->getServer()->getCommandMap();

		$commands = [
			"w" => "\\XControl\\Commands\\ChangeWorldCommand",
			"wl" => "\\XControl\\Commands\\AllWorldCommand",
            "xc" => "\\XControl\\Commands\\XCHelpCommand",
		];
		foreach($commands as $cmd => $class){
			$map->register("xcontrol", new $class($this));
		}
		$this->getLogger()->info("§b------§a全部指令注册完毕§b------");
	}
	
	
	private function checkData(){
 $this->conf = (new Config($this->path."Config.yml",Config::YAML,[
 'Mods' => [
 'XMineral' => true,
 'XKill' => true,
 'XWorld' => true,
 'XUser' => true,
 'XAdmin' => true
 ],
 'Version' => '1.7.0',
 ]))->getAll();
 }
	
	private function sendInfo(){
 $this->getLogger()->info("§b ==================");
 $this->getLogger()->info("§a XControl 完成加载!");
 $this->getLogger()->info("§e 作者 §bSnowXxm");
 $this->getLogger()->info("§c 真白么么哒~");
 $this->getLogger()->info("§6 如果有任何Bug请及时加群554719217提交，感谢您的使用~");
   $this->getLogger()->info("§b ==================");
 }
	
	
	private function checkUpdata()
    {
    $NewVersion = '1.7.0';
    $conf = new Config($this->path."Config.yml",Config::YAML,[]);
        $version = $conf->get("Version");
        if ($version !== $NewVersion) {
            $conf->set("Version", $NewVersion);
            $conf->save();
            $this->getLogger()->info("§b【XControl】" . "§e［更新检测］检测到您配置文件为旧版，已经自动为您升级配置文件");
        }
        unset ($conf);
    }
	
}
	
