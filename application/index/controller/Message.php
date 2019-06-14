<?php
/**
 * Created by PhpStorm.
 * User: Powerless
 * Blog: https://www.wzxaini9.cn/
 * Date: 18/11/1
 * Time: 22:32
 */

namespace app\index\controller;

use app\index\model\Model;
use think\Validate;

class Message extends Controller
{
    public function index()
    {
        return '<script language="javascript">window.location.href ="https://www.wzxaini9.cn/"; </script>';
    }
    /**
     * 获取内容
     */
    public function getMessage()
    {
        if($this->request->isGet()){
            $content = $this->request->get();
            $validate = new Validate([
                'page' => 'require|number|egt:1',
            ]);
            $validate->message([
                'page.require' => '页码不能为空',
                'page.number' => '页码类型错误',
                'page.egt' => '起始页过小',
            ]);
            if($validate->check($content)){
                $model = new Model();
                $data = $model->getMessage($content);
                $code = 200;
            }else{
                $code = 400;
                $data['message'] = $validate->getError();
            }
        }else{
            $data['message'] = '请求类型不符';
            $code = 500;
            $content =  $this->request->request();
        }
        $this->returnApi($data,$code,$content);
    }
    /**
     * 内容提交
     */
    public function postMessage()
    {
        if($this->request->isPost()){
            $content = $this->request->post();
            $validate = new Validate([
                'content' => 'require|max:255',
            ]);
            $validate->message([
                'content.require' => '留言内容不能为空',
                'content.max' => '留言内容不能超过255个字符',
            ]);
            if($validate->check($content)){
                $model = new Model();
                $ip = $this->ip(1,true);
                $content = $content['content'];
                $data = $model->postMessage($content,$ip);
                $code = 200;
            }else{
                $code = 400;
                $data['message'] = $validate->getError();
            }

        }else{
            $code = 500;
            $data['message'] = '请求错误';
            $content = $this->request->param();
        }
        $this->returnApi($data,$code,$content);
    }
}