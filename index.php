<?php

//phpinfo(); die();

require("config.php");

try {
    initApplication();
} catch (Exception $e) { 
    $results['errorMessage'] = $e->getMessage();
    require(TEMPLATE_PATH . "/viewErrorPage.php");
}


function initApplication()
{
    $action = isset($_GET['action']) ? $_GET['action'] : "";

    switch ($action) {
        case 'archive':
          archive();
          break;
        case 'archiveBySubcategory':
          archiveBySubcategory();
          break;
        case 'viewArticle':
          viewArticle();
          break;
        default:
          homepage();
    }
}

function archive() 
{
    $results = [];
    
    $categoryId = ( isset( $_GET['categoryId'] ) && $_GET['categoryId'] ) ? (int)$_GET['categoryId'] : null;
    
    $results['category'] = Category::getById( $categoryId );
    
    // Показываем только активные статьи на публичной странице
    $data = Article::getList( 100000, $results['category'] ? $results['category']->id : null, "publicationDate DESC", true );
    
    $results['articles'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    
    $data = Category::getList();
    $results['categories'] = array();
    
    foreach ( $data['results'] as $category ) {
        $results['categories'][$category->id] = $category;
    }
    
    // Загружаем подкатегории для отображения ссылок
    $subcategoriesData = Subcategory::getList();
    $results['subcategories'] = array();
    foreach ($subcategoriesData['results'] as $subcategory) {
        $results['subcategories'][$subcategory->id] = $subcategory;
    }
    
    $results['pageHeading'] = $results['category'] ?  $results['category']->name : "Article Archive";
    $results['pageTitle'] = $results['pageHeading'] . " | Widget News";
    
    require( TEMPLATE_PATH . "/archive.php" );
}

function archiveBySubcategory() 
{
    $results = [];
    
    $subcategoryId = ( isset( $_GET['subcategoryId'] ) && $_GET['subcategoryId'] ) ? (int)$_GET['subcategoryId'] : null;
    
    $results['subcategory'] = Subcategory::getById( $subcategoryId );
    
    if (!$results['subcategory']) {
        throw new Exception("Подкатегория не найдена");
    }
    
    // Показываем только активные статьи на публичной странице
    $data = Article::getList( 100000, null, "publicationDate DESC", true, $subcategoryId );
    
    $results['articles'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    
    // Загружаем категорию подкатегории
    $results['category'] = Category::getById($results['subcategory']->categoryId);
    
    $data = Category::getList();
    $results['categories'] = array();
    
    foreach ( $data['results'] as $category ) {
        $results['categories'][$category->id] = $category;
    }
    
    // Загружаем подкатегории для отображения ссылок
    $subcategoriesData = Subcategory::getList();
    $results['subcategories'] = array();
    foreach ($subcategoriesData['results'] as $subcategory) {
        $results['subcategories'][$subcategory->id] = $subcategory;
    }
    
    $results['pageHeading'] = $results['subcategory']->name;
    $results['pageTitle'] = $results['pageHeading'] . " | Widget News";
    
    require( TEMPLATE_PATH . "/archive.php" );
}

/**
 * Загрузка страницы с конкретной статьёй
 * 
 * @return null
 */
function viewArticle() 
{   
    if ( !isset($_GET["articleId"]) || !$_GET["articleId"] ) {
      homepage();
      return;
    }

    $results = array();
    $articleId = (int)$_GET["articleId"];
    $results['article'] = Article::getById($articleId);
    
    if (!$results['article']) {
        throw new Exception("Статья с id = $articleId не найдена");
    }
    
    // Проверяем, активна ли статья (на публичной странице показываем только активные)
    $isActive = isset($results['article']->isActive) ? (int)$results['article']->isActive : 1;
    if ($isActive != 1) {
        throw new Exception("Статья с id = $articleId не найдена");
    }
    
    $results['category'] = Category::getById($results['article']->categoryId);
    
    // Загружаем подкатегорию, если она есть
    if (isset($results['article']->subcategoryId) && $results['article']->subcategoryId) {
        $results['subcategory'] = Subcategory::getById($results['article']->subcategoryId);
    }
    
    $results['pageTitle'] = $results['article']->title . " | Простая CMS";
    
    require(TEMPLATE_PATH . "/viewArticle.php");
}

/**
 * Вывод домашней ("главной") страницы сайта
 */
function homepage() 
{
    $results = array();
    // Показываем только активные статьи на главной странице
    $data = Article::getList(HOMEPAGE_NUM_ARTICLES, null, "publicationDate DESC", true);
    $results['articles'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    
    $data = Category::getList();
    $results['categories'] = array();
    foreach ( $data['results'] as $category ) { 
        $results['categories'][$category->id] = $category;
    }
    
    // Загружаем подкатегории для отображения ссылок
    $subcategoriesData = Subcategory::getList();
    $results['subcategories'] = array();
    foreach ($subcategoriesData['results'] as $subcategory) {
        $results['subcategories'][$subcategory->id] = $subcategory;
    }
    
    $results['pageTitle'] = "Простая CMS на PHP";
    
//    echo "<pre>";
//    print_r($data);
//    echo "</pre>";
//    die();
    
    require(TEMPLATE_PATH . "/homepage.php");
    
}