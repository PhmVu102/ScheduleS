<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Thống kê</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard { padding: 20px; font-family: 'Segoe UI', sans-serif; }
        h1 { text-align: center; color: #2c3e50; margin-bottom: 30px; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            transition: 0.3s;
        }
        .stat-card:hover { transform: translateY(-8px); }
        .stat-card h2 { font-size: 2.8em; margin: 0; font-weight: bold; }
        .stat-card p { margin: 10px 0 0; font-size: 1.1em; opacity: 0.9; }
        .charts {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }
        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .chart-title {
            text-align: center;
            font-size: 1.4em;
            margin-bottom: 0 0 20px 0;
            color: #2c3e50;
            font-weight: 600;
        }
        @media (max-width: 992px) {
            .charts { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="dashboard">
    <h1>Thống Kê Hệ Thống</h1>
    <div class="stats-grid">
        <div class="stat-card">
            <h2><?= number_format($todayOrders) ?></h2>
            <p>Đơn hàng hôm nay</p>
        </div>
        <div class="stat-card">
            <h2><?= number_format($totalRevenue, 0, ',', '.') ?> ₫</h2>
            <p>Tổng doanh thu</p>
        </div>
        <div class="stat-card">
            <h2><?= number_format($newCustomers) ?></h2>
            <p>Khách hàng mới hôm nay</p>
        </div>
        <div class="stat-card">
            <h2><?= number_format($cancelledToday) ?></h2>
            <p>Đơn bị hủy hôm nay</p>
        </div>
    </div>

    <div class="charts">
        <div class="chart-container">
            <h3 class="chart-title">Doanh thu 7 ngày gần nhất</h3>
            <canvas id="revenueChart"></canvas>
        </div>
        <div class="chart-container">
            <h3 class="chart-title">Trạng thái đơn hàng</h3>
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<script>
// Biểu đồ doanh thu 7 ngày
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($dates7days) ?>,
            datasets: [{
                label: 'Doanh thu (₫)',
                data: <?= json_encode($revenue7days) ?>,
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#3498db',
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => v.toLocaleString() + ' ₫' }
                }
            }
        }
    });
    document.querySelectorAll('.stat-card h2').forEach(el => {
        const target = parseInt(el.textContent.replace(/[^0-9]/g, ''));
        let current = 0;
        const increment = target / 50;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            el.textContent = Math.floor(current).toLocaleString() + (el.textContent.includes('₫') ? ' ₫' : '');
        }, 30);
    });

    // Biểu đồ tròn trạng thái đơn hàng
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Chờ xử lý', 'Đang xử lý', 'Đang giao', 'Hoàn thành', 'Đã hủy'],
            datasets: [{
                data: [
                    <?= $statusCount['pending'] ?>,
                    <?= $statusCount['processing'] ?>,
                    <?= $statusCount['shipping'] ?>,
                    <?= $statusCount['completed'] ?>,
                    <?= $statusCount['cancelled'] ?>
                ],
                backgroundColor: ['#f39c12', '#3498db', '#9b59b6', '#2ecc71', '#e74c3c'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { callbacks: { label: ctx => ctx.label + ': ' + ctx.parsed + ' đơn' }}
            }
        }
    });
</script>
</body>
</html>