<div class="breadcrumb">ตั๋ว / #<?= $ticket['id'] ?></div>
<div class="card mt-2">
    <div class="flex justify-between">
        <div>
            <h2><?= sanitize($ticket['title']) ?></h2>
            <div class="text-muted">โดย <?= sanitize($ticket['user_name']) ?> • <?= sanitize($ticket['created_at']) ?></div>
        </div>
        <div class="flex" style="gap:8px;">
            <span class="badge"><?= sanitize($ticket['category']) ?></span>
            <span class="status status-<?= sanitize($ticket['status']) ?>"><?= human_status($ticket['status']) ?></span>
        </div>
    </div>
    <p class="mt-2"><?= nl2br(sanitize($ticket['content'])) ?></p>
    <?php if (!empty($ticket['attachment_path'])): ?>
        <div class="mt-2">
            <strong>ไฟล์แนบ:</strong> <a href="<?= sanitize($ticket['attachment_path']) ?>" target="_blank">ดาวน์โหลด</a>
        </div>
    <?php endif; ?>
</div>

<div class="card mt-2">
    <h3>การตอบกลับ</h3>
    <div class="grid">
        <?php foreach ($replies as $reply): ?>
            <div class="reply">
                <div class="flex justify-between">
                    <div><strong><?= sanitize($reply['user_name']) ?></strong> <span class="badge"><?= $reply['user_role'] === 'admin' ? 'ผู้ดูแล' : 'ผู้ใช้' ?></span></div>
                    <div class="text-muted"><?= sanitize($reply['created_at']) ?></div>
                </div>
                <p class="mt-2"><?= nl2br(sanitize($reply['message'])) ?></p>
            </div>
        <?php endforeach; ?>
        <?php if (empty($replies)): ?>
            <div class="text-muted">ยังไม่มีการตอบกลับ</div>
        <?php endif; ?>
    </div>
    <?php if ($ticket['status'] !== 'closed'): ?>
    <form method="post" class="mt-3">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <div>
            <label>ตอบกลับ</label>
            <textarea name="message" required></textarea>
        </div>
        <button class="btn" type="submit">ส่งข้อความ</button>
    </form>
    <?php endif; ?>
</div>

<?php if ($user['role'] === 'admin'): ?>
<div class="card mt-2">
    <h3>จัดการตั๋ว</h3>
    <form method="post" action="/admin/tickets/<?= $ticket['id'] ?>/status" class="flex" style="gap:12px; flex-wrap:wrap;">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <select name="status">
            <option value="open" <?= $ticket['status'] === 'open' ? 'selected' : '' ?>>รอดำเนินการ</option>
            <option value="in_progress" <?= $ticket['status'] === 'in_progress' ? 'selected' : '' ?>>กำลังดำเนินการ</option>
            <option value="closed" <?= $ticket['status'] === 'closed' ? 'selected' : '' ?>>ปิดตั๋วแล้ว</option>
        </select>
        <button class="btn" type="submit">อัปเดตสถานะ</button>
    </form>
    <form method="post" action="/admin/tickets/<?= $ticket['id'] ?>/delete" onsubmit="return confirm('ยืนยันลบตั๋วนี้?')" class="mt-2">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <button class="btn btn-secondary" type="submit">ลบตั๋ว</button>
    </form>
</div>
<?php endif; ?>
