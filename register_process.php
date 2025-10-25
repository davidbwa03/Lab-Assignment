<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstname = $_POST['first_name'] ?? '';
    $lastname = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';

  
    echo "<script>
        alert('User Registration Complete!');
        window.location.href = 'register_form.php';
    </script>";
} else {
    echo "<script>
        alert('Invalid request!');
        window.location.href = 'register_form.php';
    </script>";
}
?>
