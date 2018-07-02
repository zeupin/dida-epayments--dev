<?php
/**
 * Dida Framework  -- A Rapid Development Framework
 * Copyright (c) Zeupin LLC. (http://zeupin.com)
 *
 * Licensed under The MIT License.
 * Redistributions of files must retain the above copyright notice.
 */

namespace Dida\EPayments;

/**
 * SmartPay
 *
 * 官方API文档 https://www.kiwifast.com/doc/
 */
class SmartPay
{
    /**
     * Version
     */
    const VERSION = '20180702';

    /**
     * API的网址
     */
    static $api_url = 'https://www.kiwifast.com/api/v1/info/smartpay';

    /**
     * 商户账号
     */
    private $merchant_id = null;

    /**
     * 签名密钥
     */
    private $sign_key = null;


    /**
     * 构造函数
     *
     * @param string $merchant_id
     * @param string $sign_key
     */
    public function __construct($merchant_id, $sign_key)
    {
        $this->merchant_id = $merchant_id;
        $this->sign_key = $sign_key;
    }


    /**
     * 跳转聚合支付接口
     */
    public function createSmartPay(array $input)
    {
        // 字段列表
        $fields = [
            'merchant_id'      => [true, '商户ID'],
            'increment_id'     => [true, '订单号'],
            'grandtotal'       => [true, '订单金额'],
            'currency'         => [true, '币种'],
            'return_url'       => [true, '返回链接'],
            'notify_url'       => [true, '通知链接'],
            'valid_mins'       => [false, '有效分钟'],
            'payment_channels' => [false, '支付通道'],
            'subject'          => [true, '交易标题'],
            'describe'         => [true, '交易描述'],
            'service'          => [true, '请求服务'],
            'nonce_str'        => [true, '随机字符串'],
        ];

        // 预置字段
        $presets = [
            'merchant_id' => $this->merchant_id,
            'service'     => 'create_smart_pay',
            'nonce_str'   => $this->randomString(16),
        ];

        // 生成临时数组
        $temp = array_merge($input, $presets);
    }


    /**
     * 聚合小程序支付接口
     *
     * @param array $input
     */
    public function createMiniAppPay(array $input)
    {
        // 字段列表
        $fields = [
            'merchant_id'      => [true, '商户ID'],
            'increment_id'     => [true, '订单号'],
            'sub_appid'        => [true, '小程序APPID'],
            'sub_openid'       => [true, '用户的openid'],
            'grandtotal'       => [true, '订单金额'],
            'currency'         => [true, '币种'],
            'valid_mins'       => [false, '有效分钟'],
            'payment_channels' => [true, '支付通道'],
            'notify_url'       => [true, '通知链接'],
            'subject'          => [false, '交易标题'],
            'describe'         => [true, '交易描述'],
            'nonce_str'        => [true, '随机字符串'],
            'service'          => [true, '请求服务'],
        ];

        // 预置字段
        $presets = [
            'merchant_id' => $this->merchant_id,
            'service'     => 'create_miniapp_pay',
            'nonce_str'   => $this->randomString(16),
        ];

        // 生成临时数组
        $temp = array_merge($input, $presets);
    }


    /**
     * 签名
     *
     * @param array $data
     */
    protected function sign(array $data)
    {
        // 去除签名相关的字段
        unset($data['sign_type'], $data['signature']);

        // 把键值按照ASCII码排序
        ksort($data);

        // 构造待签名串
        $sign_str = [];
        foreach ($data as $k => $v) {
            $sign_str[] = "$k=$v";
        }
        $sign_str = implode('&', $sign_str);

        // 加上sign_key
        $sign_str = $sign_str . $this->sign_key;

        // 签名
        $signature = md5($sign_str);

        // 输出
        return $signature;
    }


    /**
     * 生成一个带签名的查询串
     *
     * @param array $data
     */
    protected function makeSignedQueryString(array $data)
    {
        // 去除签名相关的字段
        unset($data['sign_type'], $data['signature']);

        // 生成签名
        $signature = $this->sign($data);

        // 构造最终串
        $final = [];
        foreach ($data as $k => $v) {
            $final[] = "$k=" . urlencode($v);
        }
        $final[] = "signature=$signature";
        $final[] = "sign_type=MD5";
        $final = implode('&', $final);

        return $final;
    }


    /**
     * 检查传入的数组的签名是否正确
     *
     * @param array $data
     */
    public function checkSign(array $data)
    {
        // 如果没有signature字段，直接返回失败
        if (!array_key_exists('signature', $data)) {
            return false;
        }

        // 保存一下原始签名
        $origin_signature = $data["signature"];

        // 去除签名相关的字段
        unset($data['sign_type'], $data['signature']);

        // 生成签名
        $signature = $this->sign($data);

        // 检查两个签名是否一致
        if ($signature == $origin_signature) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 生成随机字符串
     *
     * @param int $num
     * @param string $set
     */
    protected function randomString($num = 32, $set = null)
    {
        if (!$set) {
            $set = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        }
        $len = strlen($set);
        $r = [];
        for ($i = 0; $i < $num; $i++) {
            $r[] = substr($set, mt_rand(0, $len - 1), 1);
        }
        return implode('', $r);
    }
}
