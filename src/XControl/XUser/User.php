<?php

namespace XControl\XUser;

use XControl\Main;

use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;

use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

use onebone\economyapi\EconomyAPI;

class User implements Listener
{
    private $str = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
        0, 1, 2, 3, 4, 5, 6, 7, 8, 9,
        '_'
    ];

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        @mkdir($this->plugin->path . "DataBase/UserBase");
        @mkdir($this->plugin->path . "DataBase/UserBase/Data");

        foreach ($this->str as $key) {
            @mkdir($this->plugin->path . "DataBase/UserBase/Data/" . $key);
        }
        //配置文件
        $this->conf = (new Config($this->plugin->path . "DataBase/UserBase/Config.yml", Config::YAML, [
            'AutoAuth' => true,//自动登录
            'AutoToSpawn' => true,//自动回城
            'NoLoginMove' => false,//不登录移动
            'RegisterTitle' => false,//新人注册大标题
            'LoginTitle' => false,//玩家登录大标题
            'AutoAuthTime' => 2,//自动登录间隔单位小时
            'LengthLimit' => 10,//名字长度
            'BanAlt' => false,//小号限制
        ]))->getAll();
        //消息数据
        $this->msg = (new Config($this->plugin->path . "DataBase/UserBase/Message.yml", Config::YAML, [
            //'Register-Form' => 0,//0-message,1-title
            'Register-First' => "§b§l-----欢迎游玩本服务器！-----\n§7§l>>>  §e请输入你的名字进行注册",//注册 开始
            'Register-Name-true' => "§f§l>>>  §a游戏名验证通过§f  <<<\n§f§l-----§a请输入你要设置的密码§f-----",//注册 正确 输入名字
            'Register-Name-false' => "§7§l>>>  §e请正确输入你的游戏名",//注册 错误 输入名字
            'Register-Password' => "§7§l>>>  §a你要设置的密码为 §6",//注册 输入 密码
            'Register-RePassword' => "§7§l>>>  请再次输入来进行确认或重新进服重新设置",//注册 确定 密码
            'Register-RePassword-false' => "§7§l>>>  §c和上次输入的密码不符，请重新设置！",//注册 错误 确定密码
            'Register-Success' => "§7§l>>>  §a完成注册\n§f§l>>>  §e请牢记你的密码§f  <<<",//注册完成
            'Register-Title' => "§e§l§a{name}欢迎来到本服！请注册！%s\n§e服务器官方QQ群-未知",//注册 新人 大标题（需要开启）
            //-------------------优美的分割线-------------------------//
            //'Login-Form' => 0,//0-message,1-title
            'Login-Ing' => "§7§l>>>  欢迎回来!\n§7§l>>>  §e请输密码登录",//登录 输入密码
            'Login-Password-false' => "§7§l>>>  §c密码错误，请重试",//登录 错误 输入密码
            'Login-End' => "§7§l>>> §a登录成功，欢迎回来",//登录完成
            'Login-End-Message' => "§b-----最新公告-----\n§7§l- 暂无",//登录完成
            'Login-Title' => "§e§l§a{name}欢迎回来%s\n§b今天你有没有签到呢？",//登录 结束 大标题（需要开启）
            //-------------------优美的分割线-------------------------//
            'PreLogin-NameLimit-false' => "§e你的名字太长啦",//预登陆 错误 名字过长
            'PreLogin-IsOnline-false' => "§e这个账号已经有人登录了",//预登陆 错误 已经登录
           'PreLogin-BanAlt-false' => "§e超出本服小号限制！§b[x个]",//预登陆 错误 小号太多
           // 'PreLogin-InBaned-false' => "§c距离解封还有 §a{$time} §c天 §b原因：{reason}",//预登陆 错误 已被禁封
        ]))->getAll();
        //禁封数据
        $this->baned = new Config($this->plugin->path . "DataBase/UserBase/XBan.yml", Config::YAML, [
        ]);
        $this->alt = new Config($this->plugin->path . "DataBase/UserBase/XAlt.yml",Config::YAML,array());

        $this->plugin->getServer()->getPluginManager()->registerEvents(new UserEvent($this), $plugin);
        $map = $this->plugin->getServer()->getCommandMap();
        $map->register("Main", new UserCommand($this));
    }

    public function checkPlayer($player, $type = 'checkData')
    {
        if ($type == 'checkData') {
            $name = strtolower($player->getName());
            //处理玩家名字
            $first = strtolower(substr($name, 0, 1));
            //如果存在玩家数据
            if (file_exists($this->plugin->path . 'DataBase/UserBase/Data/' . $first . "/$name.yml")) {
                $user = new Config($this->plugin->path . 'DataBase/UserBase/Data/' . $first . "/$name.yml", Config::YAML, []);
                //开始判断玩家登录状态
                if (!$user->get('Password') == null) {
                    if ($this->conf['AutoAuth'] == true && ($player->getAddress() == $user->get('LastIp') && (int)$player->getClientId() == $user->get('LastCid'))) {
                        //开启了自动登录
                        $last_login = explode('-', $user->get('LastLogin'));
                        $interval = (int) (date("H") - $last_login[3]);
                        $auto_auth_time = $this->conf['AutoAuthTime'];
                        if ($auto_auth_time <= $interval) {
                            $this->status[$name] = 4;
                            $user->getAll()['LastLogin'] = date("Y-m-j-H");
                            $user->setAll($user->getAll());
                            $user->save();
                            return;
                        } else {
                            //超过设置自动登录的时间
                            $this->status[$name] = 3;
                            $user->getAll()['LastLogin'] = date("Y-m-j-H");
                            $user->getAll()['LastIp'] = $player->getAddress();
                            $user->getAll()['LastCid'] = (int)$player->getClientId();
                            $user->setAll($user->getAll());
                            $user->save();
                            return;
                        }
                    } else {
                        //ip或者cid不符合
                        $this->status[$name] = 3;
                        $user->getAll()['LastLogin'] = date("Y-m-j-H");
                        $user->getAll()['LastIp'] = $player->getAddress();
                        $user->getAll()['LastCid'] = (int)$player->getClientId();
                        $user->setAll($user->getAll());
                        $user->save();
                        return;
                    }
                } else {
                    //未注册
                    $this->status[$name] = 1;
                    return;
                }
            } else {
                //新建玩家数据
                $this->createData($player);
                return;
            }
        } elseif ($type == 'checkStatus') {
            $name = strtolower($player->getName());
            switch ($this->status[$name]) {
                case"1":
                    $player->sendMessage($this->msg['Register-First']);
                    break;
                /*
                 case"2":
                    $player->sendMessage('§b>>>§e请输入要设置的密码进行注册~');
                    break;
                */
                case"3":
                    $player->sendMessage($this->msg['Login-Ing']);
                    break;
                case"4":
                    $player->sendMessage($this->msg['Login-End']);
                    $player->sendMessage($this->msg['Login-End-Message']);
                    if ($this->conf['LoginTitle']){
                        $title = str_replace('{name}',$player->getName(),$this->msg['Login-Title']);
                        $title = explode('%s',$title);
                        sleep(1);
                        $player->sendTitle($title[0],$title[1],100,100,60);
                    }
                    break;
            }
        }
    }

    /*
     * 获取玩家数据信息
     * type 0 = get（）
     * type 1 = getAll（）
     */
    public function getPlayer($name, $type = 0)
    {
        $first = strtolower(substr($name, 0, 1));
        if (file_exists($this->plugin->path . 'DataBase/UserBase/Data/' . $first . "/$name.yml")) {
            $user = new Config($this->plugin->path . 'DataBase/UserBase/Data/' . $first . "/$name.yml", Config::YAML, []);
            if ($type == 0) return $user;
            if ($type == 1) return $user->getAll();
        } else {
            return false;
        }
    }

    public function createData($player)
    {
        $name = strtolower($player->getName());
        $first = strtolower(substr($name, 0, 1));
        $user = new Config($this->plugin->path . 'DataBase/UserBase/Data/' . $first . "/$name.yml", Config::YAML, [
            'Password' => null,
            'Question' => null,
            'Answer' => null,
            'LastIp' => $player->getAddress(),
            'LastCid' => (int)$player->getClientId(),

            'LastLogin' => date("Y-m-j-H"),
        ]);
        $this->status[$name] = 1;
        if ($this->conf['RegisterTitle']){
            $title = str_replace('{name}',$player->getName(),$this->msg['Register-Title']);
            $title = explode('%s',$title);
            sleep(1);
            $player->sendTitle($title[0],$title[1],20,100,60);
        }
    }

    /**
     * 检查是否被禁封
     */
    public function checkBaned($player)
    {
        $name = strtolower($player ->getName());
        $ip = $player->getAddress();
        $cid = (int)$player->getClientId();
        //$banlist = $this->baned->getAll();
        foreach ($this->baned->getAll() as $banname => $bandata) {
            if ($name == $banname) {
                if ($bandata['BanTime'] > time()) {
                    return [
                        'bantime' => $bandata['BanTime'],
                        'baninfo' => $bandata['Reason']
                    ];
                }
            }
            if ($ip == $bandata['Ip']) {
                if ($bandata['BanTime'] > time()) {
                    return [
                        'bantime' => $bandata['BanTime'],
                        'baninfo' => $bandata['Reason']
                    ];
                }
            }
            if ($cid == $bandata['Cid']) {
                if ($bandata['BanTime'] > time()) {
                    return [
                        'bantime' => $bandata['BanTime'],
                        'baninfo' => $bandata['Reason']
                    ];
                }
            }
        }
        return true;
        unset ($name, $ip, $cid);
    }
    
    public function checkAlt($player){
    $name = $player->getName();
  $cid = (int)$player->getClientId();
  $ip = $player->getAddress();
 
  if($player->isOp()){return true;}
 
   foreach($this->alt->getAll() as $name => $data){
   static $cont = 0;
   if($data["Ip"] == $ip){
    $cont++;
    $owner = $name;
   }
   if($data["Cid"] == $cid && isset($owner) && !$owner == $name){
    $cont++;
   }
   if($cont > (int)($this->conf['BanAlt'])){
				unset ($cont,$owner,$ip,$cid);
				return false;
   }
   }
   if(!isset($this->players[$name])){
   $info = [
   "Ip" => $ip,
   "Cid" => $cid,
   ];
   $this->alt->set($name,$info);
   $this->alt->save();
   }
    return true;
    }
    /*
    public function setMsg($msg,$info = null,$type = null)
    {
        //$before-替换前，$after-替换后
        $before = ['{name}','{limit}','%n'];
        $after = [$info->getName(),"$this->conf['LengthLimit']","\n"];
        $msg = str_replace($before,$after,$msg);

        if ($type = 'title'){
            $msg = explode('%s',$msg);
        }
        return $msg;
    }
    */
}