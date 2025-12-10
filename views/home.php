<div class="grid grid-2">
    <div class="card">
        <div class="breadcrumb">หน้าหลัก / ภาพรวม</div>
        <h1>ศูนย์บริการช่วยเหลือ Tozei Ticketing</h1>
        <p class="text-muted">ระบบออกตั๋วซัพพอร์ตภาษาไทย โทนสีฟ้า สำหรับติดตามสถานะ แจ้งปัญหา และสื่อสารกับผู้ดูแล</p>
        <div class="flex mt-3">
            <a class="btn" href="<?= $user ? '/tickets/new' : '/login' ?>">เริ่มต้น</a>
            <a class="btn btn-secondary" href="<?= $user ? '/tickets' : '/login' ?>">ดูตั๋ว</a>
        </div>
        <div class="mt-3 text-muted">รองรับอัปโหลดไฟล์แนบสูงสุด 10MB (JPG/PNG/PDF/ZIP)</div>
    </div>
    <div class="grid grid-2">
        <div class="card">
            <div class="flex justify-between">
                <div>
                    <div class="text-muted">สถานะตั๋ว</div>
                    <div class="flex" style="gap:10px;">
                        <span class="status status-open">รอดำเนินการ</span>
                        <span class="status status-in_progress">กำลังดำเนินการ</span>
                        <span class="status status-closed">ปิดตั๋วแล้ว</span>
                    </div>
                </div>
                <div class="badge">Light-Blue</div>
            </div>
            <p class="mt-2 text-muted">ดีไซน์เรียบ สะอาด ใช้ฟอนต์ Anuphan และการ์ดเงาเบา</p>
        </div>
    </div>
</div>
