<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MOFA帳號管理</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        .error {
            color: red;
            font-size: 0.9em;
        }
        .success {
            color: green;
            font-size: 0.9em;
        }
    </style>
</head>
<body>

<div class="w3-panel w3-white">
<br>
<font face="Times New Roman" size="5" color="#426342"> 帳號管理 </font> 
<br><br>

<form method="POST" action="accountinf.php">

	<!-- 名稱 -->
    <label for="name">名稱更改：</label>
	<input type="text" name="T7" id="name">
	<br><br>
	
	<!-- 密碼 -->
    <label for="password">密碼更改：</label>
    <input type="password" name="T8" id="password" minlength="8" maxlength="12">
    <!-- 密碼顯示切換 -->
    <span class="toggle-visibility" onclick="togglePassword()">👁️</span>
	<span id="passwordHint" class="error"></span>
    <br><br>
	
	<!-- 手機號碼 -->
	<label for="phone">手機號碼修改：</label>
	<input type="text" name="T9" id="phone" pattern="^09\d{8}$" title="請輸入有效的手機號碼">
	<span id="phoneHint" class="error"></span>
	<br><br>

	<!-- 電子郵件 -->
	<label for="email">電子郵件修改：</label>
	<input type="email" name="T10" id="email" title="請輸入有效的電子郵件" >
	<span id="emailHint" class="error"></span>
	<br><br>
	
	<script>
		// 切換密碼顯示或隱藏
		function togglePassword() {
			const passwordField = document.getElementById('password');
			const type = passwordField.type === 'password' ? 'text' : 'password';
			passwordField.type = type;
		}
		
        const passwordInput = document.getElementById('password');
        const passwordHint = document.getElementById('passwordHint');

        // 當使用者輸入密碼時觸發
        passwordInput.addEventListener('input', () => {
            const passwordLength = passwordInput.value.length;

            if (passwordLength < 8) {
                passwordHint.textContent = "密碼格式錯誤，需8-12個字元";
                passwordHint.className = "error";
            } else if (passwordLength > 12) {
                passwordHint.textContent = "密碼格式錯誤，需8-12個字元";
                passwordHint.className = "error";
            } else {
                passwordHint.textContent = "密碼格式正確";
                passwordHint.className = "success";
            }
        });
    </script>

	<input type="submit" value="修改確認" name="B9"><br><br>
	<input type="reset" value="重新輸入" name="B10"><br><br>
</form>

<form method="POST" action="delete.php">
	<input type="submit" value="刪除帳號" name="B11">
</form>

<br><br>
<form method="POST" action="mofain.php">
	<input type="submit" value="返回" name="B11">
</form>

</div>

</body>


</html>