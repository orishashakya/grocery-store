<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:login.php');
    exit;
}

if (isset($_POST['update_product'])) {

    $pid = $_POST['pid'];
    $name = $_POST['name'];           // removed filter_var
    $price = $_POST['price'];
    $category = $_POST['category'];
    $details = $_POST['details'];

    $image = $_FILES['image']['name'];
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'images/' . $image;
    $old_image = $_POST['old_image'];

    // Update product except image first
    $stmt = mysqli_prepare($conn, "UPDATE products SET name = ?, category = ?, details = ?, price = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssssi", $name, $category, $details, $price, $pid);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $message[] = 'product updated successfully!';

    if (!empty($image)) {
        if ($image_size > 2000000) {
            $message[] = 'image size is too large!';
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE products SET image = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $image, $pid);
            mysqli_stmt_execute($stmt);

            if (mysqli_stmt_affected_rows($stmt) > 0) {
                if (!is_dir('images')) {
                    mkdir('images', 0777, true);
                }
                move_uploaded_file($image_tmp_name, $image_folder);
                if ($old_image && file_exists('images/' . $old_image)) {
                    unlink('images/' . $old_image);
                }
                $message[] = 'image updated successfully!';
            }

            mysqli_stmt_close($stmt);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>update products</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/admin_style.css" />
</head>

<body>

    <?php include 'admin_header.php'; ?>

    <section class="update-product">

        <h1 class="title">update product</h1>

        <?php
        if (!empty($message)) {
            foreach ($message as $msg) {
                echo '<p class="message">' . htmlspecialchars($msg) . '</p>';
            }
        }

        $update_id = $_GET['update'];

        $stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $update_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($fetch_products = mysqli_fetch_assoc($result)) {
        ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="old_image" value="<?= htmlspecialchars($fetch_products['image']); ?>">
                    <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_products['id']); ?>">
                    <img src="images/<?= htmlspecialchars($fetch_products['image']); ?>" alt="" />
                    <input type="text" name="name" placeholder="enter product name" required class="box" value="<?= htmlspecialchars($fetch_products['name']); ?>">
                    <input type="number" name="price" min="0" placeholder="enter product price" required class="box" value="<?= htmlspecialchars($fetch_products['price']); ?>">
                    <select name="category" class="box" required>
                        <option value="<?= htmlspecialchars($fetch_products['category']); ?>" selected><?= htmlspecialchars($fetch_products['category']); ?></option>
                        <option value="vegitables">Vegetables</option>
                        <option value="fruits">Fruits</option>
                        <option value="meat">Meat</option>
                        <option value="fish">Fish</option>
                    </select>
                    <textarea name="details" required placeholder="enter product details" class="box" cols="30" rows="10"><?= htmlspecialchars($fetch_products['details']); ?></textarea>
                    <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png" />
                    <div class="flex-btn">
                        <input type="submit" class="btn" value="update product" name="update_product" />
                        <a href="admin_products.php" class="option-btn">go back</a>
                    </div>
                </form>
        <?php
            }
        } else {
            echo '<p class="empty">no products found!</p>';
        }

        mysqli_stmt_close($stmt);
        ?>

    </section>

    <script src="js/script.js"></script>

</body>

</html>
