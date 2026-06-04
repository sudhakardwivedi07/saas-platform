<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>You're invited to {{ $companyName }}</title>
<style>
body{margin:0;padding:0;background:#f0f4f8;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased}
.wrap{max-width:540px;margin:40px auto;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)}
.header{background:#1a6aa8;padding:32px 36px 28px}
.header-logo{font-size:20px;font-weight:700;color:#fff;letter-spacing:-.3px}
.body{padding:32px 36px}
.greeting{font-size:22px;font-weight:700;color:#1a202c;margin:0 0 10px}
.sub{font-size:14px;color:#6b7280;line-height:1.6;margin:0 0 28px}
.creds-box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:20px 22px;margin-bottom:28px}
.creds-row{display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid #e2e8f0}
.creds-row:last-child{border-bottom:none;padding-bottom:0}
.creds-row:first-child{padding-top:0}
.creds-label{font-size:12px;text-transform:uppercase;letter-spacing:.6px;color:#9ca3af;font-weight:600}
.creds-value{font-size:14px;color:#1a202c;font-weight:500}
.btn-wrap{margin-bottom:28px}
.btn{display:inline-block;background:#f06b21;color:#fff !important;text-decoration:none;padding:13px 28px;border-radius:9px;font-size:15px;font-weight:600}
.note{font-size:12.5px;color:#9ca3af;line-height:1.6;border-top:1px solid #e2e8f0;padding-top:20px;margin-top:4px}
.footer{background:#f8fafc;padding:18px 36px;text-align:center;font-size:12px;color:#9ca3af;border-top:1px solid #e2e8f0}
</style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <div class="header-logo">{{ $companyName }}</div>
    </div>
    <div class="body">
        <div class="greeting">You're invited! 👋</div>
        <p class="sub">
            Hi <strong>{{ $user->name }}</strong>, you have been added as a team member to
            <strong>{{ $companyName }}</strong>. Use the credentials below to log in to your account.
        </p>

        <div class="creds-box">
            <div class="creds-row">
                <span class="creds-label">Login URL</span>
                <span class="creds-value">{{ $loginUrl }}</span>
            </div>
            <div class="creds-row">
                <span class="creds-label">Email</span>
                <span class="creds-value">{{ $user->email }}</span>
            </div>
            <div class="creds-row">
                <span class="creds-label">Password</span>
                <span class="creds-value">{{ $plainPassword }}</span>
            </div>
            <div class="creds-row">
                <span class="creds-label">Role</span>
                <span class="creds-value">{{ $user->roleDisplayName('User') }}</span>
            </div>
        </div>

        <div class="btn-wrap">
            <a href="{{ $loginUrl }}" class="btn">Log In Now</a>
        </div>

        <p class="note">
            For security, please change your password after your first login. If you did not expect this invitation,
            you can safely ignore this email.
        </p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.
    </div>
</div>
</body>
</html>
