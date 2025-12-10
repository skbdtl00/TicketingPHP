<div class="card" style="max-width: 640px;">
    <div class="breadcrumb">บัญชีผู้ใช้ / โปรไฟล์</div>
    <h2>โปรไฟล์ของฉัน</h2>
    <form method="post">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <div>
            <label>ชื่อที่แสดง</label>
            <input type="text" name="name" value="<?= sanitize($user['name']) ?>" required>
        </div>
        <div>
            <label>อีเมล</label>
            <input type="email" value="<?= sanitize($user['email']) ?>" disabled>
        </div>
        <div>
            <label>ธีมสี</label>
            <select name="theme">
                <option value="light" <?= $user['theme'] === 'light' ? 'selected' : '' ?>>โหมดสว่าง (Light-Blue)</option>
                <option value="dark" <?= $user['theme'] === 'dark' ? 'selected' : '' ?>>โหมดมืด</option>
            </select>
        </div>
        <button class="btn" type="submit">บันทึก</button>
    </form>
</div>
