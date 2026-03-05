# MOFA 論壇管理系統 (MOFA Forum Management System)

本專案為 **資料庫管理系統（Database Management Systems, DBMS）課程專案**，實作一個基於 Web 的論壇管理系統。  
系統提供使用者註冊、登入、發文、留言以及標籤管理等功能，並透過關聯式資料庫設計與實作，展示完整的資料庫系統開發流程。

---

## Demo

📄 [Project Report](https://github.com/mirror-creator/MOFA-forum-system/tree/main/Demo)
🐙 [Source Code](https://github.com/mirror-creator/MOFA-forum-system/tree/main/Demo/Source%20Code)

---

## 系統功能

- 使用者註冊與登入
- 發表文章（Post）
- 留言功能（Comments）
- 標籤分類（Tags）
- 文章與使用者搜尋
- 後台資料管理

系統允許使用者建立文章、留言互動，並透過標籤系統組織文章內容，提升資訊管理與查詢效率。 

---

## 系統架構

本系統使用以下技術建置：

- **PHP**
- **MySQL**
- **Apache (XAMPP 環境)**

透過 PHP 連接 MySQL 資料庫，實現 Web 介面與資料庫之間的互動。

---

## 資料庫設計

資料庫主要包含以下資料表：

- **User**：使用者資訊
- **Post**：文章資料
- **Comments**：留言資料
- **Tags**：文章標籤
- **PostTags**：文章與標籤的多對多關係

系統透過 ERD（Entity Relationship Diagram）設計資料表之間的關聯。

---

## 專案結構
```
project-folder
│
├── account
├── post
├── comments
├── tags
├── uploads
│
├── database_building.sql
├── index.php
├── login.php
├── register.php
└── style.css
```


其中 `uploads/` 目錄用於儲存文章上傳的圖片檔案。

---

## 安裝與執行

1️⃣ 安裝 **XAMPP**
2️⃣ 將專案資料夾放入
3️⃣ 在 **phpMyAdmin** 中匯入資料庫：
4️⃣ 啟動 Apache 與 MySQL
5️⃣ 在瀏覽器開啟：

---

## 課程資訊

課程名稱：資料庫管理系統  
Database Management Systems (DBMS)  
National Taiwan University of Science and Technology

---

## 作者

WANG SSU-HUA
