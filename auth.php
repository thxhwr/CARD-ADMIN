<?php
session_start();

// 관리자 로그인 세션 체크
if (
    !isset($_SESSION['admin_login']) ||
    $_SESSION['admin_login'] !== true
) {
    header("Location: /login.php");
    exit;
}
