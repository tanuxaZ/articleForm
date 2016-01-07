<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Список статей</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<?php
    require_once('/modules/article.php');

    $art = new article();
    $artList = $art->getArticlesList();
?>
<section class="container">
    <div class="article_add">
        <div class="article_list">
            <?php if (count($artList) > 0) { ?>
                <h1>Список статей</h1>
                <ul>
                   <?php foreach ($artList as $article) { ?>
                       <li><a href="/article.php?id=<?= $article['id']?>"><?= $article['title']?></a> </li>
                    <?php }?>
                </ul>
            <?php }?>
        </div>
        <a href="add_article.php"><div class="add_article">Создать статью</div></a>
    </div>
</section>

</body>
</html>