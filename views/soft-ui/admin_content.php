
<?php
// Koneksi database
require_once __DIR__ . '/../../config/database.php';

// Tanggal hari ini
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

// 1. Query Pendapatan Hari Ini
$query_pendapatan_today = "SELECT SUM(total_bayar) as total 
                            FROM transaksi 
                            WHERE DATE(tanggal) = '$today' 
                            AND status_bayar = 'LUNAS'";
$result_pendapatan = mysqli_query($connection, $query_pendapatan_today);
$data_pendapatan = mysqli_fetch_assoc($result_pendapatan);
$total_penjualan_hari_ini = $data_pendapatan['total'] ?? 0;

// Pendapatan kemarin untuk hitung pertumbuhan
$query_pendapatan_yesterday = "SELECT SUM(total_bayar) as total 
                                FROM transaksi 
                                WHERE DATE(tanggal) = '$yesterday' 
                                AND status_bayar = 'LUNAS'";
$result_yesterday = mysqli_query($connection, $query_pendapatan_yesterday);
$data_yesterday = mysqli_fetch_assoc($result_yesterday);
$total_kemarin = $data_yesterday['total'] ?? 1;

// Hitung persentase pertumbuhan
if ($total_kemarin > 0) {
    $pertumbuhan_penjualan = round((($total_penjualan_hari_ini - $total_kemarin) / $total_kemarin) * 100, 1);
} else {
    $pertumbuhan_penjualan = $total_penjualan_hari_ini > 0 ? 100 : 0;
}
$pertumbuhan_penjualan = ($pertumbuhan_penjualan > 0 ? '+' : '') . $pertumbuhan_penjualan;

// 2. Query Transaksi Hari Ini
$query_transaksi_today = "SELECT COUNT(*) as total 
                          FROM transaksi 
                          WHERE DATE(tanggal) = '$today'";
$result_transaksi = mysqli_query($connection, $query_transaksi_today);
$data_transaksi = mysqli_fetch_assoc($result_transaksi);
$total_transaksi_hari_ini = $data_transaksi['total'] ?? 0;

// Transaksi kemarin
$query_transaksi_yesterday = "SELECT COUNT(*) as total 
                               FROM transaksi 
                               WHERE DATE(tanggal) = '$yesterday'";
$result_transaksi_yesterday = mysqli_query($connection, $query_transaksi_yesterday);
$data_transaksi_yesterday = mysqli_fetch_assoc($result_transaksi_yesterday);
$transaksi_kemarin = $data_transaksi_yesterday['total'] ?? 0;
$selisih_transaksi = $total_transaksi_hari_ini - $transaksi_kemarin;
$selisih_transaksi_text = ($selisih_transaksi > 0 ? '+' : '') . $selisih_transaksi;

// 3. Query Total Produk
$query_produk = "SELECT COUNT(*) as total FROM produk";
$result_produk = mysqli_query($connection, $query_produk);
$data_produk = mysqli_fetch_assoc($result_produk);
$total_produk = $data_produk['total'] ?? 0;

// Hitung produk yang digunakan dalam transaksi hari ini
$query_produk_terjual = "SELECT COUNT(DISTINCT dt.produk_id) as total 
                         FROM detail_transaksi dt
                         JOIN transaksi t ON dt.transaksi_id = t.id
                         WHERE DATE(t.tanggal) = '$today'";
$result_produk_terjual = mysqli_query($connection, $query_produk_terjual);
if ($result_produk_terjual) {
    $data_produk_terjual = mysqli_fetch_assoc($result_produk_terjual);
    $produk_terjual_hari_ini = $data_produk_terjual['total'] ?? 0;
} else {
    $produk_terjual_hari_ini = 0;
}
$produk_baru_text = $produk_terjual_hari_ini > 0 ? $produk_terjual_hari_ini . ' terjual' : 'Belum ada';

// 4. Query Transaksi Belum Lunas
$query_belum_lunas = "SELECT COUNT(*) as total 
                      FROM transaksi 
                      WHERE status_bayar = 'BELUM LUNAS'";
$result_belum_lunas = mysqli_query($connection, $query_belum_lunas);
$data_belum_lunas = mysqli_fetch_assoc($result_belum_lunas);
$transaksi_belum_lunas = $data_belum_lunas['total'] ?? 0;

// Selisih belum lunas (kemarin vs hari ini)
$query_belum_lunas_yesterday = "SELECT COUNT(*) as total 
                                 FROM transaksi 
                                 WHERE status_bayar = 'BELUM LUNAS' 
                                 AND DATE(tanggal) < '$today'";
$result_belum_lunas_yesterday = mysqli_query($connection, $query_belum_lunas_yesterday);
$data_belum_lunas_yesterday = mysqli_fetch_assoc($result_belum_lunas_yesterday);
$belum_lunas_kemarin = $data_belum_lunas_yesterday['total'] ?? 0;
$selisih_belum_lunas = $transaksi_belum_lunas - $belum_lunas_kemarin;
$selisih_belum_lunas_text = ($selisih_belum_lunas > 0 ? '+' : '') . $selisih_belum_lunas;
?>

<div class="col-lg-6 col-12">
    <div class="row">
        <!-- 1. Pendapatan Hari Ini -->
        <div class="col-lg-6 col-md-6 col-12">
            <div class="card">
                <span class="mask bg-gradient-info opacity-10 border-radius-lg"></span>
                <div class="card-body p-3 position-relative">
                    <div class="row">
                        <div class="col-8 text-start">
                            <div class="icon icon-shape bg-white shadow text-center border-radius-2xl">
                                <!-- SVG Wallet -->
                                <svg class="text-info text-gradient opacity-10" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M21 18V6c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM5 6h14v12H5V6zm12 10h-2v-2h2v2zm0-4h-2V8h2v4z"/>
                                </svg>
                            </div>
                            <h5 class="text-white font-weight-bolder mb-0 mt-3">
                                Rp <?= number_format($total_penjualan_hari_ini, 0, ',', '.'); ?>
                            </h5>
                            <span class="text-white text-sm">Pendapatan Hari Ini</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Transaksi Hari Ini -->
        <div class="col-lg-6 col-md-6 col-12 mt-4 mt-md-0">
            <div class="card">
                <span class="mask bg-gradient-success opacity-10 border-radius-lg"></span>
                <div class="card-body p-3 position-relative">
                    <div class="row">
                        <div class="col-8 text-start">
                            <div class="icon icon-shape bg-white shadow text-center border-radius-2xl">
                                <!-- SVG Receipt -->
                                <svg class="text-success text-gradient opacity-10" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M16 18l-2-2-2 2-2-2-2 2V6l2-2 2 2 2-2 2 2 2-2v14l-2 2z"/>
                                </svg>
                            </div>
                            <h5 class="text-white font-weight-bolder mb-0 mt-3">
                                <?= $total_transaksi_hari_ini; ?>
                            </h5>
                            <span class="text-white text-sm">Transaksi Hari Ini</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- 3. Total Produk -->
        <div class="col-lg-6 col-md-6 col-12">
            <div class="card">
                <span class="mask bg-gradient-warning opacity-10 border-radius-lg"></span>
                <div class="card-body p-3 position-relative">
                    <div class="row">
                        <div class="col-8 text-start">
                            <div class="icon icon-shape bg-white shadow text-center border-radius-2xl">
                                <!-- SVG Boxes Stacked -->
                                <svg class="text-warning text-gradient opacity-10" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M11 13H9v-2h2v2zm0-4H9V7h2v2zm4 8h-2v-2h2v2zm0-4h-2v-2h2v2zm0-4h-2V7h2v2zm-8 8H5v-2h2v2zm0-4H5v-2h2v2zm0-4H5V7h2v2zm12 8h-2v-2h2v2zm0-4h-2v-2h2v2zm0-4h-2V7h2v2z"/>
                                </svg>
                            </div>
                            <h5 class="text-white font-weight-bolder mb-0 mt-3">
                                <?= $total_produk; ?>
                            </h5>
                            <span class="text-white text-sm">Total Produk</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Belum Lunas -->
        <div class="col-lg-6 col-md-6 col-12 mt-4 mt-md-0">
            <div class="card">
                <span class="mask bg-gradient-danger opacity-10 border-radius-lg"></span>
                <div class="card-body p-3 position-relative">
                    <div class="row">
                        <div class="col-8 text-start">
                            <div class="icon icon-shape bg-white shadow text-center border-radius-2xl">
                                <!-- SVG File Invoice Dollar -->
                                <svg class="text-danger text-gradient opacity-10" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 18H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V9h-2v2H9v2h2v2h2v-2h2v-2h-2z"/>
                                </svg>
                            </div>
                            <h5 class="text-white font-weight-bolder mb-0 mt-3">
                                <?= $transaksi_belum_lunas; ?>
                            </h5>
                            <span class="text-white text-sm">Belum Lunas</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Right Column: Transaksi Terbaru -->
<div class="col-lg-6 col-12 mt-4 mt-lg-0">
    <div class="card shadow h-100">
        <div class="card-header pb-0 p-3">
            <div class="row">
                <div class="col-6">
                    <h6 class="mb-0">Transaksi Terbaru</h6>
                </div>
                <div class="col-6 text-end">
                    <a href="<?= base_url('/transaksi/index.php') ?>" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
            </div>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-sm align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No. Bukti</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tanggal</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Total</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                            <th class="text-secondary opacity-7"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Koneksi database
                        // Path relatif dari views/soft-ui ke config/database.php
                        require_once __DIR__ . '/../../config/database.php';
                        
                        // Query untuk mengambil data transaksi terbaru
                        // Sesuaikan nama tabel dan kolom dengan database Anda
                        $query = "SELECT 
                                    id,
                                    nomor_bukti,
                                    tanggal,
                                    total_bayar,
                                    status_bayar
                                  FROM transaksi 
                                  ORDER BY tanggal DESC, id DESC 
                                  LIMIT 5";
                        
                        $result = mysqli_query($connection, $query);
                        
                        // Hitung total pendapatan bulan ini
                        $bulan_ini = date('Y-m');
                        $query_summary = "SELECT 
                                            SUM(total_bayar) as total_pendapatan,
                                            COUNT(*) as jumlah_transaksi
                                          FROM transaksi 
                                          WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$bulan_ini'
                                          AND status_bayar = 'LUNAS'";
                        $result_summary = mysqli_query($connection, $query_summary);
                        $summary = mysqli_fetch_assoc($result_summary);
                        
                        // Cek apakah ada data
                        if (mysqli_num_rows($result) > 0):
                            while ($transaksi = mysqli_fetch_assoc($result)):
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex px-2 py-1">
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="mb-0 text-sm"><?= htmlspecialchars($transaksi['nomor_bukti']); ?></h6>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <p class="text-xs font-weight-bold mb-0"><?= date('d/m/Y', strtotime($transaksi['tanggal'])); ?></p>
                            </td>
                            <td>
                                <p class="text-xs font-weight-bold mb-0">Rp <?= number_format($transaksi['total_bayar'], 0, ',', '.'); ?></p>
                            </td>
                            <td>
                                <span class="badge badge-sm <?= $transaksi['status_bayar'] == 'LUNAS' ? 'bg-gradient-success' : 'bg-gradient-warning'; ?>">
                                    <?= htmlspecialchars($transaksi['status_bayar']); ?>
                                </span>
                            </td>
                            <td class="align-middle">
                                <a href="/kasir_app//transaksi/detail.php?id=<?= $transaksi['id']; ?>" class="text-secondary font-weight-bold text-xs" data-toggle="tooltip" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="5" class="text-center">
                                <p class="text-xs text-secondary mb-0">Belum ada transaksi</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer pt-0 p-3 d-flex align-items-center">
            <div class="w-100">
                <p class="text-sm mb-0">
                    <i class="fas fa-info-circle text-primary me-1"></i>
                    Total pendapatan bulan ini: <b>Rp <?= number_format($summary['total_pendapatan'] ?? 0, 0, ',', '.'); ?></b> dari <b><?= $summary['jumlah_transaksi'] ?? 0; ?> transaksi</b>
                </p>
            </div>
        </div>
    </div>
</div>

<?php
// Koneksi tidak perlu ditutup karena akan digunakan di bagian lain halaman
?>
