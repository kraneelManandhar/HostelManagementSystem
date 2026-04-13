<?php
session_start();

// DEMO LOGIN
$_SESSION['student_id'] = 1; // must exist in DB

echo "Session set";