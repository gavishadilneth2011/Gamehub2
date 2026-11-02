<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (current_user()) { header('Location: dashboard.php'); exit; }

/* ---------- Handle the form BEFORE any HTML is output ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Remember what they typed (never the passwords) so they don't have to retype it
    $_SESSION['old'] = ['username' => $username, 'email' => $email];

    if (strlen($username) < 3 || strlen($username) > 30 || !preg_match('/^[a-zA-Z0-9_. -]+$/', $username)) {
        flash_error('Username must be 3–30 characters and use simple letters, numbers, spaces, dots, underscores or hyphens.');
        header('Location: register.php'); exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        flash_error('Please enter a valid email address.');
        header('Location: register.php'); exit;
    }
    if (strlen($password) < 8) {
        flash_error('Use a password with at least 8 characters.');
        header('Location: register.php'); exit;
    }
    if ($password !== $confirm) {
        flash_error('The passwords do not match.');
        header('Location: register.php'); exit;
    }

    try {
        $stmt = db()->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
        $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT)]);
        session_regenerate_id(true);
        unset($_SESSION['old']);
        $_SESSION['user_id'] = (int)db()->lastInsertId();
        header('Location: dashboard.php'); exit;
    } catch (PDOException $ex) {
        if ($ex->getCode() === '23000') {
            flash_error('That email is already registered. Try logging in instead.');
        } else {
            error_log($ex->getMessage());
            flash_error('Could not create your account. Check database setup and try again.');
        }
        header('Location: register.php'); exit;
    }
}

$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0b0b17">
<title>Create account | Game Hub</title>
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
    --ok: #34d399;
    --warn: #fbbf24;
    --radius: 14px;
  }
  *, *::before, *::after { box-sizing: border-box; }
  body.reg {
    margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px;
    font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    color: var(--text); background: var(--bg); overflow-x: hidden; position: relative;
  }
  /* soft animated glow background */
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
  .perks { list-style: none; padding: 0; margin: 0; display: grid; gap: 14px; }
  .perks li { display: flex; gap: 12px; align-items: center; font-size: .95rem; }
  .perks .ic {
    flex: none; width: 34px; height: 34px; border-radius: 10px; display: grid; place-items: center;
    background: rgba(255,255,255,.1); border: 1px solid var(--card-border);
  }

  /* ---- form panel ---- */
  .panel { padding: 48px 44px; }
  .eyebrow { margin: 0 0 6px; font-size: .72rem; letter-spacing: .18em; font-weight: 700; color: var(--accent-2); }
  .panel h1 { margin: 0 0 6px; font-size: 1.8rem; }
  .sub { margin: 0 0 24px; color: var(--muted); font-size: .95rem; }

  .alert {
    display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; margin-bottom: 18px;
    border-radius: 12px; font-size: .9rem; line-height: 1.45;
    background: rgba(255,93,115,.12); border: 1px solid rgba(255,93,115,.4); color: #ffc2cb;
    animation: shake .4s;
  }
  @keyframes shake { 20%,60% { transform: translateX(-5px); } 40%,80% { transform: translateX(5px); } }

  .field { margin-bottom: 16px; }
  .field label { display: block; margin-bottom: 6px; font-size: .82rem; font-weight: 600; color: #d6d6ec; }
  .control { position: relative; }
  .control input {
    width: 100%; padding: 13px 14px 13px 42px; font: inherit; color: var(--text);
    background: rgba(8,8,20,.55); border: 1px solid var(--card-border); border-radius: var(--radius);
    outline: none; transition: border-color .2s, box-shadow .2s, background .2s;
  }
  .control input::placeholder { color: #6f6f94; }
  .control input:focus { border-color: var(--accent); box-shadow: 0 0 0 4px rgba(124,92,255,.22); }
  .control input.valid   { border-color: rgba(52,211,153,.7); }
  .control input.invalid { border-color: rgba(255,93,115,.7); }
  .control .lead { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); opacity: .65; pointer-events: none; font-size: 1rem; }
  .control .eye {
    position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
    background: none; border: 0; color: var(--muted); cursor: pointer; padding: 6px 8px; border-radius: 8px; font-size: 1rem;
  }
  .control .eye:hover { color: var(--text); background: rgba(255,255,255,.08); }
  .control.has-eye input { padding-right: 46px; }

  .hint { margin-top: 6px; font-size: .78rem; color: var(--muted); min-height: 1.1em; }
  .hint.bad { color: var(--danger); }
  .hint.good { color: var(--ok); }

  .meter { display: flex; gap: 6px; margin-top: 8px; }
  .meter span { flex: 1; height: 5px; border-radius: 99px; background: rgba(255,255,255,.1); transition: background .25s; }
  .meter[data-s="1"] span:nth-child(-n+1) { background: var(--danger); }
  .meter[data-s="2"] span:nth-child(-n+2) { background: var(--warn); }
  .meter[data-s="3"] span:nth-child(-n+3) { background: #a3e635; }
  .meter[data-s="4"] span:nth-child(-n+4) { background: var(--ok); }

  .btn-main {
    width: 100%; margin-top: 8px; padding: 14px 18px; border: 0; border-radius: var(--radius); cursor: pointer;
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

  .switch { margin: 22px 0 0; text-align: center; color: var(--muted); font-size: .92rem; }
  .switch a { color: var(--accent-2); font-weight: 600; text-decoration: none; }
  .switch a:hover { text-decoration: underline; }

  @media (max-width: 800px) {
    .shell { grid-template-columns: 1fr; max-width: 480px; }
    .promo { display: none; }
    .panel { padding: 36px 26px; }
  }
  @media (prefers-reduced-motion: reduce) {
    *, *::before, *::after { animation: none !important; transition: none !important; }
  }
</style>
</head>
<body class="reg">
<div class="shell">

  <aside class="promo">
    <div class="logo"><span class="logo-badge">🕹️</span> GD Hub</div>
    <div>
      <h2>Your next favorite game is waiting.</h2>
      <p>Join the community, track your progress and jump into the action in seconds.</p>
    </div>
    <ul class="perks">
      <li><span class="ic">⚡</span> Instant access to the hub</li>
      <li><span class="ic">🏆</span> Save scores and progress</li>
      <li><span class="ic">🔒</span> Passwords securely hashed</li>
    </ul>
  </aside>

  <main class="panel">
    <p class="eyebrow">JOIN THE PLAYER COMMUNITY</p>
    <h1>Create your account</h1>
    <p class="sub">It takes less than a minute.</p>

    <?php if ($error): ?>
      <div class="alert" role="alert"><span>⚠️</span><span><?= e($error) ?></span></div>
    <?php endif; ?>

    <form id="regForm" action="register.php" method="post" novalidate>
      <?= csrf_field() ?>

      <div class="field">
        <label for="username">Username</label>
        <div class="control">
          <span class="lead">👤</span>
          <input id="username" name="username" type="text" minlength="3" maxlength="30"
                 autocomplete="username" placeholder="Choose a username"
                 value="<?= e($old['username'] ?? '') ?>" required autofocus>
        </div>
        <div class="hint" id="userHint">3–30 characters. Letters, numbers, spaces, dots, _ and -</div>
      </div>

      <div class="field">
        <label for="email">Email address</label>
        <div class="control">
          <span class="lead">✉️</span>
          <input id="email" name="email" type="email" maxlength="190"
                 autocomplete="email" placeholder="you@example.com"
                 value="<?= e($old['email'] ?? '') ?>" required>
        </div>
        <div class="hint" id="emailHint"></div>
      </div>

      <div class="field">
        <label for="password">Password</label>
        <div class="control has-eye">
          <span class="lead">🔑</span>
          <input id="password" name="password" type="password" minlength="8"
                 autocomplete="new-password" placeholder="At least 8 characters" required>
          <button class="eye" type="button" data-target="password" aria-label="Show password">👁️</button>
        </div>
        <div class="meter" id="meter" data-s="0"><span></span><span></span><span></span><span></span></div>
        <div class="hint" id="pwHint">Tip: mix upper &amp; lower case, numbers and symbols.</div>
      </div>

      <div class="field">
        <label for="confirm_password">Confirm password</label>
        <div class="control has-eye">
          <span class="lead">🔁</span>
          <input id="confirm_password" name="confirm_password" type="password" minlength="8"
                 autocomplete="new-password" placeholder="Enter password again" required>
          <button class="eye" type="button" data-target="confirm_password" aria-label="Show password">👁️</button>
        </div>
        <div class="hint" id="matchHint"></div>
      </div>

      <button class="btn-main" id="submitBtn" type="submit">
        <span class="label">Create account</span><span class="arrow">→</span><span class="spin"></span>
      </button>
    </form>

    <p class="switch">Already have an account? <a href="index.php">Log in</a></p>
  </main>
</div>

<script>
(() => {
  const $ = id => document.getElementById(id);
  const form = $('regForm'), user = $('username'), email = $('email'),
        pw = $('password'), cf = $('confirm_password');

  const mark = (el, ok) => { el.classList.toggle('valid', ok === true); el.classList.toggle('invalid', ok === false); };
  const say = (id, msg, cls) => { const h = $(id); h.textContent = msg; h.className = 'hint ' + (cls || ''); };

  // show / hide password
  document.querySelectorAll('.eye').forEach(btn => btn.addEventListener('click', () => {
    const input = $(btn.dataset.target), show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.textContent = show ? '🙈' : '👁️';
    btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
  }));

  const checkUser = () => {
    const v = user.value.trim();
    if (!v) { mark(user, null); return; }
    const ok = v.length >= 3 && v.length <= 30 && /^[a-zA-Z0-9_. -]+$/.test(v);
    mark(user, ok);
    say('userHint', ok ? 'Looks good!' : 'Use 3–30 letters, numbers, spaces, dots, _ or -', ok ? 'good' : 'bad');
  };

  const checkEmail = () => {
    const v = email.value.trim();
    if (!v) { mark(email, null); say('emailHint', ''); return; }
    const ok = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v);
    mark(email, ok);
    say('emailHint', ok ? '' : 'Enter a valid email address', ok ? '' : 'bad');
  };

  const score = v => {
    let s = 0;
    if (v.length >= 8) s++;
    if (v.length >= 12) s++;
    if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
    if (/\d/.test(v) && /[^A-Za-z0-9]/.test(v)) s++;
    return v.length ? Math.max(1, Math.min(4, s)) : 0;
  };
  const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];

  const checkPw = () => {
    const v = pw.value, s = score(v);
    $('meter').dataset.s = s;
    if (!v) { mark(pw, null); say('pwHint', 'Tip: mix upper & lower case, numbers and symbols.'); }
    else if (v.length < 8) { mark(pw, false); say('pwHint', 'Needs at least 8 characters', 'bad'); }
    else { mark(pw, true); say('pwHint', labels[s] + ' password', s >= 3 ? 'good' : ''); }
    checkMatch();
  };

  const checkMatch = () => {
    if (!cf.value) { mark(cf, null); say('matchHint', ''); return; }
    const ok = cf.value === pw.value;
    mark(cf, ok);
    say('matchHint', ok ? 'Passwords match' : 'Passwords do not match yet', ok ? 'good' : 'bad');
  };

  user.addEventListener('input', checkUser);
  email.addEventListener('input', checkEmail);
  pw.addEventListener('input', checkPw);
  cf.addEventListener('input', checkMatch);
  [user, email].forEach(i => i.value && i.dispatchEvent(new Event('input')));

  form.addEventListener('submit', e => {
    checkUser(); checkEmail(); checkPw(); checkMatch();
    if (!form.checkValidity() || pw.value !== cf.value || form.querySelector('.invalid')) {
      e.preventDefault();
      (form.querySelector('.invalid') || form.querySelector(':invalid'))?.focus();
      return;
    }
    const btn = $('submitBtn');
    btn.disabled = true; btn.classList.add('loading');
    btn.querySelector('.label').textContent = 'Creating account…';
  });
})();
</script>
</body>
</html>