<div class="card" style="max-width: 520px; margin: 0 auto;">
    <div class="breadcrumb">บัญชีผู้ใช้ / เข้าสู่ระบบ</div>
    <h2>เข้าสู่ระบบ</h2>
    <form method="post">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <div>
            <label>อีเมล</label>
            <input type="email" name="email" required>
        </div>
        <div>
            <label>รหัสผ่าน</label>
            <input type="password" name="password" required>
        </div>
        <button class="btn" type="submit">เข้าสู่ระบบ</button>
    </form>
    <p class="mt-2 text-muted">ยังไม่มีบัญชี? <a href="/register">สมัครสมาชิก</a></p>
</div>
