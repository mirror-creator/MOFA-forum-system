<html> 
<head> 
    <meta charset="utf-8"> 
    <title> MOFA註冊 </title> 
</head>
<body>
<?php

$servername = "localhost";
$username = "root"; 
$password = ""; 

// 建立資料庫連線
$conn = mysqli_connect($servername, $username, $password);

// 檢查資料庫連線
if (!$conn) {
    die("連線失敗：" . mysqli_connect_error());
}

mysqli_select_db($conn, "Account");
mysqli_query($conn, "SET NAMES utf8");

// 檢查欄位是否空白
if (empty($_POST['T3']) || empty($_POST['T4']) || empty($_POST['T5']) || empty($_POST['T6'])) {
    echo "每個欄位皆需填寫<br>";
    mysqli_close($conn);
    exit();
}

// 預防 SQL 注入，使用準備語句
$username = mysqli_real_escape_string($conn, $_POST['T3']);
$password_input = $_POST['T4'];  // 使用者輸入的密碼
$phone = mysqli_real_escape_string($conn, $_POST['T5']);
$email = mysqli_real_escape_string($conn, $_POST['T6']);

// 驗證密碼長度限制
if (strlen($password_input) < 8 || strlen($password_input) > 12) {
    echo "密碼長度必須在 8 到 12 個字元之間<br>";
    mysqli_close($conn);
    exit();
}

// 驗證手機號碼格式
if (!preg_match("/^09\d{8}$/", $phone)) {
    echo "手機號碼格式錯誤，必須是 09 開頭且總共 10 位數字<br>";
    mysqli_close($conn);
    exit();
}

// 驗證電子郵件格式
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "電子郵件格式錯誤<br>";
    mysqli_close($conn);
    exit();
}

// 檢查使用者名稱是否已存在
$sql_check_user = "SELECT COUNT(*) AS count FROM `User_` WHERE `UserName` = ?";
$stmt = mysqli_prepare($conn, $sql_check_user);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $count_user);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if ($count_user > 0) {
    echo "此使用者名稱已被使用，請選擇其他名稱<br>";
    mysqli_close($conn);
    exit();
}

// 檢查電子郵件是否已存在
$sql_check_email = "SELECT COUNT(*) AS count FROM `User_` WHERE `Email` = ?";
$stmt = mysqli_prepare($conn, $sql_check_email);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $count_email);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if ($count_email > 0) {
    echo "此電子郵件已經註冊過，請使用其他電子郵件<br>";
    mysqli_close($conn);
    exit();
}

// 密碼加密處理
$password_hash = password_hash($password_input, PASSWORD_DEFAULT);

// 插入新使用者資料
$sql_insert = "INSERT INTO `User_` (`UserName`, `UserPassword`, `Phone`, `Email`) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql_insert);
mysqli_stmt_bind_param($stmt, "ssss", $username, $password_hash, $phone, $email);

if (mysqli_stmt_execute($stmt)) {
    echo "<br><br> 註冊成功，請回到頁面重新登入";
} else {
    echo "發生錯誤：" . mysqli_error($conn);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>
<br><br>
<form method="POST" action="index.html">
    <input type="submit" value="返回首頁" name="B6">
</form>
<br>
</body>
</html>
