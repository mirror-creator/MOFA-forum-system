<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MOFA登入</title>
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
    <font face="Times New Roman" size="7" color="#426342"> 帳號登入 </font> <br><br>
    <form method="POST" action="logininf.php">
	
	<!-- 名稱 -->
    <label for="name">名稱：</label>
	<input type="text" name="T1" id="name" required>
	<br><br>
	
	<!-- 密碼 -->
    <label for="password">密碼：</label>
    <input type="password" name="T2" id="password" minlength="8" maxlength="12" required>
    <!-- 密碼顯示切換 -->
    <span class="toggle-visibility" onclick="togglePassword()">👁️</span>
	<span id="passwordHint" class="error"></span>
    <br><br>
	
	<br><br>
	<input type="submit" value="確認" name="B3">
	<input type="reset" value="重新輸入" name="B4">
	</form>
	</div>
	
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

</body>


</html>