<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Статья</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php
        require_once('/modules/article.php');

        $art = new article();
        $article = $art->getArticleById($_GET['id']);
    ?>
    <section class="container">
        <div class="article_add article_one">
            <a href="/">Вернуться в список</a>
            <?php if ($article) { ?>
                <h2><?= $article['title']?></h2>
                <span><?= $article['date']?></span>
                <div>
                    <?php if (!empty($article['image_name'])) {?>
                        <img src="<?= $art::IMG_UPLOAD_PATH . $article['image_name']?>" />
                    <?php } ?>
                    <?= $article['text']?>
                </div>
                <div style="clear: both"></div>
                <span><?= $article['tags']?></span>
            <?php } ?>
        </div>
    </section>
</body>
</html>