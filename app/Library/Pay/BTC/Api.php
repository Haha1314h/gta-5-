<?php
namespace App\Library\Pay\BTC; use App\Library\CurlRequest; use App\Library\Pay\ApiInterface; use Illuminate\Support\Facades\Log; class Api implements ApiInterface { private $url_notify = ''; private $url_return = ''; public function __construct($sp5d3f49) { $this->url_notify = SYS_URL_API . '/pay/notify/' . $sp5d3f49; $this->url_return = SYS_URL . '/pay/return/' . $sp5d3f49; } function goPay($spc8c9ef, $sp4510be, $sp930bb6, $sp9a31c1, $spfa6477) { $spab0074 = CurlRequest::get('https://api.blockchain.info/tobtc?currency=CNY&value=' . sprintf('%.2f', $spfa6477 / 100)); if (!$spab0074) { Log::error('Pay.BTC.goPay, get price error:' . @$spab0074); throw new \Exception('获取BTC价格失败，请联系客服'); } $sp497811 = CurlRequest::get('https://api.blockchain.info/v2/receive?xpub=' . $spc8c9ef['xpub'] . '&callback=' . urlencode($this->url_notify . '?secret=' . $spc8c9ef['secret']) . '&key=' . $spc8c9ef['key']); $sp3d77e6 = @json_decode($sp497811, true); if (!$sp3d77e6 || !isset($sp3d77e6['address'])) { if ($sp3d77e6['description'] === 'Gap between last used address and next address too large. This might make funds inaccessible.') { throw new \Exception('钱包地址到达限制, 请等待之前的用户完成付款'); } Log::error('Pay.BTC.goPay, get address error:' . @$sp497811); throw new \Exception('获取BTC地址失败，请联系客服'); } $spa5b547 = 'bitcoin:' . $sp3d77e6['address'] . '?amount=' . $spab0074; if (\App\Order::wherePayTradeNo($spa5b547)->exists()) { throw new \Exception('支付失败, 当前钱包地址重复'); } \App\Order::whereOrderNo($sp4510be)->update(array('pay_trade_no' => $spa5b547)); header('location: /qrcode/pay/' . $sp4510be . '/btc?url=' . urlencode(json_encode(array('address' => $sp3d77e6['address'], 'amount' => $spab0074)))); die; } function verify($spc8c9ef, $sp53cf01) { $sp412d81 = isset($spc8c9ef['isNotify']) && $spc8c9ef['isNotify']; if ($sp412d81) { if (@$_GET['secret'] !== $spc8c9ef['secret']) { echo 'error'; return false; } if (isset($_GET['confirmations'])) { $spacd648 = $_GET['address']; $spa5b547 = 'bitcoin:' . $spacd648 . '?amount=' . rtrim(rtrim(sprintf('%.8f', $_GET['value'] / 100000000.0), '0'), '.'); $sp61541f = \App\Order::wherePayTradeNo($spa5b547)->first(); if (!$sp61541f) { echo 'error'; Log::error('Pay.BTC.verify, cannot find order:' . json_encode(array('url' => $spa5b547, 'params' => $_GET))); return false; } $sp5f8fd4 = $spa5b547; $sp53cf01($sp61541f->order_no, $sp61541f->paid, $sp5f8fd4); } echo '*ok*'; return true; } else { return false; } } }