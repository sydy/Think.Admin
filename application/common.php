<?php

// +----------------------------------------------------------------------
// | Think.Admin
// +----------------------------------------------------------------------
// | 版权所有 2016~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/Think.Admin
// +----------------------------------------------------------------------


use app\admin\model\Node as NodeModal;
use think\Config;
use think\Db;
use Wechat\Loader;

/**
 * 打印输出数据到文件
 * @param mixed $data
 * @param bool $replace
 * @param string|null $pathname
 */
function p($data, $replace = false, $pathname = NULL) {
    is_null($pathname) && $pathname = RUNTIME_PATH . date('Ymd') . '.txt';
    $str = (is_string($data) ? $data : (is_array($data) || is_object($data)) ? print_r($data, true) : var_export($data, true)) . "\n";
    $replace ? file_put_contents($pathname, $str) : file_put_contents($pathname, $str, FILE_APPEND);
}

/**
 * 获取微信操作对象
 * @param string $type
 * @return \Wechat\WechatReceive|\Wechat\WechatUser
 */
function & load_wechat($type = '') {
    static $wechat = array();
    $index = md5(strtolower($type));
    if (!isset($wechat[$index])) {
        $config = Config::get('wechat');
        $config['cachepath'] = CACHE_PATH . 'wechat' . DS;
        $wechat[$index] = &Loader::get($type, $config);
    }
    return $wechat[$index];
}

/**
 * 安全URL编码
 * @param array $data
 * @return string
 */
function encode($data) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(serialize($data)));
}

/**
 * 安全URL解码
 * @param string $string
 * @return string
 */
function decode($string) {
    $data = str_replace(['-', '_'], ['+', '/'], $string);
    $mod4 = strlen($data) % 4;
    !!$mod4 && $data .= substr('====', $mod4);
    return unserialize(base64_decode($data));
}

/**
 * RBAC节点权限验证
 * @param string $node
 * @return bool
 */
function auth($node) {
    return NodeModal::checkAuthNode($node);
}

/**
 * 从配置表读取配置信息
 * @param string $name
 * @return string
 */
function sysconf($name) {
    static $config = [];
    if (empty($config)) {
        foreach (Db::name('SystemConfig')->select() as $vo) {
            $config[$vo['name']] = $vo['value'];
        }
    }
    return isset($config[$name]) ? $config[$name] : '';
}

/**
 * array_column 函数兼容
 */
if (!function_exists("array_column")) {

    function array_column(array &$rows, $column_key, $index_key = null) {
        $data = [];
        foreach ($rows as $row) {
            if (empty($index_key)) {
                $data[] = $row[$column_key];
            } else {
                $data[$row[$index_key]] = $row[$column_key];
            }
        }
        return $data;
    }

}