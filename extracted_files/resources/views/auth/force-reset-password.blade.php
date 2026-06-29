<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ganti Password – WR2School</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Inter',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#0C447C 0%,#1a5a9a 50%,#378ADD 100%);padding:1rem}
        body::before,body::after{content:'';position:fixed;border-radius:50%;filter:blur(80px);opacity:.25;animation:float 8s ease-in-out infinite alternate;pointer-events:none}
        body::before{width:420px;height:420px;background:radial-gradient(circle,#EF9F27,transparent);top:-80px;left:-80px}
        body::after{width:380px;height:380px;background:radial-gradient(circle,#378ADD,transparent);bottom:-80px;right:-80px;animation-delay:-4s}
        @keyframes float{from{transform:translate(0,0) scale(1)}to{transform:translate(30px,20px) scale(1.08)}}
        .card{position:relative;width:100%;max-width:440px;background:#fff;border:1px solid #E6F1FB;border-radius:20px;padding:2.5rem 2.25rem;box-shadow:0 25px 60px rgba(12,68,124,.3);animation:slideUp .5s cubic-bezier(.22,1,.36,1) both}
        @keyframes slideUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
        .icon-wrap{width:80px;height:80px;margin:0 auto 1rem;display:flex;align-items:center;justify-content:center}
        .icon-wrap img{width:100%;height:100%;object-fit:contain}
        h2{color:#0C447C;font-size:1.4rem;font-weight:700;text-align:center;margin-bottom:.3rem}
        .subtitle{text-align:center;color:#7baada;font-size:.85rem;margin-bottom:1.5rem}
        .form-group{margin-bottom:1rem}
        label{display:block;color:#0C447C;font-size:.82rem;font-weight:600;margin-bottom:.4rem}
        .input-wrap{position:relative}
        .input-wrap svg.fi{position:absolute;left:.9rem;top:50%;transform:translateY(-50%);width:17px;height:17px;stroke:#378ADD;fill:none;pointer-events:none;transition:stroke .2s}
        .input-wrap:focus-within svg.fi{stroke:#0C447C}
        .input-wrap input{width:100%;padding:.72rem .9rem .72rem 2.6rem;background:#E6F1FB;border:1.5px solid #E6F1FB;border-radius:10px;color:#0C447C;font-size:.92rem;font-family:'Inter',sans-serif;outline:none;transition:border-color .2s,background .2s,box-shadow .2s}
        .input-wrap input::placeholder{color:#7baada}
        .input-wrap input:focus{border-color:#378ADD;background:#fff;box-shadow:0 0 0 3px rgba(55,138,221,.2)}
        .toggle-pw{position:absolute;right:.9rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:0;display:flex;align-items:center}
        .toggle-pw svg{width:17px;height:17px;stroke:#378ADD;fill:none;transition:stroke .2s}
        .toggle-pw:hover svg{stroke:#0C447C}
        .btn-primary{width:100%;padding:.78rem;border:none;border-radius:10px;background:linear-gradient(135deg,#0C447C,#378ADD);color:#fff;font-size:.95rem;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;transition:opacity .2s,transform .15s,box-shadow .2s;box-shadow:0 6px 20px rgba(12,68,124,.35);margin-top:.8rem}
        .btn-primary:hover{opacity:.92;transform:translateY(-1px);box-shadow:0 10px 28px rgba(12,68,124,.45)}
        .btn-primary:active{transform:translateY(0)}
        .back-link{display:block;text-align:center;margin-top:1.2rem;color:#378ADD;font-size:.85rem;font-weight:600;text-decoration:none;transition:color .2s}
        .back-link:hover{color:#0C447C}
        .alert{display:flex;align-items:center;gap:.6rem;padding:.75rem 1rem;border-radius:10px;font-size:.85rem;margin-bottom:1.25rem}
        .alert-error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.3);color:#dc2626}
        .alert svg{flex-shrink:0;width:17px;height:17px;fill:none}
        .alert-error svg{stroke:#dc2626}
        .info-box{background:#E6F1FB;border:1px solid #dce8f5;border-radius:10px;padding:.75rem 1rem;font-size:.82rem;color:#0C447C;margin-bottom:1.25rem;line-height:1.5;text-align:center}
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap"><img src="{{ asset('logo-we2.png') }}" alt="Logo"></div>
        <h2>🔑 Ganti Password</h2>
        <div class="subtitle">Silakan ganti password untuk akun <strong>{{ $username }}</strong></div>

        <div class="info-box">
            Password Anda telah di-reset oleh Koordinator. Anda diharuskan membuat password baru sebelum masuk ke sistem.
        </div>

        @if ($errors->any())
        <div class="alert alert-error">
            <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <div>{{ $errors->first() }}</div>
        </div>
        @endif

        <form method="POST" action="{{ url('/force-reset-password') }}">
            @csrf
            <div class="form-group">
                <label for="password">Password Baru</label>
                <div class="input-wrap">
                    <svg class="fi" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input type="password" name="password" id="password" placeholder="Minimal 6 karakter" required minlength="6">
                    <button type="button" class="toggle-pw" onclick="togglePw('password','eyePw')"><svg id="eyePw" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                </div>
            </div>
            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password Baru</label>
                <div class="input-wrap">
                    <svg class="fi" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Ulangi password baru" required minlength="6">
                    <button type="button" class="toggle-pw" onclick="togglePw('password_confirmation','eyeConf')"><svg id="eyeConf" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                </div>
            </div>
            <button type="submit" class="btn-primary">Perbarui Password & Login</button>
        </form>

        <a href="{{ route('login') }}" class="back-link">← Kembali ke Login</a>
    </div>

<script>
function togglePw(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = '<line x1="1" y1="1" x2="23" y2="23"/><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>';
    } else {
        input.type = 'password';
        icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
}
</script>
</body>
</html>
