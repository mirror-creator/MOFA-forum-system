<?php  
session_start();
$conn = new mysqli("localhost", "root", "", "account");
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

// 當前登入者的ID
$current_user_id = $_SESSION['userid'] ?? null;
if (empty($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}

// 處理搜尋邏輯
$keyword = $_GET['keyword'] ?? "";
$likeKeyword = "%" . $keyword . "%";

// 每頁顯示的貼文數量
$postsPerPage = 5;

// 獲取當前頁數，預設為第 1 頁
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, $currentPage); // 確保頁數不小於 1

// 計算偏移量
$offset = ($currentPage - 1) * $postsPerPage;

// 根據關鍵字查詢總記錄數
if (!empty($keyword)) {
    $totalPostsQuery = "
        SELECT COUNT(DISTINCT Post.ID) AS Total
        FROM Post
        LEFT JOIN User_ ON Post.AuthorID = User_.ID
        LEFT JOIN PostTags ON Post.ID = PostTags.PostID
        LEFT JOIN Tags ON PostTags.TagID = Tags.ID
        WHERE Post.Title LIKE ?
           OR Post.Content LIKE ?
           OR User_.UserName LIKE ?
           OR Tags.TagName LIKE ?
    ";
    $stmt = $conn->prepare($totalPostsQuery);
    $stmt->bind_param("ssss", $likeKeyword, $likeKeyword, $likeKeyword, $likeKeyword);
    $stmt->execute();
    $result = $stmt->get_result();
    $totalPosts = $result->fetch_assoc()['Total'] ?? 0;
} else {
    $totalPostsQuery = "SELECT COUNT(*) AS Total FROM Post";
    $result = $conn->query($totalPostsQuery);
    $totalPosts = $result->fetch_assoc()['Total'] ?? 0;
}

// 計算總頁數
$totalPages = ceil($totalPosts / $postsPerPage);

// 查詢貼文（根據是否有關鍵字）
if (!empty($keyword)) {
    $search_query = "
        SELECT DISTINCT Post.ID, Post.Title, Post.Content, Post.Photo, Post.PostDate,
                        User_.UserName AS AuthorName,
                        GROUP_CONCAT(Tags.TagName SEPARATOR ', ') AS Tags
        FROM Post
        LEFT JOIN User_ ON Post.AuthorID = User_.ID
        LEFT JOIN PostTags ON Post.ID = PostTags.PostID
        LEFT JOIN Tags ON PostTags.TagID = Tags.ID
        WHERE Post.Title LIKE ?
           OR Post.Content LIKE ?
           OR User_.UserName LIKE ?
           OR Tags.TagName LIKE ?
        GROUP BY Post.ID
        ORDER BY Post.PostDate DESC
        LIMIT ? OFFSET ?
    ";
    $stmt = $conn->prepare($search_query);
    $stmt->bind_param("ssssii", $likeKeyword, $likeKeyword, $likeKeyword, $likeKeyword, $postsPerPage, $offset);
} else {
    $search_query = "
        SELECT Post.ID, Post.Title, Post.Content, Post.Photo, Post.PostDate,
               User_.UserName AS AuthorName,
               GROUP_CONCAT(Tags.TagName SEPARATOR ', ') AS Tags
        FROM Post
        LEFT JOIN User_ ON Post.AuthorID = User_.ID
        LEFT JOIN PostTags ON Post.ID = PostTags.PostID
        LEFT JOIN Tags ON PostTags.TagID = Tags.ID
        GROUP BY Post.ID
        ORDER BY Post.PostDate DESC
        LIMIT ? OFFSET ?
    ";
    $stmt = $conn->prepare($search_query);
    $stmt->bind_param("ii", $postsPerPage, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

// 新增刪除留言後的 DOM 更新機制
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment'])) {
    $commentId = $_POST['comment_id'];

    if (!empty($current_user_id) && !empty($commentId)) {
        // 確認該留言屬於當前使用者
        $checkCommentOwnerSql = "SELECT UserID FROM comments WHERE ID = ?";
        $stmt = $conn->prepare($checkCommentOwnerSql);
        $stmt->bind_param("i", $commentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $comment = $result->fetch_assoc();

        if ($comment && $comment['UserID'] == $current_user_id) {
            // 執行刪除
            $deleteCommentSql = "DELETE FROM comments WHERE ID = ?";
            $stmt = $conn->prepare($deleteCommentSql);
            $stmt->bind_param("i", $commentId);
            if ($stmt->execute()) {
                // 刪除成功後直接返回 JSON 格式結果，避免全頁重整
                echo json_encode(["success" => true]);
                exit;
            } else {
                echo json_encode(["success" => false, "error" => $stmt->error]);
                exit;
            }
        } else {
            echo json_encode(["success" => false, "error" => "無權刪除他人留言！"]);
            exit;
        }
    } else {
        echo json_encode(["success" => false, "error" => "無法刪除，缺少必要資料！"]);
        exit;
    }
}
?>

<script>
// JavaScript 動態處理刪除留言邏輯
function deleteComment(commentId) {
    if (confirm('確定要刪除這則留言嗎？')) {
        fetch(window.location.href, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ delete_comment: true, comment_id: commentId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`comment-${commentId}`).remove();
            } else {
                alert(`刪除失敗: ${data.error}`);
            }
        })
        .catch(error => console.error('刪除失敗', error));
    }
}
</script>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MOFA首頁</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrap">
    <div class="header">
        <h1>歡迎來到 MOFA</h1>
        <form method="POST" action="logout.php" style="display: inline;">
            <input type="submit" value="登出" name="logout" class="logout-button">
        </form>
        <div class="nav-container">
            <a href="account.php">帳號管理</a> |
            <a href="post_manage.php">貼文管理</a> |
            <a href="post_add.php">新增貼文</a>
        </div>
        <div class="search-container">
            <form method="GET" action="mofain.php" style="margin-top: 10px;">
                <input type="text" name="keyword" placeholder="輸入關鍵字..." value="<?= htmlspecialchars($keyword) ?>">
                <button type="submit">查詢</button>
                <a href="mofain.php" class="clear-search">清除搜尋</a>
            </form>
        </div>
    </div>

    <div class="content">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $post_id = $row['ID'];
                echo "<div class='blog-post'>";
                echo "<h2>" . htmlspecialchars($row['Title']) . "</h2>";
                echo "<p><strong>作者:</strong> " . htmlspecialchars($row['AuthorName'] ?? "未知作者") . "</p>";
                echo "<p><strong>發佈時間:</strong></p>";
                echo "<p>" . nl2br(htmlspecialchars($row['Content'])) . "</p>";
				
				if (!empty($row['Photo'])) {
					echo "<img src='" . htmlspecialchars($row['Photo']) . "' alt='Post Image' style='width:100%; max-height:400px; object-fit:cover;'>";
				}
				if (!empty($row['Tags'])) {
					echo "<p><strong>標籤:</strong> " . htmlspecialchars($row['Tags']) . "</p>";
				}

				// 顯示留言區
				echo "<div class='comment-section'>";
				echo "<h3>留言區</h3>";

				$comment_sql = "SELECT comments.ID, comments.Content, comments.CommentDate, comments.UserID, User_.UserName 
								FROM comments
								JOIN User_ ON comments.UserID = User_.ID
								WHERE comments.PostID = ?";
				$comment_stmt = $conn->prepare($comment_sql);
				$comment_stmt->bind_param("i", $post_id);
				$comment_stmt->execute();
				$comment_result = $comment_stmt->get_result();

				if ($comment_result->num_rows > 0) {
					while ($comment = $comment_result->fetch_assoc()) {
						echo "<div class='comment' id='comment-" . htmlspecialchars($comment['ID']) . "'>";
						echo "<p class='comment-author'>" . htmlspecialchars($comment['UserName']) . "</p>";
						echo "<p class='comment-date'>" . htmlspecialchars($comment['CommentDate']) . "</p>";
						echo "<p>" . nl2br(htmlspecialchars($comment['Content'])) . "</p>";
						if ($comment['UserID'] == $current_user_id) {
							echo "<button onclick='deleteComment(" . $comment['ID'] . ")'>刪除</button>";
						}
						echo "</div>";
					}
				} else {
					echo "<p>目前沒有留言。</p>";
				}

				// 新增留言表單
				echo "<form method='POST' action='add_comment.php' class='comment-form'>";
				echo "<textarea name='comment_content' placeholder='新增留言...'></textarea>";
				echo "<input type='hidden' name='post_id' value='$post_id'>";
				echo "<button type='submit'>送出留言</button>";
				echo "</form>";

				echo "</div>"; // end comment-section
				echo "</div>"; // end blog-post
            }
        } else {
            echo "<p>未找到相關貼文。</p>";
        }
        ?>
    </div>

    <div class="pagination">
        <?php
        if ($currentPage > 1) {
            echo '<a href="?keyword=' . urlencode($keyword) . '&page=1">第一頁</a>';
            echo '<a href="?keyword=' . urlencode($keyword) . '&page=' . ($currentPage - 1) . '">上一頁</a>';
        }
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i == $currentPage) {
                echo '<span class="current-page">' . $i . '</span>';
            } else {
                echo '<a href="?keyword=' . urlencode($keyword) . '&page=' . $i . '">' . $i . '</a>';
            }
        }
        if ($currentPage < $totalPages) {
            echo '<a href="?keyword=' . urlencode($keyword) . '&page=' . ($currentPage + 1) . '">下一頁</a>';
            echo '<a href="?keyword=' . urlencode($keyword) . '&page=' . $totalPages . '">最後一頁</a>';
        }
        ?>
    </div>
</div>
</body>
</html>