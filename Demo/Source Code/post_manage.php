<?php
// 資料庫連接
$conn = new mysqli("localhost", "root", "", "account");
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

$successMessage = "";
$errorMessage = "";

session_start();
$userid = $_SESSION['userid'];  // 假設 'userid' 是 session 中儲存的使用者 ID

// 獲取所有標籤
$tagsResult = $conn->query("SELECT ID, TagName FROM Tags");
$tags = $tagsResult->fetch_all(MYSQLI_ASSOC);

// 刪除貼文
if (isset($_POST['delete'])) {
    $postid = $_POST['post_id'];
    $deleteSql = "DELETE FROM Post WHERE ID = ? AND AuthorID = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("is", $postid, $userid);
    if ($stmt->execute()) {
        $successMessage = "貼文刪除成功！";
    } else {
        $errorMessage = "刪除失敗：" . $stmt->error;
    }
}

// 修改貼文
if (isset($_POST['update'])) {
    $postid = $_POST['post_id'];
    $title = isset($_POST['Title']) ? $_POST['Title'] : '';
    $content = isset($_POST['Content']) ? $_POST['Content'] : null;
    $photo = isset($_FILES['Photo']) ? $_FILES['Photo'] : null;
    $selectedTags = isset($_POST['Tags']) ? $_POST['Tags'] : [];

    // 查詢原始資料
    $originalSql = "SELECT Title, Content, Photo FROM Post WHERE ID = ? AND AuthorID = ?";
    $stmt = $conn->prepare($originalSql);
    $stmt->bind_param("is", $postid, $userid);
    $stmt->execute();
    $result = $stmt->get_result();
    $originalData = $result->fetch_assoc();

    if ($originalData) {
        $title = empty($title) ? $originalData['Title'] : $title;
        $content = empty($content) ? $originalData['Content'] : $content;
        $photoPath = $originalData['Photo'];

        if ($photo && $photo['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'C:/xampp/htdocs/PERSONAL_REPORT/uploads/';
            $photoPath = $uploadDir . basename($photo['name']);
            if (!move_uploaded_file($photo['tmp_name'], $photoPath)) {
                $errorMessage = "圖片上傳失敗！";
            } else {
                $photoPath = 'http://localhost/PERSONAL_REPORT/uploads/' . basename($photo['name']);
            }
        }

        $updateSql = "UPDATE Post SET Title = ?, Content = ?, Photo = ? WHERE ID = ? AND AuthorID = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("sssis", $title, $content, $photoPath, $postid, $userid);

        if ($stmt->execute()) {
            // 更新貼文標籤
            $conn->query("DELETE FROM PostTags WHERE PostID = $postid");
            foreach ($selectedTags as $tagId) {
                $tagSql = "INSERT INTO PostTags (PostID, TagID) VALUES (?, ?)";
                $tagStmt = $conn->prepare($tagSql);
                $tagStmt->bind_param("ii", $postid, $tagId);
                $tagStmt->execute();
            }
            $successMessage = "貼文修改成功！";
        } else {
            $errorMessage = "修改失敗：" . $stmt->error;
        }
    } else {
        $errorMessage = "無法找到貼文，請重試！";
    }
}

// 列出使用者的貼文及相關標籤
$sql = "SELECT p.ID, p.Title, p.Content, p.Photo, p.PostDate, GROUP_CONCAT(pt.TagID) AS TagIDs 
        FROM Post p 
        LEFT JOIN PostTags pt ON p.ID = pt.PostID 
        WHERE p.AuthorID = ?
        GROUP BY p.ID";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userid);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理貼文</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
</head>
<body>
<div class="w3-container">
    <h2>管理貼文</h2>

    <?php if ($successMessage): ?>
        <div class="w3-panel w3-green">
            <h3>成功</h3>
            <p><?php echo htmlspecialchars($successMessage); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="w3-panel w3-red">
            <h3>錯誤</h3>
            <p><?php echo htmlspecialchars($errorMessage); ?></p>
        </div>
    <?php endif; ?>

    <table class="w3-table w3-bordered">
        <tr>
            <th>標題</th>
            <th>內容</th>
            <th>圖片</th>
            <th>發布日期</th>
            <th>標籤</th>
            <th>操作</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php 
                $currentTags = explode(',', $row['TagIDs'] ?? '');
                $currentTags = array_filter($currentTags); 
            ?>
            <tr>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="post_id" value="<?php echo $row['ID']; ?>">
                    <td><input class="w3-input" type="text" name="Title" value="<?php echo htmlspecialchars($row['Title']); ?>"></td>
                    <td><textarea class="w3-input" name="Content" rows="4"><?php echo htmlspecialchars($row['Content']); ?></textarea></td>
                    <td>
                        <?php if ($row['Photo']): ?>
                            <img src="<?php echo htmlspecialchars($row['Photo']); ?>" alt="Photo" style="width:100px;"><br>
                        <?php endif; ?>
                        <input class="w3-input" type="file" name="Photo">
                    </td>
                    <td><?php echo $row['PostDate']; ?></td>
                    <td>
                        <select class="w3-select" name="Tags[]" multiple>
                            <?php foreach ($tags as $tag): ?>
                                <option value="<?php echo $tag['ID']; ?>" 
                                    <?php echo in_array($tag['ID'], $currentTags) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tag['TagName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <button class="w3-button w3-blue" type="submit" name="update">修改</button>
                        <button class="w3-button w3-red" type="submit" name="delete" onclick="return confirm('確定刪除？');">刪除</button>
                    </td>
                </form>
            </tr>
        <?php endwhile; ?>
    </table>
	<br><br>
    <form method="POST" action="mofain.php">
        <input type="submit" value="返回">
    </form> 
	<br><br>
</div>
</body>
</html>

<?php
$conn->close();
?>




