<?php

namespace app\admin\controller;
// include '../application/org/MemSession.php';
// include '../application/org/MemcachedSession.php';
// use \app\org\MemSession;
// use \app\org\MemcachedSession;
use think\Controller;

class Login extends Controller
{
    public function index(){
        return view('');
    }

    public function login(){
        $data = input();

        $username = input('username');
        $password = input('password');

        $rules = [
            'username|用户名' => 'require',   
            'password|密码' => 'require',
            'code|验证码' => 'require|captcha:login'      //这里的captcha是验证字段的规则（验证码）
        //captcha:xxx   后面的xxx作为标记使用，防止一个网站中有多个验证码，发生覆盖的情况，在相对的页面中也要{:captcha_src('login')}
        ];
        $res = $this->validate($data,$rules);
        if($res!==true){
            $this->error($res);
        }
        //调用common.php中的方法获取数据库对象
        $db = getMongoDB();
        //指定集合名
        $table = $db->users;

        //如果登录成功，返回登录信息对象，如果登录失败，返回null
        $user = $table->findOne(['username'=>$username,'password'=>$password]);
        // var_dump($user);

        $redis = getRedis();
        $errorkey = $username.':error';
        if(!$user){
            if(!$redis->exists($errorkey)){
                $redis->set($errorkey,0,86400);
            }
            $redis->incr($errorkey);
            $count = $redis->get($errorkey);
            if($count>=10){
                return $this->error('登录错误次数过多，请一天之后重试');
            }
            return $this->error('账号或密码错误');
        }else{
            $redis->del($errorkey);
            //用户登录后写入登录日志
            $tablelog = $db->loginlog;
            $logintime = time();
            $tablelog->insertOne(['username'=>$username,'logintime'=>$logintime]);
        }

        //将登录信息写入到session中（如果使用memcache，则在config.php文件中设置session type即可）
        session('hostname.'.$username,$user);

        return $this->redirect(url('admin/index/index'));



        // $login_key = 'user:'.$username;
        
        
        
        // 引用封装的助手函数
        // $redis = getRedis();


        // if(!$redis->exists($login_key)){
        //     return $this->error('登录失败：用户名不存在');
        // }



        // $login_val = $redis->get($login_key);
        // if($login_val != $password){
            
        //     if(!$redis->exists($username.":serror")){
        //         //密码错误
        //         $redis->set($username.":serror",0,86400);
        //     }

        //     //错误次数+1
        //     $redis->incr($username.":serror");

        //     // 记录登录错误的次数
        //     $count = $redis->get($username.":serror");
        //     // 如果次数达到了3次
        //     if($count >= 3){
        //         return $this->error('该用户已锁定，10s后再试');
        //     }
        //     return $this->error('登录失败：用户名或密码错误');
        // }

        // $redis->del($username.":serror");
        // session('admin:user',$username);
        // return $this->success('登录成功',url('admin/news/index'));

    }
}
