<?php
use Faker\Generator as Faker; $spf4918e->define(App\Order::class, function (Faker $sp3f5926) { $sp51ee1d = date('YmdHis') . mt_rand(10000, 99999); while (\App\Order::whereOrderNo($sp51ee1d)->exists()) { $sp51ee1d = date('YmdHis') . mt_rand(10000, 99999); } $sp9dc949 = random_int(0, 1) ? $sp3f5926->email : 'user01@qq.com'; $spcaf990 = 1000; $spb91372 = random_int(0, 1) * 100; $spd363ac = $spcaf990 - $spb91372; return array('user_id' => 2, 'order_no' => $sp51ee1d, 'product_id' => 1, 'count' => 1); });