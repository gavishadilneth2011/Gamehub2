<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (current_user()) {
    header('Location: dashboard.php');
    exit;
}

$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);

// Optional: login.php can set $_SESSION['old_email'] on a failed attempt to prefill the field
$oldEmail = $_SESSION['old_email'] ?? '';
unset($_SESSION['old_email']);

// Message shown after logging out or deleting an account
$status = $_GET['status'] ?? '';
$notice = $status === 'deleted'
    ? 'Your account has been deleted. You can create a new one anytime.'
    : ($status === 'logged_out' ? 'You have been logged out. See you soon! 👋' : '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0b0b17">
<title>Game Hub | Login</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
  :root {
    --bg: #0b0b17;
    --card: rgba(255,255,255,.06);
    --card-border: rgba(255,255,255,.12);
    --text: #f4f4fb;
    --muted: #a3a3c2;
    --accent: #7c5cff;
    --accent-2: #22d3ee;
    --danger: #ff5d73;
    --radius: 14px;
  }
  *, *::before, *::after { box-sizing: border-box; }
  body.reg {
    margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px;
    font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    color: var(--text); background: var(--bg); overflow-x: hidden; position: relative;
  }
  body.reg::before, body.reg::after {
    content: ""; position: fixed; width: 520px; height: 520px; border-radius: 50%;
    filter: blur(110px); opacity: .45; z-index: 0; pointer-events: none;
    animation: drift 16s ease-in-out infinite alternate;
  }
  body.reg::before { background: var(--accent); top: -160px; left: -120px; }
  body.reg::after  { background: var(--accent-2); bottom: -180px; right: -120px; animation-delay: -8s; }
  @keyframes drift { to { transform: translate(60px, 40px) scale(1.15); } }

  .shell {
    position: relative; z-index: 1; width: 100%; max-width: 920px;
    display: grid; grid-template-columns: 1fr 1.1fr;
    background: var(--card); border: 1px solid var(--card-border);
    border-radius: 24px; backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px);
    box-shadow: 0 30px 80px rgba(0,0,0,.45); overflow: hidden;
    animation: rise .6s cubic-bezier(.2,.8,.2,1) both;
  }
  @keyframes rise { from { opacity: 0; transform: translateY(18px); } }

  /* ---- left promo panel ---- */
  .promo {
    padding: 48px 40px; display: flex; flex-direction: column; justify-content: space-between; gap: 32px;
    background: linear-gradient(160deg, rgba(124,92,255,.35), rgba(34,211,238,.12));
    border-right: 1px solid var(--card-border);
  }
  .logo { display: flex; align-items: center; gap: 12px; font-weight: 800; font-size: 1.15rem; letter-spacing: .3px; }
  .logo-badge {
    width: 44px; height: 44px; border-radius: 12px; display: grid; place-items: center; font-size: 1.4rem;
    background: linear-gradient(135deg, var(--accent), var(--accent-2)); box-shadow: 0 8px 24px rgba(124,92,255,.5);
  }
  .promo h2 { font-size: 1.9rem; line-height: 1.2; margin: 0 0 10px; }
  .promo p { color: var(--muted); margin: 0; line-height: 1.6; }
  .tagline { font-size: .72rem; letter-spacing: .2em; font-weight: 700; color: rgba(255,255,255,.55); }

  /* ---- form panel ---- */
  .panel { padding: 56px 44px; display: flex; flex-direction: column; justify-content: center; }
  .eyebrow { margin: 0 0 6px; font-size: .72rem; letter-spacing: .18em; font-weight: 700; color: var(--accent-2); }
  .panel h1 { margin: 0 0 6px; font-size: 1.8rem; }
  .sub { margin: 0 0 26px; color: var(--muted); font-size: .95rem; }

  .alert {
    display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; margin-bottom: 18px;
    border-radius: 12px; font-size: .9rem; line-height: 1.45;
    background: rgba(255,93,115,.12); border: 1px solid rgba(255,93,115,.4); color: #ffc2cb;
    animation: shake .4s;
  }
  .alert.ok { background: rgba(52,211,153,.12); border-color: rgba(52,211,153,.4); color: #a7f3d0; animation: none; }
  @keyframes shake { 20%,60% { transform: translateX(-5px); } 40%,80% { transform: translateX(5px); } }

  .field { margin-bottom: 18px; }
  .field label { display: block; margin-bottom: 6px; font-size: .82rem; font-weight: 600; color: #d6d6ec; }
  .control { position: relative; }
  .control input {
    width: 100%; padding: 13px 14px 13px 42px; font: inherit; color: var(--text);
    background: rgba(8,8,20,.55); border: 1px solid var(--card-border); border-radius: var(--radius);
    outline: none; transition: border-color .2s, box-shadow .2s;
  }
  .control input::placeholder { color: #6f6f94; }
  .control input:focus { border-color: var(--accent); box-shadow: 0 0 0 4px rgba(124,92,255,.22); }
  .control .lead { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); opacity: .65; pointer-events: none; font-size: 1rem; }
  .control .eye {
    position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
    background: none; border: 0; color: var(--muted); cursor: pointer; padding: 6px 8px; border-radius: 8px; font-size: 1rem;
  }
  .control .eye:hover { color: var(--text); background: rgba(255,255,255,.08); }
  .control.has-eye input { padding-right: 46px; }

  .btn-main {
    width: 100%; margin-top: 6px; padding: 14px 18px; border: 0; border-radius: var(--radius); cursor: pointer;
    font: inherit; font-weight: 700; color: #fff; letter-spacing: .2px;
    background: linear-gradient(135deg, var(--accent), #5b8cff 55%, var(--accent-2));
    box-shadow: 0 12px 30px rgba(124,92,255,.45); transition: transform .15s, box-shadow .15s, opacity .2s;
    display: flex; align-items: center; justify-content: center; gap: 10px;
  }
  .btn-main:hover { transform: translateY(-2px); box-shadow: 0 16px 36px rgba(124,92,255,.55); }
  .btn-main:active { transform: translateY(0); }
  .btn-main[disabled] { opacity: .7; cursor: wait; transform: none; }
  .btn-main .spin { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,.4); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; display: none; }
  .btn-main.loading .spin { display: inline-block; }
  .btn-main.loading .arrow { display: none; }
  @keyframes spin { to { transform: rotate(360deg); } }

  .switch { margin: 24px 0 0; text-align: center; color: var(--muted); font-size: .92rem; }
  .switch a { color: var(--accent-2); font-weight: 600; text-decoration: none; }
  .switch a:hover { text-decoration: underline; }
  .foot { margin: 18px 0 0; text-align: center; font-size: .68rem; letter-spacing: .2em; color: #6f6f94; }

  @media (max-width: 800px) {
    .shell { grid-template-columns: 1fr; max-width: 480px; }
    .promo { display: none; }
    .panel { padding: 40px 26px; }
  }
  @media (prefers-reduced-motion: reduce) {
    *, *::before, *::after { animation: none !important; transition: none !important; }
  }
</style>
</head>
<body class="reg">
<div class="shell">

  <aside class="promo">
    <div class="logo"><span class="logo-badge">🎮</span> GD Hub</div>
    <div>
      <h2>Good to see you again, player.</h2>
      <p>Pick up where you left off, check your scores and jump straight back into the action.</p>
    </div>
    <div class="tagline">PLAY. COMPETE. CONQUER.</div>
  </aside>

  <main class="panel">
    <p class="eyebrow">YOUR NEXT ADVENTURE STARTS HERE</p>
    <h1>Welcome back</h1>
    <p class="sub">Log in to continue your gaming journey.</p>

    <?php if ($notice): ?>
      <div class="alert ok" role="status"><span>✅</span><span><?= e($notice) ?></span></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert" role="alert"><span>⚠️</span><span><?= e($error) ?></span></div>
    <?php endif; ?>

    <form id="loginForm" action="login.php" method="post">
      <?= csrf_field() ?>

      <div class="field">
        <label for="email">Email address</label>
        <div class="control">
          <span class="lead">✉️</span>
          <input id="email" name="email" type="email" maxlength="190"
                 autocomplete="email" placeholder="you@example.com"
                 value="<?= e($oldEmail) ?>" required <?= $oldEmail ? '' : 'autofocus' ?>>
        </div>
      </div>

      <div class="field">
        <label for="password">Password</label>
        <div class="control has-eye">
          <span class="lead">🔑</span>
          <input id="password" name="password" type="password"
                 autocomplete="current-password" placeholder="Enter your password"
                 required <?= $oldEmail ? 'autofocus' : '' ?>>
          <button class="eye" type="button" id="togglePw" aria-label="Show password">👁️</button>
        </div>
      </div>

      <button class="btn-main" id="submitBtn" type="submit">
        <span class="label">Login to Game Hub</span><span class="arrow">→</span><span class="spin"></span>
      </button>
    </form>

    <p class="switch">New here? <a href="register.php">Create an account</a></p>
    <p class="foot">GAME HUB · PLAY. COMPETE. CONQUER.</p>
  </main>
</div>

<script>
(() => {
  const pw = document.getElementById('password');
  const toggle = document.getElementById('togglePw');
  const form = document.getElementById('loginForm');
  const btn = document.getElementById('submitBtn');

  toggle.addEventListener('click', () => {
    const show = pw.type === 'password';
    pw.type = show ? 'text' : 'password';
    toggle.textContent = show ? '🙈' : '👁️';
    toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
  });

  form.addEventListener('submit', () => {
    if (!form.checkValidity()) return;
    btn.disabled = true;
    btn.classList.add('loading');
    btn.querySelector('.label').textContent = 'Logging in…';
  });
})();
</script>
</body>
</html>