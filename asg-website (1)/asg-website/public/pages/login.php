<?php
/**
 * ASG — Login Page
 */
define('ASG_BOOT', 1);
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/bootstrap.php';

// Already logged in → redirect
if (Auth::check()) {
    redirect(Auth::isAdmin() ? '/admin/' : '/');
}

$error    = htmlspecialchars($_GET['error']   ?? '');
$redirect = htmlspecialchars($_GET['redirect'] ?? '/');
$msg = match($error) {
    'oauth_failed'         => 'OAuth login failed. Please try again.',
    'registration_closed'  => 'New registrations are currently closed.',
    default                => ''
};
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — ASG Studios</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@400;600;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
<style>
:root{--gold:#c9a84c;--bg:#050508;--bg2:#0a0a10;--bg3:#0d0d18;--accent:#00e5ff;--text:#e0e0e0;--text2:#7a7a90;--border:rgba(201,168,76,0.18);--red:#ff4757;--green:#2ed573}
*{margin:0;padding:0;box-sizing:border-box}
body{background:var(--bg);color:var(--text);font-family:'Rajdhani',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.card{background:var(--bg2);border:1px solid var(--border);width:100%;max-width:440px;padding:48px 40px;position:relative}
.card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--gold),transparent)}
.logo{font-family:'Orbitron',monospace;font-size:22px;font-weight:900;color:var(--gold);letter-spacing:4px;text-align:center;margin-bottom:8px}
.tagline{font-family:'Share Tech Mono',monospace;font-size:10px;letter-spacing:3px;color:var(--text2);text-align:center;margin-bottom:36px}
.field{margin-bottom:18px}
label{display:block;font-family:'Share Tech Mono',monospace;font-size:9px;letter-spacing:3px;color:var(--text2);text-transform:uppercase;margin-bottom:6px}
input[type=email],input[type=password]{width:100%;background:var(--bg3);border:1px solid var(--border);color:var(--text);padding:12px 16px;font-family:'Rajdhani',sans-serif;font-size:15px;outline:none;transition:border-color .2s}
input:focus{border-color:var(--gold)}
.btn{display:block;width:100%;font-family:'Orbitron',monospace;font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;padding:14px;border:none;cursor:pointer;transition:all .2s;text-align:center;text-decoration:none;margin-top:8px}
.btn-gold{background:linear-gradient(135deg,var(--gold) 0%,#8b6914 100%);color:#000}
.btn-gold:hover{box-shadow:0 0 24px rgba(201,168,76,.45)}
.btn-outline{background:transparent;color:var(--text2);border:1px solid var(--border)}
.btn-outline:hover{border-color:var(--gold);color:var(--gold)}
.divider{display:flex;align-items:center;gap:12px;margin:24px 0;color:var(--text2);font-size:11px;font-family:'Share Tech Mono',monospace}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border)}
.oauth-btn{display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:11px;border:1px solid var(--border);background:transparent;color:var(--text);font-family:'Rajdhani',sans-serif;font-size:14px;font-weight:600;cursor:pointer;transition:all .2s;text-decoration:none;margin-bottom:10px}
.oauth-btn:hover{border-color:var(--gold);color:var(--gold)}
.msg{padding:10px 14px;margin-bottom:16px;font-size:13px;font-family:'Share Tech Mono',monospace;border-left:3px solid}
.msg-err{border-color:var(--red);color:var(--red);background:rgba(255,71,87,.08)}
.msg-ok{border-color:var(--green);color:var(--green);background:rgba(46,213,115,.08)}
.links{text-align:center;margin-top:24px;font-size:12px;color:var(--text2)}
.links a{color:var(--gold);text-decoration:none}
.links a:hover{text-decoration:underline}
#form-msg{display:none}
</style>
</head>
<body>
<div class="card">
  <div class="logo">ASG</div>
  <div class="tagline">STUDIOS &amp; GROUP — SIGN IN</div>

  <?php if ($msg): ?>
    <div class="msg msg-err"><?= $msg ?></div>
  <?php endif; ?>
  <div class="msg" id="form-msg"></div>

  <form id="login-form" onsubmit="doLogin(event)">
    <input type="hidden" name="csrf_token" value="<?= CSRF::token() ?>">
    <input type="hidden" name="redirect"   value="<?= $redirect ?>">
    <div class="field">
      <label>Email Address</label>
      <input type="email" name="email" placeholder="you@example.com" required autocomplete="email">
    </div>
    <div class="field">
      <label>Password</label>
      <input type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
    </div>
    <button type="submit" class="btn btn-gold">Sign In</button>
  </form>

  <?php if (feature('google_auth') || feature('github_auth')): ?>
  <div class="divider">or continue with</div>
  <?php if (feature('google_auth')): ?>
  <a href="/api/auth.php?action=google" class="oauth-btn">
    <svg width="18" height="18" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.31-8.16 2.31-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
    Continue with Google
  </a>
  <?php endif; ?>
  <?php if (feature('github_auth')): ?>
  <a href="/api/auth.php?action=github" class="oauth-btn">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
    Continue with GitHub
  </a>
  <?php endif; ?>
  <?php endif; ?>

  <?php if (feature('registration_open')): ?>
  <div class="links">
    Don't have an account?
    <a href="/#register">Create one</a>
  </div>
  <?php endif; ?>
</div>

<script>
async function doLogin(e) {
  e.preventDefault();
  const btn = e.target.querySelector('button[type=submit]');
  btn.textContent = 'SIGNING IN...';
  btn.disabled = true;

  const fd   = new FormData(e.target);
  const resp = await fetch('/api/auth.php?action=login', {method:'POST', body:fd});
  const data = await resp.json();

  const msg = document.getElementById('form-msg');
  msg.style.display = 'block';

  if (data.success) {
    msg.className = 'msg msg-ok';
    msg.textContent = data.message || 'Login successful…';
    setTimeout(() => window.location.href = data.redirect || '/', 800);
  } else {
    msg.className = 'msg msg-err';
    msg.textContent = data.error || 'Login failed';
    btn.textContent = 'Sign In';
    btn.disabled = false;
  }
}
</script>
</body>
</html>
