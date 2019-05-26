<?php
namespace App\Library\Pay\Payjs; use App\Library\Pay\ApiInterface; require_once __DIR__ . '/sdk/Payjs.php'; use Xhat\Payjs\Payjs; class Api implements ApiInterface { private $url_notify = ''; private $url_return = ''; public function __construct($sp5d3f49) { $this->url_notify = SYS_URL_API . '/pay/notify/' . $sp5d3f49; $this->url_return = SYS_URL . '/pay/return/' . $sp5d3f49; } function goPay($spc8c9ef, $sp4510be, $sp930bb6, $sp9a31c1, $spfa6477) { if (!isset($spc8c9ef['mchid'])) { throw new \Exception('请填写mchid'); } if (!isset($spc8c9ef['key'])) { throw new \Exception('请填写key'); } $sp5b516a = new Payjs($spc8c9ef); $sp2688c6 = strtolower($spc8c9ef['payway']); $sp69c4ce = array('total_fee' => $spfa6477, 'out_trade_no' => $sp4510be, 'body' => $sp4510be, 'notify_url' => $this->url_notify, 'callback_url' => SYS_URL . '/pay/result/' . $sp4510be); if ($sp2688c6 === 'native') { $spc34ab4 = $sp5b516a->native($sp69c4ce); if (@(int) $spc34ab4['return_code'] !== 1) { die('<h1>支付渠道出错: ' . $spc34ab4['msg'] . '</h1>'); } header('location: /qrcode/pay/' . $sp4510be . '/payjs/' . base64_encode($spc34ab4['code_url'])); } elseif ($sp2688c6 === 'cashier') { $spc34ab4 = $sp5b516a->cashier($sp69c4ce); header('Location: ' . $spc34ab4); } else { die('<h1>请填写支付方式</h1>'); } echo '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><title>正在跳转到支付渠道...</title></head><body><h1 style="text-align: center">正在跳转到支付渠道...</h1>'; die; } function verify($spc8c9ef, $sp53cf01) { $sp412d81 = isset($spc8c9ef['isNotify']) && $spc8c9ef['isNotify']; $sp5b516a = new Payjs($spc8c9ef); if ($sp412d81) { $sp820aff = $sp5b516a->checkSign($_POST); echo $sp820aff ? 'success' : 'fail'; } else { $sp820aff = false; } if ($sp820aff) { $sp4510be = $_REQUEST['out_trade_no']; $sp6f1ff6 = $_REQUEST['total_fee']; $spc80011 = $_REQUEST['payjs_order_id']; $sp53cf01($sp4510be, $sp6f1ff6, $spc80011); return true; } return false; } }