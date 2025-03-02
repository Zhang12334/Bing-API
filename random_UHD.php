<?php
// 获取 Bing 图片
function getBingImageInfo() {
    $url = "https://cn.bing.com/HPImageArchive.aspx?format=js&idx=0&n=8&mkt=zh-CN";
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

// 获取随机图片 URL
function getRandomImageUrl() {
    $imageInfo = getBingImageInfo();

    if (!isset($imageInfo['images']) || empty($imageInfo['images'])) {
        error_log("No images available");
        return null;
    }

    $randomIndex = array_rand($imageInfo['images']);
    $randomImage = $imageInfo['images'][$randomIndex];

    return isset($randomImage['urlbase'])
        ? 'https://cn.bing.com' . $randomImage['urlbase'] . '_UHD.jpg'
        : null;
}

// 获取图片并输出
if ($imageUrl = getRandomImageUrl()) {
    header('Content-Type: image/jpeg'); 
    readfile($imageUrl);
    exit();
}

// 失败时返回404
header("HTTP/1.1 404 Not Found");
echo "No image available";
?>
