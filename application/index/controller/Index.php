<?php
namespace app\index\controller;

use app\index\model\Model;
use think\Validate;

class Index extends Controller
{
    public function index()
    {
        return '<script language="javascript">window.location.href ="https://www.wzxaini9.cn/"; </script>';
    }

    /**
     * 图片上传
     */
    public function uploadImg()
    {
        if($this->request->isPost()){
            $content = $this->request->post();
            if($content){
                $model = new Model();
                $data = $model->uploadImg($content['image']);
                if(!empty($data['code'])){
                    $code = $data['code'];
                    $data = $data['error'];
                }else{
                    $code = 200;
                }
            }else{
                $data['message'] = '图片上传错误';
                $code = 400;
            }
        }else{
            $code = 500;
            $data['message'] = '请求错误';
            $content = $this->request->param();
        }
        $this->returnApi($data,$code,$content);

    }

    /**
     * 内容提交
     */
    public function postContent()
    {
        if($this->request->isPost()){
            $content = $this->request->post();
            $validate = new Validate([
                'receive' => 'require|max:16',
                'content' => 'max:255',
                'template' => 'require|number',
            ]);
            $validate->message([
                'receive.require' => '接收人不能为空',
                'receive.max' => '接收人不能超过32个字符',
                'content.max' => '发送内容不能超过255个字符',
                'template.require' => '模板不能为空',
                'template.number' => '模板编号只能为数字',
            ]);
            if($validate->check($content)){
                $model = new Model();
                $data = $model->postContent($content);
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

    /**
     * 内容获取
     */
    public function getContent()
    {
        if($this->request->isPost()){
            $content = $this->request->post();
            $validate = new Validate([
                'lid' => 'require|number|egt:1',
            ]);
            $validate->message([
                'lid.require' => '信笺ID不能为空',
                'lid.number' => '信笺ID类型错误',
                'lid.egt' => '信笺ID错误',
            ]);
            if($validate->check($content)){
                $model = new Model();
                $data = $model->getContent($content);
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
