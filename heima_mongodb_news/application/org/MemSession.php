<?php

class MemSession {
    // memcached对象
    private static $handler = null;
    // 过期时间 单位是秒
    private static $lifetime = null;
    // 当前时间
    private static $time = null;
    // session名前缀 类的常量
    const NS = 'sess_wuchen_';

    // 初始化
    private static function init(Memcached $memcached) {
        // memcached对象
        self::$handler = $memcached;
        self::$lifetime = 1440;
        self::$time = time();
    }

    // 静态方法，公共对外可能访问，对外的开始方法 session_start()
    public static function start(Memcached $memcached) {
        self::init($memcached);
        // 让session存储在memcached的函数
        session_set_save_handler(
        // __CLASS__ 当前类
            array(__CLASS__, 'open'),
            array(__CLASS__, 'close'),
            array(__CLASS__, 'read'),
            array(__CLASS__, 'write'),
            array(__CLASS__, 'destroy'),
            array(__CLASS__, 'gc')
        );
        session_start();
    }

    public static function open($path, $name) {
        return true;
    }

    public static function close() {
        return true;
    }

    // 获取session  没有数据一定要返回空字符串
    public static function read($PHPSESSID) {
        $out = self::$handler->get(self::session_key($PHPSESSID));
        if ($out === false || $out == null)
            return '';
        return $out;
    }

    // 写session
    public static function write($PHPSESSID, $data) {
        $key = self::session_key($PHPSESSID);
        return self::$handler->set($key, $data, self::$lifetime);
    }

    public static function destroy($PHPSESSID) {
        return self::$handler->delete(self::session_key($PHPSESSID));
    }

    public static function gc($lifetime) {
        return true;
    }

    // 生成key
    private static function session_key($PHPSESSID) {
        $session_key = self::NS . $PHPSESSID;
        return $session_key;
    }
}

$memcached = new Memcached();
$memcached->addServer("127.0.0.1", 11211);
MemSession::start($memcached);
