<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lupa Password – WR2School</title>
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
        .btn-primary:disabled{opacity:.5;cursor:not-allowed;transform:none}
        .back-link{display:block;text-align:center;margin-top:1.2rem;color:#378ADD;font-size:.85rem;font-weight:600;text-decoration:none;transition:color .2s}
        .back-link:hover{color:#0C447C}
        .alert{display:flex;align-items:center;gap:.6rem;padding:.75rem 1rem;border-radius:10px;font-size:.85rem;margin-bottom:1.25rem}
        .alert-error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.3);color:#dc2626}
        .alert-success{background:rgba(40,167,69,.08);border:1px solid rgba(40,167,69,.3);color:#28a745}
        .alert svg{flex-shrink:0;width:17px;height:17px;fill:none}
        .alert-error svg{stroke:#dc2626}
        .alert-success svg{stroke:#28a745}
        .info-box{background:#E6F1FB;border:1px solid #dce8f5;border-radius:10px;padding:.75rem 1rem;font-size:.82rem;color:#0C447C;margin-bottom:1.25rem;line-height:1.5}
        .info-box strong{color:#0C447C}
        .step{display:none}.step.active{display:block}
        .otp-inputs{display:flex;gap:8px;justify-content:center;margin:1rem 0}
        .otp-inputs input{width:44px;height:50px;text-align:center;font-size:1.3rem;font-weight:700;border:2px solid #E6F1FB;border-radius:10px;background:#E6F1FB;color:#0C447C;font-family:'Inter',sans-serif;outline:none;transition:all .2s}
        .otp-inputs input:focus{border-color:#378ADD;background:#fff;box-shadow:0 0 0 3px rgba(55,138,221,.2)}
        .resend-link{text-align:center;margin-top:.8rem;font-size:.82rem;color:#7baada}
        .resend-link a{color:#378ADD;font-weight:600;text-decoration:none;cursor:pointer}
        .resend-link a:hover{color:#0C447C}
        .resend-link a.disabled{opacity:.5;pointer-events:none}
        .spinner{display:inline-block;width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;vertical-align:middle;margin-right:6px}
        @keyframes spin{to{transform:rotate(360deg)}}
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap"><img src="{{ asset('logo-we2.png') }}" alt="Logo"></div>
        <h2>🔑 Lupa Password</h2>

        <div id="alertBox"></div>

        <!-- STEP 1: Enter username -->
        <div class="step active" id="step1">
            <div class="subtitle">Masukkan username Anda untuk menerima kode OTP WhatsApp</div>
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrap">
                    <svg class="fi" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <input type="text" id="username" placeholder="Masukkan username Anda" required>
                </div>
            </div>
            <button type="button" class="btn-primary" id="btnSendOtp" onclick="sendOtp()">Kirim Kode OTP</button>
        </div>

        <!-- STEP 2: Enter OTP -->
        <div class="step" id="step2">
            <div class="subtitle">Masukkan 6 digit kode OTP yang dikirim ke <strong id="maskedTarget"></strong></div>
            <div class="otp-inputs" id="otpInputs">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="off">
            </div>
            <button type="button" class="btn-primary" id="btnVerifyOtp" onclick="verifyOtp()">Verifikasi OTP</button>
            <div class="resend-link">Tidak menerima kode? <a id="resendLink" onclick="sendOtp()">Kirim ulang</a> <span id="resendTimer"></span></div>
        </div>

        <!-- STEP 3: New password -->
        <div class="step" id="step3">
            <div class="subtitle">Buat password baru untuk akun Anda</div>
            <div class="form-group">
                <label for="new_password">Password Baru</label>
                <div class="input-wrap">
                    <svg class="fi" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input type="password" id="new_password" placeholder="Minimal 6 karakter" required>
                    <button type="button" class="toggle-pw" onclick="togglePw('new_password','eyeNew')"><svg id="eyeNew" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                </div>
            </div>
            <div class="form-group">
                <label for="new_password_confirmation">Konfirmasi Password</label>
                <div class="input-wrap">
                    <svg class="fi" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input type="password" id="new_password_confirmation" placeholder="Ulangi password baru" required>
                    <button type="button" class="toggle-pw" onclick="togglePw('new_password_confirmation','eyeConf')"><svg id="eyeConf" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                </div>
            </div>
            <button type="button" class="btn-primary" id="btnReset" onclick="resetPassword()">Reset Password</button>
        </div>

        <a href="{{ route('login') }}" class="back-link">← Kembali ke Login</a>
    </div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let currentUsername = '';
let verifiedOtp = '';
let resendCountdown = 0;

function showAlert(msg, type) {
    const box = document.getElementById('alertBox');
    const strokeColor = type === 'error' ? '#dc2626' : '#28a745';
    const icon = type === 'error'
        ? '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'
        : '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>';
    box.innerHTML = `<div class="alert alert-${type}"><svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="stroke:${strokeColor}">${icon}</svg>${msg}</div>`;
}

function clearAlert() { document.getElementById('alertBox').innerHTML = ''; }

function setLoading(btn, loading) {
    if (loading) {
        btn.disabled = true;
        btn.dataset.text = btn.textContent;
        btn.innerHTML = '<span class="spinner"></span>Memproses...';
    } else {
        btn.disabled = false;
        btn.textContent = btn.dataset.text || 'Submit';
    }
}

function goToStep(n) {
    document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
    document.getElementById('step' + n).classList.add('active');
    clearAlert();
}

async function sendOtp() {
    const username = document.getElementById('username').value.trim();
    if (!username) { showAlert('Masukkan username.', 'error'); return; }
    currentUsername = username;

    const method = 'whatsapp';

    const btn = document.getElementById('btnSendOtp');
    setLoading(btn, true);
    clearAlert();

    try {
        const res = await fetch('{{ route("forgot-password.send-otp") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ username, method })
        });
        const data = await res.json();
        setLoading(btn, false);

        if (data.success) {
            document.getElementById('maskedTarget').textContent = data.masked_target;
            goToStep(2);
            showAlert(data.message, 'success');
            startResendTimer();
            // Focus first OTP input
            document.querySelector('#otpInputs input').focus();
        } else {
            showAlert(data.message, 'error');
        }
    } catch (e) {
        setLoading(btn, false);
        showAlert('Terjadi kesalahan. Coba lagi.', 'error');
    }
}

function startResendTimer() {
    resendCountdown = 60;
    const link = document.getElementById('resendLink');
    const timer = document.getElementById('resendTimer');
    link.classList.add('disabled');
    const interval = setInterval(() => {
        resendCountdown--;
        timer.textContent = `(${resendCountdown}s)`;
        if (resendCountdown <= 0) {
            clearInterval(interval);
            timer.textContent = '';
            link.classList.remove('disabled');
        }
    }, 1000);
}

function getOtpValue() {
    return Array.from(document.querySelectorAll('#otpInputs input')).map(i => i.value).join('');
}

async function verifyOtp() {
    const otp = getOtpValue();
    if (otp.length !== 6) { showAlert('Masukkan 6 digit kode OTP.', 'error'); return; }

    const btn = document.getElementById('btnVerifyOtp');
    setLoading(btn, true);
    clearAlert();

    try {
        const res = await fetch('{{ route("forgot-password.verify-otp") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ username: currentUsername, otp })
        });
        const data = await res.json();
        setLoading(btn, false);

        if (data.success) {
            verifiedOtp = otp;
            goToStep(3);
            showAlert(data.message, 'success');
        } else {
            showAlert(data.message, 'error');
        }
    } catch (e) {
        setLoading(btn, false);
        showAlert('Terjadi kesalahan. Coba lagi.', 'error');
    }
}

async function resetPassword() {
    const pw = document.getElementById('new_password').value;
    const pwc = document.getElementById('new_password_confirmation').value;
    if (pw.length < 6) { showAlert('Password minimal 6 karakter.', 'error'); return; }
    if (pw !== pwc) { showAlert('Konfirmasi password tidak cocok.', 'error'); return; }

    const btn = document.getElementById('btnReset');
    setLoading(btn, true);
    clearAlert();

    try {
        const res = await fetch('{{ route("forgot-password.reset") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ username: currentUsername, otp: verifiedOtp, new_password: pw, new_password_confirmation: pwc })
        });
        const data = await res.json();
        setLoading(btn, false);

        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => window.location.href = '{{ route("login") }}', 2000);
        } else {
            showAlert(data.message, 'error');
        }
    } catch (e) {
        setLoading(btn, false);
        showAlert('Terjadi kesalahan. Coba lagi.', 'error');
    }
}

// OTP input auto-focus behavior
document.querySelectorAll('#otpInputs input').forEach((inp, i, all) => {
    inp.addEventListener('input', () => {
        inp.value = inp.value.replace(/\D/g, '');
        if (inp.value && i < all.length - 1) all[i + 1].focus();
    });
    inp.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !inp.value && i > 0) all[i - 1].focus();
    });
    inp.addEventListener('paste', (e) => {
        e.preventDefault();
        const paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
        paste.split('').forEach((c, j) => { if (all[j]) all[j].value = c; });
        if (all[Math.min(paste.length, 5)]) all[Math.min(paste.length, 5)].focus();
    });
});

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