<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
   exit;
}

$message = [];

if(isset($_POST['send'])){

   $name = $_POST['name'];
   $email = $_POST['email'];
   $number = $_POST['number'];
   $msg = $_POST['msg'];

   // Prepare and execute SELECT query to check duplicate message
   $stmt = $conn->prepare("SELECT * FROM `message` WHERE name = ? AND email = ? AND number = ? AND message = ?");
   $stmt->bind_param("ssss", $name, $email, $number, $msg);
   $stmt->execute();
   $result = $stmt->get_result();

   if($result->num_rows > 0){
      $message[] = 'already sent message!';
   } else {
      // Prepare and execute INSERT query
      $stmt = $conn->prepare("INSERT INTO `message`(user_id, name, email, number, message) VALUES (?, ?, ?, ?, ?)");
      $stmt->bind_param("issss", $user_id, $name, $email, $number, $msg);
      $stmt->execute();

      $message[] = 'sent message successfully!';
   }

   $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8" />
   <meta http-equiv="X-UA-Compatible" content="IE=edge" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <title>contact</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css" />
</head>
<body>

<?php include 'header.php'; ?>

<?php
if (!empty($message)) {
    foreach ($message as $msg) {
        echo '<div class="message"><span>' . htmlspecialchars($msg) . '</span>
        <i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
    }
}
?>

<section class="contact">

   <h1 class="title">get in touch</h1>

   <form action="" method="POST">
      <input type="text" name="name" class="box" required placeholder="enter your name" />
      <input type="email" name="email" class="box" required placeholder="enter your email" />
      <input type="number" name="number" min="0" class="box" required placeholder="enter your number" />
      <textarea name="msg" class="box" required placeholder="enter your message" cols="30" rows="10"></textarea>
      <input type="submit" value="send message" class="btn" name="send" />
   </form>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
