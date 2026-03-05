<?php
// 連接資料庫
$conn = new mysqli("localhost", "root", "", "account");

// 檢查資料庫連線
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 擷取表單資料
    $name = isset($_POST['T7']) ? trim($_POST['T7']) : '';
    $password = isset($_POST['T8']) ? trim($_POST['T8']) : '';
    $phone = isset($_POST['T9']) ? trim($_POST['T9']) : '';
    $email = isset($_POST['T10']) ? trim($_POST['T10']) : '';

    // 檢查是否有輸入任何資料
    if (empty($name) && empty($password) && empty($phone) && empty($email)) {
        echo "請至少填寫一項資料以便更改！";
        header("refresh:5;url=http://localhost/PERSONAL_REPORT/account.php");
        echo '<br>五秒後自動轉回頁面';
        exit;
    }

    // 開啟 Session，提取當前使用者 ID
    session_start();
    if (!isset($_SESSION['userid'])) {
        echo "未登入，請重新登入！";
        header("refresh:5;url=http://localhost/PERSONAL_REPORT/login.php");
        exit;
    }
    $user_id = $_SESSION['userid'];

    // 檢查名稱是否已存在於資料庫
    if (!empty($name)) {
        $sql = "SELECT * FROM user_ WHERE UserName = ? AND ID != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $name, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            echo "名稱已存在，請選擇其他名稱！";
            header("refresh:5;url=http://localhost/PERSONAL_REPORT/account.php");
            echo '<br>五秒後自動轉回頁面';
            exit;
        }
    }

    // 檢查手機號碼格式 (必須是 09 開頭且長度為 10 位)
    if (!empty($phone) && !preg_match('/^09\d{8}$/', $phone)) {
        echo "手機號碼格式錯誤，必須以 09 開頭，並且長度為 10 位數！";
        header("refresh:5;url=http://localhost/PERSONAL_REPORT/account.php");
        echo '<br>五秒後自動轉回頁面';
        exit;
    }

    // 檢查密碼格式 (8 到 12 位數)
    if (!empty($password) && !(strlen($password) >= 8 && strlen($password) <= 12)) {
        echo "密碼長度應為 8 到 12 位數！";
        header("refresh:5;url=http://localhost/PERSONAL_REPORT/account.php");
        echo '<br>五秒後自動轉回頁面';
        exit;
    }

    // 準備 SQL 更新語句
    $sql = "UPDATE user_ SET ";
    $updates = [];
    $params = [];

    if (!empty($name)) {
        $updates[] = "UserName = ?";
        $params[] = $name;
    }
    if (!empty($password)) {
        $updates[] = "UserPassword = ?";
        $params[] = password_hash($password, PASSWORD_DEFAULT); // 密碼加密
    }
    if (!empty($phone)) {
        $updates[] = "Phone = ?";
        $params[] = $phone;
    }
    if (!empty($email)) {
        $updates[] = "Email = ?";
        $params[] = $email;
    }

    // 組合 SQL 語句
    $sql .= implode(", ", $updates) . " WHERE ID = ?";
    $params[] = $user_id;

    // 執行更新語句
    $stmt = $conn->prepare($sql);
    $types = str_repeat('s', count($params) - 1) . 'i'; // 動態設定參數類型
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo "帳號資料更新成功！";
    } else {
        echo "更新失敗: " . $stmt->error;
    }

    // 關閉資料庫連接
    $stmt->close();
    $conn->close();
}
?>

<br><br>
<form method="POST" action="account.php">
    <input type="submit" value="返回" name="B11">
</form>


