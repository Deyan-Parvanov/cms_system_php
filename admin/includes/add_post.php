<?php

    global $connection;

    if(isset($_POST['create_post'])) {
        $post_title = $_POST['title'];
        $post_user = $_POST['post_user'];
        $post_category_id = $_POST['post_category'];
        $post_status = $_POST['post_status'];

        $post_image = $_FILES['image']['name'];
        $post_image_tmp = $_FILES['image']['tmp_name']; // Temporary location of the file, before we submit it

        $post_tags = $_POST['post_tags'];
        $post_content = $_POST['post_content'];
        $post_date = date('d-m-y');

        // Moves the img file from the temporary location to the server once we submit
        move_uploaded_file($post_image_tmp, "../images/$post_image");

        $query = "INSERT INTO posts(post_category_id, post_title, post_user, post_date,
                      post_image, post_content, post_tags, post_status) ";

        $query .= "VALUES({$post_category_id}, '{$post_title}', '{$post_user}', '{$post_date}', '{$post_image}', 
        '{$post_content}', '{$post_tags}', '{$post_status}') ";

        $create_post = mysqli_query($connection, $query);

        confirm($create_post);

        // getting the last id from the table
        $the_post_id = mysqli_insert_id($connection);

        echo "<p class='bg-success'>Post Created. <a href='../post.php?p_id={$the_post_id}'>View Post </a> or <a href='posts.php'>Edit More Posts</a></p>";

}

?>



<form action="" method="post" enctype="multipart/form-data">

    <div class="form-group">
        <label for="title">Post Title</label>
        <input type="text" class="form-control" name="title">
    </div>

    <div class="form-group">
        <label for="category">Category</label>
        <select name="post_category" id="">

            <?php

                $query = "SELECT * from categories";
                $select_categories = mysqli_query($connection, $query);

                confirm($select_categories);

                while ($row = mysqli_fetch_assoc($select_categories)) {
                    $cat_id = $row['cat_id'];
                    $cat_title = $row['cat_title'];

                    echo "<option value='{$cat_id}'>{$cat_title}</option>";

                }

                if(isset($_POST['update_post'])) {
                    $the_post_id = $_POST['post_id'];
                    $post_author = $_POST['post_author'];
                    $post_title = $_POST['post_title'];
                    $post_category_id = $_POST['post_category_id'];
                    $post_status = $_POST['post_status'];
                    $post_image = $_FILES['image']['name'];
                    $post_image_tmp = $_FILES['image']['tmp'];
                    $post_content = $_POST['post_content'];
                    $post_tags = $_POST['post_tags'];

                    move_uploaded_file($post_image_tmp, "../images/$post_image");

                    if (empty($post_image)) {
                        $query = "SELECT * FROM posts WHERE post_id = $the_post_id ";
                        $select_image = mysqli_query($connection, $query);

                        while ($row = mysqli_fetch_assoc($select_image)) {
                            $post_image = $row['post_image'];
                        }
                    }

                    $query = "UPDATE posts SET ";
                    $query .="post_title  = '{$post_title}', ";
                    $query .="post_category_id = '{$post_category_id}', ";
                    $query .="post_date   =  now(), ";
                    $query .="post_author = '{$post_author}', ";
                    $query .="post_status = '{$post_status}', ";
                    $query .="post_tags   = '{$post_tags}', ";
                    $query .="post_content= '{$post_content}', ";
                    $query .="post_image  = '{$post_image}' ";
                    $query .= "WHERE post_id = {$the_post_id} ";

                    $update_post = mysqli_query($connection, $query);

                    confirm($update_post);

                }

            ?>


        </select>


    </div>

    <div class="form-group">
        <label for="users">Users</label>
        <select name="post_user" id="">

            <?php

                $users_query = "SELECT * from users";
                $select_users = mysqli_query($connection, $users_query);

                confirm($select_users);

                while ($row = mysqli_fetch_assoc($select_users)) {
                    $user_id = $row['user_id'];
                    $username = $row['username'];

                    echo "<option value='{$username}'>{$username}</option>";

                }

            ?>

        </select>
    </div>

    <div class="form-group">
        <label for="post_status">Status</label>
        <select name="post_status" id="">
            <option value="draft">Post Status</option>
            <option value="published">Published</option>
            <option value="draft">Draft</option>
        </select>
    </div>

    <div class="form-group">
        <label for="post_image">Post Image</label>
        <input type="file" name="image">
    </div>

    <div class="form-group">
        <label for="post_tags">Post Tags</label>
        <input type="text" class="form-control" name="post_tags">
    </div>

    <div class="form-group">
        <label for="summernote">Post Content</label>
        <textarea class="form-control" name="post_content" id="summernote" cols="30" rows="10"></textarea>
    </div>

    <div class="form-group">
        <input class="btn btn-primary" type="submit" name="create_post" value="Publish Post">
    </div>

</form>
