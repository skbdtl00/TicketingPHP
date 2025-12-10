<div class="card" style="max-width: 520px; margin: 0 auto;">
    <div class="breadcrumb">บัญชีผู้ใช้ / สมัครสมาชิก</div>
    <h2>สมัครสมาชิกใหม่</h2>
    <form method="post">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <div>
            <label>ชื่อ-นามสกุล</label>
            <input type="text" name="name" required>
        </div>
        <div>
            <label>อีเมล</label>
            <input type="email" name="email" required>
        </div>
        <div>
            <label>รหัสผ่าน</label>
            <input type="password" name="password" required minlength="6">
        </div>
        <button class="btn" type="submit">สร้างบัญชี</button>
    </form>
    <p class="mt-2 text-muted">มีบัญชีอยู่แล้ว? <a href="/login">เข้าสู่ระบบ</a></p>
</div>
