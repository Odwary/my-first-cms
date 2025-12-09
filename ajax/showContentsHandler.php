<?php
require ('../config.php');

// Обработка GET запроса - возвращаем JSON
if (isset($_GET['articleId'])) {
    $article = Article::getById((int)$_GET['articleId']);
    // Проверяем, активна ли статья
    if ($article && (isset($article->isActive) ? (int)$article->isActive : 1) == 1) {
        header('Content-Type: application/json');
        echo json_encode(array(
            'content' => $article->content,
            'title' => $article->title,
            'id' => $article->id
        ));
    } else {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(array('error' => 'Article not found'));
    }
}

// Обработка POST запроса - возвращаем текст (content)
if (isset($_POST['articleId'])) {
    $article = Article::getById((int)$_POST['articleId']);
    // Проверяем, активна ли статья
    if ($article && (isset($article->isActive) ? (int)$article->isActive : 1) == 1) {
        header('Content-Type: text/html; charset=utf-8');
        echo $article->content;
    } else {
        http_response_code(404);
        echo "Article not found";
    }
//        die("Привет)");
//    $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
//    
//        if (isset($conn)) {
//            die("Соединенте установлено");
//        }
//        else {
//            die("Соединение не установлено");
//        }
//    $article = "WHERE Id=". (int)$_POST[articleId];
//    echo $article;
//    $sql = "SELECT content FROM articles". $article;
//    $contentFromDb = $conn->prepare( $sql );
//    $contentFromDb->execute();
//    $result = $contentFromDb->fetch();
//    $conn = null;
//    echo json_encode($result);
}

