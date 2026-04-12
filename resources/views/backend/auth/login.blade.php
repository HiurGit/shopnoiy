<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Đăng nhập quản trị</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <style>
    body {
      min-height: 100vh;
      margin: 0;
      display: grid;
      place-items: center;
      background:
        radial-gradient(circle at top, rgba(255, 226, 229, 0.95), rgba(255, 255, 255, 0.92) 45%),
        linear-gradient(160deg, #fff7f8 0%, #fff 45%, #f9f1f2 100%);
      font-family: "Segoe UI", sans-serif;
    }
    .admin-login-card {
      width: min(100%, 420px);
      border: 0;
      border-radius: 24px;
      box-shadow: 0 24px 70px rgba(120, 45, 61, 0.14);
      overflow: hidden;
    }
    .admin-login-head {
      padding: 28px 28px 12px;
    }
    .admin-login-title {
      margin: 0;
      font-size: 1.75rem;
      font-weight: 700;
      color: #7c3043;
    }
    .admin-login-subtitle {
      margin: 8px 0 0;
      color: #7b7480;
    }
    .admin-login-body {
      padding: 16px 28px 28px;
    }
    .admin-login-btn {
      background: linear-gradient(135deg, #a93d5b, #d05d80);
      border: 0;
      border-radius: 14px;
      padding: 12px 16px;
      font-weight: 600;
    }
    .admin-login-btn:hover {
      background: linear-gradient(135deg, #972f4f, #c04c71);
    }
  </style>
</head>
<body>
  <div class="card admin-login-card">
    <div class="admin-login-head">
      <div class="small text-uppercase text-secondary fw-semibold">Shopnoiy Admin</div>
      <h1 class="admin-login-title">Đăng nhập quản trị</h1>
      <p class="admin-login-subtitle">Tài khoản admin hoặc staff đang hoạt động có thể đăng nhập backend.</p>
    </div>
    <div class="admin-login-body">
      @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('backend.login.submit') }}">
        @csrf
        <div class="mb-3">
          <label class="form-label">Email</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" autocomplete="username" required autofocus />
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Mật khẩu</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" class="form-control" autocomplete="current-password" required />
          </div>
        </div>
        <div class="form-check mb-4">
          <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
          <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
        </div>
        <button type="submit" class="btn btn-primary w-100 admin-login-btn">Đăng nhập</button>
      </form>
    </div>
  </div>
</body>
</html>
