<?php
namespace App; use App\Library\Helper; use Illuminate\Database\Eloquent\Model; class Category extends Model { protected $guarded = array(); function getUrlAttribute() { return config('app.url') . '/c/' . Helper::id_encode($this->id, Helper::ID_TYPE_CATEGORY); } function products() { return $this->hasMany(Product::class); } function user() { return $this->belongsTo(User::class); } function getTmpPassword() { return md5('$wGgMd45Jgi@dBDR' . $this->password . '1#DS2%!VLqJolmMD'); } function getProductsForShop() { $sp395729 = Product::where('category_id', $this->id)->where('enabled', 1)->orderBy('sort')->get(); foreach ($sp395729 as $spdbee16) { $spdbee16->setForShop($this->user); } $this->addVisible(array('products')); $this->setAttribute('products', $sp395729); return $sp395729; } }