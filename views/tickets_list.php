<div class="flex justify-between mt-2">
    <div class="breadcrumb">ตั๋วของฉัน</div>
    <a class="btn" href="/tickets/new">+ เปิดตั๋วใหม่</a>
</div>
<div class="card table-scroll mt-2">
    <table>
        <thead>
            <tr>
                <th>หัวข้อ</th>
                <th>หมวดหมู่</th>
                <th>สถานะ</th>
                <th>อัปเดตล่าสุด</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tickets as $ticket): ?>
                <tr>
                    <td><a href="/tickets/<?= $ticket['id'] ?>"><?= sanitize($ticket['title']) ?></a></td>
                    <td><span class="badge"><?= sanitize($ticket['category']) ?></span></td>
                    <td><span class="status status-<?= sanitize($ticket['status']) ?>"><?= human_status($ticket['status']) ?></span></td>
                    <td class="text-muted"><?= sanitize($ticket['updated_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($tickets)): ?>
                <tr><td colspan="4" class="text-muted">ยังไม่มีตั๋ว</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
