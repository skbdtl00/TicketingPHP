<div class="card" style="max-width: 760px;">
    <div class="breadcrumb">ตั๋ว / เปิดตั๋วใหม่</div>
    <h2>เปิดตั๋วใหม่</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <div>
            <label>หัวข้อ</label>
            <input type="text" name="title" required>
        </div>
        <div>
            <label>หมวดหมู่</label>
            <select name="category" required>
                <option value="ทั่วไป">ทั่วไป</option>
                <option value="เทคนิค">เทคนิค</option>
                <option value="บัญชี">บัญชี</option>
            </select>
        </div>
        <div>
            <label>รายละเอียด</label>
            <textarea name="content" required></textarea>
        </div>
        <div>
            <label>ไฟล์แนบ (สูงสุด 10MB, JPG/PNG/PDF/ZIP)</label>
            <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf,.zip">
        </div>
        <button class="btn" type="submit">ส่งตั๋ว</button>
    </form>
</div>
