<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

/**
 * 主要是处理 onMessage onClose
 */


use \GatewayWorker\Lib\Gateway;

class Events
{
    public static $Logic;

    public static function onWorkerStart($businessWorker)
    {
        self::$Logic = new Logic();
    }

    /**
     * 有消息时
     * @param int $client_id
     * @param mixed $message
     */
    public static function onMessage($client_id, $message)
    {
        // debug
        self::$Logic->Write("[file:Event.php-47] client_id:$client_id  onMessage:" . $message);

        // 客户端传递的是json数据
        $message_data = json_decode($message, true);
        if (!$message_data || !$message_data['event']) {
            return;
        }

        if (is_callable(array('Logic', 'All_RECV'))) {
            call_user_func_array(array(self::$Logic, 'All_RECV'), array($client_id, $message_data));
        } else {
            self::$Logic->Write('[file:Event.php-58] Logic onCentralMessage Unknown message : ' . $message);
        }

    }

    /**
     * 当客户端断开连接时
     * @param integer $client_id 客户端id
     */
    public static function onClose($client_id)
    {
        // debug
        self::$Logic->Write("[file:Event.php-70] client_id:$client_id room:" . $_SESSION['room_id'] . " close");
        $message_data = [
            'event' => 'close',
            'roomID' => $_SESSION['room_id'],
            'my' => $_SESSION['my']
        ];
        call_user_func_array(array(self::$Logic, 'All_RECV'), array($client_id, $message_data));
    }

}
