<?php

use \GatewayWorker\Lib\Gateway;
use \GatewayWorker\Lib\DbModel;
use Workerman\Lib\Timer;

header("content-type:text/html;charset=utf-8");
ini_set('date.timezone', 'Asia/Shanghai');
include('Room.php');

class Logic
{
    public $roomlist = [];// [房间号,[true=>client_id,false=>client_id,room=>数据]]

    public $roomAI = []; //[房间号,[0=>AI_ID,1=>AI_COUNT_TRUE,2=>AI_COUNT_FALSE]]

    public function All_RECV($client_id, $message)
    {
        $event = $message['event'];

        switch ($event) {
            case 'login':
                $this->Login($client_id, $message);
                break;
            case 'pong':
                break;
            case 'move':
                if ($message['roomId'] != $_SESSION['room_id'] && $message['my'] != $_SESSION['my']) {
                    $this->Write("[Logic.php 29] roomid or my error");
                    return 0;
                }
                $this->Move($message);
                break;
            case 'reset':
                $this->Reset($client_id, $_SESSION['room_id']);
                break;
            default:

                break;
        }
    }

    public function Write($data)
    {
        file_put_contents("Gomoku.log", date("Y-m-d h:i:sa") . ":  " . $data . "\r\n", FILE_APPEND);
    }

    public function Login($client_id, $message)
    {
        for ($room_id = 1; $room_id < 100; $room_id++) {
            //人人对战
            if ($message['complexity'] == 0) {
                if (!isset($this->roomlist[$room_id])) {
                    $my = true;
                    $_SESSION['room_id'] = $room_id;
                    $_SESSION['my'] = $my;
                    $this->roomlist[$room_id][true] = $client_id;
                    $this->roomlist[$room_id]['room'] = new Room();
                    $data = [
                        'event' => 'login',
                        'my' => $my,
                        'roomId' => $room_id
                    ];
                    $this->Send($client_id, $data);
                    break;
                } elseif (count($this->roomlist[$room_id]) == 3) {
                    continue;
                } else {
                    $my = false;
                    $_SESSION['room_id'] = $room_id;
                    $_SESSION['my'] = $my;
                    $this->roomlist[$room_id][false] = $client_id;
                    $data = [
                        'event' => 'login',
                        'my' => $my,
                        'roomId' => $room_id
                    ];
                    $this->roomAI[$room_id] = array(0, 0, 0);
                    $this->Send($client_id, $data);
                    $this->roomAI[$room_id][0] = Timer::add(1, function ($client_id_false, $client_id_true, $room_id) {
                        $data = [
                            'event' => 'gameBegin',
                        ];
                        $this->Send($client_id_false, $data);
                        $this->Send($client_id_true, $data);
                        $this->roomlist[$room_id]['room']->gameRun = true;
                        //增加个计时器
                        $this->roomAI[$room_id][0] = Timer::add(5, function ($roomID, $my) {
                            $this->AI($roomID, $my);
                        }, array($room_id, true), false);

                    }, array($client_id, $this->roomlist[$room_id][true], $room_id), false);
                    break;
                }
            } else {
                //人机对战
                if (!isset($this->roomlist[$room_id])) {
                    $a = rand(0, 1);
                    $my = $a ? true : false;
                    $_SESSION['room_id'] = $room_id;
                    $_SESSION['my'] = $my;
                    $this->roomAI[$room_id] = array(0, 0, 0);
                    $this->roomlist[$room_id][$my] = $client_id;
                    $this->roomlist[$room_id][!$my] = 0;//0是机器人ID
                    $this->roomlist[$room_id]['room'] = new Room();
                    $this->roomlist[$room_id]['room']->gameMode = false;
                    $this->roomlist[$room_id]['room']->complexity = $message['complexity'];
                    $data = [
                        'event' => 'login',
                        'my' => $my,
                        'roomId' => $room_id
                    ];
                    $this->Send($client_id, $data);
                    $this->roomlist[$room_id]['room']->gameRun = true;
                    $data = [
                        'event' => 'gameBegin',
                    ];
                    $this->Send($client_id, $data);
                    //增加个计时器
                    $this->roomAI[$room_id][0] = Timer::add(5, function ($roomID, $my) {
                        $this->AI($roomID, $my);
                    }, array($room_id, true), false);
                    break;
                }
            }

        }
        $this->Write("[Logic.php-128] RoomId:" . $room_id . '        黑棋:' . @$this->roomlist[$room_id][true] . '   白棋:' . @$this->roomlist[$room_id][false]);
    }

    public function AI($roomID, $my)
    {
        /*
        if ($this->roomAI[$roomID][1] == 3) {
            Gateway::closeClient($this->roomlist[$roomID][true]);
            $this->Close($roomID, true);
        }
        if ($this->roomAI[$roomID][2] == 3) {
            Gateway::closeClient($this->roomlist[$roomID][false]);
            $this->Close($roomID, false);
        }
        */
        if ($my) {
            $this->roomAI[$roomID][1]++;
        } else {
            $this->roomAI[$roomID][2]++;
        }

        //调用Room里面的接口
        $move = $this->roomlist[$roomID]['room']->AI();
        $message = [
            'roomId' => $roomID,
            'x' => $move['x'],
            'y' => $move['y'],
            'my' => $my,
            'AI' => true
        ];
        $this->Move($message);
    }

    public function Close($roomID, $my)
    {
        Timer::del($this->roomAI[$roomID][0]);
        if ($my == true) {
            if (count($this->roomlist[$roomID]) == 2) {
                $this->roomlist[$roomID][true] = $this->roomlist[$roomID][false];//将false位换到true位
                unset($this->roomlist[$roomID][false]);
                $data = [
                    'event' => 'userClose',
                ];
                $this->Send($this->roomlist[$roomID][true], $data);
            } else {
                unset($this->roomlist[$roomID]);
            };
        } else {
            unset($this->roomlist[$roomID][false]);
            $data = [
                'event' => 'userClose'
            ];
            $this->Send($this->roomlist[$roomID][true], $data);
        }
    }

    public function Move($message)
    {
        if (!isset($message['AI'])) {
            if ($message['my']) {
                $this->roomAI[$message['roomId']][1] = 0;//黑棋
            } else {
                $this->roomAI[$message['roomId']][2] = 0;//白棋
            }
        }

        if (!$this->roomlist[$message['roomId']]['room']->gameRun) {
            $data = [
                "message" => "游戏未开始"
            ];
            $this->Send($this->roomlist[$message['roomId']][$message['my']], $data);
            $this->Write("[Logic.php-191] " . $message['roomId'] . ": game not begin");
            return 0;
        }
        if ($this->roomlist[$message['roomId']]['room']->me != $message['my']) {
            $data = [
                "message" => "未轮到你"
            ];
            $this->Send($this->roomlist[$message['roomId']][$message['my']], $data);
            $this->Write("[Logic.php-199] " . $message['roomId'] . ": not you");
            return 0;
        }
        //移动
        $data = $this->roomlist[$message['roomId']]['room']->moveLater($message['x'], $message['y']);
        $data = [
            "event" => "DownState",
            "state" => $data['state'],
            "type" => $data['type'],
            "message" => $data['message']
        ];
        $this->Write("[Logic.php-210] RoomId:" . $message['roomId'] . " 消息: " . $data['message']);
        if ($data['state'] == 0 && $data['type'] == 5) {
            $this->Send($this->roomlist[$message['roomId']][$message['my']], $data);
            return 0;
        }

        if (!isset($message['AI'])) {
            Timer::del($this->roomAI[$message['roomId']][0]);
        } else {
            if ($message['AI']) {
                if ($message['my']) {
                    $this->roomAI[$message['roomId']][1]++;//黑棋
                } else {
                    $this->roomAI[$message['roomId']][2]++;//白棋
                }
            }
        }

        $message['event'] = "move";
        $this->Send($this->roomlist[$message['roomId']][!$message['my']], $message);
        $this->Send($this->roomlist[$message['roomId']][$message['my']], $message);

        //游戏结束 棋盘满 ||游戏结束 棋子胜利
        if (($data['state'] == 0 && $data['type'] == 8) || $data['state'] == 2) {
            Timer::add(5, function ($roomID, $my, $data) {
                $this->Send($this->roomlist[$roomID][$my], $data);
                $this->Send($this->roomlist[$roomID][!$my], $data);
                $this->Recover($roomID);
            }, array($message['roomId'], $message['my'], $data), false);
            return 0;
        }

        //落棋失败 已被占
        if ($data['state'] == 0 && $data['type'] == 5) {
            $this->Send($this->roomlist[$message['roomId']][$message['my']], $data);
            return 0;
        }

        if (!$this->roomlist[$message['roomId']]['room']->gameMode && $this->roomlist[$message['roomId']][$message['my']] != 0) {
            //人机模式 给机器人设置一个随机时间
            $time = rand(1, 5);
            $this->roomAI[$message['roomId']][0] = Timer::add($time, function ($roomID, $my) {
                $this->AI($roomID, $my);
            }, array($message['roomId'], !$message['my']), false);
        } else {
            $time = 30;
            if ($message['my']) {
                //给白棋设置时间
                if ($this->roomAI[$message['roomId']][2] > 2) {
                    $time = 5;
                }
                if ($this->roomAI[$message['roomId']][2] > 5) {
                    $time = 1;
                }
            } else {
                //给黑棋设置时间
                if ($this->roomAI[$message['roomId']][1] > 2) {
                    $time = 5;
                }
                if ($this->roomAI[$message['roomId']][1] > 5) {
                    $time = 1;
                }
            }
            $this->roomAI[$message['roomId']][0] = Timer::add($time, function ($roomID, $my) {
                $this->AI($roomID, $my);
            }, array($message['roomId'], !$message['my']), false);
        }


    }

    public function Reset($client_id)
    {
        $_SESSION['my'] = true;
    }

    public function Send($client_id, $data)
    {
        if ($client_id) {
            Gateway::sendToClient($client_id, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
    }

    public function Recover($roomID)
    {
        unset($this->roomlist[$roomID]);
        unset($this->roomAI[$roomID]);
        $this->Write("[Logic.php-284] 房间[" . $roomID . "]已被销毁");
    }
}