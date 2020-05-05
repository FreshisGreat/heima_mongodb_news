<?php

namespace app\admin\controller;
use MongoDB\Client;
use think\Controller;
use think\Request;
use app\org\Page;
class Index extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $search = input('get.search');
        // var_dump($search);
        // $data = [];

        $db = getMongoDB();
        $table = $db->news;
        
        // 分页
        // $total：数据库中的数据总条数
        $total = $table->count();
        // $pagesize：每页显示的数据条数
        $pagesize = 10;

        // 调用封装好的org/Page.php类
        $page = new Page($total,$pagesize);
        // $skip：表示在当前页之前所有页具有的数据总条数
        $skip = $page->offset;

        if($search){
            $total = $table->count(['title'=> new \MongoDB\BSON\Regex("$search")],[
                'projection' => ['content'=>0],
                // 分页取数据
                // 'skip' => $skip,
                // 'limit' => $pagesize
            ]);
            // var_dump($total);
            // 调用封装好的org/Page.php类
            $page = new Page($total,$pagesize);
            // $skip：表示在当前页之前所有页具有的数据总条数
            $skip = $page->offset;
            $data = $table->find(['title'=> new \MongoDB\BSON\Regex("$search")],[
                'projection' => ['content'=>0],
                // 分页取数据
                'skip' => $skip,
                'limit' => $pagesize,
                'sort' => ['_id' => -1]
            ]);
        }else{
            $data = $table->find([],[
                'projection' => ['content'=>0],
                // 分页取数据
                'skip' => $skip,
                'limit' => $pagesize,
                'sort' => ['_id' => -1]
            ]);
        }
            
        //将获取的数据对象转换为数组
        $data = $data->toArray();
        
        // var_dump($data);
            
        // 显示页脚
        $fpage = $page->fpage();
        // var_dump($fpage);
        //头像获取
        $username = getusername();
        // var_dump($username);
        $tableusers = $db->users;
        $obj = $tableusers->findOne(
            ['username'=>$username],
            ['projection' => ['path'=>1,'_id'=>0]]
        );
        $head = $obj->path;
        // var_dump($head);
        return view('',compact('data','fpage','head'));

        // var_dump(session('admin:user'));
        // // 引用封装的助手函数
        // $redis = getRedis();
        // //取出zset的所有id
        // $ids = $redis->zrange('news:zset:id',0,-1);
        // // var_dump($ids);
        // foreach ($ids as $id){
        //     $key = 'news:id:'.$id;
        //     $one = $redis->hgetall($key);
        //     $data[] = $one;
        // }
        // var_dump($data);
        // if($data==[]){
        //     return view('');
        // }else{
            // return view('',compact('data'));
        // }
    }

    public function hot()
    {
        $search = input('get.search');
        // var_dump($search);
        // $data = [];

        $db = getMongoDB();
        $table = $db->news;
        
        // 分页
        // $total：数据库中的数据总条数
        $total = $table->count();
        // $pagesize：每页显示的数据条数
        $pagesize = 10;

        // 调用封装好的org/Page.php类
        $page = new Page($total,$pagesize);
        // $skip：表示在当前页之前所有页具有的数据总条数
        $skip = $page->offset;

        if($search){
            $total = $table->count(['title'=> new \MongoDB\BSON\Regex("$search")],[
                'projection' => ['content'=>0],
                // 分页取数据
                'skip' => $skip,
                'limit' => $pagesize
            ]);
            // 调用封装好的org/Page.php类
            $page = new Page($total,$pagesize);
            // $skip：表示在当前页之前所有页具有的数据总条数
            $skip = $page->offset;
            $data = $table->find(['title'=> new \MongoDB\BSON\Regex("$search")],[
                'projection' => ['content'=>0],
                // 分页取数据
                'skip' => $skip,
                'limit' => $pagesize,
                'sort' => ['click' => -1]
            ]);
        }else{
            $data = $table->find([],[
                'projection' => ['content'=>0],
                // 分页取数据
                'skip' => $skip,
                'limit' => $pagesize,
                'sort' => ['click' => -1]
            ]);
        }
            
        //将获取的数据对象转换为数组
        $data = $data->toArray();
        
        // var_dump($data);
            
        // 显示页脚
        $fpage = $page->fpage();

        //头像获取
        $username = getusername();
        // var_dump($username);
        $tableusers = $db->users;
        $obj = $tableusers->findOne(
            ['username'=>$username],
            ['projection' => ['path'=>1,'_id'=>0]]
        );
        $head = $obj->path;
        // var_dump($head);
        return view('',compact('data','fpage','head'));

        // var_dump(session('admin:user'));
        // // 引用封装的助手函数
        // $redis = getRedis();
        // //取出zset的所有id
        // $ids = $redis->zrange('news:zset:id',0,-1);
        // // var_dump($ids);
        // foreach ($ids as $id){
        //     $key = 'news:id:'.$id;
        //     $one = $redis->hgetall($key);
        //     $data[] = $one;
        // }
        // var_dump($data);
        // if($data==[]){
        //     return view('');
        // }else{
            // return view('',compact('data'));
        // }
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {

        return view('');
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //获取页面上传的数据（数组）
        $data = input('post.');
        $data['ctime'] = time();
        $data['click'] = 0;
        
        //进行数据的验证
        $rules = [
            "title|新闻标题" => "require",
            "desn|新闻描述" => "require",
            "author|新闻作者" => "require",
            "content|新闻内容" => "require"
        ];
        $res = $this->validate($data,$rules);
        // var_dump($res);
        if($res !== true){
            $this->error($res);
            exit;
        }



        // var_dump($data);
        $db = getMongoDB();
        $table = $db->news;
        $r = $table->insertOne($data);
        echo '<pre>';

        // $redis

        return $this->redirect(url('admin/index/index'));



        // // //连接$redis数据库
        // // $redis = new \Redis();
        // // //读取config.php文件中的redis配置
        // // $config_redis = config('redis');
        // // $redis->connect($config_redis['host'],$config_redis['port'],$config_redis['timeout']);
        // // //进行数据库口令验证
        // // $redis->auth($config_redis['auth']);
        
        // // 引用封装的助手函数
        // $redis = getRedis();

        // // 键名：news:id  用来记录自增长id(始终都只有一个数据，因为下一次会将上一次的数据覆盖)
        // $skey = "news:id";
        // $id = $redis->incr($skey);

        // // 键名：news:id:自增长id  设置hash的键名
        // $hkey = "news:id:".$id;
        // // hash类型以数组形式存入数据,并设置它的id为上面获取到的自增长id
        // $data['id'] = $id;
        // $redis->hmset($hkey,$data);

        // // 把data的id记录在zset类型的表中，方便以后删除
        // $zkey = "news:zset:id";
        // $redis->zadd($zkey,$id,$id);

        // $this->success('新闻添加成功',url('admin/news/index'));
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($_id)
    {
        // var_dump($_id);
        //获取前台传入的_id值
        $_id = input('_id');
        // var_dump($_id);

        //将存储数据存入redis
        $redis = getRedis();
        $visitkey = 'id:'.$_id;
        if(!$redis->exists($visitkey)){
            $redis->set($visitkey,0);
        }
        $click = $redis->incr($visitkey);
        // var_dump($click);


        $db = getMongoDB();
        $table = $db->news;
        $one = $table->findOne(['_id' => new \MongoDB\BSON\ObjectID($_id)]);
        $table->updateOne(
            ['_id' => new \MongoDB\BSON\ObjectID($_id)],
            ['$set' => ['click' => $click]],
            // 没有找到是否添加数据 false否添加
            [ 'upsert'  =>  false ]
        );
        // echo '<pre>';
        // var_dump($one);
        // $one['_id'] =  '';

        // var_dump($one);
        $comments = [];
        $comments = $db->comment->find(['news_id' => $_id]);
        // var_dump($comments);
        // die;

        return view('',compact('one','comments'));
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit()
    {
        //获取前台传入的_id值
        $_id = input('_id');
        // var_dump($_id);

        $db = getMongoDB();
        $table = $db->news;
        $one = $table->findOne(['_id' => new \MongoDB\BSON\ObjectID($_id)]);

        // echo '<pre>';
        // var_dump($one);
        // $one['_id'] =  '';

        return view('',compact('one'));

        // $table->deleteMany(['_id'=> new \MongoDB\BSON\ObjectID($_id)]);
        // $redis = getRedis();
        // $hkey = "news:id:".$id;
        // $one = $redis->hgetall($hkey);
        // return view('',compact('one'));
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update($_id)
    {
        echo '<pre>';
        var_dump(input());
        $data = input();
        $db = getmongoDB();
        $table = $db->news;

        unset($data['_id']);

        $table->updateOne(
            ['_id' => new \MongoDB\BSON\ObjectID($_id)],
            ['$set' => $data],
            // 没有找到是否添加数据 false否添加
            [ 'upsert'  =>  false ]
        );

        return $this->redirect(url('admin/index/index'));


        // // var_dump(input());
        // $id = input('id');
        // $hkey = "news:id:".$id;
        // //获取页面上传的数据（数组）
        // $data = input();
        // //进行数据的验证
        // $rules = [
        //     "title|新闻标题" => "require",
        //     "desn|新闻描述" => "require",
        //     "author|新闻作者" => "require",
        //     "content|新闻内容" => "require"
        // ];
        // $res = $this->validate($data,$rules);
        // // var_dump($res);
        // if($res !== true){
        //     $this->error($res);
        //     exit;
        // }

        // $redis = getRedis();
        // $redis->hmset($hkey,$data);
        
        // $this->success('新闻编辑成功',url('admin/news/index'));
        
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete()
    {
        //获取前台传入的_id值
        $_id = input('_id');
        // var_dump($_id);

        $db = getMongoDB();
        $table = $db->news;

        $table->deleteMany(['_id'=> new \MongoDB\BSON\ObjectID($_id)]);

        $response = [
            "msg" => "删除成功",
            "code" => "202"
        ];

        return $response;
        // // 引用封装的助手函数
        // $redis = getRedis();

        // //获取用户指定id所对应数据的key
        // $hkey = "news:id:".$id;
        // //删除hash表中的数据
        // $redis->del($hkey);

        // //删除zset表中的数据
        // $zkey = "news:zset:id";
        // $redis->zrem($zkey,$id);

        // $response = [
        //     "msg" => "删除成功",
        //     "code" => "202"
        // ];
        // return $response;
    }

    //删除全部
    // public function deleteall(){
    //     $redis = getRedis();
    //     $skey = $redis->get("news:id");
    //     for($i=0;$i<=$skey;$i++){
    //         $redis->del("news:id:".$i);
    //     }
    //     $redis->del("news:zset:id");
    //     $redis->del("news:id");
    //     $this->success('删除全部成功',url("admin/news/index"));
    // }

    //选中删除
    public function deletecheck(){
        $str = input('str');

        $arr = explode(',',$str);
        var_dump($arr);

        $db = getMongoDB();
        $table = $db->news;
        foreach($arr as $k => $_id){
            $table->deleteMany(['_id'=> new \MongoDB\BSON\ObjectID($_id)]);
        }
    }

    //修改密码的页面展示
    public function newpassview(){
        //获取用户名
        $session = $_SESSION['think'];
        // var_dump($session);
        reset($session);
        // echo '<br>';

        $hostname = current($session);
        // var_dump($hostname);
        reset($hostname);
        // echo '<br>';

        $username = key($hostname);
        // var_dump($username);
        return view('',compact('username'));
    }

    //修改密码
    public function newpassupdate(){
        $up = input('post.');
        // var_dump($up);
        $username = $up['username'];
        $db = getMongoDB();
        $table = $db->users;

        $table->updateOne(
            ['username' => $username],
            ['$set' => $up],
            ['upsert' => false]
        );
        
        return $this->redirect(url('admin/index/index'));

    }

    //查看登录日志
    public function loginlog(){
        $db = getMongoDB();
        $table = $db->loginlog;
        $data = $table->find([]);
        // echo '<pre>';
        // var_dump($data);
        return view('',compact('data'));
    }

    public function headview(){
        return view();
    }
     # ---------------------- 图 片 上 传 ----------------------
    //return string  返回文件路径
    public function headupload(){
        // 获取文件
        $file = request()->file('head');
        // var_dump($file);die;
        if(empty($file)){
            $this->error('请上传logo图片');
        }
        // 移动文件
        $res = $file -> validate(['size'=>2097152,'ext'=>'jpg,jpeg,png','type'=>'image/jpeg,image/png'])->move(ROOT_PATH.'public'.DS.'uploads');
        // 判断结果
        if($res){
            //成功返回路径
            // var_dump($res);
            // die;
            $path = DS.'uploads'.DS.$res->getSaveName();
            \think\Image::open('.' . $path)->thumb(150,150)->save('.' . $path);
            // var_dump($path);
            $path = str_replace('\\','/',$path);     //因为是给前端使用，所以要转为/
            var_dump($path);
            //获取用户名
            $session = $_SESSION['think'];
            // var_dump($session);
            reset($session);
            // echo '<br>';

            $hostname = current($session);
            // var_dump($hostname);
            reset($hostname);
            // echo '<br>';

            $username = key($hostname);
            var_dump($username); 
            // $up['username'] = $username;
            // $up['path'] = $path;
            $db = getMongoDB();
            $table = $db->users;
            $table->updateOne(
                ['username' => $username],
                ['$set' => ['path'=>$path]],
                ['upsert' => false]
            );
            $this->redirect(url('admin/index/index'));

        }else{
            $this->error('图片上传失败');
        }
    }

    //添加新闻评论
    public function addcomment(){
        $_id = input('_id');
        var_dump($_id);
        $comment = input('comment');
        var_dump($comment);

        $username = getusername();

        $db = getMongoDB();
        $table = $db->comment;
        $table->insertOne(['news_id'=>$_id,'comment'=>$comment,'username'=>$username]);
        $this->redirect(url('admin/index/read',['_id'=>$_id]));
    }

    //删除新闻评论
    public function delcomment($_id,$news_id){
        // var_dump($_id);
        // var_dump($news_id);die;
        $db = getMongoDB();
        $table = $db->comment;
        $table->deleteMany(['_id'=> new \MongoDB\BSON\ObjectID($_id)]);
        $this->redirect(url('admin/index/read',['_id'=>$news_id]));
    }

    //回复评论
    public function repcomment($news_id){
        // var_dump(input());

        $purpose = input('purpose');
        $repcomment = input('repcomment');
        // var_dump($repcomment);
        $comment = $repcomment . $purpose;
        var_dump($comment);
        $username = getusername();

        $db = getMongoDB();
        $table = $db->comment;
        $table->insertOne(['news_id'=>$news_id,'comment'=>$comment,'username'=>$username]);
        $this->redirect(url('admin/index/read',['_id'=>$news_id]));
    }
}


