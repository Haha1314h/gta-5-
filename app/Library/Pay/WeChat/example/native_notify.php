<?php
ini_set('date.timezone', 'Asia/Shanghai'); error_reporting(E_ERROR); require_once '../lib/WxPay.Api.php'; require_once '../lib/WxPay.Notify.php'; require_once '../WxLog.php'; class NativeNotifyCallBack extends WxPayNotify { public function unifiedorder($sp23551f, $spcf7e6d) { $sp5bf110 = new WxPayUnifiedOrder(); $sp5bf110->SetBody('test'); $sp5bf110->SetAttach('test'); $sp5bf110->SetOut_trade_no(WxPayConfig::MCHID . date('YmdHis')); $sp5bf110->SetTotal_fee('1'); $sp5bf110->SetTime_start(date('YmdHis')); $sp5bf110->SetTime_expire(date('YmdHis', time() + 600)); $sp5bf110->SetGoods_tag('test'); $sp5bf110->SetNotify_url('http://paysdk.weixin.qq.com/example/notify.php'); $sp5bf110->SetTrade_type('NATIVE'); $sp5bf110->SetOpenid($sp23551f); $sp5bf110->SetProduct_id($spcf7e6d); $sp820aff = WxPayApi::unifiedOrder($sp5bf110); \WxLog::DEBUG('unifiedorder:' . json_encode($sp820aff)); return $sp820aff; } public function NotifyProcess($sp69c4ce, &$sp3a6f3c) { \WxLog::DEBUG('call back:' . json_encode($sp69c4ce)); if (!array_key_exists('openid', $sp69c4ce) || !array_key_exists('product_id', $sp69c4ce)) { $sp3a6f3c = '回调数据异常'; return false; } $spabb08a = $sp69c4ce['openid']; $spcf7e6d = $sp69c4ce['product_id']; $sp820aff = $this->unifiedorder($spabb08a, $spcf7e6d); if (!array_key_exists('appid', $sp820aff) || !array_key_exists('mch_id', $sp820aff) || !array_key_exists('prepay_id', $sp820aff)) { $sp3a6f3c = '统一下单失败'; return false; } $this->SetData('appid', $sp820aff['appid']); $this->SetData('mch_id', $sp820aff['mch_id']); $this->SetData('nonce_str', WxPayApi::getNonceStr()); $this->SetData('prepay_id', $sp820aff['prepay_id']); $this->SetData('result_code', 'SUCCESS'); $this->SetData('err_code_des', 'OK'); return true; } } \WxLog::DEBUG('begin notify!'); $sp3f3238 = new NativeNotifyCallBack(); $sp3f3238->Handle(true);