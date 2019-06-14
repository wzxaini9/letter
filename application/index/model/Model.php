<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 18/2/9
 * Time: 10:37
 */

namespace app\index\model;

use think\Db;

class Model
{
    protected $img_url = 'http://letter.api.zhongxuanzhuli.com/image/';
    protected $img_path = 'public/image/';

    function uploadImg($base64_image_content){
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
            //图片后缀
            $type = $result[2];
            //保存位置--图片名
            $root = ROOT_PATH.$this->img_path;
            $paths = $root.'Default/'.$this->upPath();
            $this->checkPath($paths);
            $image_name=$this->upName().".".$type;
            //解码
            $decode=base64_decode(str_replace($result[1], '', $base64_image_content));
            if (file_put_contents($paths.$image_name, $decode)){
                $data['code']=0;
                $data['path'] = $this->upPath();
                $data['file'] = $image_name;
                $data['msg']='保存成功！';
            }else{
                $data['code']=400;
                $data['error']='图片保存失败！';
            }
        }else{
            $data['code']=400;
            $data['error']='base64图片格式有误！';
        }
        return $data;
    }

    public function postContent($data)
    {
        $insert['receive'] = $data['receive'];
        $insert['content'] = $data['content'];
        if($data['path'] && $data['file']){
            $filePath = $this->moveImg($data['path'],$data['file'],1);
        }else{
            $filePath = '';
        }
        $insert['image'] = $filePath;
        $insert['template'] = $data['template'];
        $lid['lid'] = Db::name('letter')->insertGetId($insert);
        return $lid;
    }

    public function getContent($data)
    {
        $letter = Db::name('letter')->where(['id'=>$data['lid']])->find();
        return $letter;
    }

    public function upPath()
    {
        return date('Y/m/');
    }

    public function upName()
    {
        return (microtime (true)*10000);
    }

    public function moveImg($path,$file,$type)
    {
        $root = ROOT_PATH.$this->img_path;
        $original = $root.'Default'.DS.$path.$file;
        switch ($type){
            case 1:
                $address = $root.'Letter'.DS.$path;
                $filePath = $this->img_url.'Letter/'.$path.$file;
                break;
            default:
                $address = $root.'Default'.DS.$path;
                $filePath = $this->img_url.'Default/'.$path.$file;
                break;
        }
        $this->checkPath($address);
        $new = $address.$file;
        @rename($original, $new);
        return $filePath;
    }

    public function deleteImg($pathFile)
    {
        $root = ROOT_PATH.$this->img_path.$pathFile;
        @unlink($root);
    }

    protected function checkPath($path)
    {
        if (is_dir($path)) {
            return true;
        }
        if (mkdir($path, 0755, true)) {
            return true;
        } else {
            $this->error = "目录 {$path} 创建失败！";
            return false;
        }
    }

    public function getRound($data)
    {
        $where['sign'] =$data['sign'];
        $where['rid'] =$data['rid'];
        $where['status'] =1;
        $qids = Db::name('question')->field('id')->where($where)->select();
        if(count($qids)){
            $ids =[];
            foreach ($qids as $k => $v)
            {
                $ids[$v['id']] = $k;
            }
            $ids = array_rand($ids,5);
            $data = Db::name('question')
                ->field('id,rid,topic,option_a,option_b,option_c,option_d,correct')
                ->where(['id'=>['in',$ids]])
                ->select();
        }else{
            $data['message'] = '题库为空';
        }
        return $data;
    }

    public function postRound($result,$sign,$uid)
    {
        if(Db::name('ranking')->where(['uid'=>$uid,'sign'=>$sign])->find()){
            $msg['message'] = '您已参与过答题';
            $msg['code'] = 400;
        }else{
            $correct = $total = 0;
            $c = count($result);
            for($i=1;$i<=$c;$i++){//循环轮次
                $ids = $options = $data = null;
                foreach ($result[$i-1] as $v){//取出第I轮的答题信息
                    $ids[] = $v['id'];
                    $options[$v['id']] = $v['correct'];
                }
                $question = Db::name('question')->field('id ,correct')->where(['id'=>['in',$ids]])->select();
                foreach ($question as $k => $v){
                    $data[$k]['sign'] = $sign;
                    $data[$k]['qid'] = $v['id'];
                    $data[$k]['uid'] = $uid;
                    $data[$k]['q_correct'] = $v['correct'];
                    $data[$k]['m_correct'] = $options[$v['id']];
                    if($options[$v['id']] == $v['correct']){
                        $correct++;
                    }
                    $total++;
                }
                if(!empty($data)){
                    Db::name('answer')->insertAll($data);
                }
            }
            $ranking['uid'] = $uid;
            $ranking['sign'] = $sign;
            $ranking['total'] = $total;
            $ranking['correct'] = $correct;
            Db::name('ranking')->insert($ranking);
            $msg['message'] = '提交成功';
        }
        return $msg;
    }

    public function users($content,$ip)
    {
        $user =  Db::name('users')
            ->field('id')
            ->where(['phone'=>$content['phone']])
            ->find();
        if(empty($user)){
            $user['phone'] = $content['phone'];
            $user['nickname'] = $content['nickname'];
            $user['createtime'] = date('Y-m-d h:i:s');
            $user['createip'] = $ip;
            $user['lastip'] = $ip;
            $uid = Db::name('users')->insertGetId($user);
        }else{
            $uid = $user['id'];
            Db::name('users')->where(['id'=>$uid])->update(['lastip'=>$ip]);
        }
        return $uid;
    }

    public function getRanking($content)
    {
        $data['lists'] = Db::name('ranking')
            ->field('u.nickname,r.total,r.correct,r.createtime')->alias('r')
            ->join('users u','r.uid = u.id')
            ->where(['sign'=>$content['sign']])
            ->page($content['page'],20)
            ->order('correct desc , createtime desc')
            ->select();
        $data['page'] = $content['page'];
        $data['count'] = Db::name('ranking')
            ->where(['sign'=>$content['sign']])
            ->count();
        return $data;
    }

    public function getMessage($content)
    {
        $data['lists'] = Db::name('message')
            ->field('content')
            ->page($content['page'],20)
            ->order('createtime asc')
            ->select();
        $data['page'] = $content['page'];
        $data['count'] = Db::name('message')->count();
        return $data;
    }

    public function postMessage($content,$ip)
    {
        $ranking['createip'] = $ip;
        $ranking['content'] = $content;
        Db::name('message')->insert($ranking);
        $msg['message'] = '留言成功';
        return $msg;
    }
}
