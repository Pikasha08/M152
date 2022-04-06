<?php

include './db/posts.php';

include './models/header.php';

include './models/nav.php';

$post = array();

if (filter_has_var(INPUT_GET, 'id'))
{
    $idPost = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    $post = getPost($idPost);
}
else
{
    header("Location: index.php");
    exit();
}
?>



<?php

include './models/footer.php';