<?php
/**
 * Created by PhpStorm.
 * User: wzxai
 * Date: 18/5/23
 * Time: 13:01
 */

namespace app\index\controller;

use app\index\model\Model;
use think\Validate;

class Cattle extends Controller
{

    public function index()
    {
        return '<script language="javascript">window.location.href ="https://www.wzxaini9.cn/"; </script>';
    }

    public function round()
    {
        if($this->request->isGet()){
            $content = $this->request->get();
            $validate = new Validate([
                'rid' => 'require|number|between:0,5',
                'sign'=> 'require|number|between:1,9',
            ]);
            $validate->message([
                'rid.require' => '关卡ID不能为空',
                'rid.number' => '关卡ID类型错误',
                'rid.between' => '关卡ID不在范围内',
                'sign.require' => '题目标识不能为空',
                'sign.number' => '题目标识类型错误',
                'sign.between' => '题目标识不在范围内',
            ]);
            $code = 400;
            if($validate->check($content)){
                $model = new Model();
                $data = $model->getRound($content);
                if(empty($data['message'])){
                    $code = 200;
                }
            }else{
                $data['message'] = $validate->getError();
            }
        }elseif($this->request->isPost()){
            $content = $this->request->post();
            $validate = new Validate([
                'phone' => 'require|number|length:11|between:13000000000,19999999999',
                'nickname' => 'require|chsDash|min:1|max:16',
                'result' => 'require',
                'sign'=> 'require|number|between:1,9',
            ]);
            $validate->message([
                'phone.require' => '手机号不能为空',
                'phone.number' => '手机号只能是数字',
                'phone.length' => '手机号长度错误',
                'phone.between' => '手机号不在范围内',
                'nickname.require' => '姓名不能为空',
                'nickname.chsDash' => '姓名只能是汉字、字母、数字和下划线_及破折号-',
                'nickname.min' => '姓名不能小于1个字符',
                'nickname.max' => '姓名不能大于16个字符',
                'result.require' => '答题结果不能为空',
                'sign.require' => '题目标识不能为空',
                'sign.number' => '题目标识类型错误',
                'sign.between' => '题目标识不在范围内',
            ]);
            if($validate->check($content)){
                $result = json_decode($content['result'],true);
                $model = new Model();
                $ip = $this->ip(1,true);
                $user['phone'] = $content['phone'];
                $user['nickname'] = $content['nickname'];
                $uid = $model->users($user,$ip);
                $data = $model->PostRound($result,$content['sign'],$uid);
                if(empty($data['code'])){
                    $code = 200;
                }else{
                    $code = 400;
                }
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

    public function ranking()
    {
        if($this->request->isGet()){
            $content = $this->request->get();
            $validate = new Validate([
                'page' => 'require|number|egt:1',
                'sign'=> 'require|number|between:1,9',
            ]);
            $validate->message([
                'page.require' => '页码不能为空',
                'page.number' => '页码类型错误',
                'page.egt' => '起始页过小',
                'sign.require' => '题目标识不能为空',
                'sign.number' => '题目标识类型错误',
                'sign.between' => '题目标识不在范围内',
            ]);
            if($validate->check($content)){
                $model = new Model();
                $data = $model->getRanking($content);
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
}