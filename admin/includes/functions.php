<?php

//===== DATABASE HELPERS =====//
function redirect($location) {

    return header("Location:" . $location);

}


function query($query) {

    global $connection;
    $result = mysqli_query($connection, $query);
    confirm($result);

    return $result;

}


function confirm($result) {
    global $connection;

    if(!$result) {
        die("QUERY FAILED" . mysqli_error($connection));
    }

}


function fetchRecords($result) {

    return mysqli_fetch_array($result);

}


function count_records($result) {
    return mysqli_num_rows($result);
}


function get_username() {

    if(isset($_SESSION['username'])) {
        return $_SESSION['username'];
    }

}


//===== AUTHENTICATION =====//

function ifItIsMethod($method=null){

    if($_SERVER['REQUEST_METHOD'] == strtoupper($method)){

        return true;

    }

    return false;

}


function isLoggedIn(){

    if(isset($_SESSION['user_role'])) {
        return true;
    }

    return false;

}


function loggedInUserId() {

    if(isLoggedIn()) {
        $result = query("SELECT * FROM users WHERE username='" . $_SESSION['username'] ."'");
        $user = mysqli_fetch_array($result);
        return mysqli_num_rows($result) >= 1 ? $user['user_id'] : false;
    }

    return false;

}


function is_admin() {
    global $connection;

    if(isLoggedIn()) {
        $result = query("SELECT user_role FROM users WHERE user_id=".$_SESSION['user_id']."");

        $row = mysqli_fetch_array($result);

        if($row['user_role'] == 'admin') {
            return true;
        } else {
            return false;
        }
    }
}


//===== POSTS =====//

function userLikedPost($post_id = '') {
    $result = query("SELECT * FROM likes WHERE user_id=".loggedInUserId()." AND post_id={$post_id}");
    return mysqli_num_rows($result) >= 1 ? true : false;
}


function getPostLikes($post_id) {
    $result = query("SELECT * FROM likes WHERE post_id=$post_id");
    confirm($result);
    echo mysqli_num_rows($result);
}



function escape($string) {
    global $connection;
    mysqli_real_escape_string($connection, trim($string));
}

//===== HELPER functions =====//

function users_online() {

        global $connection;

        include ("admin_db.php");

        $session = session_id(); // getting the id of each new session
        $time = time();
        $time_out_in_seconds = 30; // the amount of time a user is marked offline
        $time_out = $time - $time_out_in_seconds;

        // count users
        $query = "SELECT * FROM users_online WHERE session = '$session'";
        $send_query = mysqli_query($connection, $query);
        $count = mysqli_num_rows($send_query);

        if($count == NULL) {
            mysqli_query($connection, "INSERT INTO users_online(session, time) VALUES('$session', '$time')"); // new users
        } else {
            mysqli_query($connection, "UPDATE users_online SET time = '$time' WHERE session = '$session'"); // updating existing user (user that already logged in the past)
        }

        // how much time the user has been online. If a user stays more than 30 sec without any action he is considered offline
        $users_online = mysqli_query($connection, "SELECT * FROM users_online WHERE time > '$time_out'");
        return $count_user = mysqli_num_rows($users_online);

}
users_online();


//===== CATEGORIES =====//

function insert_categories () {
    global $connection;

    if(isset($_POST['submit'])) {
        $cat_title = $_POST['cat_title'];

        if ($cat_title == "" || empty($cat_title)) {
            echo "This field should not be empty!";
        } else {
            $query = "INSERT INTO categories(cat_title) ";
            $query .= "VALUE('{$cat_title}') ";

            $create_category_query = mysqli_query($connection, $query);

            if (!$create_category_query) {
                die("QUERY FAILED" . mysqli_error($connection));
            }
        }
    }
}


function findAllCategories () {
    global $connection;

    $query = "SELECT * FROM categories";
    $select_categories = mysqli_query($connection, $query);

    while ($row = mysqli_fetch_assoc($select_categories)) {
        $cat_id = $row['cat_id'];
        $cat_title = $row['cat_title'];
        echo "<tr>";
        echo "<td>$cat_id</td>";
        echo "<td>$cat_title</td>";
        echo "<td><a href='categories.php?delete={$cat_id}'>Delete</a></td>";
        echo "<td><a href='categories.php?edit={$cat_id}'>Edit</a></td>";
        echo "</tr>";
    }
}


function deleteCategories () {
    global $connection;

    if(isset($_GET['delete'])) {
        $the_cat_id = $_GET['delete'];
        $query = "DELETE FROM categories WHERE cat_id = {$the_cat_id} ";
        $delete_query = mysqli_query($connection, $query);
        header("Location: categories.php");
    }
}


//===== USER SPECIFIC =====//

function recordCount($table) {
    global $connection;

    $query = "SELECT * FROM $table ";
    $select_all_posts = mysqli_query($connection, $query);

    $result = mysqli_num_rows($select_all_posts);

    confirm($result);

    return $result;

}


//===== DASHBOARD =====//

function get_all_user_posts() {
    return query("SELECT * FROM posts WHERE user_id=".loggedInUserId()."");
}


function get_all_posts_user_comments() {
    return query("SELECT * FROM posts 
    INNER JOIN  comments ON posts.post_id = comments.comment_post_id
    WHERE user_id=".loggedInUserId()."");
}


function get_all_user_categories() {
    return query("SELECT * FROM categories WHERE user_id=".loggedInUserId()."");
}


function checkStatus($table, $column, $status) {
    global $connection;

    $query = "SELECT * FROM $table WHERE $column = '$status' ";
    $result = mysqli_query($connection, $query);

    return mysqli_num_rows($result);
}


//===== REGISTRATION ERRORS =====//


function username_exists($username) {
    global $connection;

    $query = "SELECT username FROM users WHERE username = '$username' ";
    $result = mysqli_query($connection, $query);

    confirm($result);

    if(mysqli_num_rows($result) > 0) {
        return true;
    } else {
        return false;
    }
}


function email_exists($email) {
    global $connection;

    $query = "SELECT user_email FROM users WHERE user_email = '$email' ";
    $result = mysqli_query($connection, $query);

    confirm($result);

    if(mysqli_num_rows($result) > 0) {
        return true;
    } else {
        return false;
    }
}


function register_user($username, $email, $password) {
    global $connection;

    $username = mysqli_real_escape_string($connection, $username);
    $email = mysqli_real_escape_string($connection, $email);
    $password = mysqli_real_escape_string($connection, $password);

    $password = password_hash($password, PASSWORD_BCRYPT, array('cost' => 12));

    $query = "INSERT INTO users (username, user_email, user_password, user_role) ";
    $query .= "VALUES('{$username}', '{$email}', '{$password}', 'subscriber') ";
    $register_user_query = mysqli_query($connection, $query);

    confirm($register_user_query);

}


function login_user($username, $password) {
    global $connection;

    $username = trim($username);
    $password = trim($password);

    // preventing mysqli injection
    $username = mysqli_real_escape_string($connection, $username);
    $password = mysqli_real_escape_string($connection, $password);

    $query = "SELECT * FROM users WHERE username = '{$username}' ";
    $select_user_query = mysqli_query($connection, $query); // returns true or false on the query and assigns the rows to the variable

    if(!$select_user_query) {
        die("QUERY FAILED" . mysqli_error($connection));
    }

    // row returns array with objects containing the rows of the query table
    while($row = mysqli_fetch_array($select_user_query)) {
        $db_user_id = $row['user_id'];
        $db_username = $row['username'];
        $db_user_password = $row['user_password'];
        $db_user_firstname = $row['user_firstname'];
        $db_user_lastname = $row['user_lastname'];
        $db_user_role = $row['user_role'];
    }

    if (password_verify($password, $db_user_password)) {

        // we assign the value on the right to the session with name "username" on the left
        $_SESSION['user_id'] = $db_user_id;
        $_SESSION['username'] = $db_username;
        $_SESSION['firstname'] = $db_user_firstname;
        $_SESSION['lastname'] = $db_user_lastname;
        $_SESSION['user_role'] = $db_user_role;

        redirect("/admin");

    } else {

//        redirect("/index.php");
        return false;

    }
}



?>
