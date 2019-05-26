<?php
namespace App; use Illuminate\Database\Eloquent\Model; class ShopTheme extends Model { protected $guarded = array(); public $timestamps = false; protected $casts = array('config' => 'array'); private static $default_theme; public static function defaultTheme() { if (!static::$default_theme) { static::$default_theme = ShopTheme::query()->where('name', \App\System::_get('shop_theme_default', 'Material'))->first(); if (!static::$default_theme) { static::$default_theme = ShopTheme::query()->firstOrFail(); } } return static::$default_theme; } public static function freshList() { $sp8157ee = realpath(app_path('..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'shop_theme')); \App\ShopTheme::query()->get()->each(function ($sp686c09) use($sp8157ee) { if (!file_exists($sp8157ee . DIRECTORY_SEPARATOR . $sp686c09->name . DIRECTORY_SEPARATOR . 'config.php')) { $sp686c09->delete(); } }); foreach (scandir($sp8157ee) as $spf94c9d) { if ($spf94c9d === '.' || $spf94c9d === '..') { continue; } if (!\App\ShopTheme::query()->where('name', $spf94c9d)->exists()) { try { @($sp686c09 = (include $sp8157ee . DIRECTORY_SEPARATOR . $spf94c9d . DIRECTORY_SEPARATOR . 'config.php')); } catch (\Exception $spbcc446) { continue; } if ($sp686c09 && isset($sp686c09['description'])) { \App\ShopTheme::query()->insert(array('name' => $spf94c9d, 'description' => $sp686c09['description'], 'config' => json_encode(@$sp686c09['config']))); } } } } }