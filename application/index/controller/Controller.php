<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 18/2/9
 * Time: 10:35
 */

namespace app\index\controller;

use think\Log;
use think\Request;

class Controller
{
    /**
     * @var \think\Request Request实例
     */
    protected $request;

    /**
     * 构造方法
     * @param Request $request Request对象
     * @access public
     */
    public function __construct(Request $request = null)
    {
        if (is_null($request)) {
            $request = Request::instance();
        }
        $this->request = $request;
    }
    /**
     * 数据返回
     */
    public function returnApi($data = [], $code = 100,$content = [])
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials:true");
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Max-Age: 600');
        $result = [
            'data' => $data,
            'code' => $code,
            'time' => $_SERVER['REQUEST_TIME'],
            'content'  => $content,
        ];
        Log::write(json_encode($result),'info');
        echo json_encode($result);
    }
    /**
     * 获取客户端IP地址
     * @param integer   $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param boolean   $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    public function ip($type = 0, $adv = false)
    {
        $type      = $type ? 1 : 0;
        static $ip = null;
        if (null !== $ip) {
            return $ip[$type];
        }

        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }
                $ip = trim(current($arr));
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip   = $long ? [$ip, $long] : ['0.0.0.0', 0];
        return $ip[$type];
    }
}