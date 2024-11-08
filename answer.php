<?php
// 设置返回的内容类型为 JSON
header("Content-Type: application/json");

// 准备响应数据
$response = array();

// 检查是否通过 POST 提交了 'message' 参数
if (isset($_POST['message'])) {
    $response['status'] = "success";
    $response['message_received'] = $_POST['message'];
} else {
    $response['status'] = "error";
    $response['message'] = "No message received.";
}

// 返回 JSON 编码的响应
echo json_encode($response);
?>
