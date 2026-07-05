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
    'contacted'  => 'var(--primary)',
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
$courseColors = ['var(--primary)', 'var(--indigo)', 'var(--info)', 'var(--success)', 'var(--warning)'];

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
        <span class="metric-lbl">Tổng khách hàng</span>
        <span class="metric-num"><?= number_format($totalLeads) ?></span>
    </div>

    <div class="metric-card">
        <span class="metric-lbl">Đã ghi danh</span>
        <span class="metric-num">
            <?= number_format($enrolled) ?>
            <small class="text-success font-bold text-xs" style="margin-left:4px;"><?= $enrollRate ?>%</small>
        </span>
    </div>

    <div class="metric-card">
        <span class="metric-lbl">Doanh thu đã thu</span>
        <span class="metric-num"><?= fmt_money($paidRevenue) ?></span>
    </div>

    <div class="metric-card">
        <span class="metric-lbl">Chờ thu (<?= $totalPayments ?> phiếu)</span>
        <span class="metric-num"><?= fmt_money($pendingRev) ?></span>
    </div>
</div>

<!-- ── Distributions ───────────────────────────────────────────── -->
<div class="grid-2 mb-lg">

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
                <span class="badge badge-<?= h($key) ?>"><?= h($label) ?></span>
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
                <span class="badge badge-<?= h($key) ?>"><?= h($label) ?></span>
                <div class="distrib-bar">
                    <div class="distrib-bar-fill" style="width:<?= $pct ?>%;background:<?= $color ?>;"></div>
                </div>
                <span class="distrib-count"><?= number_format($cnt) ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($paidRevenue + $pendingRev > 0): ?>
        <div class="revenue-summary">
            <span>Đã thu: <strong class="text-success"><?= number_format($paidRevenue, 0, ',', '.') ?> đ</strong></span>
            <span>Chờ thu: <strong class="text-warning"><?= number_format($pendingRev, 0, ',', '.') ?> đ</strong></span>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- ── Monthly revenue bar chart ───────────────────────────────── -->
<div class="card mb-lg">
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
<div class="grid-2 mb-lg">

    <!-- Revenue by course table -->
    <div class="card">
        <div class="section-head">
            Doanh thu theo khóa học
            <span>Chỉ phiếu đã thanh toán</span>
        </div>
        <?php if ($revenueByCourse === []): ?>
            <p class="text-muted" style="padding:8px 0;">Chưa có dữ liệu.</p>
        <?php else: ?>
        <table class="table" style="margin:-1px;">
            <thead>
                <tr>
                    <th>Khóa học</th>
                    <th class="text-right">Số phiếu</th>
                    <th class="text-right">Doanh thu</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($revenueByCourse as $row): ?>
                <tr>
                    <td><?= h($row['course_name']) ?></td>
                    <td class="text-right"><?= number_format((int)$row['cnt']) ?></td>
                    <td class="text-right font-bold text-success"><?= number_format((float)$row['total'], 0, ',', '.') ?> đ</td>
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
                <span class="text-muted course-label"><?= h($label) ?></span>
                <div class="distrib-bar">
                    <div class="distrib-bar-fill" style="width:<?= $pct ?>%;background:<?= $color ?>;"></div>
                </div>
                <span class="distrib-count"><?= number_format($cnt) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- ── Staff Leaderboard (Admin Only) ─────────────────────────── -->
<?php if (is_admin() && !empty($leaderboard)): ?>
<div class="card mb-lg">
    <div class="section-head">
        Bảng xếp hạng hiệu suất nhân viên
        <span>Chỉ quản trị viên xem được</span>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Tên nhân viên</th>
                    <th>Email</th>
                    <th class="text-right">Số lead phụ trách</th>
                    <th class="text-right">Đã ghi danh</th>
                    <th class="text-right">Tỉ lệ chốt</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($leaderboard as $row): ?>
                <tr>
                    <td><strong><?= h($row['name']) ?></strong></td>
                    <td class="text-2"><?= h($row['email']) ?></td>
                    <td class="text-right"><?= number_format((int)$row['total_leads']) ?></td>
                    <td class="text-right"><?= number_format((int)$row['enrolled_leads']) ?></td>
                    <td class="text-right amount text-success" style="font-weight: bold;"><?= h($row['conversion_rate']) ?>%</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ── Recent leads ─────────────────────────────────────────────── -->
<?php if ($recentLeads !== []): ?>
<div class="card">
    <div class="section-head">
        Khách hàng mới nhất
        <a href="/leads" class="text-sm font-bold">Xem tất cả →</a>
    </div>
    <div class="table-wrap recent-leads-table">
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
                    <td class="text-2"><?= h($lead['email']) ?></td>
                    <td><span class="badge badge-<?= h($lead['care_status']) ?>"><?= h($careLabels[$lead['care_status']] ?? $lead['care_status']) ?></span></td>
                    <td class="text-muted"><?= h(substr($lead['created_at'], 0, 10)) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
