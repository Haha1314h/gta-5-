<?php
if (isset($_SERVER['HTTP_ALI_SWIFT_STAT_HOST'])) { $_SERVER['SERVER_NAME'] = $_SERVER['HTTP_ALI_SWIFT_STAT_HOST']; $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_ALI_SWIFT_STAT_HOST']; } define('LARAVEL_START', microtime(true)); require __DIR__ . '/../vendor/autoload.php'; $spe49dca = (require_once __DIR__ . '/../bootstrap/app.php'); $sp9f6f48 = $spe49dca->make(Illuminate\Contracts\Http\Kernel::class); $sp31c557 = $sp9f6f48->handle($sp6a5e99 = Illuminate\Http\Request::capture()); $sp31c557->send(); $sp9f6f48->terminate($sp6a5e99, $sp31c557);