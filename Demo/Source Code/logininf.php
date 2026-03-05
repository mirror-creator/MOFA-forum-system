<html>
<head> 
    <meta charset="utf-8"> 
    <title> lofter登入 </title> 
</head>

<?php

session_start();
$_SESSION['username'] = $_POST['T1'];

require_once 'connect.php';
mysqli_query($conn, "set names utf8");

$sql = "SELECT `ID`, `UserName`, `UserPassword`, `Phone`, `email` FROM `User_` WHERE UserName='{$_POST['T1']}'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_array($result)) {
        // 使用 password_verify() 驗證密碼
        if (password_verify($_POST['T2'], $row['UserPassword'])) {
            $_SESSION['userid'] = $row['ID'];
            if ($row['UserName'] == '管理員') {
                header("Location: backmanage.php");
            } else {
                header("Location: mofain.php");
            }
        } else {
            echo "您的名稱或密碼輸入錯誤，請重新登入<br><br>";
        }
    }
} else {
    echo "您的名稱或密碼輸入錯誤，請重新登入";
}

mysqli_close($conn);

?>

<form method="POST" action="login.php">
    <input type="submit" value="返回" name="B11">
</form>

</html>
