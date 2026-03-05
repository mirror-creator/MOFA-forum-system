<html>
<head> <meta charset="utf-8"> <title> MOFA帳號刪除 </title> </head>

<?php


require_once'connect.php';
mysqli_query($conn,"set names utf8");
if($_COOKIE['username'] === '管理員'){
	echo"管理員帳號無法刪除<br>";
}else{
	$sql = "DELETE FROM `account` WHERE Account='{$_COOKIE['name']}'";
	if(mysqli_query($conn,$sql)){
		echo"刪除成功<br><br>";
	}else{
		echo"刪除失敗".$sql."<br>".mysqli_error($conn);
	};
	mysqli_close($conn);
};

?>
<form method="POST" action="index.html">
<input type="submit" value="確認" name="B14">
</form>

</html>