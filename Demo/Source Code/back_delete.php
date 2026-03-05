<?php
if (isset($_GET['table']) && isset($_GET['id'])) {
    $tableName = $_GET['table'];
    $recordId = $_GET['id'];


    $conn = new mysqli("localhost", "root", "", "account");
    if ($conn->connect_error) {
        die("連線失敗: " . $conn->connect_error);
    }

    // 使用預處理語句防止 SQL 注入
	$sql = "DELETE FROM $tableName WHERE ID = ?";
	$stmt = $conn->prepare($sql);
	if ($stmt) {
	$stmt->bind_param("i", $recordId);
	$stmt->execute();
	if ($stmt->affected_rows > 0) {
		echo "資料已成功刪除";
	} else {
		echo "刪除失敗，可能是資料不存在";
	}
		$stmt->close();
	} else {
		echo "錯誤：無法準備刪除語句，請檢查表名是否正確";
	}


    $conn->close();
} else {
    echo "缺少必要的參數。";
}
?>

<br><br>
<form method="POST" action="backmanage.php">
    <input type="submit" value="返回">
</form>
