<?php

namespace app\admin\controller;

use think\Controller;

class Base extends Controller
{
    //判断用户是否登录
    public function _initialize(){
        if(!session('?hostname')){
            return $this->error('请先登录',url('admin/login/index'));
            // return $this->redirect(url('admin/login/login'));
        }
    }
}
// think|a:1:
// {
//     s:5:"admin";a:1:
//     {
//         s:4:"user";C:26:"MongoDB\Model\BSONDocument":168:
//         {
//             x:i:2;a:3:
//             {
//                 s:3:"_id";C:21:"MongoDB\BSON\ObjectId":48:
//                 {
//                     a:1:
//                     {
//                         s:3:"oid";s:24:"5ea974ae51730000f60028c4";
//                     }
//                 }s:8:"username";s:5:"admin";s:8:"password";s:6:"123123";
//             }
//             ;m:a:0:{}
//         }
//     }
// }

