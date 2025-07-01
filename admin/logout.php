<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Destroy session
session_destroy();

// Redirect to login page
redirect('login.php', 'Vous avez été déconnecté avec succès.', 'success');
