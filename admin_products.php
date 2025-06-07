<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:login.php');
    exit;
}

if (isset($_POST['add_product'])) {

    // Directly get POST data without filter_var
    $name = $_POST['name'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $details = $_POST['details'];

    $image = $_FILES['image']['name'];
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'images/' . $image;

    // Check if product name exists
    $stmt = $conn->prepare("SELECT id FROM products WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $message[] = 'product name already exist!';
    } else {
        $stmt->close();

        // Insert new product
        $stmt = $conn->prepare("INSERT INTO products (name, category, details, price, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $category, $details, $price, $image);
        $execute = $stmt->execute();

        if ($execute) {
            if ($image_size > 2000000) {
                $message[] = 'image size is too large!';
            } else {
                if (!is_dir('images')) {
                    mkdir('images', 0777, true);
                }
                move_uploaded_file($image_tmp_name, $image_folder);
                $message[] = 'new product added!';
            }
        } else {
            $message[] = 'failed to add product!';
        }
        $stmt->close();
    }
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    // Get image name to delete file
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->bind_result($image_name);
    $stmt->fetch();
    $stmt->close();

    if ($image_name && file_exists('images/' . $image_name)) {
        unlink('images/' . $image_name);
    }

    // Delete product
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    // Delete wishlist entries for product
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE pid = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    // Delete cart entries for product
    $stmt = $conn->prepare("DELETE FROM cart WHERE pid = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    header('location:admin_products.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>products</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />

    <!-- custom css file link  -->
 <link rel="stylesheet" href="css/admin_style.css?v=<?= time(); ?>">

</head>

<body>

    <?php include 'admin_header.php'; ?>

    <section class="add-products">

        <h1 class="title">add new product</h1>

        <?php
        if (!empty($message)) {
            foreach ($message as $msg) {
                echo '<p class="message">' . htmlspecialchars($msg) . '</p>';
            }
        }
        ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="flex">
                <div class="inputBox">
                    <input type="text" name="name" class="box" required placeholder="enter product name" />
                    <select name="category" class="box" required>
                        <option value="" selected disabled>select category</option>
                        <option value="vegitables">vegitables</option>
                        <option value="fruits">fruits</option>
                        <option value="meat">meat</option>
                        <option value="fish">fish</option>
                    </select>
                </div>
                <div class="inputBox">
                    <input type="number" min="0" name="price" class="box" required placeholder="enter product price" />
                    <input type="file" name="image" required class="box" accept="image/jpg, image/jpeg, image/png" />
                </div>
            </div>
            <textarea name="details" class="box" required placeholder="enter product details" cols="30" rows="10"></textarea>
            <input type="submit" class="btn" value="add product" name="add_product" />
        </form>

    </section>

    <section class="show-products">

        <h1 class="title">products added</h1>

        <div class="box-container">

            <?php
            $result = $conn->query("SELECT * FROM products");
            if ($result->num_rows > 0) {
                while ($fetch_products = $result->fetch_assoc()) {
            ?>
                    <div class="box">
                        <div class="price">$<?= htmlspecialchars($fetch_products['price']); ?>/-</div>
                        <img src="/<?= htmlspecialchars($fetch_products['image']); ?>" alt="" />
                        <div class="name"><?= htmlspecialchars($fetch_products['name']); ?></div>
                        <div class="cat"><?= htmlspecialchars($fetch_products['category']); ?></div>
                        <div class="details"><?= htmlspecialchars($fetch_products['details']); ?></div>
                        <div class="flex-btn">
                            <a href="admin_update_product.php?update=<?= htmlspecialchars($fetch_products['id']); ?>" class="option-btn">update</a>
                            <a href="admin_products.php?delete=<?= htmlspecialchars($fetch_products['id']); ?>" class="delete-btn" onclick="return confirm('delete this product?');">delete</a>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo '<p class="empty">no products added yet!</p>';
            }
            ?>

        </div>

    </section>

    <script src="js/script.js"></script>

</body>

</html>
