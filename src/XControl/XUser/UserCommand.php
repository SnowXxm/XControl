<?php

namespace XControl\XUser;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\utils\Config;
use pocketmine\Player;

use XControl\API\QQMailer;


class UserCommand extends Command
{
    private $str = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
        0, 1, 2, 3, 4, 5, 6, 7, 8, 9,
        '_'
    ];

    public function __construct(User $plugin)
    {

        $this->plugin = $plugin;
        $this->main = $plugin->plugin;
        $this->conf = $this->plugin->conf;
        $this->baned = $this->plugin->baned;
        parent::__construct("xcu", "§b[XControl]§e -> 用户控制区块指令");
    }

    public function execute(CommandSender $sender, $label, array $args)
    {

        if (isset($args[0])) {
            switch ($args[0]) {
                case"help":
                    if ($sender instanceof Player) {
                    if(!$sender->isOp()){
                        $sender->sendMessage("§b[XUser] §7->§e这个操作是非法的~");
                        break;
                    }
                    else
                    {
                    $helpList = [
                        '§a>-------- §f[§bXControl-User§f] §a--------<',
                        '§7 /xcu transform top/pct §c登录数据[TopLogin/Protection]转换',
                        '§7 /xcu ban <player> <time> <reason> §e禁封某个玩家指定时间',
                        '§7 /xcu pardon <player> §e解封某个玩家',
                        '§7 /xcu password <player> <新密码> §e修改某玩家密码',
                    ];
                    foreach ($helpList as $helps) $sender->sendMessage($helps);
                    break;
                    }
                    }
                    $helpList = [
                        '§a>-------- §f[§bXControl-User§f] §a--------<',
                        '§7 /xcu transform top/pct §c登录数据[TopLogin/Protection]转换',
                        '§7 /xcu ban <player> <time> <reason> §e禁封某个玩家指定时间',
                        '§7 /xcu pardon <player> §e解封某个玩家',
                        '§7 /xcu password <player> <新密码> §e修改某玩家密码',
                    ];
                    foreach ($helpList as $helps) $sender->sendMessage($helps);
                    break;

                case'transform':
                    if ($sender instanceof Player) {
                        $sender->sendMessage("§b[XUser] §7->§c这个指令只允许后台执行~");
                        return true;
                    }
                    if (isset($args[1])) {
                        if ($args[1] == 'top') {
                            //top的数据转换
                            $count = 0;
                            foreach ($this->str as $key) {
                                $filepath = $this->main->getServer()->getDataPath() . "plugins/TopLogin/user/$key/";
                                if (is_dir($filepath)) {
                                    $filesnames = scandir($filepath);
                                    foreach ($filesnames as $a => $b) {
                                        $data = $filepath . $b;
                                        if (!file_exists($this->main->path . "DataBase/UserBase/Data/$key/$b")) {
                                            $data = yaml_parse_file($data);
                                            $user = new Config($this->main->path . "DataBase/UserBase/Data/$key/$b", Config::YAML, [
                                                'Password' => $data['password'],
                                                'Question' => null,
                                                'Answer' => null,
                                                'LastIp' => null,
                                                'LastCid' => null,

                                                'LastLogin' => date("Y-m-j-H"),
                                            ]);
                                            $sender->sendMessage("§b[XUser] §e-> §a转换数据§e[$b]");
                                            $count++;
                                        }
                                    }//读取所有的文件
                                }//判断是不是文件夹
                            }//读取所有的开头
                            $sender->sendMessage("§a[XUser] §a 数据转换完成\n§e成功转换§b[TopLogin]§e玩家数据 §b{$count} §e个");
                            break;
                        } elseif ($args[1] == 'pct') {
                            //pct的数据转换
                            $count = 0;
                            $filepath = $this->main->getServer()->getDataPath() . "plugins/Protection/Players/";
                            if (is_dir($filepath)) {
                                $filesnames = scandir($filepath);
                                foreach ($filesnames as $a => $b) {
                                    $first = strtolower(substr($b, 0, 1));
                                    $data = $filepath . $b;
                                    if (!file_exists($this->main->path . 'DataBase/UserBase/Data/' . $first . "/$b")) {
                                        $data = yaml_parse_file($data);
                                        $user = new Config($this->main->path . 'DataBase/UserBase/Data/' . $first . "/$b", Config::YAML, [
                                            'Password' => $data['password'],
                                            'Question' => null,
                                            'Answer' => null,
                                            'LastIp' => null,
                                            'LastCid' => null,
                                            'LastLogin' => date("Y-m-j-H"),
                                        ]);
                                        $sender->sendMessage("§b[XUser] §e-> §a转换数据§e[$b]");
                                        $count++;
                                    }
                                }//读取所有的文件
                            }//判断是不是文件夹
                            $sender->sendMessage("§a[XUser] §a 数据转换完成\n§e成功转换§b[Protection]§e玩家数据 §b{$count} §e个");
                            break;
                        }
                    } else {
                        $sender->sendMessage("§a[XUser] §e请选择数据类型");
                        break;
                    }
                    break;

                case'ban':
                    if ($sender instanceof Player && !$sender->isOp()) {
                        $sender->sendMessage("§b[XUser] §7->§e这个操作是非法的");
                        break;
                    }
                    if (isset($args[1])) {
                        if (isset($args[2])) {
                                $data = $this->plugin->getPlayer($args[1], 1);
                                //设置了禁封理由
                                if (isset($args[3])) {
                                    $this->baned->set(strtolower($args[1]), [
                                        'Ip' => $data['LastIp'],
                                        'Cid' => $data['LastCid'],
                                        'BanTime' => time() + (int)$args[2] * 86400,
                                        'Reason' => $args[3]
                                    ]);
                                    $this->baned->save();
                                    $sender->sendMessage("§c[XUser-Ban] §7-------->\n
                                    §3>>> §c已禁封玩家 §6{$args[1]}\n
                                    §3>>> §e禁封时长   §6{$args[2]}  §e天\n
                                    §3>>> §d禁封理由   §7$args[3]
                                    ");
                                    break;
                                    } else {
                                    $this->baned->set(strtolower($args[1]), [
                                        'Ip' => $data['LastIp'],
                                        'Cid' => $data['LastCid'],
                                        'BanTime' => time() + (int)$args[2] * 86400,
                                        'Reason' => null
                                    ]);
                                    $this->baned->save();
                                    $sender->sendMessage("§c[XUser-Ban] §7-------->\n
                                    §3>>> §c已禁封玩家 §6{$args[1]}\n
                                    §3>>> §e禁封时长   §6{$args[2]}  §e天\n
                                    §3>>> §d禁封理由   §7无
                                    ");
                                    break;
                                }
                            } else {
                            $sender->sendMessage("§b[XUser] §7->§e请输入禁封时间（天）");
                            break;
                        }
                    } else {
                            $sender->sendMessage("§b[XUser] §7->§e请输入目标玩家游戏名");
                            break;
                        }
                        break;

                case 'pardon':
                    if (isset($args[1])) {
                        if (!$this->baned->get(strtolower($args[1])) === false){
                            $this->baned->remove(strtolower($args[1]));
                            $this->baned->save();
                            $sender->sendMessage("§b[XUser] §7->§a成功解除对 【{$args[1]}】 的禁封");
                        }else{
                            $sender->sendMessage("§b[XUser] §7->§e未找到 【{$args[1]}】 的禁封数据");
                        }
                    }else{
                        $sender->sendMessage("§b[XUser] §7->§e请输入目标玩家游戏名");
                        break;
                    }
                    break;
                    case'password':
                    if (isset($args[1])) {
                    if (isset($args[2])) {
                    if($this->plugin->getPlayer($args[1])){
                    $data = $this->plugin->getPlayer($args[1]);
                    $data->set('Password',$args[2]);
                    $data->save();
                    $sender->sendMessage("§b[XUser] §7->§e{$args[1]} §b的密码已经重置为 {$args[2]}");
                    break;
                    }else{
                    $sender->sendMessage("§b[XUser] §7->§c没有找到玩家 §e{$args[1]} §c的数据");
                    break;
                    }
                    
                    }else{
                    $sender->sendMessage("§b[XUser] §7->§e请输入需要设置的新密码");
                        break;
                    }
                    
                    }else{
                    $sender->sendMessage("§b[XUser] §7->§e请输入目标玩家游戏名");
                        break;
                    }
                    case'test':
                    // 实例化 QQMailer
$mailer = new QQMailer(true);
// 添加附件
//$mailer->addFile('20130VL.jpg');
// 邮件标题
$title = '[XControl_User] 邮箱验证~';
// 邮件内容
$content = <<< EOF
<p align="center">
你的验证码是 : 426187<br>
请在5分钟内输入！<br>
        ---XControl邮件密保功能测试</p>
EOF;
// 发送QQ邮件
$mailer->sendMail('16769334@qq.com', $title, $content);
                    break;
            }//command结尾
        }
    }
}