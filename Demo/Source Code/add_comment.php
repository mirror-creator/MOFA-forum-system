<?php
session_start();
$conn = new mysqli("localhost", "root", "", "account");

if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

// 確認使用者是否登入
if (!isset($_SESSION['userid'])) {
    echo "您尚未登入，無法新增留言。";
    exit;
}

// 確認表單數據是否完整
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id']) && isset($_POST['comment_content'])) {
    $post_id = intval($_POST['post_id']);
    $comment_content = trim($_POST['comment_content']);
    $user_id = $_SESSION['userid']; // 從 Session 獲取登入使用者的 ID

    if (!empty($comment_content)) {
        $sql = "INSERT INTO comments (PostID, UserID, Content, CommentDate) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $post_id, $user_id, $comment_content);

        if ($stmt->execute()) {
            // 新增成功，回到貼文頁面
            header("Location: mofain.php");
            exit;
        } else {
            echo "留言新增失敗: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "留言內容不可為空！";
    }
} else {
    echo "無效的請求！";
}

$conn->close();
?>
