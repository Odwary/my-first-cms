<?php

/**
 * Класс для обработки пользователей
 */
class User
{
    // Свойства
    /**
    * @var int ID пользователя из базы данных
    */
    public $id = null;

    /**
    * @var string Логин пользователя
    */
    public $login = null;

    /**
    * @var string Пароль пользователя (хэшированный)
    */
    public $password = null;

    /**
    * @var int Активен ли пользователь (1 - активен, 0 - неактивен)
    */
    public $isActive = 1;
    
    /**
     * Создаст объект пользователя
     * 
     * @param array $data массив значений (столбцов) строки таблицы пользователей
     */
    public function __construct($data=array())
    {
        if (isset($data['id'])) {
            $this->id = (int) $data['id'];
        }
        
        if (isset($data['login'])) {
            $this->login = $data['login'];        
        }
        
        if (isset($data['password'])) {
            $this->password = $data['password'];  
        }
        
        if (isset($data['isActive'])) {
            $this->isActive = (int) $data['isActive'];  
        }
    }

    /**
    * Устанавливаем свойства с помощью значений формы редактирования записи в заданном массиве
    *
    * @param assoc Значения записи формы
    */
    public function storeFormValues ( $params ) {
        // Сохраняем все параметры (кроме пароля, его обработаем отдельно)
        if (isset($params['id'])) {
            $this->id = (int) $params['id'];
        }
        
        if (isset($params['login'])) {
            $this->login = $params['login'];        
        }
        
        // Обрабатываем чекбокс isActive (если не отмечен, он не отправляется в POST)
        if ( isset($params['isActive']) && $params['isActive'] == 1 ) {
            $this->isActive = 1;
        } else {
            $this->isActive = 0;
        }
        
        // Если пароль указан и не пустой, хэшируем его
        // Если пароль не указан или пустой, не трогаем существующий пароль
        if ( isset($params['password']) && !empty(trim($params['password'])) ) {
            $this->password = password_hash($params['password'], PASSWORD_DEFAULT);
        }
        // Если пароль не указан, оставляем $this->password как есть (null или существующее значение)
    }

    /**
    * Возвращаем объект пользователя соответствующий заданному ID
    *
    * @param int ID пользователя
    * @return User|false Объект пользователя или false, если запись не найдена или возникли проблемы
    */
    public static function getById($id) {
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $sql = "SELECT * FROM users WHERE id = :id";
        $st = $conn->prepare($sql);
        $st->bindValue(":id", $id, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch();
        $conn = null;
        
        if ($row) { 
            return new User($row);
        }
    }

    /**
    * Возвращаем объект пользователя по логину
    *
    * @param string $login Логин пользователя
    * @return User|false Объект пользователя или false, если запись не найдена
    */
    public static function getByLogin($login) {
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $sql = "SELECT * FROM users WHERE login = :login";
        $st = $conn->prepare($sql);
        $st->bindValue(":login", $login, PDO::PARAM_STR);
        $st->execute();

        $row = $st->fetch();
        $conn = null;
        
        if ($row) { 
            return new User($row);
        }
    }

    /**
    * Проверяет логин и пароль пользователя
    *
    * @param string $login Логин пользователя
    * @param string $password Пароль пользователя (не хэшированный)
    * @return User|false Объект пользователя если логин/пароль верны, иначе false
    */
    public static function authenticate($login, $password) {
        $user = self::getByLogin($login);
        
        if ($user && password_verify($password, $user->password)) {
            return $user;
        }
        
        return false;
    }

    /**
    * Возвращает все (или диапазон) объекты User из базы данных
    *
    * @param int $numRows Количество возвращаемых строк (по умолчанию = 1000000)
    * @param string $order Столбец, по которому выполняется сортировка (по умолчанию = "login ASC")
    * @return Array|false Двух элементный массив: results => массив объектов User; totalRows => общее количество строк
    */
    public static function getList($numRows=1000000, $order="login ASC") 
    {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
        $fromPart = "FROM users";
        $sql = "SELECT * $fromPart ORDER BY $order LIMIT :numRows";
        
        $st = $conn->prepare($sql);
        $st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
        $st->execute();
        $list = array();

        while ($row = $st->fetch()) {
            $user = new User($row);
            $list[] = $user;
        }

        // Получаем общее количество пользователей
        $sql = "SELECT COUNT(*) AS totalRows $fromPart";
        $st = $conn->prepare($sql);
        $st->execute();
        $totalRows = $st->fetch();
        $conn = null;
        
        return (array(
            "results" => $list, 
            "totalRows" => $totalRows[0]
            ) 
        );
    }

    /**
    * Вставляем текущий объект User в базу данных, устанавливаем его ID
    */
    public function insert() {
        // Есть уже у объекта User ID?
        if ( !is_null( $this->id ) ) trigger_error ( "User::insert(): Attempt to insert a User object that already has its ID property set (to $this->id).", E_USER_ERROR );

        // Вставляем пользователя
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $sql = "INSERT INTO users ( login, password, isActive ) VALUES ( :login, :password, :isActive )";
        $st = $conn->prepare ( $sql );
        $st->bindValue( ":login", $this->login, PDO::PARAM_STR );
        $st->bindValue( ":password", $this->password, PDO::PARAM_STR );
        $st->bindValue( ":isActive", $this->isActive, PDO::PARAM_INT );
        $st->execute();
        $this->id = $conn->lastInsertId();
        $conn = null;
    }

    /**
    * Обновляем текущий объект пользователя в базе данных
    */
    public function update() {
        // Есть ли у объекта пользователя ID?
        if ( is_null( $this->id ) ) trigger_error ( "User::update(): "
                . "Attempt to update a User object "
                . "that does not have its ID property set.", E_USER_ERROR );

        // Обновляем пользователя
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        
        // Если пароль не указан, не обновляем его
        if (empty($this->password)) {
            $sql = "UPDATE users SET login=:login, isActive=:isActive WHERE id = :id";
            $st = $conn->prepare ( $sql );
            $st->bindValue( ":login", $this->login, PDO::PARAM_STR );
            $st->bindValue( ":isActive", $this->isActive, PDO::PARAM_INT );
            $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
        } else {
            $sql = "UPDATE users SET login=:login, password=:password, isActive=:isActive WHERE id = :id";
            $st = $conn->prepare ( $sql );
            $st->bindValue( ":login", $this->login, PDO::PARAM_STR );
            $st->bindValue( ":password", $this->password, PDO::PARAM_STR );
            $st->bindValue( ":isActive", $this->isActive, PDO::PARAM_INT );
            $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
        }
        
        $st->execute();
        $conn = null;
    }

    /**
    * Удаляем текущий объект пользователя из базы данных
    */
    public function delete() {
        // Есть ли у объекта пользователя ID?
        if ( is_null( $this->id ) ) trigger_error ( "User::delete(): Attempt to delete a User object that does not have its ID property set.", E_USER_ERROR );

        // Удаляем пользователя
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $st = $conn->prepare ( "DELETE FROM users WHERE id = :id LIMIT 1" );
        $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
        $st->execute();
        $conn = null;
    }

}

