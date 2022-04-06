<?php

require_once './db/posts.php';

if (!isset($_FILES['userfile']) || 
    !is_uploaded_file($_FILES['userfile']['tmp_name'])) {
    echo('Probleme de transfert');  
}
else
{
    $data = file_get_contents($_FILES['userfile']['tmp_name']);
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES['userfile']['tmp_name']);
    $src = 'data:' . $mime . ';base64,' . base64_encode($data);

    $_SESSION['email'] = 'sashawrc2004@gmail.com';
    if (isset($_SESSION['email']))
    {
        $commentaire = filter_input(INPUT_POST, 'Commentaire', FILTER_SANITIZE_STRING);

        addPost($_SESSION['email'], $commentaire, $src);

        header("Location: index.php");
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <title>Post</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link href="style/bootstrap.css" rel="stylesheet">
    <link href="style/facebook.css" rel="stylesheet">
</head>

<body>
    <?php include './models/nav.php'; ?>

    <div class="padding">
        <div class="full col-sm-9">
            <!-- content -->
            <div class="row">
                <form action="#" method="POST" enctype="multipart/form-data">
                    Select a file: <input type="file" name="userfile" id="userfile" accept=".png, .jpg, .jpeg">
                    <label for="Commentaire">Commentaire : </label>
                    <input type="text" name="Commentaire"><br>
                    <input type="submit" value="Envoyer" id="submit">
                </form>
                <img src="<?= $src ?>">
            </div>
            <!--/row-->
        </div>
        <!-- /col-9 -->
    </div>
    <!-- /padding -->
    <?php include './models/footer.php'; ?>
</body>