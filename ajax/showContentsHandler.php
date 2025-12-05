<?php
require ('../config.php');

if (isset($_GET['articleId'])) {
    $article = Article::getById((int)$_GET['articleId']);
    // Проверяем, активна ли статья
    if ($article && (isset($article->isActive) ? (int)$article->isActive : 1) == 1) {
        echo $article->content;
    } else {
        http_response_code(404);
        echo "Article not found";
    }
}
if (isset ($_POST['articleId'])) {
    //die("Привет)");
    $article = Article::getById((int)$_POST['articleId']);
    // Проверяем, активна ли статья
    if ($article && (isset($article->isActive) ? (int)$article->isActive : 1) == 1) {
        echo json_encode($article);
    } else {
        http_response_code(404);
        echo json_encode(array('error' => 'Article not found'));
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

