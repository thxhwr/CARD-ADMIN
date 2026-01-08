<?php
session_start();
if (isset($_SESSION['admin_login']) && $_SESSION['admin_login'] === true) {
    header("Location: /index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>THXDEAL 관리자 로그인</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="/admin/assets/css/admin.css">
  <style>
    body {
      margin: 0;
      background: #0f172a; /* 네이비 계열 */
      font-family: -apple-system, BlinkMacSystemFont, "Apple SD Gothic Neo", sans-serif;
    }

    .login-wrap {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-box {
      width: 360px;
      background: #ffffff;
      border-radius: 14px;
      padding: 36px 32px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.25);
    }

    .login-logo {
      text-align: center;
      font-size: 22px;
      font-weight: 800;
      letter-spacing: 1px;
      margin-bottom: 6px;
    }

    .login-sub {
      text-align: center;
      font-size: 13px;
      color: #64748b;
      margin-bottom: 28px;
    }

    .login-field {
      margin-bottom: 16px;
    }

    .login-field label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 6px;
      color: #334155;
    }

    .login-field input {
      width: 100%;
      height: 44px;
      padding: 0 12px;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      font-size: 14px;
      box-sizing: border-box;
    }

    .login-field input:focus {
      outline: none;
      border-color: #2563eb;
    }

    .login-btn {
      margin-top: 10px;
      width: 100%;
      height: 46px;
      border-radius: 8px;
      border: none;
      background: #2563eb;
      color: #fff;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
    }

    .login-btn:hover {
      background: #1e40af;
    }

    .login-error {
      margin-top: 14px;
      font-size: 13px;
      color: #dc2626;
      text-align: center;
    }

    .login-footer {
      margin-top: 26px;
      text-align: center;
      font-size: 11px;
      color: #94a3b8;
    }
  </style>
</head>
<body>

<div class="login-wrap">
  <form class="login-box" method="post" action="login-process.php">
    <div class="login-logo">THXDEAL</div>
    <div class="login-sub">관리자 시스템</div>

    <div class="login-field">
      <label>아이디</label>
      <input type="email" name="admin_id" placeholder="admin@thxdeal.com" required>
    </div>

    <div class="login-field">
      <label>비밀번호</label>
      <input type="password" name="admin_pw" placeholder="비밀번호 입력" required>
    </div>

    <button type="submit" class="login-btn">로그인</button>

    <?php if (isset($_GET['error'])): ?>
      <div class="login-error">아이디 또는 비밀번호가 올바르지 않습니다.</div>
      <?php elseif (($_GET['error'] ?? '') === 'denied'): ?>
      <div class="login-error">접근 권한이 없는 관리자 계정입니다.</div>
    <?php endif; ?>

    <div class="login-footer">
      © THXDEAL Admin
    </div>
  </form>
</div>

</body>
</html>
