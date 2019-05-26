<?php
namespace App; use App\Library\Helper; use Illuminate\Database\Eloquent\Model; class Product extends Model { protected $guarded = array(); const ID_API = -1001; function getUrlAttribute() { return config('app.url') . '/p/' . Helper::id_encode($this->id, Helper::ID_TYPE_PRODUCT); } function getCountAttribute() { return $this->count_all - $this->count_sold; } function category() { return $this->belongsTo(Category::class); } function cards() { return $this->hasMany(Card::class); } function coupons() { return $this->hasMany(Coupon::class); } function orders() { return $this->hasMany(Order::class); } function user() { return $this->belongsTo(User::class); } public static function refreshCount($sp2b1668) { \App\Card::where('user_id', $sp2b1668->id)->selectRaw('`product_id`,SUM(`count_sold`) as `count_sold`,SUM(`count_all`) as `count_all`')->groupBy('product_id')->orderByRaw('`product_id`')->chunk(100, function ($sp8a1187) { foreach ($sp8a1187 as $sp02a3d4) { \App\Product::where('id', $sp02a3d4->product_id)->update(array('count_sold' => $sp02a3d4->count_sold, 'count_all' => $sp02a3d4->count_all)); } }); } function setForShop($sp2b1668 = null) { $spdbee16 = $this; $sp8e7e1d = $spdbee16->count; $sp43d44c = System::_getInt('shop_inventory'); if ($sp43d44c == User::INVENTORY_RANGE) { if ($sp8e7e1d <= 0) { $sp5406ef = '不足'; } elseif ($sp8e7e1d <= 10) { $sp5406ef = '少量'; } elseif ($sp8e7e1d <= 20) { $sp5406ef = '一般'; } else { $sp5406ef = '大量'; } $spdbee16->setAttribute('count2', $sp5406ef); } else { $spdbee16->setAttribute('count2', $sp8e7e1d); } $spdbee16->setAttribute('count', $sp8e7e1d); $spdbee16->setVisible(array('id', 'name', 'description', 'count', 'count2', 'buy_min', 'buy_max', 'support_coupon', 'password_open', 'price', 'price_whole')); } }