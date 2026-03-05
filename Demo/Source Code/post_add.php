<?php
// 資料庫連接
$conn = new mysqli("localhost", "root", "", "account");
// 檢查資料庫連線
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

$successMessage = "";
$errorMessage = "";

// 處理貼文提交
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_post') {
    // 擷取表單資料
    $title = isset($_POST['Title']) ? $_POST['Title'] : '';
    $content = isset($_POST['Content']) ? $_POST['Content'] : null;
    $photo = isset($_FILES['Photo']) ? $_FILES['Photo'] : null;
    $tags = isset($_POST['Tags']) ? $_POST['Tags'] : []; // 選中的標籤

    session_start();

    // 驗證必填欄位
    if (empty($title)) {
        $errorMessage = "請填寫所有必要欄位！";
    } else {
        // 處理圖片上傳
        $photoPath = null;
        if ($photo && $photo['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'C:/xampp/htdocs/PERSONAL_REPORT/uploads/';
            $photoPath = $uploadDir . basename($photo['name']);
            if (!move_uploaded_file($photo['tmp_name'], $photoPath)) {
                $errorMessage = "圖片上傳失敗！";
                $photoPath = null;
            } else {
                $photoPath = 'http://localhost/PERSONAL_REPORT/uploads/' . basename($photo['name']);
            }
        }

        // 新增貼文
        $postDate = date('Y-m-d');
        $sql = "INSERT INTO Post (AuthorID, Title, Content, Photo, PostDate) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $authorid = $_SESSION['userid'];
        $stmt->bind_param("sssss", $authorid, $title, $content, $photoPath, $postDate);

        if ($stmt->execute()) {
            $postId = $stmt->insert_id; // 獲取新增貼文的 ID
            // 為貼文附加標籤
            foreach ($tags as $tagId) {
                $tagSql = "INSERT INTO PostTags (PostID, TagID) VALUES (?, ?)";
                $tagStmt = $conn->prepare($tagSql);
                $tagStmt->bind_param("ii", $postId, $tagId);
                $tagStmt->execute();
            }
            $successMessage = "貼文新增成功！";
        } else {
            $errorMessage = "新增失敗：" . $stmt->error;
        }
    }
}

// 處理新增標籤
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_tag') {
    $tagName = isset($_POST['TagName']) ? $_POST['TagName'] : '';
    $tagDescription = isset($_POST['TagDescription']) ? $_POST['TagDescription'] : '';
    if (!empty($tagName)) {
        $sql = "INSERT INTO Tags (TagName, TagDescription) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $tagName, $tagDescription);
        if ($stmt->execute()) {
            $successMessage = "標籤新增成功！";
        } else {
            $errorMessage = "新增標籤失敗：" . $stmt->error;
        }
    } else {
        $errorMessage = "請輸入標籤名稱！";
    }
}

// 獲取所有標籤
$tagsResult = $conn->query("SELECT ID, TagName FROM Tags");
$tags = $tagsResult->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新增貼文</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="css_work/style.css">
</head>

<body>

<div class="w3-container">
    <h2>新增貼文</h2>

    <?php if ($successMessage): ?>
        <div class="w3-panel w3-green">
            <h3>成功</h3>
            <p><?php echo $successMessage; ?></p>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="w3-panel w3-red">
            <h3>錯誤</h3>
            <p><?php echo $errorMessage; ?></p>
        </div>
    <?php endif; ?>

    <!-- 貼文表單 -->
    <form method="POST" action="post_add.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_post">
        <label for="Title">標題:</label>
        <input class="w3-input" type="text" name="Title" required>

        <label for="Content">內容:</label>
        <textarea class="w3-input" name="Content" rows="6"></textarea>

        <label for="Photo">圖片:</label>
        <input class="w3-input" type="file" name="Photo">

        <label for="Tags">選擇標籤(按ctrl可選多個標籤):</label>
        <select class="w3-select" name="Tags[]" multiple>
            <?php foreach ($tags as $tag): ?>
                <option value="<?php echo $tag['ID']; ?>"><?php echo $tag['TagName']; ?></option>
            <?php endforeach; ?>
        </select>

        <input class="w3-button w3-blue" type="submit" value="提交貼文">
    </form>

    <hr>

    <!-- 新增標籤表單 -->
    <h3>新增標籤</h3>
    <form method="POST" action="post_add.php">
        <input type="hidden" name="action" value="add_tag">
        <label for="TagName">標籤名稱:</label>
        <input class="w3-input" type="text" name="TagName" required>

        <label for="TagDescription">標籤描述:</label>
        <textarea class="w3-input" name="TagDescription" rows="3"></textarea>

        <input class="w3-button w3-green" type="submit" value="新增標籤">
    </form>
	<br><br>
    <form method="POST" action="mofain.php">
        <input type="submit" value="返回">
    </form> 
	<br><br>
</div>

</body>
</html>

<?php
// 關閉資料庫連接
$conn->close();
?>














