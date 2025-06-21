<?php
require_once '../includes/functions.php';

// Destroy session
session_destroy();

// Redirect to login page
setFlashMessage('info', 'You have been logged out successfully.');
redirect('login.php');
?> 