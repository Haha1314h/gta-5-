<?php
namespace App\Http\Controllers\Merchant; use App\Library\Response; use App\System; use function GuzzleHttp\Psr7\mimetype_from_filename; use Illuminate\Http\Request; use App\Http\Controllers\Controller; use Illuminate\Support\Facades\Auth; use Illuminate\Support\Facades\Storage; class File extends Controller { public static function uploadImg($sp6aa048, $sp9f2e5e, $sp6b01f3, $spcc4d71 = false) { try { $sp62e459 = $sp6aa048->extension(); } catch (\Throwable $spbcc446) { return Response::fail($spbcc446->getMessage()); } if (!$sp6aa048 || !in_array(strtolower($sp62e459), array('jpg', 'jpeg', 'png', 'gif'))) { return Response::fail('图片错误, 系统支持jpg/png/gif格式'); } if ($sp6aa048->getSize() > 5 * 1024 * 1024) { return Response::fail('图片不能大于5MB'); } try { $sp8157ee = $sp6aa048->store($sp6b01f3, array('disk' => System::_get('storage_driver'))); } catch (\Exception $spbcc446) { \Log::error('File.uploadImg folder:' . $sp6b01f3 . ', error:' . $spbcc446->getMessage(), array('exception' => $spbcc446)); if (config('app.debug')) { return Response::fail($spbcc446->getMessage()); } else { return Response::fail('上传文件失败, 内部错误, 请联系客服'); } } if (!$sp8157ee) { return Response::fail('系统保存文件出错, 请稍后再试'); } $sp35b7d6 = System::_get('storage_driver'); $sp59c732 = Storage::disk($sp35b7d6)->url($sp8157ee); $spf63d19 = \App\File::insertGetId(array('user_id' => $sp9f2e5e, 'driver' => $sp35b7d6, 'path' => $sp8157ee, 'url' => $sp59c732)); if ($spf63d19 < 1) { Storage::disk($sp35b7d6)->delete($sp8157ee); return Response::fail('数据库繁忙，请稍后再试'); } $sp6f1294 = array('id' => $spf63d19, 'url' => $sp59c732, 'name' => pathinfo($sp8157ee, PATHINFO_BASENAME)); if ($spcc4d71) { return $sp6f1294; } return Response::success($sp6f1294); } function upload_merchant(Request $sp6a5e99) { $sp2b1668 = $this->getUser($sp6a5e99); if ($sp2b1668 === null) { return Response::forbidden('无效的用户'); } $sp6aa048 = $sp6a5e99->file('file'); return $this->uploadImg($sp6aa048, $sp2b1668->id, \App\File::getProductFolder()); } public function renderImage(Request $sp6a5e99, $spd836a1) { if (str_contains($spd836a1, '..') || str_contains($spd836a1, './') || str_contains($spd836a1, '.\\') || !starts_with($spd836a1, 'images/')) { $sp9be6a8 = file_get_contents(public_path('images/illegal.jpg')); } else { $spd836a1 = str_replace('\\', '/', $spd836a1); $sp6aa048 = \App\File::wherePath($spd836a1)->first(); if ($sp6aa048) { $sp35b7d6 = $sp6aa048->driver; } else { $sp35b7d6 = System::_get('storage_driver'); } if (!in_array($sp35b7d6, array('local', 's3', 'oss', 'qiniu'))) { return response()->view('message', array('title' => '404', 'message' => '404 Driver NotFound'), 404); } try { $sp9be6a8 = Storage::disk($sp35b7d6)->get($spd836a1); } catch (\Illuminate\Contracts\Filesystem\FileNotFoundException $spbcc446) { \Log::error('File.renderImage error: ' . $spbcc446->getMessage(), array('exception' => $spbcc446)); return response()->view('message', array('title' => '404', 'message' => '404 NotFound'), 404); } } ob_end_clean(); header('Content-Type: ' . mimetype_from_filename($spd836a1)); die($sp9be6a8); } }