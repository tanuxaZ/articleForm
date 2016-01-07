<?php

/**
 * Class uploadImage
 */

class uploadImage
{
    /**
     *  время жизни временных файлов, в секундах
     */
    const LIFE_TIME_TMP_FILE = 7200;

    public $error = array();

    private $uploadPath = '';
    private $allowedTypes = '';
    private $maxSize = 0;
    private $fileTemp = '';
    private $fileSize = null;
    private $fileType = '';
    private $fileName = '';
    private $fileExt = '';

    /**
     * Конструктор
     *
     * @param $uploadPath - путь, куда сохранять файл
     * @param $maxSize - максимальный допустимый размер файла (Кб)
     * @param string $allowedTypes - список допустимых разширений
     * $allowedTypes = 'jpg|png' или $allowedTypes = array('jpg', 'png');
     */
    public function __construct($uploadPath, $maxSize, $allowedTypes = '*'){
        $this->uploadPath = $uploadPath;
        $this->maxSize = $maxSize;
        $this->allowedTypes = (is_array($allowedTypes) || $allowedTypes === '*')
            ? $allowedTypes
            : explode('|', $allowedTypes);
    }

    /**
     * Сохранение картинки
     *
     * @param $field - название поля в форме
     * @param $isTemp - true - сохраняем в tmp папку, false - в главную
     * @return bool|string - false, если сохранение прошло не удачно, название файла - если удачно
     */
    public function saveImage($field, $isTmp = false)
    {
        $this->deleteOldTmpFile();

        if ($isTmp) {
            $this->updateUploadPath(true);
        }

        if (!isset($_FILES[$field])) {
            $this->error[] = "Такой картинки нет.";
            if ($isTmp) {
                $this->updateUploadPath(false);
            }
            return false;
        }

        if (!$this->validateUploadPath())
        {
            if ($isTmp) {
                $this->updateUploadPath(false);
            }
            return false;
        }
        
        $thisFile = $_FILES[$field];

        if (!$this->isFileUpload($thisFile)) {
            if ($isTmp) {
                $this->updateUploadPath(false);
            }
            return false;
        }

        $this->fileTemp = $thisFile['tmp_name'];
        $this->fileSize = $thisFile['size'];
        $this->fileType = $thisFile['type'];
        $this->fileName = preg_replace('/\s+/', '_', $thisFile['name']);
        $this->fileExt = $this->getExtension($this->fileName);
        $this->fileName = $this->rebuildFileName();

        if (!$this->isAllowedFileType()) {
            $this->error[] = "Недопустимое разширение.";
            if ($isTmp) {
                $this->updateUploadPath(false);
            }
            return false;
        }

        if ($this->fileSize > 0) {
            $this->fileSize = round($this->fileSize/1024, 2);
        }

        if (!$this->isAllowedFileSize()) {
            $this->error[] = "Размер файла привышает допустимый.";
            if ($isTmp) {
                $this->updateUploadPath(false);
            }
            return false;
        }

        if (!@copy($this->fileTemp, $this->uploadPath.$this->fileName)) {
            if (!@move_uploaded_file($this->fileTemp, $this->uploadPath.$this->fileName)) {
                $this->error[] = "Возникла ошибка при перемещении файла в конечное расположение.";
                if ($isTmp) {
                    $this->updateUploadPath(false);
                }
                return false;
            }
        }

        if ($isTmp) {
            $this->updateUploadPath(false);
        }

        return $this->fileName;
    }

    /**
     * Копирует файл из временной папки в основную
     *
     * @param $tmpFileName - имя временного файла
     * @return bool - true - если файл скопирован успешно, иначе - false
     */
    public function copyImageFromTmp($tmpFileName)
    {
        if (!$this->validateUploadPath())
        {
            return false;
        }

        $tmpFile = $this->uploadPath . 'tmp/' . $tmpFileName;
        $newFile = $this->uploadPath . $tmpFileName;

        if (!is_readable($tmpFile)) {
            $this->error[] = 'Временный файл не найден.';
            return false;
        }

        if (!copy($tmpFile, $newFile)) {
            $this->error[] = 'Возникла ошибка при копировании временного файла.';
            return false;
        }

        chmod($newFile, 0777);
        unlink($tmpFile);

        return true;
    }

    /**
     * Удаляет файл - картинку по указаному пути
     *
     * @param $path - имя картинке (test.jpg)
     * @return bool - возвращает true, если удалено успешно, иначе - false
     */
    public function deleteImage($imageName)
    {
        if (!is_file($this->uploadPath . $imageName) || !file_exists($this->uploadPath . $imageName)) {
            return false;
        }

        return unlink($this->uploadPath . $imageName);
    }

    /**
     * Удаляет все временные файлы, у которых истек срок жизни
     *
     * @return bool
     */
    private function deleteOldTmpFile()
    {
        $this->updateUploadPath(true);

        if (!$this->validateUploadPath()) {
            $this->updateUploadPath(false);
            return false;
        }

        if ($dh = opendir($this->uploadPath)) {
            while (($file = readdir($dh)) !== false) {
                if (is_file($this->uploadPath . $file)) {
                    if (time() - filemtime($this->uploadPath . $file) > $this::LIFE_TIME_TMP_FILE) {
                        unlink($this->uploadPath . $file);
                    }
                }
            }
        }

        closedir($dh);

        $this->updateUploadPath(false);

        return true;
    }

    /**
     * Валидация пути сохранения картинки
     *
     * @return bool - возвращает true, если папка существует, достпна для записи, в другом случае false
     */
    private function validateUploadPath()
    {
        if ($this->uploadPath === '') {
            $this->error[] = "Не указан путь сохранения картинки.";
            return false;
        }

        if (realpath($this->uploadPath) !== false) {
            $this->uploadPath = str_replace('\\', '/', realpath($this->uploadPath)) . '/';
        }

        if (!is_dir($this->uploadPath)) {
            $this->error[] = "Указаный путь для сохранения картинки не существует";
            return false;
        }

        if (!is_writable($this->uploadPath)) {
            $this->error[] = "По указаному пути нет доступа на запись";
            return false;
        }

        return true;
    }

    /**
     * Проверка загружен файл или нет
     *
     * @param $thisFile - массив даных файла
     * @return bool - true - если при загрузке не возникло ошибок, иначе - false
     */
    private function isFileUpload($thisFile)
    {
        if (!is_uploaded_file($thisFile['tmp_name'])) {
            $error = isset($thisFile['error']) ? $thisFile['error'] : 4;

            switch ($error) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $this->error[] = "Размер принятого файла превысил максимально допустимый размер.";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $this->error[] = "Загружаемый файл был получен только частично.";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $this->error[] = "Файл не был загружен.";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $this->error[] = "Отсутствует временная папка.";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $this->error[] = "Не удалось записать файл на диск.";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $this->error[] = "Не определено какое расширение остановило загрузку файла.";
                    break;
                default:
                    $this->error[] = "Не выбрано файл загрузки.";
                    break;
            }

            return false;
        }

        return true;
    }

    /**
     * Валидация разширения
     *
     * @return bool - возвращает true, если есть список допустимых разширений
     * и загружаемый файл включен в него, иначе - false
     */
    private function isAllowedFileType()
    {
        if ($this->allowedTypes === '*') {
            return true;
        }

        if (empty($this->allowedTypes) || !is_array($this->allowedTypes)) {
            $this->error[] = "Не указаны допустимые расширения файлов";
            return false;
        }

        if (!in_array(substr($this->fileExt,1), $this->allowedTypes, TRUE)) {
            return false;
        }

        return true;
    }

    /**
     * Извлекает разширение файла из имени
     *
     * @param $filename - имя файла
     * @return string - разширение с точкой
     */
    private function getExtension($filename)
    {
        $x = explode('.', $filename);

        if (count($x) === 1) {
            return '';
        }

        $ext = end($x);

        return '.'.$ext;
    }

    /**
     * Валидация максимально допустимого, для загрузки, размера файла
     *
     * @return bool - возвращает true, если размер меньше допустимого, иначе - false
     */
    private function isAllowedFileSize()
    {
        return ($this->maxSize === 0 || $this->maxSize > $this->fileSize);
    }

    /**
     * Возвращает уникальное для загружаемого файла
     *
     * @return string
     */
    private function rebuildFileName(){
        $fileName = explode('.', $this->fileName)[0];
        return $this->fileName = $fileName . '_' . md5(time() + rand(1, 10000)) . $this->fileExt;
    }

    /**
     * Преобразует путь к картинкам модуля
     *
     * @param $isTemp - bool
     * если true - преобразует путь во временную папку
     * если false - возвращает из временной в главную
     */
    private function updateUploadPath($isTemp)
    {
        if ($isTemp){
            $this->uploadPath = $this->uploadPath . 'tmp/';
        } else {
            $pathArr = explode('/', $this->uploadPath);
            array_splice($pathArr, count($pathArr) - 2);
            $this->uploadPath = implode('/', $pathArr) . '/';
        }
    }

}