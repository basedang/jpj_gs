<?php
//支持编辑评论功能
        include_once '../module/sql_connet.php';

if (isset($_GET['id'])) {
    $commentId = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT content FROM comments WHERE id = ?");
    $stmt->bind_param("i", $commentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['content' => $row['content']]);
    } else {
        echo json_encode(['error' => '评论不存在']);
    }
    $stmt->close();
} else {
    echo json_encode(['error' => '缺少评论ID']);
}
?>