<?php
require_once '../lib/WxPay.Api.php'; class MicroPay { public function pay($spb7bc91) { $sp820aff = WxPayApi::micropay($spb7bc91, 5); if (!array_key_exists('return_code', $sp820aff) || !array_key_exists('out_trade_no', $sp820aff) || !array_key_exists('result_code', $sp820aff)) { echo '接口调用失败,请确认是否输入是否有误！'; throw new WxPayException('接口调用失败！'); } $sp4510be = $spb7bc91->GetOut_trade_no(); if ($sp820aff['return_code'] == 'SUCCESS' && $sp820aff['result_code'] == 'FAIL' && $sp820aff['err_code'] != 'USERPAYING' && $sp820aff['err_code'] != 'SYSTEMERROR') { return false; } $sp2ead0e = 10; while ($sp2ead0e > 0) { $spc15bb5 = 0; $sp0f2b96 = $this->query($sp4510be, $spc15bb5); if ($spc15bb5 == 2) { sleep(2); continue; } else { if ($spc15bb5 == 1) { return $sp0f2b96; } else { return false; } } } if (!$this->cancel($sp4510be)) { throw new WxpayException('撤销单失败！'); } return false; } public function query($sp4510be, &$sp417515) { $sp6bb204 = new WxPayOrderQuery(); $sp6bb204->SetOut_trade_no($sp4510be); $sp820aff = WxPayApi::orderQuery($sp6bb204); if ($sp820aff['return_code'] == 'SUCCESS' && $sp820aff['result_code'] == 'SUCCESS') { if ($sp820aff['trade_state'] == 'SUCCESS') { $sp417515 = 1; return $sp820aff; } else { if ($sp820aff['trade_state'] == 'USERPAYING') { $sp417515 = 2; return false; } } } if ($sp820aff['err_code'] == 'ORDERNOTEXIST') { $sp417515 = 0; } else { $sp417515 = 2; } return false; } public function cancel($sp4510be, $sp24f0f2 = 0) { if ($sp24f0f2 > 10) { return false; } $sp614b0d = new WxPayReverse(); $sp614b0d->SetOut_trade_no($sp4510be); $sp820aff = WxPayApi::reverse($sp614b0d); if ($sp820aff['return_code'] != 'SUCCESS') { return false; } if ($sp820aff['result_code'] != 'SUCCESS' && $sp820aff['recall'] == 'N') { return true; } else { if ($sp820aff['recall'] == 'Y') { return $this->cancel($sp4510be, ++$sp24f0f2); } } return false; } }