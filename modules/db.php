<?php
/**
 * Класс, предназначен для работы с базой данных
 */

class db
{

    public $connect;

    public $lastError = array();

    /**
     * Подключение к базе данных
     *
     * @param $inf - массив с данными для подключения
     */
    public function connect($inf)
    {
        $dsn = 'mysql:host='.$inf['host'].';dbname='.$inf['dbName'].';charset=utf8';
        $this->connect = new PDO($dsn, $inf['user'], $inf['pass']);
    }

    /**
     * Метод считывает данные с конфигурационного файла и возвращает конфигурации нужной базы
     *
     * @param $configName - строка, имя секции в файле конфигурации
     * @return bool - возвращает массив данных если есть указанная секция,
     * false - если нет указаной секции
     */
    public function getDBConnectionArray($configName, $configFile = '/config.ini')
    {
        $configArray = parse_ini_file($configFile, true);

        if (array_key_exists($configName, $configArray)) {
            return $configArray[$configName];
        }

        return false;
    }

    /**
     * Метод выполняет запрос
     *
     * @param string $sql - запрос
     * @return object - возвращет объект результата запроса
     */
    public function query($sql, $vars = array())
    {
        $res = $this->connect->prepare($sql);

        if ($res->execute($vars)) {
            $this->lastError = null;
            return $res;
        } else {
            $this->lastError[] = $res->errorInfo()[2];
            return false;
        }
    }

    /**
     * Добавляет записи в таблицу
     *
     * @param $tableName - имя таблицы
     * @param array $insert - массив значений
     * @return int|bool - возвращает id добавленого значения, если без ошибок,
     * false - если возникла ошибка добавления
     */
    public function insert($tableName, Array $insert)
    {
        $fieldsArray = array();
        $paramArray = array();
        foreach ($insert as $k => $v) {
            $fieldsArray[] = $k;
            $paramArray[] = ':' . $k;
        }

        $query = 'INSERT INTO '.$tableName.' (' . implode(', ', $fieldsArray) . ')' .
        ' VALUES(' . implode(', ', $paramArray) .')';

        $res = $this->query($query, $insert);

        if ($res) {
            return $this->connect->lastInsertId();
        }

        return false;
    }

    /**
     * Возвращает объект - результат запроса список полей из таблицы по критериям выборки
     *
     * @param $table - название таблицы
     * @param string $fields - список полей через запятую
     * @param string $where - список условий в виде строки
     * @return object
     */
    public function get($table, $fields = '*', $where = '')
    {
        $sql = '';

        $fields_sql = (is_array($fields)) ? implode(', ',$fields) : $fields;
        $sql .= 'SELECT ' . $fields_sql . ' FROM ' . $table;

        if (!empty($where)) {
            $sql .= ' WHERE '.$where;
        }

        return $this->query($sql);
    }

    /**
     * Построчный перебор результата запроса
     *
     * @param object $res - объект запроса
     * @return array - возвращает массив из строки объекта
     */
    public function fetch(&$res)
    {
        return $res->fetch(PDO::FETCH_ASSOC);
    }


    /**
     * Возвращение данных в виде массива
     *
     * @param $res - объект запроса
     */
    public function fetchAll(&$res)
    {
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Количество строк в результате зарпоса
     *
     * @param object $res - объект запроса
     * @return int - количество строк объекта
     */
    public function numRows(&$res)
    {
        return is_bool($res) ? 0 : $res->rowCount();
    }


}
