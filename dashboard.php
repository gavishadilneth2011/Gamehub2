<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = require_login();

$username = (string) ($user['username'] ?? 'Player');
$firstLetter = strtoupper(substr($username, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#0b0b17">
<title>Game Hub | Player Dashboard</title>
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
    --radius: 18px;
  }
    /* ---------- logout / delete popup ---------- */
  .modal[hidden], .toast[hidden] { display: none; }
  .modal {
    position: fixed; inset: 0; z-index: 100; display: grid; place-items: center; padding: 20px;
    background: rgba(5,5,14,.7); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);
    animation: fade .2s ease both;
  }
  @keyframes fade { from { opacity: 0; } }
  .modal-box {
    width: 100%; max-width: 430px; max-height: 92vh; overflow-y: auto; padding: 30px 28px 26px; border-radius: 24px;
    background: #12122a; border: 1px solid var(--card-border); box-shadow: 0 30px 80px rgba(0,0,0,.6);
    text-align: center; animation: pop .25s cubic-bezier(.2,.9,.3,1.2) both;
  }
  @keyframes pop { from { opacity: 0; transform: scale(.9) translateY(10px); } }
  .modal-icon {
    width: 64px; height: 64px; margin: 0 auto 14px; border-radius: 20px; display: grid; place-items: center; font-size: 2rem;
    background: linear-gradient(135deg, var(--accent), var(--accent-2)); box-shadow: 0 14px 34px rgba(124,92,255,.5);
  }
  .modal-box h3 { margin: 0 0 6px; font-size: 1.4rem; }
  .modal-text { margin: 0 0 22px; color: var(--muted); font-size: .95rem; }
  .modal-actions { display: flex; gap: 10px; }
  .m-btn {
    flex: 1; padding: 13px 16px; border-radius: 12px; cursor: pointer; font: inherit; font-weight: 700; font-size: .88rem;
    color: #fff; border: 1px solid transparent; transition: transform .15s, opacity .2s, background .2s;
  }
  .m-btn:hover:not(:disabled) { transform: translateY(-2px); }
  .m-btn.ghost { background: var(--card); border-color: var(--card-border); }
  .m-btn.primary { background: linear-gradient(135deg, var(--accent), #5b8cff 55%, var(--accent-2)); box-shadow: 0 10px 26px rgba(124,92,255,.4); }
  .m-btn.danger { width: 100%; margin-top: 12px; background: var(--danger); box-shadow: 0 10px 26px rgba(255,93,115,.35); }
  .m-btn:disabled { opacity: .4; cursor: not-allowed; }

  .danger-zone {
    margin-top: 22px; padding: 16px; border-radius: 16px; text-align: left;
    background: rgba(255,93,115,.08); border: 1px solid rgba(255,93,115,.35);
  }
  .danger-title { margin: 0 0 6px; font-weight: 700; color: #ffc2cb; font-size: .92rem; }
  .danger-text { margin: 0 0 12px; font-size: .8rem; line-height: 1.55; color: #e9b8c0; }
  .danger-label { display: block; margin-bottom: 6px; font-size: .78rem; color: #e9b8c0; }
  .danger-zone input {
    width: 100%; padding: 11px 12px; font: inherit; color: var(--text); letter-spacing: .1em;
    background: rgba(8,8,20,.6); border: 1px solid rgba(255,93,115,.4); border-radius: 10px; outline: none;
  }
  .danger-zone input:focus { border-color: var(--danger); box-shadow: 0 0 0 4px rgba(255,93,115,.2); }

  .toast {
    position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); z-index: 110;
    padding: 12px 18px; border-radius: 12px; font-size: .88rem; color: #ffc2cb;
    background: #2a1018; border: 1px solid rgba(255,93,115,.5); box-shadow: 0 14px 40px rgba(0,0,0,.5);
  }
  *, *::before, *::after { box-sizing: border-box; }
  html { scroll-behavior: smooth; }
  body.hub {
    margin: 0; min-height: 100vh; color: var(--text); background: var(--bg);
    font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    overflow-x: hidden; position: relative;
  }
  body.hub::before, body.hub::after {
    content: ""; position: fixed; width: 560px; height: 560px; border-radius: 50%;
    filter: blur(120px); opacity: .35; z-index: 0; pointer-events: none;
    animation: drift 18s ease-in-out infinite alternate;
  }
  body.hub::before { background: var(--accent); top: -200px; left: -160px; }
  body.hub::after  { background: var(--accent-2); bottom: -220px; right: -160px; animation-delay: -9s; }
  @keyframes drift { to { transform: translate(60px, 40px) scale(1.15); } }

  a { color: inherit; text-decoration: none; }

  /* ---------- top bar ---------- */
  .topbar {
    position: sticky; top: 0; z-index: 20;
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
    padding: 14px clamp(16px, 4vw, 40px);
    background: rgba(11,11,23,.65); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
    border-bottom: 1px solid var(--card-border);
  }
  .brand { display: flex; align-items: center; gap: 12px; font-weight: 800; letter-spacing: .12em; font-size: 1.05rem; }
  .brand-icon {
    width: 40px; height: 40px; border-radius: 12px; display: grid; place-items: center; font-size: 1.3rem;
    background: linear-gradient(135deg, var(--accent), var(--accent-2)); box-shadow: 0 8px 22px rgba(124,92,255,.5);
  }
  .brand .accent { color: var(--accent-2); }

  .user-area { display: flex; align-items: center; gap: 14px; }
  .avatar {
    width: 40px; height: 40px; border-radius: 50%; display: grid; place-items: center; font-weight: 800;
    background: linear-gradient(135deg, var(--accent), #5b8cff); border: 2px solid rgba(255,255,255,.25);
  }
  .user-info { display: flex; flex-direction: column; line-height: 1.25; max-width: 180px; }
  .user-info small { font-size: .62rem; letter-spacing: .16em; color: var(--muted); }
  .user-info strong { font-size: .95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .logout {
    display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; cursor: pointer;
    font: inherit; font-size: .85rem; font-weight: 600; color: var(--text);
    background: var(--card); border: 1px solid var(--card-border); border-radius: 12px;
    transition: background .2s, border-color .2s, transform .15s;
  }
  .logout:hover { background: rgba(255,93,115,.15); border-color: rgba(255,93,115,.5); transform: translateY(-1px); }

  /* ---------- layout ---------- */
  .main { position: relative; z-index: 1; max-width: 1180px; margin: 0 auto; padding: clamp(20px, 4vw, 40px) clamp(16px, 4vw, 40px) 40px; }

  /* ---------- hero ---------- */
  .hero {
    display: grid; grid-template-columns: 1.3fr 1fr; align-items: center; gap: 24px;
    padding: clamp(28px, 5vw, 56px); border-radius: 28px; overflow: hidden; position: relative;
    background: linear-gradient(135deg, rgba(124,92,255,.28), rgba(34,211,238,.08) 60%, rgba(255,255,255,.03));
    border: 1px solid var(--card-border); box-shadow: 0 30px 80px rgba(0,0,0,.4);
    animation: rise .6s cubic-bezier(.2,.8,.2,1) both;
  }
  @keyframes rise { from { opacity: 0; transform: translateY(18px); } }
  .status {
    display: inline-flex; align-items: center; gap: 8px; padding: 7px 14px; border-radius: 99px;
    font-size: .68rem; font-weight: 700; letter-spacing: .16em;
    background: rgba(52,211,153,.12); border: 1px solid rgba(52,211,153,.4); color: #a7f3d0;
  }
  .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--ok); box-shadow: 0 0 0 0 rgba(52,211,153,.7); animation: pulse 1.8s infinite; }
  @keyframes pulse { 70% { box-shadow: 0 0 0 9px rgba(52,211,153,0); } 100% { box-shadow: 0 0 0 0 rgba(52,211,153,0); } }
  .kicker { margin: 22px 0 8px; font-size: .72rem; letter-spacing: .2em; font-weight: 700; color: var(--accent-2); }
  .hero h1 { margin: 0; font-size: clamp(2rem, 5vw, 3.6rem); line-height: 1.05; font-weight: 800; letter-spacing: -.01em; overflow-wrap: anywhere; }
  .hero h1 span {
    background: linear-gradient(90deg, var(--accent), var(--accent-2)); -webkit-background-clip: text; background-clip: text; color: transparent;
  }
  .greet { margin: 14px 0 0; color: var(--muted); font-size: .95rem; }
  .desc { margin: 12px 0 28px; max-width: 480px; color: var(--muted); line-height: 1.65; }
  .primary-btn {
    display: inline-flex; align-items: center; gap: 10px; padding: 14px 24px; border-radius: 14px;
    font-weight: 700; letter-spacing: .06em; font-size: .88rem; color: #fff;
    background: linear-gradient(135deg, var(--accent), #5b8cff 55%, var(--accent-2));
    box-shadow: 0 12px 30px rgba(124,92,255,.45); transition: transform .15s, box-shadow .15s;
  }
  .primary-btn:hover { transform: translateY(-2px); box-shadow: 0 16px 36px rgba(124,92,255,.55); }

  .chips { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 26px; }
  .chip { padding: 8px 14px; border-radius: 12px; font-size: .78rem; color: var(--muted); background: rgba(255,255,255,.06); border: 1px solid var(--card-border); }
  .chip b { color: var(--text); }

  /* hero visual */
  .visual { position: relative; min-height: 280px; display: grid; place-items: center; }
  .orbit { position: absolute; border-radius: 50%; border: 1px dashed rgba(255,255,255,.22); animation: spin 22s linear infinite; }
  .orbit.one { width: 250px; height: 250px; }
  .orbit.two { width: 170px; height: 170px; animation-direction: reverse; animation-duration: 14s; border-color: rgba(34,211,238,.4); }
  .orbit::after { content: ""; position: absolute; top: -5px; left: 50%; width: 10px; height: 10px; border-radius: 50%; background: var(--accent-2); box-shadow: 0 0 14px var(--accent-2); }
  @keyframes spin { to { transform: rotate(360deg); } }
  .symbol {
    position: relative; width: 110px; height: 110px; border-radius: 30px; display: grid; place-items: center; font-size: 3rem;
    background: linear-gradient(135deg, var(--accent), var(--accent-2)); box-shadow: 0 20px 50px rgba(124,92,255,.55);
    animation: float 4s ease-in-out infinite;
  }
  @keyframes float { 50% { transform: translateY(-10px) rotate(-3deg); } }
  .tag {
    position: absolute; padding: 7px 12px; border-radius: 10px; font-size: .65rem; font-weight: 700; letter-spacing: .14em;
    background: rgba(11,11,23,.75); border: 1px solid var(--card-border); backdrop-filter: blur(8px);
  }
  .tag.top { top: 14px; right: 6px; color: var(--ok); }
  .tag.bottom { bottom: 14px; left: 6px; color: var(--accent-2); }

  /* ---------- library ---------- */
  .library { margin-top: 56px; scroll-margin-top: 90px; }
  .section-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; margin-bottom: 24px; }
  .section-header h2 { margin: 0; font-size: clamp(1.6rem, 3vw, 2.2rem); }
  .section-header h2 span { color: var(--accent-2); }
  .count { padding: 8px 14px; border-radius: 99px; font-size: .72rem; font-weight: 700; letter-spacing: .14em; color: var(--muted); background: var(--card); border: 1px solid var(--card-border); }

  .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; }

  .game-card {
    --mx: 50%; --my: 50%;
    position: relative; display: flex; flex-direction: column; border-radius: 24px; overflow: hidden;
    background: var(--card); border: 1px solid var(--card-border);
    transition: transform .25s, border-color .25s, box-shadow .25s;
  }
  .game-card::before {
    content: ""; position: absolute; inset: 0; pointer-events: none; opacity: 0; transition: opacity .25s;
    background: radial-gradient(360px circle at var(--mx) var(--my), rgba(124,92,255,.22), transparent 60%);
  }
  .game-card:hover { transform: translateY(-6px); border-color: rgba(124,92,255,.55); box-shadow: 0 24px 60px rgba(0,0,0,.5); }
  .game-card:hover::before { opacity: 1; }

  .art { position: relative; height: 190px; display: grid; place-items: center; overflow: hidden; }
  .fighting .art { background: linear-gradient(135deg, #3b1d8f, #7c5cff 55%, #22d3ee); }
  .zombie .art   { background: linear-gradient(135deg, #0f3d2e, #1f7a4d 55%, #84cc16); }
  .art::after { content: ""; position: absolute; inset: 0; background: radial-gradient(circle at 70% 20%, rgba(255,255,255,.22), transparent 55%); }
  .art-number { position: absolute; top: 16px; left: 18px; font-size: .68rem; font-weight: 700; letter-spacing: .18em; opacity: .85; z-index: 2; }
  .art-emoji { font-size: 4.2rem; filter: drop-shadow(0 12px 20px rgba(0,0,0,.4)); transition: transform .3s; z-index: 1; }
  .game-card:hover .art-emoji { transform: scale(1.15) rotate(-6deg); }
  .art-label { position: absolute; bottom: 14px; left: 18px; font-size: .68rem; font-weight: 800; letter-spacing: .22em; opacity: .9; z-index: 2; }
  .coming-badge {
    position: absolute; top: 14px; right: 14px; z-index: 2; padding: 6px 12px; border-radius: 99px;
    font-size: .62rem; font-weight: 800; letter-spacing: .14em; color: #1a1400; background: var(--warn);
  }

  .body { display: flex; flex-direction: column; flex: 1; padding: 22px 24px 26px; }
  .meta { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-bottom: 12px; }
  .category { font-size: .68rem; font-weight: 700; letter-spacing: .16em; color: var(--accent-2); }
  .state { font-size: .68rem; font-weight: 700; letter-spacing: .08em; color: var(--ok); }
  .state.pending { color: var(--warn); }
  .body h3 { margin: 0 0 8px; font-size: 1.4rem; }
  .body p { margin: 0 0 22px; color: var(--muted); line-height: 1.6; font-size: .93rem; flex: 1; }

  .play-btn {
    display: flex; align-items: center; justify-content: center; gap: 10px; padding: 14px 18px; border-radius: 14px;
    font-weight: 700; letter-spacing: .06em; font-size: .85rem; color: #fff;
    background: linear-gradient(135deg, var(--accent), #5b8cff 55%, var(--accent-2));
    box-shadow: 0 10px 26px rgba(124,92,255,.4); transition: transform .15s, box-shadow .15s;
  }
  .play-btn:hover { transform: translateY(-2px); box-shadow: 0 14px 32px rgba(124,92,255,.55); }
  .play-btn span { transition: transform .2s; }
  .play-btn:hover span { transform: translateX(4px); }

  .pending-btn {
    display: flex; align-items: center; justify-content: center; gap: 10px; padding: 14px 18px; border-radius: 14px;
    font-weight: 700; letter-spacing: .08em; font-size: .8rem; color: var(--muted); cursor: not-allowed;
    background: repeating-linear-gradient(45deg, rgba(255,255,255,.04) 0 10px, rgba(255,255,255,.08) 10px 20px);
    border: 1px dashed rgba(255,255,255,.22);
  }
  .pending-note { display: flex; align-items: center; gap: 8px; margin: 14px 0 0 !important; font-size: .78rem !important; color: var(--warn) !important; flex: none !important; }
  .pulse-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--warn); animation: pulse2 1.8s infinite; }
  @keyframes pulse2 { 70% { box-shadow: 0 0 0 9px rgba(251,191,36,0); } 0% { box-shadow: 0 0 0 0 rgba(251,191,36,.6); } }
  .coming-soon .art { filter: saturate(.7); }

  /* ---------- developer card ---------- */
  .dev {
    margin-top: 64px; display: grid; grid-template-columns: auto 1fr; gap: 28px; align-items: center;
    padding: clamp(24px, 4vw, 40px); border-radius: 28px; position: relative; overflow: hidden;
    background: linear-gradient(135deg, rgba(34,211,238,.12), rgba(124,92,255,.22));
    border: 1px solid var(--card-border); box-shadow: 0 24px 60px rgba(0,0,0,.35);
  }
  .dev::before {
    content: ""; position: absolute; width: 280px; height: 280px; right: -80px; top: -100px; border-radius: 50%;
    background: var(--accent); filter: blur(90px); opacity: .25; pointer-events: none;
  }
  .dev-avatar {
    position: relative; width: 108px; height: 108px; border-radius: 30px; display: grid; place-items: center;
    font-size: 2.8rem; font-weight: 800; object-fit: cover;
    background: linear-gradient(135deg, var(--accent), var(--accent-2)); box-shadow: 0 18px 44px rgba(124,92,255,.5);
  }
  .dev-avatar::after {
    content: ""; position: absolute; inset: -8px; border-radius: 36px; border: 1px dashed rgba(255,255,255,.28);
  }
  .dev-info { position: relative; }
  .dev-kicker { margin: 0 0 6px; font-size: .7rem; font-weight: 700; letter-spacing: .2em; color: var(--accent-2); }
  .dev-info h3 { margin: 0; font-size: clamp(1.5rem, 3vw, 2rem); }
  .dev-role { margin: 4px 0 14px; color: var(--muted); font-size: .92rem; }
  .dev-bio { margin: 0 0 18px; max-width: 620px; color: #d6d6ec; line-height: 1.7; font-size: .95rem; }
  .skills { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
  .skill {
    padding: 6px 12px; border-radius: 99px; font-size: .72rem; font-weight: 700; letter-spacing: .06em;
    background: rgba(255,255,255,.08); border: 1px solid var(--card-border);
  }
  .socials { display: flex; flex-wrap: wrap; gap: 10px; }
  .social {
    display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border-radius: 12px;
    font-size: .82rem; font-weight: 600; background: var(--card); border: 1px solid var(--card-border);
    transition: transform .15s, border-color .2s, background .2s;
  }
  .social:hover { transform: translateY(-2px); border-color: rgba(34,211,238,.6); background: rgba(34,211,238,.1); }

  /* ---------- footer ---------- */
  .footer {
    display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;
    margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--card-border);
    font-size: .72rem; letter-spacing: .12em; color: var(--muted);
  }
  .footer span:first-child { font-weight: 800; color: var(--text); }

  /* ---------- responsive ---------- */
  @media (max-width: 860px) {
    .hero { grid-template-columns: 1fr; }
    .visual { display: none; }
    .dev { grid-template-columns: 1fr; text-align: center; justify-items: center; }
    .dev-bio { margin-inline: auto; }
    .skills, .socials { justify-content: center; }
  }
  @media (max-width: 560px) {
    .user-info { display: none; }
    .logout { padding: 10px 12px; }
    .footer { flex-direction: column; text-align: center; }
  }
  @media (prefers-reduced-motion: reduce) {
    *, *::before, *::after { animation: none !important; transition: none !important; }
    html { scroll-behavior: auto; }
  }
</style>
</head>
<body class="hub">

<header class="topbar">
  <a class="brand" href="dashboard.php">
    <span class="brand-icon">🎮</span>
    <span>GD<span class="accent">HUB</span></span>
  </a>

  <div class="user-area">
    <div class="avatar"><?= e($firstLetter) ?></div>
    <div class="user-info">
      <small>SIGNED IN AS</small>
      <strong><?= e($username) ?></strong>
    </div>
    <form action="logout.php" method="post">
      <?= csrf_field() ?>
       <button class="logout" type="button" id="openLogout"><span>↪</span> Logout</button>
    </form>
  </div>
</header>

<main class="main">

  <section class="hero">
    <div>
      <div class="status"><span class="dot"></span> PLAYER SESSION ACTIVE</div>
      <p class="kicker">YOUR PERSONAL GAMING SPACE</p>
      <h1>READY TO<br><span>PLAY, <?= e($username) ?>?</span></h1>
      <p class="greet" id="greeting"></p>
      <p class="desc">Your next adventure starts here. Choose your game, enter the arena and make your mark.</p>
      <a class="primary-btn" href="#games">EXPLORE GAMES <span>↘</span></a>

      <div class="chips">
        <span class="chip"><b>2</b> games</span>
        <span class="chip"><b>1</b> playable now</span>
        <span class="chip"><b>1</b> coming soon</span>
      </div>
    </div>

    <div class="visual" aria-hidden="true">
      <div class="orbit one"></div>
      <div class="orbit two"></div>
      <div class="symbol">⚔</div>
      <div class="tag top">PLAYER ONLINE</div>
      <div class="tag bottom">LEVEL UP YOUR GAME</div>
    </div>
  </section>

  <section class="library" id="games">
    <div class="section-header">
      <div>
        <p class="kicker" style="margin-top:0">SELECT YOUR DESTINATION</p>
        <h2>Game <span>Library</span></h2>
      </div>
      <span class="count">02 GAMES</span>
    </div>

    <div class="grid">

      <!-- FIGHTING GAME -->
      <article class="game-card fighting">
        <div class="art">
          <span class="art-number">01 / ACTION</span>
          <span class="art-emoji">⚔️</span>
          <span class="art-label">NEON ARENA</span>
        </div>
        <div class="body">
          <div class="meta">
            <span class="category">FIGHTING</span>
            <span class="state">● READY TO PLAY</span>
          </div>
          <h3>Fighting Arena</h3>
          <p>Enter the battle, choose your fighter and prove your skills in an action-packed arena.</p>
          <a class="play-btn" href="games/fighting-game/Neon%20Areana%20game/index2.html">
            PLAY FIGHTING GAME <span>→</span>
          </a>
        </div>
      </article>

      <!-- ZOMBIE GAME - COMING SOON -->
      <article class="game-card zombie coming-soon">
        <div class="art">
          <span class="art-number">02 / SURVIVAL</span>
          <span class="art-emoji">🧟</span>
          <span class="art-label">SURVIVAL MODE</span>
          <span class="coming-badge">✦ COMING SOON</span>
        </div>
        <div class="body">
          <div class="meta">
            <span class="category">ZOMBIE SURVIVAL</span>
            <span class="state pending">● IN DEVELOPMENT</span>
          </div>
          <h3>Zombie Survival</h3>
          <p>A new survival adventure is on the way. Prepare to face the outbreak, protect your health and fight to survive. The mission is coming soon.</p>
          <div class="pending-btn" aria-label="Game is coming soon">
            <span>🚧</span> GAME IN DEVELOPMENT <span>🔒</span>
          </div>
          <p class="pending-note"><span class="pulse-dot"></span> Currently under development</p>
        </div>
      </article>

    </div>
  </section>

  <!-- DEVELOPER -->
  <section class="dev" id="developer">
    <div class="dev-avatar" aria-hidden="true">G</div>

    <div class="dev-info">
      <p class="dev-kicker">MEET THE DEVELOPER</p>
      <h3>Gavisha Dilneth</h3>
      <p class="dev-role">Web &amp; Game Developer</p>

      <p class="dev-bio">
        Hi, I'm Gavisha, the developer behind Game Hub. I love building games and
        web experiences that are fun to play and nice to look at. Game Hub started
        as a way to bring my projects together in one place, and it keeps growing
        with every new game I build.
      </p>

      <div class="skills">
        <span class="skill">HTML</span>
        <span class="skill">CSS</span>
        <span class="skill">JavaScript</span>
        <span class="skill">PHP</span>
        <span class="skill">MySQL</span>
      </div>

      <div class="socials">
        <a class="social" href="https://github.com/gavishadilneth2011" target="_blank" rel="noopener noreferrer">💻 GitHub</a>
        <a class="social" href="https://www.linkedin.com/in/gavisha-dilneth-924803428/?lipi=urn%3Ali%3Apage%3Ad_flagship3_profile_view_base_contact_details%3BC9GSR24ORJ6RKJL0elCxFw%3D%3D" target="_blank" rel="noopener noreferrer">🔗 LinkedIn</a>
        <a class="social" href="mailto:gavishadilneth5@gmail.com">✉️ Email me</a>
      </div>
    </div>
  </section>

  <footer class="footer">
    <span>🎮 GAME HUB</span>
    <span>BUILT FOR PLAYERS. DESIGNED FOR ADVENTURE.</span>
    <span>© <?= date('Y') ?> Gavisha Dilneth</span>
  </footer>

</main>
<!-- LOGOUT / DELETE POPUP -->
<div class="modal" id="logoutModal" hidden>
  <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-icon">👋</div>
    <h3 id="modalTitle">Leaving so soon?</h3>
    <p class="modal-text">Are you sure you want to log out of Game Hub?</p>

    <form action="logout.php" method="post" id="logoutForm">
      <?= csrf_field() ?>

      <div class="modal-actions">
        <button type="button" class="m-btn ghost" id="cancelLogout">Stay</button>
        <button type="submit" name="action" value="logout" class="m-btn primary">Yes, log out</button>
      </div>

      <div class="danger-zone">
        <p class="danger-title">⚠️ Delete my account</p>
        <p class="danger-text">
          This permanently removes your account from our database.
          You will not be able to log in again unless you create a new account.
        </p>
        <label for="confirmText" class="danger-label">Type <b>DELETE</b> to confirm</label>
        <input id="confirmText" name="confirm_text" type="text" autocomplete="off" placeholder="DELETE">
        <button type="submit" name="action" value="delete" class="m-btn danger" id="deleteBtn" disabled>
          Delete account &amp; log out
        </button>
      </div>
    </form>
  </div>
</div>

<div class="toast" id="toast" hidden>Could not delete your account. Please try again.</div>
<script>
(() => {

      // ----- logout / delete popup -----
  const modal = document.getElementById('logoutModal');
  const openBtn = document.getElementById('openLogout');
  const cancelBtn = document.getElementById('cancelLogout');
  const confirmText = document.getElementById('confirmText');
  const deleteBtn = document.getElementById('deleteBtn');

  const openModal = () => { modal.hidden = false; document.body.style.overflow = 'hidden'; cancelBtn.focus(); };
  const closeModal = () => { modal.hidden = true; document.body.style.overflow = ''; confirmText.value = ''; deleteBtn.disabled = true; };

  openBtn.addEventListener('click', openModal);
  cancelBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && !modal.hidden) closeModal(); });
  confirmText.addEventListener('input', () => { deleteBtn.disabled = confirmText.value.trim() !== 'DELETE'; });

  // show an error if the delete failed on the server
  if (new URLSearchParams(location.search).has('delete_failed')) {
    const t = document.getElementById('toast');
    t.hidden = false;
    setTimeout(() => t.hidden = true, 5000);
    history.replaceState(null, '', 'dashboard.php');
  }

  // time-based greeting (uses the visitor's local time)
  const h = new Date().getHours();
  const g = h < 5 ? 'Late-night session?' : h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening';
  const el = document.getElementById('greeting');
  if (el) el.textContent = g + ' 👋';

  // cursor-follow glow on game cards
  document.querySelectorAll('.game-card').forEach(card => {
    card.addEventListener('pointermove', e => {
      const r = card.getBoundingClientRect();
      card.style.setProperty('--mx', (e.clientX - r.left) + 'px');
      card.style.setProperty('--my', (e.clientY - r.top) + 'px');
    });
  });
})();
</script>
</body>
</html>