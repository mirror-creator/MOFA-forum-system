<?php
// 資料庫連接共用設定
$conn = new mysqli("localhost", "root", "", "account");
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

// 資料庫連接
$conn = new mysqli("localhost", "root", "", "account");
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

// 搜索處理邏輯
function handleSearch($conn, $searchTerm, $tables) {
    $query = "";
    $paramCount = 0;  // 用來追蹤需要的參數數量

    if (in_array("user_", $tables) && in_array("post", $tables) && in_array("comments", $tables)) {
        // 查詢使用者、其貼文及相關留言
        $query = "
            SELECT u.UserName, p.Title, p.Content, COALESCE(c.Content, '無留言') AS CommentContent
            FROM user_ u
            LEFT JOIN post p ON u.ID = p.AuthorID
            LEFT JOIN comments c ON p.ID = c.PostID
            WHERE u.UserName LIKE ? OR p.Title LIKE ? OR c.Content LIKE ?";
        $paramCount = 3;  // 這個查詢需要 3 個參數
    } elseif (in_array("user_", $tables) && in_array("post", $tables)) {
        // 查詢使用者及其發佈的貼文
        $query = "
            SELECT u.UserName, p.Title, p.Content
            FROM user_ u
            LEFT JOIN post p ON u.ID = p.AuthorID
            WHERE u.UserName LIKE ? OR p.Title LIKE ? OR p.Content LIKE ?";
        $paramCount = 3;  // 這個查詢需要 3 個參數
    } elseif (in_array("post", $tables) && in_array("comments", $tables)) {
        // 查詢貼文及相關留言
        $query = "
            SELECT p.Title, p.Content, c.Content AS CommentContent
            FROM post p
            LEFT JOIN comments c ON p.ID = c.PostID
            WHERE p.Title LIKE ? OR p.Content LIKE ?  OR c.Content LIKE ?";
        $paramCount = 3;  // 這個查詢需要 3 個參數
    } elseif (in_array("user_", $tables) && in_array("comments", $tables)) {
        // 查詢使用者及其相關留言
        $query = "
            SELECT u.UserName, c.Content AS CommentContent
            FROM user_ u
            LEFT JOIN comments c ON u.ID = c.UserID
            WHERE u.UserName LIKE ? OR c.Content LIKE ?";
        $paramCount = 2;  // 這個查詢需要 2 個參數
    } elseif (in_array("post", $tables) && in_array("posttags", $tables) && in_array("tags", $tables)) {
        // 查詢貼文及其相關標籤
        $query = "
            SELECT p.Title, p.Content, t.TagName
            FROM post p
            LEFT JOIN posttags pt ON p.ID = pt.PostID
            LEFT JOIN tags t ON pt.TagID = t.ID
            WHERE p.Title LIKE ? OR t.TagName LIKE ?";
        $paramCount = 2;  // 這個查詢需要 2 個參數
    }

    if ($query) {
        $stmt = $conn->prepare($query);
        $searchTerm = "%" . $searchTerm . "%"; // 加上通配符進行模糊查詢
        
        // 動態處理 bind_param，根據所需的參數數量進行調整
        if ($paramCount == 2) {
            $stmt->bind_param("ss", $searchTerm, $searchTerm);  // 需要兩個參數
        } elseif ($paramCount == 3) {
            $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);  // 需要三個參數
        } elseif ($paramCount == 4) {
            $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);  // 需要四個參數
        }

        $stmt->execute();
        return $stmt->get_result();
    }
    return null;
}


// 顯示搜索欄和表單
echo "<h1>資料表搜尋</h1>";
echo "<form method='POST'>";
echo "<input type='text' name='searchTerm' placeholder='輸入搜尋關鍵字' required>";
echo "<br><br>";
echo "選擇查詢類型: ";
echo "<input type='checkbox' name='tables[]' value='user_'> 使用者 ";
echo "<input type='checkbox' name='tables[]' value='post'> 貼文 ";
echo "<input type='checkbox' name='tables[]' value='comments'> 留言 ";
echo "<input type='checkbox' name='tables[]' value='posttags'> 貼文標籤 ";
echo "<input type='checkbox' name='tables[]' value='tags'> 標籤 ";
echo "<br><br>";
echo "<button type='submit' name='search' value='1'>查詢</button>";
echo "</form>";

// 處理搜尋請求
if (isset($_POST['search'])) {
    $searchTerm = $_POST['searchTerm'];
    $tables = $_POST['tables'] ?? [];
    
    if (!empty($tables)) {
        $result = handleSearch($conn, $searchTerm, $tables);
        if ($result && $result->num_rows > 0) {
            echo "<h2>搜尋結果</h2>";
            echo "<table border='1'>";
            $columns = array_keys($result->fetch_assoc());
            echo "<tr>";
            foreach ($columns as $col) {
                echo "<th>{$col}</th>";
            }
            echo "</tr>";
            $result->data_seek(0); // 重置結果指標
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>沒有找到相關資料。</p>";
        }
    } else {
        echo "<p>請選擇至少一個查詢條件。</p>";
    }
}



// 共用的刪除邏輯函數
function canDelete($conn, $table, $id, $adminId) {
    if ($table === 'user_') {
        $query = "SELECT UserName FROM user_ WHERE ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            return $row['UserName'] !== '管理員';
        }
        return false;
    } elseif ($table === 'comments') {
        $query = "SELECT UserID FROM comments WHERE ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            return $row['UserID'] !== $adminId;
        }
        return false;
    } elseif ($table === 'post') {
        $query = "SELECT AuthorID FROM post WHERE ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            return $row['AuthorID'] !== $adminId;
        }
        return false;
    }
    return true;
}

// 獨立資料表頁面樣板生成器
function generateTablePage($tableName, $columns) {
    global $conn;
    $adminId = 0; // 管理員 ID
    $deleteMessage = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
        $id = $_POST['id'] ?? "";
        if (!empty($id) && canDelete($conn, $tableName, $id, $adminId) && $tableName !== 'posttags') {
            $query = "DELETE FROM `$tableName` WHERE ID = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $deleteMessage = "資料已成功刪除。";
            } else {
                $deleteMessage = "刪除失敗: " . $stmt->error;
            }
        } else {
            $deleteMessage = "無法刪除管理員的資料或相關資料。";
        }
    }

    $query = "SELECT * FROM `$tableName`";
    $result = $conn->query($query);
    echo "<h1>{$tableName} 資料表</h1>";
    if ($deleteMessage) {
        echo "<div style='color: red;'>{$deleteMessage}</div>";
    }
    echo "<table border='1'>";
    echo "<tr>";
    foreach ($columns as $col) {
        echo "<th>{$col}</th>";
    }
    
    // 如果表格名稱不是 posttags，則顯示刪除按鈕
    if ($tableName !== 'posttags') {
        echo "<th>操作</th>";
    }
    echo "</tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($columns as $col) {
            echo "<td>" . htmlspecialchars($row[$col]) . "</td>";
        }
        
        // 只有當表格名稱不是 posttags 時，才顯示刪除按鈕
        if ($tableName !== 'posttags') {
            echo "<td>
                    <form method='POST' style='display:inline;'>
                        <input type='hidden' name='id' value='{$row['ID']}'>
                        <button type='submit' name='delete' value='1'>刪除</button>
                    </form>
                  </td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

// 生成 (各表獨立頁面與查詢邏輯)
generateTablePage('user_', ['ID', 'UserName', 'UserPassword', 'Phone','Email']);
generateTablePage('post', ['ID', 'AuthorID', 'Title','Content', 'Photo', 'PostDate']);
generateTablePage('comments', ['ID', 'PostID', 'UserID','Content', 'CommentDate']);
generateTablePage('tags', ['ID', 'TagName', 'TagDescription']);
generateTablePage('posttags', ['PostID', 'TagID']);


?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>後台管理</title>
</head>
<body>
<br><br>
	<form method="POST" action="logout.php">
		<input type="submit" value="登出">
	</form>
</body>
</html>