<?php
/**
 * logout.php
 *
 * Handles user logout by destroying the session and redirecting to the main portal.
 */
session_start(); // Start the session
session_unset();   // Unset all session variables
session_destroy(); // Destroy the session
header("Location: index.php"); // Redirect to the main portal
exit();
?>