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
    ZXDA::init(733,$this);
    ZXDA::requestCheck();
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
  
  ZXDA::tokenCheck('MTE3Mjc5OTE5MzM4MzI5OTE0ODgzMTg2MjAzOTc5MDM0NDY0ODA3MDA5MDA3MTk4NDcxNzUyMDQ5MDg3NDk0NzcxNzgwNjM3NzMzNTkxMjY3NzYwMDczNTA0NzQ5MjY1NDQzMTcxNTMzNjI5NjIxOTY2MTQ2MzEwMDAxMjczOTA0MzUxMDc1MDA1NDM4NzA0MTY2MTA0MTg1NjI3OTc0MjYxOTA2ODM3MTI5MjMzNjAzNDk4Njk5Mzc2MzM2ODgxMzQ2Mjk2MDU3Njg0NTEwODU3MzAyMDE4ODE4ODc4Njg5OTMzNTE4MQ==');
		$data=ZXDA::getInfo();
		if($data['success'])
		{
			if(version_compare($data['version'],$this->getDescription()->getVersion())>0)
			{
				$this->getLogger()->info(TextFormat::GREEN.'检测到新版本,最新版:'.$data['version'].",更新日志:\n    ".str_replace("\n","\n    ",$data['update_info']));
			}
		}
		else
		{
			$this->getLogger()->warning('更新检查失败:'.$data['message']);
		}
		if(ZXDA::isTrialVersion())
		{
			$this->getLogger()->warning('当前正在使用试用版授权,试用时间到后将强制关闭服务器');
		}
		//继续加载插件
 
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
	
class ZXDA
{
	private static $_PID=false;
	private static $_TOKEN=false;
	private static $_PLUGIN=null;
	private static $_VERIFIED=false;
	private static $_API_VERSION=5012;
	
	public static function init($pid,$plugin)
	{
		if(!is_numeric($pid))
		{
			self::killit('参数错误,请传入正确的PID(0001)');
			exit();
		}
		self::$_PLUGIN=$plugin;
		if(self::$_PID!==false && self::$_PID!=$pid)
		{
			self::killit('非法访问(0002)');
			exit();
		}
		self::$_PID=$pid;
	}
	
	public static function checkKernelVersion()
	{
		if(self::$_PID===false)
		{
			self::killit('SDK尚未初始化(0003)');
			exit();
		}
		if(!class_exists('\\ZXDAKernel\\Main',true))
		{
			self::killit('请到 https://pl.zxda.net/ 下载安装最新版ZXDA Kernel后再使用此插件(0004)');
			exit();
		}
		$version=\ZXDAKernel\Main::getVersion();
		if($version<self::$_API_VERSION)
		{
			self::killit('当前ZXDA Kernel版本太旧,无法使用此插件,请到 https://pl.zxda.net/ 下载安装最新版后再使用此插件(0005)');
			exit();
		}
		return $version;
	}
	
	public static function isTrialVersion()
	{
		try
		{
			self::checkKernelVersion();
			return \ZXDAKernel\Main::isTrialVersion(self::$_PID);
		}
		catch(\Exception $err)
		{
			@file_put_contents(self::$_PLUGIN->getServer()->getDataPath().'0007_data.dump',var_export($err,true));
			self::killit('未知错误(0007),错误数据已保存到 0007_data.dump 中,请提交到群内获取帮助');
		}
	}
	
	public static function requestCheck()
	{
		try
		{
			self::checkKernelVersion();
			self::$_VERIFIED=false;
			self::$_TOKEN=sha1(uniqid());
			if(!\ZXDAKernel\Main::requestAuthorization(self::$_PID,self::$_PLUGIN,self::$_TOKEN))
			{
				self::killit('请求授权失败,请检查PID是否已正确传入(0006)');
				exit();
			}
		}
		catch(\Exception $err)
		{
			@file_put_contents(self::$_PLUGIN->getServer()->getDataPath().'0007_data.dump',var_export($err,true));
			self::killit('未知错误(0007),错误数据已保存到 0007_data.dump 中,请提交到群内获取帮助');
		}
	}
	
	public static function tokenCheck($key)
	{
		try
		{
			self::checkKernelVersion();
			self::$_VERIFIED=false;
			$manager=self::$_PLUGIN->getServer()->getPluginManager();
			if(!($plugin=$manager->getPlugin('ZXDAKernel')) instanceof \ZXDAKernel\Main)
			{
				self::killit('ZXDA Kernel加载失败,请检查插件是否已正常安装(0008)');
			}
			if(!$manager->isPluginEnabled($plugin))
			{
				$manager->enablePlugin($plugin);
			}
			$key=base64_decode($key);
			if(($token=\ZXDAKernel\Main::getResultToken(self::$_PID))===false)
			{
				self::killit('请勿进行非法破解(0009)');
			}
			if(self::rsa_decode(base64_decode($token),$key,768)!=sha1(strrev(self::$_TOKEN)))
			{
				self::killit('插件Key错误,请更新插件或联系作者(0010)');
			}
			self::$_VERIFIED=true;
		}
		catch(\Exception $err)
		{
			@file_put_contents(self::$_PLUGIN->getServer()->getDataPath().'0007_data.dump',var_export($err,true));
			self::killit('未知错误(0007),错误数据已保存到 0007_data.dump 中,请提交到群内获取帮助');
		}
	}
	
	public static function isVerified()
	{
		return self::$_VERIFIED;
	}
	
	public static function getInfo()
	{
		try
		{
			self::checkKernelVersion();
			$manager=self::$_PLUGIN->getServer()->getPluginManager();
			if(!($plugin=$manager->getPlugin('ZXDAKernel')) instanceof \ZXDAKernel\Main)
			{
				self::killit('ZXDA Kernel加载失败,请检查插件是否已正常安装(0008)');
			}
			if(($data=\ZXDAKernel\Main::getPluginInfo(self::$_PID))===false)
			{
				self::killit('请勿进行非法破解(0009)');
			}
			if(count($data=explode(',',$data))!=2)
			{
				return array(
					'success'=>false,
					'message'=>'未知错误');
			}
			return array(
				'success'=>true,
				'version'=>base64_decode($data[0]),
				'update_info'=>base64_decode($data[1]));
		}
		catch(\Exception $err)
		{
			@file_put_contents(self::$_PLUGIN->getServer()->getDataPath().'0007_data.dump',var_export($err,true));
			self::killit('未知错误(0007),错误数据已保存到 0007_data.dump 中,请提交到群内获取帮助');
		}
	}
	
	public static function killit($msg)
	{
		if(self::$_PLUGIN===null)
		{
			echo('抱歉,插件授权验证失败[SDK:'.self::$_API_VERSION."]\n附加信息:".$msg);
		}
		else
		{
			@self::$_PLUGIN->getLogger()->warning('§e抱歉,插件授权验证失败[SDK:'.self::$_API_VERSION.']');
			@self::$_PLUGIN->getLogger()->warning('§e附加信息:'.$msg);
			@self::$_PLUGIN->getServer()->forceShutdown();
		}
		exit();
	}
	
	//RSA加密算法实现
	public static function rsa_encode($message,$modulus,$keylength=1024,$isPriv=true){$result=array();while(strlen($msg=substr($message,0,$keylength/8-5))>0){$message=substr($message,strlen($msg));$result[]=self::number_to_binary(self::pow_mod(self::binary_to_number(self::add_PKCS1_padding($msg,$isPriv,$keylength/8)),'65537',$modulus),$keylength/8);unset($msg);}return implode('***&&&***',$result);}
	public static function rsa_decode($message,$modulus,$keylength=1024){$result=array();foreach(explode('***&&&***',$message) as $message){$result[]=self::remove_PKCS1_padding(self::number_to_binary(self::pow_mod(self::binary_to_number($message),'65537',$modulus),$keylength/8),$keylength/8);unset($message);}return implode('',$result);}
	private static function pow_mod($p,$q,$r){$factors=array();$div=$q;$power_of_two=0;while(bccomp($div,'0')==1){$rem=bcmod($div,2);$div=bcdiv($div,2);if($rem){array_push($factors,$power_of_two);}$power_of_two++;}$partial_results=array();$part_res=$p;$idx=0;foreach($factors as $factor){while($idx<$factor){$part_res=bcpow($part_res,'2');$part_res=bcmod($part_res,$r);$idx++;}array_push($partial_results,$part_res);}$result='1';foreach($partial_results as $part_res){$result=bcmul($result,$part_res);$result=bcmod($result,$r);}return $result;}
	private static function add_PKCS1_padding($data,$isprivateKey,$blocksize){$pad_length=$blocksize-3-strlen($data);if($isprivateKey){$block_type="\x02";$padding='';for($i=0;$i<$pad_length;$i++){$rnd=mt_rand(1,255);$padding .= chr($rnd);}}else{$block_type="\x01";$padding=str_repeat("\xFF",$pad_length);}return "\x00".$block_type.$padding."\x00".$data;}
	private static function remove_PKCS1_padding($data,$blocksize){assert(strlen($data)==$blocksize);$data=substr($data,1);if($data{0}=='\0'){return '';}assert(($data{0}=="\x01") || ($data{0}=="\x02"));$offset=strpos($data,"\0",1);return substr($data,$offset+1);}
	private static function binary_to_number($data){$radix='1';$result='0';for($i=strlen($data)-1;$i>=0;$i--){$digit=ord($data{$i});$part_res=bcmul($digit,$radix);$result=bcadd($result,$part_res);$radix=bcmul($radix,'256');}return $result;}
	private static function number_to_binary($number,$blocksize){$result='';$div=$number;while($div>0){$mod=bcmod($div,'256');$div=bcdiv($div,'256');$result=chr($mod).$result;}return str_pad($result,$blocksize,"\x00",STR_PAD_LEFT);}
}
