<?php
require_once 'includes/functions.php';

// Redirect based on login status
if (isLoggedIn()) {
    redirect('pages/home.php');
} else {
    redirect('auth/login.php');
}
?> 