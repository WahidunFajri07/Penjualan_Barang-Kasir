<?php
$current_page = $current_page ?? 'dashboard';
?>

</main>

<!--   Core JS Files   -->
<script src="<?= base_url('assets/soft-ui/js/core/popper.min.js'); ?>"></script>
<script src="<?= base_url('assets/soft-ui/js/core/bootstrap.min.js'); ?>"></script>
<script src="<?= base_url('assets/soft-ui/js/plugins/perfect-scrollbar.min.js'); ?>"></script>
<script src="<?= base_url('assets/soft-ui/js/plugins/smooth-scrollbar.min.js'); ?>"></script>

<?php if($current_page == 'dashboard'): ?>
<script src="<?= base_url('assets/soft-ui/js/plugins/chartjs.min.js'); ?>"></script>
<script>
var ctx1 = document.getElementById("chart-line").getContext("2d");
var gradientStroke1 = ctx1.createLinearGradient(0, 230, 0, 50);
gradientStroke1.addColorStop(1, 'rgba(94, 114, 228, 0.2)');
gradientStroke1.addColorStop(0.2, 'rgba(94, 114, 228, 0.0)');
gradientStroke1.addColorStop(0, 'rgba(94, 114, 228, 0)');

new Chart(ctx1, {
    type: "line",
    data: {
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        datasets: [{
            label: "Penjualan",
            tension: 0.4,
            borderWidth: 0,
            pointRadius: 0,
            borderColor: "#5e72e4",
            backgroundColor: gradientStroke1,
            borderWidth: 3,
            fill: true,
            data: [50, 40, 300, 220, 500, 250, 400, 230, 500, 350, 400, 500],
            maxBarThickness: 6
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false,
            }
        },
        interaction: {
            intersect: false,
            mode: 'index',
        },
        scales: {
            y: {
                grid: {
                    drawBorder: false,
                    display: true,
                    drawOnChartArea: true,
                    drawTicks: false,
                    borderDash: [5, 5]
                },
                ticks: {
                    display: true,
                    padding: 10,
                    color: '#fbfbfb',
                    font: {
                        size: 11,
                        family: "Open Sans",
                        style: 'normal',
                        lineHeight: 2
                    },
                }
            },
            x: {
                grid: {
                    drawBorder: false,
                    display: false,
                    drawOnChartArea: false,
                    drawTicks: false,
                    borderDash: [5, 5]
                },
                ticks: {
                    display: true,
                    color: '#ccc',
                    padding: 20,
                    font: {
                        size: 11,
                        family: "Open Sans",
                        style: 'normal',
                        lineHeight: 2
                    },
                }
            },
        },
    },
});
</script>
<?php endif; ?>

<!-- SIDEBAR TOGGLE SCRIPT -->
<script>
// Toggle Sidenav
const iconNavbarSidenav = document.getElementById('iconNavbarSidenav');
const iconSidenav = document.getElementById('iconSidenav');
const sidenav = document.getElementById('sidenav-main');
let body = document.getElementsByTagName('body')[0];
let className = 'g-sidenav-pinned';

if (iconNavbarSidenav) {
    iconNavbarSidenav.addEventListener("click", toggleSidenav);
}

if (iconSidenav) {
    iconSidenav.addEventListener("click", toggleSidenav);
}

function toggleSidenav() {
    if (body.classList.contains(className)) {
        body.classList.remove(className);
        setTimeout(function() {
            sidenav.classList.remove('bg-white');
        }, 100);
        sidenav.classList.remove('bg-transparent');
    } else {
        body.classList.add(className);
        sidenav.classList.add('bg-white');
        sidenav.classList.remove('bg-transparent');
        iconSidenav.classList.remove('d-none');
    }
}

// Auto-hide alerts
setTimeout(function() {
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        var bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl + N = Transaksi Baru
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        window.location.href = '/transaksi/add.php';
    }
    // Ctrl + L = Logout
    if (e.ctrlKey && e.key === 'l') {
        e.preventDefault();
        window.location.href = 'logout.php';
    }
});
</script>

<!-- Control Center for Soft Dashboard -->
<script src="<?= base_url('assets/soft-ui/js/soft-ui-dashboard.min.js?v=1.1.0'); ?>"></script>

</body>
</html>