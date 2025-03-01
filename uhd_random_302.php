<?php

// 获取 Bing 每日图片信息的函数
function getBingImageInfo($num_images = 32) {
    $allImages = [];
    $images_per_call = 8; // 每次 API 调用获取的图片数量
    $num_calls = ceil($num_images / $images_per_call); // 计算需要调用 API 的次数

    for ($i = 0; $i < $num_calls; $i++) {
        $idx = $i * $images_per_call; // 计算 idx 参数
        $url = "https://cn.bing.com/HPImageArchive.aspx?format=js&idx={$idx}&n={$images_per_call}&mkt=zh-CN";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $headers = array(
            "Accept: application/json",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 生产环境不要禁用
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);  // 生产环境不要禁用
        $resp = curl_exec($curl);

        if ($resp === false) {
            error_log("cURL error: " . curl_error($curl));
            curl_close($curl);
            return null;
        }

        curl_close($curl);

        $array = json_decode($resp, true); // 使用 true 返回数组

        if ($array === null && json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error: " . json_last_error_msg());
            return null;
        }

        if (isset($array['images']) && is_array($array['images'])) {
            $allImages = array_merge($allImages, $array['images']); // 合并图片信息
        }
    }

    // 截取到需要的数量
    $allImages = array_slice($allImages, 0, $num_images);

    return ['images' => $allImages]; // 返回包含所有图片的数组
}

// 获取随机图片 URL 的函数
function getRandomImageUrl() {
    $imageInfo = getBingImageInfo();

    if ($imageInfo && isset($imageInfo['images']) && is_array($imageInfo['images']) && count($imageInfo['images']) > 0) {
        $randomIndex = array_rand($imageInfo['images']); // 随机选择一个索引
        $randomImage = $imageInfo['images'][$randomIndex];

        if (isset($randomImage['urlbase'])) {
            return 'https://cn.bing.com' . $randomImage['urlbase'] . '_1920x1080.jpg';
        } else {
            error_log("urlbase not found in image info.");
            return null;
        }
    } else {
        error_log("No images found in Bing API response.");
        return null;
    }
}

// 获取随机图片 URL
$imageUrl = getRandomImageUrl();

// 如果成功获取了图片 URL，则重定向
if ($imageUrl) {
    header('HTTP/1.1 302 Found'); // 使用 302 临时重定向
    header('Location: ' . $imageUrl);
    exit();
}
?>
