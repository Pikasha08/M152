<?php

require_once './db/posts.php';

if (filter_has_var(INPUT_POST, 'submit'))
{
    $idPost = filter_input(INPUT_POST, 'idPost', FILTER_VALIDATE_INT);

    if (is_numeric($idPost))
    {
        deletePostAndMedia($idPost);
    }
}

header("Location: index.php");
exit;