<?php
/**
 * @var array  $summary          [total_leads, enrolled, total_payments, paid_revenue, pending_revenue]
 * @var array  $leadsByStatus    [status => count]
 * @var array  $leadsByCourse    [course => count]
 * @var array  $paymentsByStatus [status => [cnt, total]]
 * @var array  $revenueByCourse  [[course_name, cnt, total], ...]
 * @var array  $monthly          [Y-m => [cnt, revenue], ...] (6 months)
 * @var array  $recentLeads      [[full_name, email, care_status, created_at], ...]
 * @var array  $careLabels
 * @var array  $courseLabels
 * @var array  $paymentStatusLabels
 */

$totalLeads    = (int) ($summary['total_leads'] ?? 0);
$enrolled      = (int) ($summary['enrolled'] ?? 0);
$totalPayments = (int) ($summary['total_payments'] ?? 0);
$paidRevenue   = (float) ($summary['paid_revenue'] ?? 0);
$pendingRev    = (float) ($summary['pending_revenue'] ?? 0);

$enrollRate = $totalLeads > 0 ? round($enrolled / $totalLeads * 100) : 0;

function fmt_money(float $n): string {
    if ($n >= 1_000_000_000) return number_format($n / 1_000_000_000, 1) . ' tỷ đồng';
    if ($n >= 1_000_000)     return number_format($n / 1_000_000, 1) . ' triệu đồng';
    return number_format($n, 0, ',', '.');
}

$careColors = [
    'new'        => 'var(--info)',
    'contacted'  => '#0369a1',
    'consulting' => 'var(--warning)',
    'enrolled'   => 'var(--success)',
    'dropped'    => 'var(--muted-clr)',
];
$payColors = [
    'paid'      => 'var(--success)',
    'pending'   => 'var(--warning)',
    'refunded'  => 'var(--indigo)',
    'cancelled' => 'var(--danger)',
];
$courseColors = ['#2563eb','#7c3aed','#0891b2','#16a34a','#ca8a04'];

$maxRevenue = max(array_map(fn($m) => $m['revenue'], $monthly) ?: [1]);
?>

<section class="page-head">
    <div>
        <h1>Thống kê &amp; Báo cáo</h1>
        <p class="muted">Tổng quan hiệu quả tư vấn và doanh thu học phí</p>
    </div>
    <a class="btn btn-secondary" href="/">← Trang chủ</a>
</section>

<!-- ── Summary cards ───────────────────────────────────────────── -->
<div class="stats-grid">
    <div class="metric-card">
        <div class="metric-icon" style="background:linear-gradient(135deg,#2563eb,#1d4ed8);">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="metric-body">
            <span class="metric-num"><?= number_format($totalLeads) ?></span>
            <span class="metric-lbl">Tổng khách hàng</span>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-icon" style="background:linear-gradient(135deg,#16a34a,#15803d);">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div class="metric-body">
            <span class="metric-num"><?= number_format($enrolled) ?> <small style="font-size:14px;font-weight:500;color:var(--success);"><?= $enrollRate ?>%</small></span>
            <span class="metric-lbl">Đã ghi danh</span>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-icon" style="background:linear-gradient(135deg,#7c3aed,#6d28d9);">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        </div>
        <div class="metric-body">
            <span class="metric-num"><?= fmt_money($paidRevenue) ?> đ</span>
            <span class="metric-lbl">Doanh thu đã thu</span>
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-icon" style="background:linear-gradient(135deg,#ca8a04,#a16207);">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="metric-body">
            <span class="metric-num"><?= fmt_money($pendingRev) ?> đ</span>
            <span class="metric-lbl">Chờ thu (<?= $totalPayments ?> phiếu)</span>
        </div>
    </div>
</div>

<!-- ── Distributions ───────────────────────────────────────────── -->
<div class="grid-2" style="margin-bottom:20px;">

    <!-- Lead by status -->
    <div class="card">
        <div class="section-head">
            Phân bổ lead theo trạng thái
            <span><?= number_format($totalLeads) ?> leads</span>
        </div>
        <div class="distrib-list">
            <?php foreach ($careLabels as $key => $label):
                $cnt = (int) ($leadsByStatus[$key] ?? 0);
                $pct = $totalLeads > 0 ? round($cnt / $totalLeads * 100) : 0;
                $color = $careColors[$key] ?? 'var(--primary)';
            ?>
            <div class="distrib-row">
                <span class="badge badge-<?= h($key) ?>" style="min-width:120px;justify-content:center;"><?= h($label) ?></span>
                <div class="distrib-bar">
                    <div class="distrib-bar-fill" style="width:<?= $pct ?>%;background:<?= $color ?>;"></div>
                </div>
                <span class="distrib-count"><?= number_format($cnt) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Payment by status -->
    <div class="card">
        <div class="section-head">
            Thanh toán theo trạng thái
            <span><?= number_format($totalPayments) ?> phiếu</span>
        </div>
        <div class="distrib-list">
            <?php foreach ($paymentStatusLabels as $key => $label):
                $data = $paymentsByStatus[$key] ?? ['cnt' => 0, 'total' => 0];
                $cnt = (int) $data['cnt'];
                $pct = $totalPayments > 0 ? round($cnt / $totalPayments * 100) : 0;
                $color = $payColors[$key] ?? 'var(--primary)';
            ?>
            <div class="distrib-row">
                <span class="badge badge-<?= h($key) ?>" style="min-width:130px;justify-content:center;"><?= h($label) ?></span>
                <div class="distrib-bar">
                    <div class="distrib-bar-fill" style="width:<?= $pct ?>%;background:<?= $color ?>;"></div>
                </div>
                <span class="distrib-count"><?= number_format($cnt) ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($paidRevenue + $pendingRev > 0): ?>
        <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--border);font-size:13px;color:var(--text-2);display:flex;gap:16px;flex-wrap:wrap;">
            <span>Đã thu: <strong style="color:var(--success);"><?= number_format($paidRevenue, 0, ',', '.') ?> đ</strong></span>
            <span>Chờ thu: <strong style="color:var(--warning);"><?= number_format($pendingRev, 0, ',', '.') ?> đ</strong></span>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- ── Monthly revenue bar chart ───────────────────────────────── -->
<div class="card" style="margin-bottom:20px;">
    <div class="section-head">
        Doanh thu theo tháng (6 tháng gần nhất)
        <span>Tính theo phiếu đã thanh toán</span>
    </div>
    <div class="bar-chart-wrap">
        <div class="bar-chart">
            <?php foreach ($monthly as $month => $data):
                $rev = (float) $data['revenue'];
                $heightPct = $maxRevenue > 0 ? round($rev / $maxRevenue * 100) : 0;
                $monthLabel = date('T/n', strtotime($month . '-01'));
            ?>
            <div class="bar-col">
                <div class="bar" style="height:<?= max($heightPct, 2) ?>%;">
                    <?php if ($rev > 0): ?>
                    <span class="bar-val"><?= fmt_money($rev) ?></span>
                    <?php endif; ?>
                </div>
                <span class="bar-lbl"><?= h($monthLabel) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ── Revenue by course + Lead by course ──────────────────────── -->
<div class="grid-2" style="margin-bottom:20px;">

    <!-- Revenue by course table -->
    <div class="card">
        <div class="section-head">
            Doanh thu theo khóa học
            <span>Chỉ phiếu đã thanh toán</span>
        </div>
        <?php if ($revenueByCourse === []): ?>
            <p style="color:var(--text-2);font-size:14px;padding:8px 0;">Chưa có dữ liệu.</p>
        <?php else: ?>
        <table class="table" style="margin:-1px;">
            <thead>
                <tr>
                    <th>Khóa học</th>
                    <th style="text-align:right;">Số phiếu</th>
                    <th style="text-align:right;">Doanh thu</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($revenueByCourse as $row): ?>
                <tr>
                    <td><?= h($row['course_name']) ?></td>
                    <td style="text-align:right;"><?= number_format((int)$row['cnt']) ?></td>
                    <td style="text-align:right;font-weight:600;color:var(--success);"><?= number_format((float)$row['total'], 0, ',', '.') ?> đ</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Lead by course interest -->
    <div class="card">
        <div class="section-head">
            Lead theo khóa quan tâm
            <span><?= number_format($totalLeads) ?> leads</span>
        </div>
        <div class="distrib-list">
            <?php
            $maxCourse = max(array_values($leadsByCourse) ?: [1]);
            $ci = 0;
            foreach ($courseLabels as $key => $label):
                $cnt = (int) ($leadsByCourse[$key] ?? 0);
                $pct = $maxCourse > 0 ? round($cnt / $maxCourse * 100) : 0;
                $color = $courseColors[$ci++ % count($courseColors)];
            ?>
            <div class="distrib-row">
                <span style="font-size:13px;color:var(--text-2);min-width:130px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= h($label) ?></span>
                <div class="distrib-bar">
                    <div class="distrib-bar-fill" style="width:<?= $pct ?>%;background:<?= $color ?>;"></div>
                </div>
                <span class="distrib-count"><?= number_format($cnt) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- ── Recent leads ─────────────────────────────────────────────── -->
<?php if ($recentLeads !== []): ?>
<div class="card">
    <div class="section-head">
        Khách hàng mới nhất
        <a href="/leads" style="font-size:13px;font-weight:500;">Xem tất cả →</a>
    </div>
    <div class="table-wrap" style="border:none;box-shadow:none;margin:0 -24px -24px;">
        <table class="table">
            <thead>
                <tr>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recentLeads as $lead): ?>
                <tr>
                    <td><strong><?= h($lead['full_name']) ?></strong></td>
                    <td style="color:var(--text-2);"><?= h($lead['email']) ?></td>
                    <td><span class="badge badge-<?= h($lead['care_status']) ?>"><?= h($careLabels[$lead['care_status']] ?? $lead['care_status']) ?></span></td>
                    <td style="color:var(--text-2);font-size:13px;"><?= h(substr($lead['created_at'], 0, 10)) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
