<?php
require './db/posts.php';

try {
    if (isset($_POST['Post']))
    {
        for ($i = 0; $i < sizeof($_FILES['userfile']['error']); $i++)
        {
            if ($_FILES['userfile']['error'][$i] != 0) {
                throw (new Exception("Il y a un erreur avec le fichier"));
            }
        }

        if (!isset($_FILES['userfile']) ||
        !is_uploaded_file($_FILES['userfile']['tmp_name'][0])) {
            throw (new Exception("Probleme de transfert"));
        }
        else
        {
            $srcs = array();

            $numberOfFiles = sizeof($_FILES['userfile']['name']);
            for ($i = 0; $i < $numberOfFiles; $i++)
            {
                $data = file_get_contents($_FILES['userfile']['tmp_name'][$i]);
                $mime = $_FILES['userfile']['type'][$i];

                array_push($srcs, 'data:' . $mime . ';base64,' . base64_encode($data));
            }

            $_SESSION['email'] = 'sashawrc2004@gmail.com';
            if (isset($_SESSION['email']))
            {
                $commentaire = filter_input(INPUT_POST, 'Commentaire', FILTER_SANITIZE_STRING);

                addPost($_SESSION['email'], $commentaire, $srcs);
            }
        }
    }
}
catch (Exception $e) {
    echo $e->getMessage();
}


if (!empty($_GET['user'])) {
    $email = filter_input(INPUT_GET, 'user', FILTER_SANITIZE_STRING);
    $posts = getAllPostsFrom($email);
}
else {
    $posts = getAllPosts();
}

include './models/header.php';
?>

<body>
    <!-- top nav -->
    <?php include './models/nav.php'; ?>
    <!-- /top nav -->

    <div class="padding">
        <div class="full col-sm-9">
            <!-- content -->
            <div class="row">
                <!-- main col left -->
                <div class="col-sm-5">
                    <div class="panel panel-default">
                        <div class="panel-thumbnail"><img src="img/Ussr.png" class="img-responsive"></div>
                        <div class="panel-body">
                            <p class="lead">Motherland</p>
                            <p>69 Followers, 1256 Posts</p>
                            <p>
                                <img src="img/uFp_tsTJboUY7kue5XAsGAs28.png" height="28px" width="28px">
                            </p>
                        </div>
                    </div>

                    <?php include './models/addPostForm.php'; ?>
                </div>

                <!-- main col right -->
                <div class="col-sm-7">
                    <?php foreach ($posts as $post) { ?>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4><a href="index.php?user=<?= $post['nickname'] ?>"> <?= $post['nickname'] ?></a><div style="font-size: smaller; display: inline-block; padding-left: 10px">il y a <?php
                                    $interval = (new DateTime())->diff(new DateTime($post['dateCrea']));
                                    if ($interval->format('%a') > 0) {
                                        echo $interval->format('%a j');
                                    }
                                    else if ($interval->format('%h') > 0) {
                                        echo $interval->format('%h h');
                                    }
                                    else if ($interval->format('%i') > 0) {
                                        echo $interval->format('%i m');
                                    }
                                    else {
                                        echo $interval->format('%s s');
                                    }
                                ?></div></h4>
                            </div>
                            <div class="panel-body">
                                <p><img src="<?php if (empty($post['avatar'])) { echo './img/150x150.gif'; } else { echo $post['avatar']; } ?>" class="img-circle pull-right"></p>
                                <div class="clearfix">
                                    <?= $post['comm'] ?>
                                </div>
                                <hr>
                                <?php
                                $imagesBalises = "";

                                if (is_array($post['dataMedia']))
                                {
                                    $sizeOfMedia = sizeof($post['dataMedia']);

                                    for ($i = 0; $i < $sizeOfMedia; $i++) {
                                        $type = substr($post['dataMedia'][$i]['dataMedia'], 5, 10);

                                       if (strpos($type, 'image') !== false) {
                                        $imagesBalises .= '<img src="' . $post['dataMedia'] . '" width="200px">';
                                        }
                                        else if (strpos($type, 'audio') !== false) {
                                            $imagesBalises .= '<audio src="' . $post['dataMedia'] . '" width="200px">';
                                        }
                                        else if (strpos($type, 'video') !== false) {
                                            $imagesBalises .= '<video width="200px" controls autoplay muted loop>';
                                            $imagesBalises .= '<source src="' . $post['dataMedia'][$i]['dataMedia'] . '"></video>';
                                        }
                                    }
                                }
                                else
                                {
                                    $type = substr($post['dataMedia'], 5, 10);

                                    if (strpos($type, "image") !== false) {
                                        $imagesBalises .= '<img src="' . $post['dataMedia'] . '" width="200px">';
                                    }
                                    else if (strpos($type, "audio") !== false) {
                                        $imagesBalises .= '<audio src="' . $post['dataMedia'] . '" width="200px">';
                                    }
                                    else if (strpos($type, "video") !== false) {
                                        $imagesBalises .= '<video width="200px" controls autoplay muted loop>';
                                        $imagesBalises .= '<source src="' . $post['dataMedia'] . '"></video>';
                                    }
                                }

                                echo $imagesBalises;
                                ?>
                                <hr>
                                <button data-toggle="modal" class="btn btn-default btn-sm" href="#delete<?php echo $post['idPost'] ?>">
                                    <span class="bi bi-trash"></span></button>

                                    <div class="modal" id="delete<?php echo $post['idPost'] ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    <h4 class="modal-title">Voulez-vous vraiment supprimer ce post ?</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p><?php echo $post['comm']; ?> :
                                                    </p>
                                                </div>
                                                <div class="modal-footer">
                                                    <form action="./deletepost.php" method="post">
                                                        <input type="hidden" name="idPost" value="<?php echo $post['idPost'] ?>" />
                                                        <input class="btn" type="submit" name="submit" value="Supprimer" />
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <a class="btn btn-default btn-sm" href="editpost.php?id=<?= $post['idPost'] ?>">
                                <span class="bi bi-pen"></span></a>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <!--/row-->
        </div>
        <!-- /col-9 -->
    </div>
    <!-- /padding -->

    <?php include './models/footer.php'; ?>

    <!--post modal-->
    <div id="postModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">�</button>
                    Update Status
                </div>
                <div class="modal-body">
                    <form class="form center-block">
                        <div class="form-group">
                            <textarea class="form-control input-lg" autofocus="" placeholder="What do you want to share?"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <div>
                        <button class="btn btn-primary btn-sm" data-dismiss="modal" aria-hidden="true">Post</button>
                        <ul class="pull-left list-inline">
                            <li><a href=""></a></li>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
</body>
</html>