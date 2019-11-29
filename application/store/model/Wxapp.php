<?php

namespace app\store\model;

use app\common\model\Wxapp as WxappModel;
use think\Cache;

/**
 * 微信小程序模型
 * Class Wxapp
 * @package app\store\model
 */
class Wxapp extends WxappModel
{


    /**
     * 添加小程序支付设置
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-23
     * Time: 11:58
     */
    public function add($data)
    {
        $this->startTrans();
        try {
            // 删除wxapp缓存
            self::deleteCache($data['store_id']);
            // 写入微信支付证书文件
            $this->writeCertPemFiles($data['cert_pem'], $data['key_pem'],$data['store_id']);
            // 更新小程序设置
            $this->allowField(true)->save($data);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }


    /**
     * 更新小程序支付设置
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-22
     * Time: 11:58
     */
    public function edit($data)
    {
        $this->startTrans();
        try {
            // 删除wxapp缓存
            self::deleteCache($data['store_id']);
            // 写入微信支付证书文件
            $this->writeCertPemFiles($data['cert_pem'], $data['key_pem'],$data['store_id']);
            // 更新小程序设置
            $this->allowField(true)->save($data);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 写入cert证书文件
     * @param string $cert_pem
     * @param string $key_pem
     * @return bool
     */
    private function writeCertPemFiles($cert_pem = '', $key_pem = '',$store_id = '')
    {
        if (empty($cert_pem) || empty($key_pem) || empty($store_id)) {
            return false;
        }
        // 证书目录
        $filePath = VENDOR_PATH . 'wxpay/cert/' . $store_id . '/';
        // 目录不存在则自动创建
        if (!is_dir($filePath)) {
            mkdir($filePath, 0755, true);
        }
        // 写入cert.pem文件
        if (!empty($cert_pem)) {
            file_put_contents($filePath . 'apiclient_cert.pem', $cert_pem);
        }
        // 写入key.pem文件
        if (!empty($key_pem)) {
            file_put_contents($filePath . 'apiclient_key.pem', $key_pem);
        }
        return true;
    }


    /**
     * 删除wxapp缓存
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-22
     * Time: 11:58
     */
    public static function deleteCache($store_id = 0)
    {
        return Cache::rm('wxapp_' . $store_id);
    }

}
