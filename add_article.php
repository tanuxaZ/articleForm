<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Создание статьи</title>
    <link rel="stylesheet" href="/css/style.css">
    <script type="application/javascript" src="/js/jquery.js"></script>
    <script type="application/javascript" src="/js/jquery_datetimepicker.js"></script>
    <script type="application/javascript" src="/js/articles.js"></script>
</head>
<body>
    <section class="container">
        <div class="article_add">
            <?php
                session_start();
                $errors = array();
                $values = array();
                if (isset($_SESSION) && array_key_exists('errors', $_SESSION)) {
                    $errors = $_SESSION['errors'];
                    $values = $_SESSION['fieldsValue'];
                }
                unset($_SESSION['errors']);
                unset($_SESSION['fieldsValue']);
            ?>

            <h1>Добавить статью</h1>
            <form method="post" action="/save_article.php" class="main" ENCTYPE="multipart/form-data">
                <div class="error">
                    <?php
                    if (count($errors) > 0 && array_key_exists('0', $errors)) {
                        foreach ($errors['0'] as $error) {
                            echo $error . "<br>";
                        }
                    }
                    ?>
                </div>
                <p>
                    <input class="ClsRequired"
                           type="text"
                           id="datepicker"
                           name="date"
                           value="<?= (count($values) > 0 && array_key_exists('date', $values)) ? $values['date'] : '' ?>"
                           placeholder="Дата*">
                </p>
                <div class="error">
                    <?php
                        if (count($errors) > 0 && array_key_exists('date', $errors)) {
                            foreach ($errors['date'] as $error) {
                                echo $error . "<br>";
                            }
                        }
                    ?>
                </div>
                <p>
                    <input class="ClsRequired"
                           type="text"
                           name="title"
                           value="<?= (count($values) > 0 && array_key_exists('title', $values)) ? $values['title'] : '' ?>"
                           placeholder="Заголовок*">
                </p>
                <div class="error">
                    <?php
                    if (count($errors) > 0 && array_key_exists('title', $errors)) {
                        foreach ($errors['title'] as $error) {
                            echo $error . "<br>";
                        }
                    }
                    ?>
                </div>
                <p>
                    <textarea class="ClsRequired"
                              name="text"
                              placeholder="Текст*"><?= (count($values) > 0 && array_key_exists('text', $values)) ? $values['text'] : '' ?></textarea>
                </p>
                <div class="error">
                    <?php
                    if (count($errors) > 0 && array_key_exists('text', $errors)) {
                        foreach ($errors['text'] as $error) {
                            echo $error . "<br>";
                        }
                    }
                    ?>
                </div>
                <p><input type="text"
                          name="tags"
                          value="<?= (count($values) > 0 && array_key_exists('tags', $values)) ? $values['tags'] : '' ?>"
                          placeholder="Теги">
                </p>
                <div class="error">
                    <?php
                    if (count($errors) > 0 && array_key_exists('tags', $errors)) {
                        foreach($errors['tags'] as $error){
                            echo $error . "<br>";
                        }
                    }
                    ?>
                </div>
                <p>
                    <input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
                    <input type="file" name="image_name" id="fileImg" value="">
                    <input type="hidden"
                           name="image_data"
                           value="<?= (count($values) > 0 && array_key_exists('image_data', $values)) ? $values['image_data'] : '' ?>">
                </p>

                <div id="fileDisplayArea">
                    <?php if (count($values) > 0 && array_key_exists('image_data', $values) && !empty($values['image_data'])) { ?>
                        <img width="400" src="<?= $values['image_data']?>">
                    <? }?>
                    <div class="error">
                        <?php
                        if (count($errors) > 0 && array_key_exists('image_name', $errors)) {
                            foreach ($errors['image_name'] as $error) {
                                echo $error . "<br>";
                            }
                        }
                        ?>
                    </div>
                </div>

                <p class="submit"><input onclick="return false;" type="submit" name="save" value="Сохранить"></p>
            </form>
        </div>
    </section>
</body>
</html>