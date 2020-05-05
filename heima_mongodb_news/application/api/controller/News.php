<?php

namespace app\api\controller;
use MongoDB\Client;
use think\Controller;
use think\Request;
use app\vue_org\Page;
class News extends Controller
{
    public function index(){
        $search = input('get.search');
        // var_dump($search);
        // $data = [];

        $db = getMongoDB();
        $table = $db->news;
        
        // 分页
        // $pagesize：每页显示的数据条数
        $pagesize = 2;

        if($search){
            // $total：数据库中符合条件的数据总条数
            $total = $table->count(['title'=> new \MongoDB\BSON\Regex("$search")],[
                'projection' => ['content'=>0],
                // 分页取数据
                'skip' => $skip,
                'limit' => $pagesize
            ]);
            // 调用封装好的org/Page.php类
            $page = new Page($total,2);
            // $skip：表示在当前页之前所有页具有的数据总条数
            $skip = $page->offset;

            // $data = $table->find(['title'=>array("/$search/i")],[
            $data = $table->find(['title'=> new \MongoDB\BSON\Regex("$search")],[
                'projection' => ['content'=>0],
                // 分页取数据
                'skip' => $skip,
                'limit' => $pagesize
            ]);
        }else{
            // $total：数据库中的数据总条数
            $total = $table->count();
            // 调用封装好的org/Page.php类
            $page = new Page($total,2);
            // $skip：表示在当前页之前所有页具有的数据总条数
            $skip = $page->offset;
            $data = $table->find([],[
                'projection' => ['content'=>0],
                // 分页取数据
                'skip' => $skip,
                'limit' => $pagesize
            ]);
        }
            
        //将获取的数据对象转换为数组
        $data = $data->toArray();
            
        // 显示页脚
        $fpage = $page->fpage();

        $ret[] = $data;
        $ret[] = $fpage;

        $ret = [
            'data'=>$data,
            'fpage'=>$fpage,
        ];

        return api($ret);
        
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
    public function save()
    {
        //获取页面上传的数据（数组）
        $data = input();
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

        // //连接$redis数据库
        // $redis = new \Redis();
        // //读取config.php文件中的redis配置
        // $config_redis = config('redis');
        // $redis->connect($config_redis['host'],$config_redis['port'],$config_redis['timeout']);
        // //进行数据库口令验证
        // $redis->auth($config_redis['auth']);
        
        // 引用封装的助手函数
        $redis = getRedis();

        // 键名：news:id  用来记录自增长id(始终都只有一个数据，因为下一次会将上一次的数据覆盖)
        $skey = "news:id";
        $id = $redis->incr($skey);

        // 键名：news:id:自增长id  设置hash的键名
        $hkey = "news:id:".$id;
        // hash类型以数组形式存入数据,并设置它的id为上面获取到的自增长id
        $data['id'] = $id;
        $redis->hmset($hkey,$data);

        // 把data的id记录在zset类型的表中，方便以后删除
        $zkey = "news:zset:id";
        $redis->zadd($zkey,$id,$id);
        $this->redirect('http://heimavue.fuzzbear.club/index.html',301);
        // $this->success('新闻添加成功',url('http://heimavue.fuzzbear.club/index.html'));
        return api();
    }


        /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        var_dump($id);
        $id = input('id');

        // 引用封装的助手函数
        $redis = getRedis();

        //获取用户指定id所对应数据的key
        $hkey = "news:id:".$id;
        //删除hash表中的数据
        $redis->del($hkey);

        //删除zset表中的数据
        $zkey = "news:zset:id";
        $redis->zrem($zkey,$id);

        return api();
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $redis = getRedis();
        $hkey = "news:id:".$id;
        $data = $redis->hgetall($hkey);
        return api($data);
    }

        /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $redis = getRedis();
        $hkey = "news:id:".$id;
        $one = $redis->hgetall($hkey);
        return view('',compact('one'));
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update($id)
    {
        // var_dump(input());
        $id = input('id');
        $hkey = "news:id:".$id;
        //获取页面上传的数据（数组）
        $data = input();
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

        $redis = getRedis();
        $redis->hmset($hkey,$data);
        
        $this->redirect('http://heimavue.fuzzbear.club/index.html',301);
        return api();
        // $this->success('新闻编辑成功',url('admin/news/index'));
        
    }
    //删除全部
    public function deleteall(){
        $redis = getRedis();
        $skey = $redis->get("news:id");
        for($i=0;$i<=$skey;$i++){
            $redis->del("news:id:".$i);
        }
        $redis->del("news:zset:id");
        $redis->del("news:id");
        // $this->redirect('http://heimavue.fuzzbear.club/index.html',301);
        return api();
        // $this->success('删除全部成功',url("admin/news/index"));
    }
}
