<?php
namespace App\Library\Pay\MoTonePay; use App\Library\Pay\ApiInterface; class Api implements ApiInterface { private $url_notify = ''; private $url_return = ''; public function __construct($sp5d3f49) { $this->url_notify = SYS_URL_API . '/pay/notify/' . $sp5d3f49; $this->url_return = SYS_URL . '/pay/return/' . $sp5d3f49; } function goPay($spc8c9ef, $sp4510be, $sp930bb6, $sp9a31c1, $spfa6477) { $sp6f1ff6 = sprintf('%.2f', $spfa6477 / 100); $spc801f8 = '1.0'; $sp02f719 = $spc8c9ef['payway']; $sp0747b4 = '0'; $sp1e1df1 = md5('version=' . $spc801f8 . '&customerid=' . $spc8c9ef['id'] . '&total_fee=' . $sp6f1ff6 . '&sdorderno=' . $sp4510be . '&notifyurl=' . $this->url_notify . '&returnurl=' . $this->url_return . '&' . $spc8c9ef['key']); ?>
        <!doctype html>
        <html>
        <head>
            <meta charset="utf8">
            <title>正在转到付款页</title>
        </head>
        <body onLoad="document.pay.submit()">
        <form name="pay" action="http://www.motonepay.com/apisubmit" method="post">
            <input type="hidden" name="version" value="<?php  echo $spc801f8; ?>
">
            <input type="hidden" name="customerid" value="<?php  echo $spc8c9ef['id']; ?>
">
            <input type="hidden" name="sdorderno" value="<?php  echo $sp4510be; ?>
">
            <input type="hidden" name="total_fee" value="<?php  echo $sp6f1ff6; ?>
">
            <input type="hidden" name="paytype" value="<?php  echo $sp02f719; ?>
">
            <input type="hidden" name="notifyurl" value="<?php  echo $this->url_notify; ?>
">
            <input type="hidden" name="returnurl" value="<?php  echo $this->url_return; ?>
">
            <input type="hidden" name="sign" value="<?php  echo $sp1e1df1; ?>
">
            <input type="hidden" name="get_code" value="<?php  echo $sp0747b4; ?>
">
        </form>
        </body>
        </html>
        <?php  die; } function verify($spc8c9ef, $sp53cf01) { $sp412d81 = isset($spc8c9ef['isNotify']) && $spc8c9ef['isNotify']; if ($sp412d81) { $spf9795c = $_POST['status']; $spdb7403 = $_POST['customerid']; $sp162cde = $_POST['sdorderno']; $sp6f1ff6 = $_POST['total_fee']; $sp02f719 = $_POST['paytype']; $spb3e47d = $_POST['sdpayno']; $sp1e1df1 = $_POST['sign']; $sp922f2b = md5('customerid=' . $spdb7403 . '&status=' . $spf9795c . '&sdpayno=' . $spb3e47d . '&sdorderno=' . $sp162cde . '&total_fee=' . $sp6f1ff6 . '&paytype=' . $sp02f719 . '&' . $spc8c9ef['key']); if ($sp1e1df1 == $sp922f2b) { if ($spf9795c == '1') { $sp6f1ff6 = (int) round($sp6f1ff6 * 100); $sp53cf01($sp162cde, $sp6f1ff6, $spb3e47d); echo 'success'; return true; } else { echo 'success'; } } else { echo 'sign_err'; } } else { if (!empty($spc8c9ef['out_trade_no'])) { return false; } $spf9795c = $_GET['status']; $spdb7403 = $_GET['customerid']; $sp162cde = $_GET['sdorderno']; $sp6f1ff6 = $_GET['total_fee']; $sp02f719 = $_GET['paytype']; $spb3e47d = $_GET['sdpayno']; $sp1e1df1 = $_GET['sign']; $sp922f2b = md5('customerid=' . $spdb7403 . '&status=' . $spf9795c . '&sdpayno=' . $spb3e47d . '&sdorderno=' . $sp162cde . '&total_fee=' . $sp6f1ff6 . '&paytype=' . $sp02f719 . '&' . $spc8c9ef['key']); if ($sp1e1df1 == $sp922f2b) { if ($spf9795c == '1') { $sp6f1ff6 = (int) round($sp6f1ff6 * 100); $sp53cf01($sp162cde, $sp6f1ff6, $spb3e47d); return true; } else { throw new \Exception('付款失败'); } } else { throw new \Exception('sign error'); } } return false; } }