<html>
<head>
    <meta charset="utf-8">
    <title>MOFA登出</title>
</head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<body>

<div class="w3-panel w3-white">
    <br>
    <font face="Times New Roman" size="5" color="#426342"> 帳號登出 </font>
    <br><br>

    <form method="POST" action="index.html">
        確認登出？
        <br><br>
        <input type="submit" value="確認" name="B7">
    </form>

    <?php
    session_start();
    $userName = $_SESSION['username'];

    // 判斷 UserName 是否為 '管理員'
    $returnAction = ($userName === '管理員') ? 'backmanage.php' : 'mofain.php';
    ?>

    <form method="POST" action="<?php echo $returnAction; ?>">
        <input type="submit" value="返回" name="B8">
    </form>
</div>

</body>
</html>
