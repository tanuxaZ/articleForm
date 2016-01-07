<?php
/**
 * Класс для работы со статьями
 */

require_once('db.php');
require_once('uploadImage.php');


class article
{
    const TABLE_NAME = 'articles';
    const IMG_UPLOAD_PATH = './upload/articles/';
    const IMG_UPLOAD_TYPE = 'jpg|jpeg|png';
    const IMG_UPLOAD_MAX_SIZE = '2000';

    public $dbConnect;
    public $error = array();

    /**
     * Массив правил валидации полей в форме добавления статьи
     *
     * @var array
     */
    public $addValidateRules = array(
        array(
            'field'   => 'date',
            'label'   => 'Дата',
            'rules'   => 'required'
        ),
        array(
            'field'   => 'title',
            'label'   => 'Заголовок',
            'rules'   => 'required|max_length[200]'
        ),
        array(
            'field'   => 'text',
            'label'   => 'Текст',
            'rules'   => 'required|max_length[2355]'
        )
    );

    /**
     * Конструктор
     *
     */
    public function __construct()
    {
        $this->dbConnect = new db();
        $dbConfig = $this->dbConnect->getDBConnectionArray('database');
        $this->dbConnect->connect($dbConfig);
    }

    /**
     * Сохранение статьи в базу
     *
     * @param array $arValues - массив значений статьи
     * структура:
     * $arValues = array(
     *      'date' => '2016-01-07 10:00',
     *      'title' => 'Первая статья',
     *      'text' => 'Текст статьи',
     *      'tags' => 'Не обязательное поле с тегами',
     *      'image_name' => 'Не обязательное поле с названием картинки'
     * );
     * @return bool|int
     */
    public function addArticle(Array $arValues)
    {
        if (!array_key_exists('date', $arValues) ||
            !array_key_exists('title', $arValues) ||
            !array_key_exists('text', $arValues)) {
            return false;
        }

        $insertData = array(
            'date' => $arValues['date'],
            'title' => htmlentities($arValues['title']),
            'text' => htmlentities($arValues['text']),
            'tags' => (array_key_exists('tags', $arValues) && !empty($arValues['tags']))
                ? htmlentities($arValues['tags']) : null,
            'image_name' => (array_key_exists('image_name', $arValues)) ? $arValues['image_name'] : null
        );

        $res = $this->dbConnect->insert(self::TABLE_NAME, $insertData);

        if (!$res) {
            $this->error[] = $this->dbConnect->lastError;
        } else {
            if (!is_null($insertData['image_name'])) {
                $img = new uploadImage($this::IMG_UPLOAD_PATH, $this::IMG_UPLOAD_MAX_SIZE);
                $img->copyImageFromTmp($insertData['image_name']);
            }
        }

        return $res;
    }

    /**
     * Возвращает статью по идентификатору
     *
     * @param $id - идентификатор статьи
     * @return array|bool - false - если статья не найдена,
     * массив полей - если найдена
     */
    public function getArticleById($id)
    {
        if (!$id) {
            $this->error[] = "Статью не найдено";
            return false;
        }

        $fieldList = 'id, date, title, text, tags, image_name';
        $where = 'id = ' . intval($id);
        $res = $this->dbConnect->get($this::TABLE_NAME, $fieldList, $where);

        if ($res && $this->dbConnect->numRows($res) > 0) {
            return $this->dbConnect->fetch($res);
        }

        $this->error[] = "Статью не найдено";
        return false;
    }

    /**
     * Возвращает весь список статей из таблицы
     *
     * @return array - массив статей
     */
    public function getArticlesList()
    {
        $result = array();

        $res = $this->dbConnect->get($this::TABLE_NAME, 'id, date, title, text, tags, image_name');

        if ($res && $this->dbConnect->numRows($res) > 0) {
            $result = $this->dbConnect->fetchAll($res);
        }

        return $result;
    }

}