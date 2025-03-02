<?php
// 获取 Bing 图片
function getBingImageInfo() {
    $url = "https://cn.bing.com/HPImageArchive.aspx?format=js&idx=0&n=1&mkt=zh-CN"; // n=1
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $resp = curl_exec($curl);

    if ($resp === false) {
        error_log("cURL Error: " . curl_error($curl));
        return ['images' => []];
    }

    $data = json_decode($resp, true);
    curl_close($curl);

    return $data;
}

// 获取今日图片 URL
function getTodaysImageUrl() {
    $imageInfo = getBingImageInfo();

    if (!isset($imageInfo['images']) || empty($imageInfo['images'])) {
        error_log("No images available");
        return null;
    }

    $todaysImage = $imageInfo['images'][0];

    return isset($todaysImage['urlbase'])
        ? 'https://cn.bing.com' . $todaysImage['urlbase'] . '_UHD.jpg'
        : null;
}

// 获取图片并重定向
if ($imageUrl = getTodaysImageUrl()) {
    header("Location: $imageUrl", true, 302);
    exit();
}

// 失败时返回404
header("HTTP/1.1 404 Not Found");
echo "No image available";
?>
