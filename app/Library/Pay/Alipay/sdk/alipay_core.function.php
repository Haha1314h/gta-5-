<?php
function createLinkString($sp11e254) { $sp3373de = ''; foreach ($sp11e254 as $sp1e4b49 => $sp7a0550) { $sp3373de .= $sp1e4b49 . '=' . $sp7a0550 . '&'; } $sp3373de = substr($sp3373de, 0, strlen($sp3373de) - 1); if (get_magic_quotes_gpc()) { $sp3373de = stripslashes($sp3373de); } return $sp3373de; } function createLinkStringUrlEncode($sp11e254) { $sp3373de = ''; foreach ($sp11e254 as $sp1e4b49 => $sp7a0550) { $sp3373de .= $sp1e4b49 . '=' . urlencode($sp7a0550) . '&'; } $sp3373de = substr($sp3373de, 0, strlen($sp3373de) - 1); if (get_magic_quotes_gpc()) { $sp3373de = stripslashes($sp3373de); } return $sp3373de; } function paraFilter($sp11e254) { $sp60c4f8 = array(); foreach ($sp11e254 as $sp1e4b49 => $sp7a0550) { if ($sp1e4b49 == 'sign' || $sp1e4b49 == 'sign_type' || $sp7a0550 == '') { continue; } else { $sp60c4f8[$sp1e4b49] = $sp11e254[$sp1e4b49]; } } return $sp60c4f8; } function argSort($sp11e254) { ksort($sp11e254); reset($sp11e254); return $sp11e254; } function logResult($spdb95a8 = '') { $sp2fb581 = fopen('log.txt', 'a'); flock($sp2fb581, LOCK_EX); fwrite($sp2fb581, '执行日期：' . strftime('%Y%m%d%H%M%S', time()) . '
' . $spdb95a8 . '
'); flock($sp2fb581, LOCK_UN); fclose($sp2fb581); } function getHttpResponsePOST($sp59c732, $spc666a6, $sp11e254, $spab3111 = '') { if (trim($spab3111) != '') { $sp59c732 = $sp59c732 . '_input_charset=' . $spab3111; } $spe00444 = curl_init($sp59c732); curl_setopt($spe00444, CURLOPT_SSL_VERIFYPEER, true); curl_setopt($spe00444, CURLOPT_SSL_VERIFYHOST, 2); curl_setopt($spe00444, CURLOPT_CAINFO, $spc666a6); curl_setopt($spe00444, CURLOPT_HEADER, 0); curl_setopt($spe00444, CURLOPT_RETURNTRANSFER, 1); curl_setopt($spe00444, CURLOPT_POST, true); curl_setopt($spe00444, CURLOPT_POSTFIELDS, $sp11e254); $sp1b19a2 = curl_exec($spe00444); curl_close($spe00444); return $sp1b19a2; } function getHttpResponseGET($sp59c732, $spa8b3f0) { $spe00444 = curl_init($sp59c732); curl_setopt($spe00444, CURLOPT_HEADER, 0); curl_setopt($spe00444, CURLOPT_RETURNTRANSFER, 1); curl_setopt($spe00444, CURLOPT_SSL_VERIFYPEER, true); curl_setopt($spe00444, CURLOPT_SSL_VERIFYHOST, 2); curl_setopt($spe00444, CURLOPT_CAINFO, $spa8b3f0); $sp1b19a2 = curl_exec($spe00444); curl_close($spe00444); return $sp1b19a2; } function charsetEncode($sp5bf110, $sp3e0df1, $spfbeaa1) { $sp333444 = ''; if (!isset($sp3e0df1)) { $sp3e0df1 = $spfbeaa1; } if ($spfbeaa1 == $sp3e0df1 || $sp5bf110 == null) { $sp333444 = $sp5bf110; } elseif (function_exists('mb_convert_encoding')) { $sp333444 = mb_convert_encoding($sp5bf110, $sp3e0df1, $spfbeaa1); } elseif (function_exists('iconv')) { $sp333444 = iconv($spfbeaa1, $sp3e0df1, $sp5bf110); } else { die('sorry, you have no libs support for charset change.'); } return $sp333444; } function charsetDecode($sp5bf110, $spfbeaa1, $sp3e0df1) { if (!isset($spfbeaa1)) { $sp3e0df1 = $spfbeaa1; } if ($spfbeaa1 == $sp3e0df1 || $sp5bf110 == null) { $sp333444 = $sp5bf110; } elseif (function_exists('mb_convert_encoding')) { $sp333444 = mb_convert_encoding($sp5bf110, $sp3e0df1, $spfbeaa1); } elseif (function_exists('iconv')) { $sp333444 = iconv($spfbeaa1, $sp3e0df1, $sp5bf110); } else { die('sorry, you have no libs support for charset changes.'); } return $sp333444; } function md5Sign($sp350c49, $sp1e4b49) { $sp350c49 = $sp350c49 . $sp1e4b49; return md5($sp350c49); } function md5Verify($sp350c49, $sp1e1df1, $sp1e4b49) { $sp350c49 = $sp350c49 . $sp1e4b49; $spdd560c = md5($sp350c49); if ($spdd560c == $sp1e1df1) { return true; } else { return false; } }