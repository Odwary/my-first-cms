<?php

require("config.php");
session_start();
$action = isset($_GET['action']) ? $_GET['action'] : "";
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "";

if ($action != "login" && $action != "logout" && !$username) {
    login();
    exit;
}

switch ($action) {
    case 'login':
        login();
        break;
    case 'logout':
        logout();
        break;
    case 'newArticle':
        newArticle();
        break;
    case 'editArticle':
        editArticle();
        break;
    case 'deleteArticle':
        deleteArticle();
        break;
    case 'listCategories':
        listCategories();
        break;
    case 'newCategory':
        newCategory();
        break;
    case 'editCategory':
        editCategory();
        break;
    case 'deleteCategory':
        deleteCategory();
        break;
    case 'listUsers':
        listUsers();
        break;
    case 'newUser':
        newUser();
        break;
    case 'editUser':
        editUser();
        break;
    case 'deleteUser':
        deleteUser();
        break;
    default:
        listArticles();
}

/**
 * Авторизация пользователя (админа) -- установка значения в сессию
 */
function login() {

    $results = array();
    $results['pageTitle'] = "Admin Login | Widget News";

    if (isset($_POST['login'])) {

        // Пользователь получает форму входа: попытка авторизировать пользователя
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        // Если логин "admin", используем старую проверку через константы
        if ($username == ADMIN_USERNAME && $password == ADMIN_PASSWORD) {
            // Вход прошел успешно: создаем сессию и перенаправляем на страницу администратора
            $_SESSION['username'] = ADMIN_USERNAME;
            header( "Location: admin.php");
            return;
        }
        
        // Если логин не "admin", проверяем через БД
        if ($username != ADMIN_USERNAME) {
            $user = User::authenticate($username, $password);
            
            if ($user) {
                // Проверяем, активен ли пользователь
                if ($user->isActive == 1) {
                    // Вход прошел успешно: создаем сессию и перенаправляем на страницу администратора
                    $_SESSION['username'] = $user->login;
                    header( "Location: admin.php");
                    return;
                } else {
                    // Пользователь неактивен
                    $results['errorMessage'] = "Ваш аккаунт деактивирован. Обратитесь к администратору.";
                    require( TEMPLATE_PATH . "/admin/loginForm.php" );
                    return;
                }
            } else {
                // Неверный логин или пароль
                $results['errorMessage'] = "Неправильный логин или пароль, попробуйте ещё раз.";
                require( TEMPLATE_PATH . "/admin/loginForm.php" );
                return;
            }
        }

        // Если дошли сюда, значит логин "admin", но пароль неверный
        $results['errorMessage'] = "Неправильный пароль, попробуйте ещё раз.";
        require( TEMPLATE_PATH . "/admin/loginForm.php" );

    } else {

      // Пользователь еще не получил форму: выводим форму
      require(TEMPLATE_PATH . "/admin/loginForm.php");
    }

}


function logout() {
    unset( $_SESSION['username'] );
    header( "Location: admin.php" );
}


function newArticle() {
	  
    $results = array();
    $results['pageTitle'] = "New Article";
    $results['formAction'] = "newArticle";

    if ( isset( $_POST['saveChanges'] ) ) {
//            echo "<pre>";
//            print_r($results);
//            print_r($_POST);
//            echo "<pre>";
//            В $_POST данные о статье сохраняются корректно
        // Пользователь получает форму редактирования статьи: сохраняем новую статью
        $article = new Article();
        $article->storeFormValues( $_POST );
//            echo "<pre>";
//            print_r($article);
//            echo "<pre>";
//            А здесь данные массива $article уже неполные(есть только Число от даты, категория и полный текст статьи)          
        $article->insert();
        header( "Location: admin.php?status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // Пользователь сбросил результаты редактирования: возвращаемся к списку статей
        header( "Location: admin.php" );
    } else {

        // Пользователь еще не получил форму редактирования: выводим форму
        $results['article'] = new Article;
        $data = Category::getList();
        $results['categories'] = $data['results'];
        require( TEMPLATE_PATH . "/admin/editArticle.php" );
    }
}


/**
 * Редактирование статьи
 * 
 * @return null
 */
function editArticle() {
	  
    $results = array();
    $results['pageTitle'] = "Edit Article";
    $results['formAction'] = "editArticle";

    if (isset($_POST['saveChanges'])) {

        // Пользователь получил форму редактирования статьи: сохраняем изменения
        if ( !$article = Article::getById( (int)$_POST['articleId'] ) ) {
            header( "Location: admin.php?error=articleNotFound" );
            return;
        }

        $article->storeFormValues( $_POST );
        $article->update();
        header( "Location: admin.php?status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // Пользователь отказался от результатов редактирования: возвращаемся к списку статей
        header( "Location: admin.php" );
    } else {

        // Пользвоатель еще не получил форму редактирования: выводим форму
        $results['article'] = Article::getById((int)$_GET['articleId']);
        $data = Category::getList();
        $results['categories'] = $data['results'];
        require(TEMPLATE_PATH . "/admin/editArticle.php");
    }

}


function deleteArticle() {

    if ( !$article = Article::getById( (int)$_GET['articleId'] ) ) {
        header( "Location: admin.php?error=articleNotFound" );
        return;
    }

    $article->delete();
    header( "Location: admin.php?status=articleDeleted" );
}


function listArticles() {
    $results = array();
    
    $data = Article::getList();
    $results['articles'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    
    $data = Category::getList();
    $results['categories'] = array();
    foreach ($data['results'] as $category) { 
        $results['categories'][$category->id] = $category;
    }
    
    $results['pageTitle'] = "Все статьи";

    if (isset($_GET['error'])) { // вывод сообщения об ошибке (если есть)
        if ($_GET['error'] == "articleNotFound") 
            $results['errorMessage'] = "Error: Article not found.";
        if ($_GET['error'] == "accessDenied") 
            $results['errorMessage'] = "Access denied. Only administrator can manage users.";
    }

    if (isset($_GET['status'])) { // вывод сообщения (если есть)
        if ($_GET['status'] == "changesSaved") {
            $results['statusMessage'] = "Your changes have been saved.";
        }
        if ($_GET['status'] == "articleDeleted")  {
            $results['statusMessage'] = "Article deleted.";
        }
    }

    require(TEMPLATE_PATH . "/admin/listArticles.php" );
}

function listCategories() {
    $results = array();
    $data = Category::getList();
    $results['categories'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    $results['pageTitle'] = "Article Categories";

    if ( isset( $_GET['error'] ) ) {
        if ( $_GET['error'] == "categoryNotFound" ) $results['errorMessage'] = "Error: Category not found.";
        if ( $_GET['error'] == "categoryContainsArticles" ) $results['errorMessage'] = "Error: Category contains articles. Delete the articles, or assign them to another category, before deleting this category.";
    }

    if ( isset( $_GET['status'] ) ) {
        if ( $_GET['status'] == "changesSaved" ) $results['statusMessage'] = "Your changes have been saved.";
        if ( $_GET['status'] == "categoryDeleted" ) $results['statusMessage'] = "Category deleted.";
    }

    require( TEMPLATE_PATH . "/admin/listCategories.php" );
}
	  
	  
function newCategory() {

    $results = array();
    $results['pageTitle'] = "New Article Category";
    $results['formAction'] = "newCategory";

    if ( isset( $_POST['saveChanges'] ) ) {

        // User has posted the category edit form: save the new category
        $category = new Category;
        $category->storeFormValues( $_POST );
        $category->insert();
        header( "Location: admin.php?action=listCategories&status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // User has cancelled their edits: return to the category list
        header( "Location: admin.php?action=listCategories" );
    } else {

        // User has not posted the category edit form yet: display the form
        $results['category'] = new Category;
        require( TEMPLATE_PATH . "/admin/editCategory.php" );
    }

}


function editCategory() {

    $results = array();
    $results['pageTitle'] = "Edit Article Category";
    $results['formAction'] = "editCategory";

    if ( isset( $_POST['saveChanges'] ) ) {

        // User has posted the category edit form: save the category changes

        if ( !$category = Category::getById( (int)$_POST['categoryId'] ) ) {
          header( "Location: admin.php?action=listCategories&error=categoryNotFound" );
          return;
        }

        $category->storeFormValues( $_POST );
        $category->update();
        header( "Location: admin.php?action=listCategories&status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // User has cancelled their edits: return to the category list
        header( "Location: admin.php?action=listCategories" );
    } else {

        // User has not posted the category edit form yet: display the form
        $results['category'] = Category::getById( (int)$_GET['categoryId'] );
        require( TEMPLATE_PATH . "/admin/editCategory.php" );
    }

}


function deleteCategory() {

    if ( !$category = Category::getById( (int)$_GET['categoryId'] ) ) {
        header( "Location: admin.php?action=listCategories&error=categoryNotFound" );
        return;
    }

    $articles = Article::getList( 1000000, $category->id );

    if ( $articles['totalRows'] > 0 ) {
        header( "Location: admin.php?action=listCategories&error=categoryContainsArticles" );
        return;
    }

    $category->delete();
    header( "Location: admin.php?action=listCategories&status=categoryDeleted" );
}

function listUsers() {
    // Проверяем, что только admin может управлять пользователями
    if ($_SESSION['username'] != ADMIN_USERNAME) {
        header( "Location: admin.php?error=accessDenied" );
        return;
    }
    
    $results = array();
    
    $data = User::getList();
    $results['users'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    
    $results['pageTitle'] = "All Users";

    if (isset($_GET['error'])) {
        if ($_GET['error'] == "userNotFound") 
            $results['errorMessage'] = "Error: User not found.";
        if ($_GET['error'] == "userExists") 
            $results['errorMessage'] = "Error: User with this login already exists.";
    }

    if (isset($_GET['status'])) {
        if ($_GET['status'] == "changesSaved") {
            $results['statusMessage'] = "Your changes have been saved.";
        }
        if ($_GET['status'] == "userDeleted")  {
            $results['statusMessage'] = "User deleted.";
        }
    }

    require(TEMPLATE_PATH . "/admin/listUsers.php" );
}

function newUser() {
    // Проверяем, что только admin может управлять пользователями
    if ($_SESSION['username'] != ADMIN_USERNAME) {
        header( "Location: admin.php?error=accessDenied" );
        return;
    }
    
    $results = array();
    $results['pageTitle'] = "New User";
    $results['formAction'] = "newUser";

    if ( isset( $_POST['saveChanges'] ) ) {
        // Пользователь получает форму редактирования: сохраняем нового пользователя
        $user = new User();
        $user->storeFormValues( $_POST );
        
        // Проверяем, что пароль указан для нового пользователя
        if (empty($_POST['password'])) {
            $results['errorMessage'] = "Password is required for new user.";
            $results['user'] = $user;
            require( TEMPLATE_PATH . "/admin/editUser.php" );
            return;
        }
        
        // Проверяем, не существует ли уже пользователь с таким логином
        $existingUser = User::getByLogin($user->login);
        if ($existingUser) {
            $results['errorMessage'] = "User with login '{$user->login}' already exists.";
            $results['user'] = $user;
            require( TEMPLATE_PATH . "/admin/editUser.php" );
            return;
        }
        
        $user->insert();
        header( "Location: admin.php?action=listUsers&status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {
        // Пользователь сбросил результаты редактирования: возвращаемся к списку пользователей
        header( "Location: admin.php?action=listUsers" );
    } else {
        // Пользователь еще не получил форму редактирования: выводим форму
        $results['user'] = new User;
        require( TEMPLATE_PATH . "/admin/editUser.php" );
    }
}

function editUser() {
    // Проверяем, что только admin может управлять пользователями
    if ($_SESSION['username'] != ADMIN_USERNAME) {
        header( "Location: admin.php?error=accessDenied" );
        return;
    }
    
    $results = array();
    $results['pageTitle'] = "Edit User";
    $results['formAction'] = "editUser";

    if (isset($_POST['saveChanges'])) {
        // Пользователь получил форму редактирования: сохраняем изменения
        if ( !$user = User::getById( (int)$_POST['userId'] ) ) {
            header( "Location: admin.php?action=listUsers&error=userNotFound" );
            return;
        }

        // Проверяем, не существует ли уже пользователь с таким логином (если логин изменился)
        $newLogin = $_POST['login'];
        if ($newLogin != $user->login) {
            $existingUser = User::getByLogin($newLogin);
            if ($existingUser) {
                $results['errorMessage'] = "User with login '{$newLogin}' already exists.";
                $results['user'] = $user;
                require(TEMPLATE_PATH . "/admin/editUser.php");
                return;
            }
        }

        // Сохраняем старый пароль, если новый не указан
        $oldPassword = $user->password;
        $user->storeFormValues( $_POST );
        
        // Если пароль не указан, оставляем старый
        if (empty($_POST['password'])) {
            $user->password = $oldPassword;
        }
        
        $user->update();
        header( "Location: admin.php?action=listUsers&status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {
        // Пользователь отказался от результатов редактирования: возвращаемся к списку пользователей
        header( "Location: admin.php?action=listUsers" );
    } else {
        // Пользователь еще не получил форму редактирования: выводим форму
        $results['user'] = User::getById((int)$_GET['userId']);
        require(TEMPLATE_PATH . "/admin/editUser.php");
    }
}

function deleteUser() {
    // Проверяем, что только admin может управлять пользователями
    if ($_SESSION['username'] != ADMIN_USERNAME) {
        header( "Location: admin.php?error=accessDenied" );
        return;
    }
    
    if ( !$user = User::getById( (int)$_GET['userId'] ) ) {
        header( "Location: admin.php?action=listUsers&error=userNotFound" );
        return;
    }

    $user->delete();
    header( "Location: admin.php?action=listUsers&status=userDeleted" );
}

        