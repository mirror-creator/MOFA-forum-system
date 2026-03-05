<html>
<head>
    <meta charset="utf-8">
    <title>MOFA註冊</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
		.input-container {
			position: relative;
			margin-bottom: 20px;
		}
		.input-container input {
			width: 100%;
			padding: 10px;
		}
		.input-container .toggle-visibility {
			position: absolute;
			right: 10px;
			top: 50%;
			transform: translateY(-50%);
			cursor: pointer;
		}
		.hint {
			display: block;
			margin-top: 5px;
			font-size: 14px;
		}
		.error {
			color: red;
		}
		.success {
			color: green;
		}
		.status-icon.correct {
			color: green;
		}
		.status-icon.error {
			color: red;
		}
	</style>
</head>
<body>
<div class="w3-panel w3-white">
    <br>
    <font face="Times New Roman" size="5" color="#426342">帳號註冊</font>
    <br><br>

    <form method="POST" action="registerinf.php">
        名稱：
        <div class="input-container">
            <input type="text" name="T3" required>
        </div>
        
        密碼：
        <div class="input-container">
            <input type="password" name="T4" id="password" required>
            <span class="toggle-visibility" onclick="togglePassword()">👁️</span>
            <span id="passwordHint" class="hint"></span>
        </div>
        
        手機號碼：
        <div class="input-container">
            <input type="text" name="T5" id="phone" required oninput="validatePhone()">
            <span id="phone-status" class="status-icon"></span>
        </div>

        電子郵件：
        <div class="input-container">
            <input type="email" name="T6" id="email" required oninput="validateEmail()">
            <span id="email-status" class="status-icon"></span>
        </div>

        <button type="submit">註冊</button>
    </form>
</div>

<script>
    // 切換密碼顯示或隱藏
    function togglePassword() {
        const passwordField = document.getElementById('password');
        const type = passwordField.type === 'password' ? 'text' : 'password';
        passwordField.type = type;
    }
    
    // 密碼提示
    const passwordInput = document.getElementById('password');
    const passwordHint = document.getElementById('passwordHint');

    // 當使用者輸入密碼時觸發
    passwordInput.addEventListener('input', () => {
        const passwordLength = passwordInput.value.length;

        if (passwordLength < 8 || passwordLength > 12) {
            passwordHint.textContent = "密碼格式錯誤，需8-12個字元";
            passwordHint.className = "error";
            passwordHint.style.color = "red";
        } else {
            passwordHint.textContent = "密碼格式正確";
            passwordHint.className = "success";
            passwordHint.style.color = "green";
        }
    });

    function validatePhone() {
        const phoneField = document.getElementById('phone');
        const phoneStatus = document.getElementById('phone-status');
        const phoneRegex = /^\d{10}$/; // Example: Taiwan phone number format
        if (phoneRegex.test(phoneField.value)) {
            phoneStatus.textContent = '✔️';
            phoneStatus.className = 'status-icon correct';
        } else {
            phoneStatus.textContent = '❌';
            phoneStatus.className = 'status-icon error';
        }
    }

    function validateEmail() {
        const emailField = document.getElementById('email');
        const emailStatus = document.getElementById('email-status');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[a-z]{2,}$/i;
        if (emailRegex.test(emailField.value)) {
            emailStatus.textContent = '✔️';
            emailStatus.className = 'status-icon correct';
        } else {
            emailStatus.textContent = '❌';
            emailStatus.className = 'status-icon error';
        }
    }
</script>
</body>
</html>
