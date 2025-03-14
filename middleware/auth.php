<?php
// middleware/auth.php
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /se-connecter');
        exit();
    }
}

function checkAdmin() {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
        header('Location: /acces-refuse');
        exit();
    }
}
?>
