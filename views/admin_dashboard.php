<div class="breadcrumb">แผงควบคุม / ผู้ดูแล</div>
<div class="grid grid-2 mt-2">
    <div class="card">
        <div class="text-muted">จำนวนตั๋วทั้งหมด</div>
        <h2><?= $stats['total'] ?> รายการ</h2>
        <div class="flex" style="gap:10px;">
            <span class="status status-open">เปิด <?= $stats['open'] ?></span>
            <span class="status status-in_progress">กำลังดำเนินการ <?= $stats['in_progress'] ?></span>
            <span class="status status-closed">ปิดแล้ว <?= $stats['closed'] ?></span>
        </div>
    </div>
    <div class="card">
        <div class="text-muted">ฟิลเตอร์</div>
        <form class="grid grid-2" method="get" action="/admin/tickets">
            <div>
                <label>สถานะ</label>
                <select name="status">
                    <option value="">ทั้งหมด</option>
                    <option value="open">รอดำเนินการ</option>
                    <option value="in_progress">กำลังดำเนินการ</option>
                    <option value="closed">ปิดตั๋วแล้ว</option>
                </select>
            </div>
            <div>
                <label>หมวดหมู่</label>
                <input type="text" name="category" placeholder="เช่น เทคนิค">
            </div>
            <div>
                <label>อีเมลผู้ใช้</label>
                <input type="text" name="user" placeholder="ค้นหาอีเมล">
            </div>
            <div class="flex" style="justify-content:flex-end; align-self:flex-end;">
                <button class="btn" type="submit">ค้นหา</button>
            </div>
        </form>
    </div>
</div>

<div class="card table-scroll mt-2">
    <div class="flex justify-between">
        <h3>ตั๋วทั้งหมด</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>หัวข้อ</th>
                <th>ผู้ส่ง</th>
                <th>สถานะ</th>
                <th>อัปเดตล่าสุด</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tickets as $t): ?>
                <tr>
                    <td><?= $t['id'] ?></td>
                    <td><a href="/tickets/<?= $t['id'] ?>"><?= sanitize($t['title']) ?></a></td>
                    <td><?= sanitize($t['user_email']) ?></td>
                    <td><span class="status status-<?= sanitize($t['status']) ?>"><?= human_status($t['status']) ?></span></td>
                    <td class="text-muted"><?= sanitize($t['updated_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($tickets)): ?>
                <tr><td colspan="5" class="text-muted">ไม่พบตั๋ว</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
