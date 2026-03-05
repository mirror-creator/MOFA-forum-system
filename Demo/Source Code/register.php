<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MOFA註冊</title>
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
    <font face="Times New Roman" size="7" color="#426342"> 註冊帳號 </font> <br><br>
    <form method="POST" action="registerinf.php">
        <!-- 使用者名稱 -->
        <label for="username">使用者名稱：</label>
        <input type="text" name="T3" id="username" required>
        <br><br>

        <!-- 密碼 -->
        <label for="password">密碼：</label>
        <input type="password" name="T4" id="password" minlength="8" maxlength="12" required>
        <span id="passwordHint" class="error"></span>
        <br>
        <!-- 密碼顯示切換 -->
		<label>
			<input type="checkbox" id="togglePassword"> 顯示密碼
		</label>
		<br><br>

        <!-- 手機號碼 -->
        <label for="phone">手機號碼：</label>
        <input type="text" name="T5" id="phone" pattern="^09\d{8}$" title="請輸入有效的手機號碼" required>
        <span id="phoneHint" class="error"></span>
        <br><br>

        <!-- 電子郵件 -->
        <label for="email">電子郵件：</label>
        <input type="email" name="t1" id="email" title="請輸入有效的電子郵件" required>
		<span id="emailHint" class="error"></span>
        <br><br>

        <input type="submit" value="提交">
    </form>
	</div>
	
    <script>
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
