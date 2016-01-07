<?php
/**
 * Скрипт обработки формы добавления статьи
 */

require_once('/modules/formValidate.php');
require_once('/modules/article.php');

session_start();

$valid = new formValidate();
$article = new article();
$err = $valid->isValidate($article->addValidateRules);
$fileName = '';

if (isset($_FILES['image_name']) && $_FILES['image_name']['size'] > 0) {
    require_once('/modules/uploadImage.php');

    $img = new uploadImage($article::IMG_UPLOAD_PATH, $article::IMG_UPLOAD_MAX_SIZE, $article::IMG_UPLOAD_TYPE);

    $fileName = $img->saveImage('image_name', true);
    if (!$fileName) {
        $_SESSION['errors'] = array($img->error);
        $_SESSION['fieldsValue'] = $_POST;
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

$insertData = array(
    'date' => $_POST['date'],
    'title' => $_POST['title'],
    'text' => $_POST['text'],
    'tags' => $_POST['tags'],
    'image_name' => (!empty($fileName)) ? $fileName : basename($_POST['image_data'])
);

if (is_array($err)) {
    $_SESSION['errors'] = $err;
    $_SESSION['fieldsValue'] = $_POST;
    $_SESSION['fieldsValue']['image_data'] = (!empty($_POST['image_data']))
        ? $article::IMG_UPLOAD_PATH . 'tmp/' . $insertData['image_name'] : '';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

if (!$article->addArticle($insertData)) {
    $_SESSION['errors'] = $article->error;
    $_SESSION['fieldsValue'] = $_POST;
    $_SESSION['fieldsValue']['image_data'] = (!empty($_POST['image_data']))
        ? $article::IMG_UPLOAD_PATH . 'tmp/' . $insertData['image_name'] : '';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

header("Location: " . '/index.php');