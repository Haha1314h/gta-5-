<?php
namespace App\Http\Controllers\Shop; use App\Card; use App\Category; use App\Library\FundHelper; use App\Library\Helper; use App\Product; use App\Library\Response; use App\Library\Pay\Pay as PayApi; use App\Library\Geetest; use App\Mail\OrderShipped; use App\Mail\ProductCountWarn; use App\System; use Carbon\Carbon; use Illuminate\Database\Eloquent\Relations\Relation; use Illuminate\Http\Request; use App\Http\Controllers\Controller; use Illuminate\Support\Facades\Cookie; use Illuminate\Support\Facades\DB; use Illuminate\Support\Facades\Log; use Illuminate\Support\Facades\Mail; class Pay extends Controller { public function __construct() { define('SYS_NAME', config('app.name')); define('SYS_URL', config('app.url')); define('SYS_URL_API', config('app.url_api')); } private $payApi = null; public function goPay($sp6a5e99, $sp51ee1d, $sp508d60, $sp2688c6, $spa9a109) { try { (new PayApi())->goPay($sp2688c6, $sp51ee1d, $sp508d60, $sp508d60, $spa9a109); return self::renderResultPage($sp6a5e99, array('success' => false, 'title' => '请稍后', 'msg' => '支付方式加载中，请稍后')); } catch (\Exception $spbcc446) { return self::renderResultPage($sp6a5e99, array('msg' => $spbcc446->getMessage())); } } function buy(Request $sp6a5e99) { $sp7b3b78 = $sp6a5e99->input('customer'); if (strlen($sp7b3b78) !== 32) { return self::renderResultPage($sp6a5e99, array('msg' => '提交超时，请刷新购买页面并重新提交<br><br>
当前网址: ' . $sp6a5e99->getQueryString() . '
提交内容: ' . var_export($sp7b3b78) . ', 提交长度:' . strlen($sp7b3b78) . '<br>
若您刷新后仍然出现此问题. 请加网站客服反馈')); } if ((int) System::_get('vcode_shop_buy') === 1) { $sp6f1294 = Geetest\API::verify($sp6a5e99->input('geetest_challenge'), $sp6a5e99->input('geetest_validate'), $sp6a5e99->input('geetest_seccode')); if (!$sp6f1294) { return self::renderResultPage($sp6a5e99, array('msg' => '滑动验证超时，请返回页面重试。')); } } $sp0e6d39 = (int) $sp6a5e99->input('category_id'); $spcf7e6d = (int) $sp6a5e99->input('product_id'); $sp8e7e1d = (int) $sp6a5e99->input('count'); $spe16253 = $sp6a5e99->input('coupon'); $sp9dc949 = $sp6a5e99->input('email'); $sp4b1cb7 = (int) $sp6a5e99->input('pay_id'); if (!$sp0e6d39 || !$spcf7e6d) { return self::renderResultPage($sp6a5e99, array('msg' => '请选择商品')); } if (strlen($sp9dc949) < 1) { return self::renderResultPage($sp6a5e99, array('msg' => '请输入联系方式')); } $sp6643b9 = Category::findOrFail($sp0e6d39); $spdbee16 = Product::where('id', $spcf7e6d)->where('category_id', $sp0e6d39)->where('enabled', 1)->with(array('user'))->first(); if ($spdbee16 == null || $spdbee16->user == null) { return self::renderResultPage($sp6a5e99, array('msg' => '该商品未找到，请重新选择')); } if ($spdbee16->password_open) { if ($spdbee16->password !== $sp6a5e99->input('product_password')) { return self::renderResultPage($sp6a5e99, array('msg' => '商品密码输入错误')); } } else { if ($sp6643b9->password_open) { if ($sp6643b9->password !== $sp6a5e99->input('category_password')) { if ($sp6643b9->getTmpPassword() !== $sp6a5e99->input('category_password')) { return self::renderResultPage($sp6a5e99, array('msg' => '分类密码输入错误')); } } } } if ($sp8e7e1d < $spdbee16->buy_min) { return self::renderResultPage($sp6a5e99, array('msg' => '该商品最少购买' . $spdbee16->buy_min . '件，请重新选择')); } if ($sp8e7e1d > $spdbee16->buy_max) { return self::renderResultPage($sp6a5e99, array('msg' => '该商品限购' . $spdbee16->buy_max . '件，请重新选择')); } if ($spdbee16->count < $sp8e7e1d) { return self::renderResultPage($sp6a5e99, array('msg' => '该商品库存不足')); } $spf13a06 = \App\Pay::find($sp4b1cb7); if ($spf13a06 == null || !$spf13a06->enabled) { return self::renderResultPage($sp6a5e99, array('msg' => '支付方式未找到，请重新选择')); } $sp17e589 = $spdbee16->price; if ($spdbee16->price_whole) { $sp484668 = json_decode($spdbee16->price_whole, true); for ($spf69b52 = count($sp484668) - 1; $spf69b52 >= 0; $spf69b52--) { if ($sp8e7e1d >= (int) $sp484668[$spf69b52][0]) { $sp17e589 = (int) $sp484668[$spf69b52][1]; break; } } } $spcaf990 = $sp8e7e1d * $sp17e589; $spa9a109 = $spcaf990; $spa8a421 = 0; $sp6a8574 = null; if ($spdbee16->support_coupon && strlen($spe16253) > 0) { $spd94cd9 = \App\Coupon::where('user_id', $spdbee16->user_id)->where('coupon', $spe16253)->where('expire_at', '>', Carbon::now())->whereRaw('`count_used`<`count_all`')->get(); foreach ($spd94cd9 as $spfa0fd3) { if ($spfa0fd3->category_id === -1 || $spfa0fd3->category_id === $sp0e6d39 && ($spfa0fd3->product_id === -1 || $spfa0fd3->product_id === $spcf7e6d)) { if ($spfa0fd3->discount_type === \App\Coupon::DISCOUNT_TYPE_AMOUNT && $spa9a109 >= $spfa0fd3->discount_val) { $sp6a8574 = $spfa0fd3; $spa8a421 = $spfa0fd3->discount_val; break; } if ($spfa0fd3->discount_type === \App\Coupon::DISCOUNT_TYPE_PERCENT) { $sp6a8574 = $spfa0fd3; $spa8a421 = (int) round($spa9a109 * $spfa0fd3->discount_val / 100); break; } } } if ($sp6a8574 === null) { return self::renderResultPage($sp6a5e99, array('msg' => '优惠券信息错误，请重新输入')); } $spa9a109 -= $spa8a421; } if ($sp6a8574) { $sp6a8574->status = \App\Coupon::STATUS_USED; $sp6a8574->count_used++; $sp6a8574->save(); $sp113942 = '使用优惠券: ' . $sp6a8574->coupon; } else { $sp113942 = null; } $sp968713 = (int) round($spa9a109 * $spf13a06->fee_system); $sp9621e0 = $spa9a109 - $sp968713; $sp51ee1d = \App\Order::unique_no(); \App\Order::insert(array('user_id' => $spdbee16->user_id, 'order_no' => $sp51ee1d, 'product_id' => $spcf7e6d, 'count' => $sp8e7e1d, 'email' => $sp9dc949, 'ip' => Helper::getIP(), 'customer' => $sp7b3b78, 'email_sent' => false, 'cost' => $sp8e7e1d * $spdbee16->cost, 'price' => $spcaf990, 'discount' => $spa8a421, 'paid' => $spa9a109, 'pay_id' => $spf13a06->id, 'fee' => $sp968713, 'system_fee' => $sp968713, 'income' => $sp9621e0, 'status' => \App\Order::STATUS_UNPAY, 'remark' => $sp113942, 'created_at' => Carbon::now())); if ($spa9a109 === 0) { $this->shipOrder($sp6a5e99, $sp51ee1d, $spa9a109, null); return redirect('/pay/result/' . $sp51ee1d); } $sp508d60 = $sp51ee1d; return $this->goPay($sp6a5e99, $sp51ee1d, $sp508d60, $spf13a06, $spa9a109); } function pay(Request $sp6a5e99, $sp51ee1d) { $sp61541f = \App\Order::whereOrderNo($sp51ee1d)->first(); if ($sp61541f == null) { return self::renderResultPage($sp6a5e99, array('msg' => '订单未找到，请重试')); } if ($sp61541f->status !== \App\Order::STATUS_UNPAY) { return redirect('/pay/result/' . $sp51ee1d); } $sp0e4eec = 'pay: ' . $sp61541f->pay_id; $sp2688c6 = $sp61541f->pay; if (!$sp2688c6) { \Log::error($sp0e4eec . ' cannot find Pay'); return $this->renderResultPage($sp6a5e99, array('msg' => '支付方式未找到')); } $sp0e4eec .= ',' . $sp2688c6->driver; $spc8c9ef = json_decode($sp2688c6->config, true); $spc8c9ef['payway'] = $sp2688c6->way; $spc8c9ef['out_trade_no'] = $sp51ee1d; try { $this->payApi = PayApi::getDriver($sp2688c6->id, $sp2688c6->driver); } catch (\Exception $spbcc446) { \Log::error($sp0e4eec . ' cannot find Driver: ' . $spbcc446->getMessage()); return $this->renderResultPage($sp6a5e99, array('msg' => '支付驱动未找到')); } if ($this->payApi->verify($spc8c9ef, function ($sp51ee1d, $sp7918ab, $sp040923) use($sp6a5e99) { try { $this->shipOrder($sp6a5e99, $sp51ee1d, $sp7918ab, $sp040923); } catch (\Exception $spbcc446) { $this->renderResultPage($sp6a5e99, array('success' => false, 'msg' => $spbcc446->getMessage())); } })) { \Log::notice($sp0e4eec . ' already success' . '

'); return redirect('/pay/result/' . $sp51ee1d); } if ($sp61541f->created_at < Carbon::now()->addMinutes(-5)) { return $this->renderResultPage($sp6a5e99, array('msg' => '当前订单长时间未支付已作废, 请重新下单')); } $spdbee16 = Product::where('id', $sp61541f->product_id)->where('enabled', 1)->first(); if ($spdbee16 == null) { return self::renderResultPage($sp6a5e99, array('msg' => '该商品已下架')); } $spdbee16->setAttribute('count', count($spdbee16->cards) ? $spdbee16->cards[0]->count : 0); if ($spdbee16->count < $sp61541f->count) { return self::renderResultPage($sp6a5e99, array('msg' => '该商品库存不足')); } $sp508d60 = $sp51ee1d; return $this->goPay($sp6a5e99, $sp51ee1d, $sp508d60, $sp2688c6, $sp61541f->paid); } function qrcode(Request $sp6a5e99, $sp51ee1d, $spc7624e) { $sp61541f = \App\Order::whereOrderNo($sp51ee1d)->with('product')->first(); if ($sp61541f == null) { return self::renderResultPage($sp6a5e99, array('msg' => '订单未找到，请重试')); } if ($sp61541f->product_id !== \App\Product::ID_API && $sp61541f->product == null) { return self::renderResultPage($sp6a5e99, array('msg' => '商品未找到，请重试')); } return view('pay/' . $spc7624e, array('pay_id' => $sp61541f->pay_id, 'name' => $sp61541f->product->name . ' x ' . $sp61541f->count . '件', 'amount' => $sp61541f->paid, 'qrcode' => $sp6a5e99->get('url'), 'id' => $sp51ee1d)); } function qrQuery(Request $sp6a5e99, $sp4b1cb7) { $sp66a3d9 = $sp6a5e99->input('id', ''); return self::payReturn($sp6a5e99, $sp4b1cb7, $sp66a3d9); } function payReturn(Request $sp6a5e99, $sp4b1cb7, $sp4510be = '') { $sp0e4eec = 'payReturn: ' . $sp4b1cb7; \Log::debug($sp0e4eec); $sp2688c6 = \App\Pay::where('id', $sp4b1cb7)->first(); if (!$sp2688c6) { return $this->renderResultPage($sp6a5e99, array('success' => 0, 'msg' => '支付方式错误')); } $sp0e4eec .= ',' . $sp2688c6->driver; if (strlen($sp4510be) > 0) { $sp61541f = \App\Order::whereOrderNo($sp4510be)->first(); if ($sp61541f && ($sp61541f->status === \App\Order::STATUS_PAID || $sp61541f->status === \App\Order::STATUS_SUCCESS)) { \Log::notice($sp0e4eec . ' already success' . '

'); if ($sp6a5e99->ajax()) { return self::renderResultPage($sp6a5e99, array('success' => 1, 'data' => '/pay/result/' . $sp4510be), array('order' => $sp61541f)); } else { return redirect('/pay/result/' . $sp4510be); } } } try { $this->payApi = PayApi::getDriver($sp2688c6->id, $sp2688c6->driver); } catch (\Exception $spbcc446) { \Log::error($sp0e4eec . ' cannot find Driver: ' . $spbcc446->getMessage()); return $this->renderResultPage($sp6a5e99, array('success' => 0, 'msg' => '支付驱动未找到')); } $spc8c9ef = json_decode($sp2688c6->config, true); $spc8c9ef['out_trade_no'] = $sp4510be; $spc8c9ef['payway'] = $sp2688c6->way; \Log::debug($sp0e4eec . ' will verify'); if ($this->payApi->verify($spc8c9ef, function ($sp51ee1d, $sp7918ab, $sp040923) use($sp6a5e99, $sp0e4eec, &$sp4510be) { $sp4510be = $sp51ee1d; try { \Log::debug($sp0e4eec . " shipOrder start, order_no: {$sp51ee1d}, amount: {$sp7918ab}, trade_no: {$sp040923}"); $this->shipOrder($sp6a5e99, $sp51ee1d, $sp7918ab, $sp040923); \Log::debug($sp0e4eec . ' shipOrder end, order_no: ' . $sp51ee1d); } catch (\Exception $spbcc446) { \Log::error($sp0e4eec . ' shipOrder Exception: ' . $spbcc446->getMessage()); } })) { \Log::debug($sp0e4eec . ' verify finished: 1' . '

'); if ($sp6a5e99->ajax()) { return self::renderResultPage($sp6a5e99, array('success' => 1, 'data' => '/pay/result/' . $sp4510be)); } else { return redirect('/pay/result/' . $sp4510be); } } else { \Log::debug($sp0e4eec . ' verify finished: 0' . '

'); return $this->renderResultPage($sp6a5e99, array('success' => 0, 'msg' => '支付验证失败，您可以稍后查看支付状态。')); } } function payNotify(Request $sp6a5e99, $sp4b1cb7) { $sp0e4eec = 'payNotify pay_id: ' . $sp4b1cb7; \Log::debug($sp0e4eec); $sp2688c6 = \App\Pay::where('id', $sp4b1cb7)->first(); if (!$sp2688c6) { \Log::error($sp0e4eec . ' cannot find PayModel'); echo 'fail'; die; } $sp0e4eec .= ',' . $sp2688c6->driver; try { $this->payApi = PayApi::getDriver($sp2688c6->id, $sp2688c6->driver); } catch (\Exception $spbcc446) { \Log::error($sp0e4eec . ' cannot find Driver: ' . $spbcc446->getMessage()); echo 'fail'; die; } $spc8c9ef = json_decode($sp2688c6->config, true); $spc8c9ef['payway'] = $sp2688c6->way; $spc8c9ef['isNotify'] = true; \Log::debug($sp0e4eec . ' will verify'); $sp6f1294 = $this->payApi->verify($spc8c9ef, function ($sp51ee1d, $sp7918ab, $sp040923) use($sp6a5e99, $sp0e4eec) { try { \Log::debug($sp0e4eec . " shipOrder start, order_no: {$sp51ee1d}, amount: {$sp7918ab}, trade_no: {$sp040923}"); $this->shipOrder($sp6a5e99, $sp51ee1d, $sp7918ab, $sp040923); \Log::debug($sp0e4eec . ' shipOrder end, order_no: ' . $sp51ee1d); } catch (\Exception $spbcc446) { \Log::error($sp0e4eec . ' shipOrder Exception: ' . $spbcc446->getMessage()); } }); \Log::debug($sp0e4eec . ' notify finished: ' . (int) $sp6f1294 . '

'); die; } function result(Request $sp6a5e99, $sp51ee1d) { $sp61541f = \App\Order::whereOrderNo($sp51ee1d)->first(); if ($sp61541f == null) { return self::renderResultPage($sp6a5e99, array('msg' => '订单未找到，请重试')); } if ($sp61541f->status === \App\Order::STATUS_PAID) { $sp2f3660 = $sp61541f->user->qq; $sp3a6f3c = '商家库存不足，因此没有自动发货，请联系商家客服发货'; if ($sp2f3660) { $sp3a6f3c .= '<br><a href="http://wpa.qq.com/msgrd?v=3&uin=' . $sp2f3660 . '&site=qq&menu=yes" target="_blank">客服QQ:' . $sp2f3660 . '</a>'; } return self::renderResultPage($sp6a5e99, array('success' => false, 'title' => '订单已支付', 'msg' => $sp3a6f3c), array('order' => $sp61541f)); } elseif ($sp61541f->status === \App\Order::STATUS_SUCCESS) { return self::showOrderResult($sp6a5e99, $sp61541f); } return self::renderResultPage($sp6a5e99, array('success' => false, 'msg' => $sp61541f->remark ? '失败原因:<br>' . $sp61541f->remark : '订单支付失败，请重试'), array('order' => $sp61541f)); } function renderResultPage(Request $sp6a5e99, $sp820aff, $spccb4f9 = array()) { if ($sp6a5e99->ajax()) { if (@$sp820aff['success']) { return Response::success($sp820aff['data']); } else { return Response::fail('error', $sp820aff['msg']); } } else { return view('pay.result', array_merge(array('result' => $sp820aff, 'data' => $spccb4f9), $spccb4f9)); } } function shipOrder($sp6a5e99, $sp51ee1d, $sp7918ab, $sp040923) { $sp61541f = \App\Order::whereOrderNo($sp51ee1d)->first(); if ($sp61541f === null) { \Log::error('shipOrder: No query results for model [App\\Order:' . $sp51ee1d . ',trade_no:' . $sp040923 . ',amount:' . $sp7918ab . ']. die(\'success\');'); die('success'); } if ($sp61541f->paid > $sp7918ab) { \Log::alert('shipOrder, price may error, order_no:' . $sp51ee1d . ', paid:' . $sp61541f->paid . ', $amount get:' . $sp7918ab); $sp61541f->remark = '支付金额(' . sprintf('%0.2f', $sp7918ab / 100) . ') 小于 订单金额(' . sprintf('%0.2f', $sp61541f->paid / 100) . ')'; $sp61541f->save(); throw new \Exception($sp61541f->remark); } $spdbee16 = null; if ($sp61541f->status === \App\Order::STATUS_UNPAY) { \Log::debug('shipOrder.first_process:' . $sp51ee1d); $spea8d79 = $sp61541f->id; if (FundHelper::orderSuccess($sp61541f->id, function ($sp354a91) use($spea8d79, $sp040923, &$sp61541f, &$spdbee16) { $sp61541f = $sp354a91; if ($sp61541f->status !== \App\Order::STATUS_UNPAY) { \Log::debug('Shop.Pay.shipOrder: .first_process:' . $sp61541f->order_no . ' already processed! #2'); return false; } $spdbee16 = $sp61541f->product()->lockForUpdate()->firstOrFail(); $spdbee16->count_sold += $sp61541f->count; $spdbee16->saveOrFail(); $sp61541f->pay_trade_no = $sp040923; $sp61541f->paid_at = Carbon::now(); $sp3ae78c = Card::where('product_id', $sp61541f->product_id)->whereRaw('`count_sold`<`count_all`')->take($sp61541f->count)->lockForUpdate()->get(); if (count($sp3ae78c) !== $sp61541f->count) { Log::alert('Shop.Pay.shipOrder: 订单:' . $sp61541f->order_no . ', 购买数量:' . $sp61541f->count . ', 卡数量:' . count($sp3ae78c) . ' 卡密不足(已支付 未发货)'); $sp61541f->status = \App\Order::STATUS_PAID; $sp61541f->saveOrFail(); return true; } else { $spd784ca = array(); foreach ($sp3ae78c as $spb26e8d) { $spd784ca[] = $spb26e8d->id; } $sp61541f->cards()->attach($spd784ca); Card::whereIn('id', $spd784ca)->update(array('status' => Card::STATUS_SOLD, 'count_sold' => DB::raw('`count_sold`+1'))); $sp61541f->status = \App\Order::STATUS_SUCCESS; $sp61541f->saveOrFail(); return FundHelper::ACTION_CONTINUE; } })) { if ($spdbee16->count_warn > 0 && $spdbee16->count < $spdbee16->count_warn) { try { \Mail::to($sp61541f->user->email)->Queue(new ProductCountWarn($spdbee16, $spdbee16->count)); } catch (\Throwable $spbcc446) { \App\Library\LogHelper::setLogFile('mail'); \Log::error('shipOrder.count_warn error, product_id:' . $sp61541f->product_id . ', email:' . $sp61541f->user->email . ', Exception:' . $spbcc446); \App\Library\LogHelper::setLogFile('card'); } } } else { throw new \Exception('merchant operate exception!'); } if (System::_getInt('mail_send_order') === 1 && filter_var($sp61541f->email, FILTER_VALIDATE_EMAIL)) { self::showOrderResult($sp6a5e99, $sp61541f, true); } } else { Log::debug('Shop.Pay.shipOrder: .order_no:' . $sp61541f->order_no . ' already processed! #1'); } return FALSE; } private function showOrderResult($sp6a5e99, $sp61541f, $sp24cf0e = false) { $sp3ae78c = array(); $sp61541f->cards->each(function ($spb26e8d) use(&$sp3ae78c) { $sp3ae78c[] = $spb26e8d->card; }); if (count($sp3ae78c) < $sp61541f->count) { if (count($sp3ae78c)) { $spf79fd7 = '订单#' . $sp61541f->order_no . '&nbsp;已支付，目前库存不足，您还有' . ($sp61541f->count - count($sp3ae78c)) . '件未发货，请联系商家客服发货<br>已发货商品见下方：<br>'; } else { $spf79fd7 = '订单#' . $sp61541f->order_no . '&nbsp;已支付，目前库存不足，您购买的' . ($sp61541f->count - count($sp3ae78c)) . '件未发货，请联系商家客服发货<br>'; } $sp2f3660 = $sp61541f->user->qq; if ($sp2f3660) { $spf79fd7 .= '<a href="http://wpa.qq.com/msgrd?v=3&uin=' . $sp2f3660 . '&site=qq&menu=yes" target="_blank">商家客服QQ:' . $sp2f3660 . '</a><br>'; } } else { $spf79fd7 = '订单#' . $sp61541f->order_no . '&nbsp;已支付，您购买的内容如下：'; } if ($sp24cf0e) { $sp6944b2 = join('<br>', $sp3ae78c); try { Mail::to($sp61541f->email)->Queue(new OrderShipped($sp61541f, $spf79fd7, $sp6944b2)); $sp61541f->email_sent = true; $sp61541f->saveOrFail(); } catch (\Throwable $spbcc446) { \App\Library\LogHelper::setLogFile('mail'); \Log::error('shipOrder.need_mail error, order_no:' . $sp61541f->order_no . ', email:' . $sp61541f->email . ', cards:' . $sp6944b2 . ', Exception:' . $spbcc446->getMessage()); \App\Library\LogHelper::setLogFile('card'); } return FALSE; } return self::renderResultPage($sp6a5e99, array('success' => true, 'msg' => $spf79fd7), array('card_txt' => join('&#013;&#010;', $sp3ae78c), 'order' => $sp61541f, 'product' => $sp61541f->product)); } }