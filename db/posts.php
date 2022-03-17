<?php

require_once "connectDB.php";

/**
 * Retourne tous les posts
 *
 * @return array Posts
 */
function getAllPosts()
{
    return getAllPostsFrom(null);
}

/**
 * Retourne tous les posts d'un utilisateur
 *
 * @param string $email Nom d'utilisateur
 * @return array Posts de l'utilisateur
 */
function getAllPostsFrom($email)
{
    static $ps = null;

    $email_exists = (!is_null($email) || !empty($email));

    $sql = 'SELECT p.commentaire AS comm, NICKNAME AS nickname, m.dataMedia AS dataMedia, AVATAR AS avatar, p.creationDate AS dateCrea';
    $sql .= ' FROM Post AS p';
    $sql .= ' JOIN USERS u ON u.EMAIL = p.USERS_EMAIL';
    $sql .= ' JOIN contenir c ON c.Post_idPost = p.idPost';
    $sql .= ' JOIN Media m ON m.idMedia = c.Media_idMedia';

    if ($email_exists)
        $sql .= ' WHERE u.NICKNAME = :USERNAME';
    $sql .= ' ORDER BY p.creationDate DESC';


    if ($ps == null)
    {
        $ps = connectDB()->prepare($sql);
    }
    $answer = false;
    
    try
    {
        if ($email_exists) {
            $ps->bindParam(':USERNAME', $email, PDO::PARAM_STR);
        }

        if ($ps->execute())
            $answer = $ps->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e)
    {
        echo $e->getMessage();
    }

    return $answer;
}

/**
 * Ajoute un post
 *
 * @param string $email Email de l'utilisateur
 * @param string $commentaire Commentaire de l'image
 * @param string $b64Img Image en base 64
 * @return bool true si réussi
 */
function addPost($email, $commentaire, $b64Img = null)
{
    static $ps = null;
    $sql = 'INSERT INTO `Post` (`commentaire`, `creationDate`, `USERS_EMAIL`)';
    $sql .= ' VALUES (:COMM, :CREADATE, :USERSMAIL)';

    if ($ps == null)
    {
        $ps = connectDB()->prepare($sql);
    }
    $answer = false;

    try
    {
        connectDB()->beginTransaction();

        $dateHeure = date("Y-m-d H:i:s");
        $ps->bindParam(':COMM', $commentaire, PDO::PARAM_STR);
        $ps->bindParam(':CREADATE', $dateHeure, PDO::PARAM_STR);
        $ps->bindParam(':USERSMAIL', $email, PDO::PARAM_STR);

        $answer = $ps->execute();

        $postID = connectDB()->lastInsertId();
        
        if (is_array($b64Img))
        {
            for ($i = 0; $i < count($b64Img); $i++)
            {
                if (!addMedia($b64Img[$i], $postID)) {
                    connectDB()->rollBack();
                    return false;
                }
            }
        }

        connectDB()->commit();
    }
    catch (PDOException $e)
    {
        //echo $e->getMessage();
        connectDB()->rollBack();
        return false;
    }

    return $answer;
}

/**
 * Ajoute un média
 *
 * @param string $b64Img Image en base 64
 * @return bool true si réussi
 */
function addMedia($b64Img, $postID)
{
    static $ps = null;
    $sql = 'INSERT INTO `Media` (`dataMedia`, `creationDate`)';
    $sql .= ' VALUES (:DATA, :CREADATE)';

    if ($ps == null)
    {
        $ps = connectDB()->prepare($sql);
    }
    $answer = false;

    try
    {
        $dateHeure = date("Y-m-d H:i:s");
        $ps->bindParam(':DATA', $b64Img, PDO::PARAM_STR);
        $ps->bindParam(':CREADATE', $dateHeure, PDO::PARAM_STR);

        $answer = $ps->execute();

        $mediaID = connectDB()->lastInsertId();
        
        return addContenir($postID, $mediaID);
    }
    catch (PDOException $e)
    {
        echo $e->getMessage();
        return false;
    }

    return $answer;
}


function addContenir($postID, $mediaID)
{
    static $ps = null;
    $sql = 'INSERT INTO `contenir` (`Post_idPost`, `Media_idMedia`)';
    $sql .= ' VALUES (:IDPOST, :IDMEDIA)';

    if ($ps == null)
    {
        $ps = connectDB()->prepare($sql);
    }
    $answer = false;

    try
    {
        $ps->bindParam(':IDPOST', $postID, PDO::PARAM_INT);
        $ps->bindParam(':IDMEDIA', $mediaID, PDO::PARAM_INT);

        $answer = $ps->execute();
    }
    catch (PDOException $e)
    {
        echo $e->getMessage();
        return false;
    }

    return $answer;
}