<?php

// === Fungsi Dasar ===
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    if (!preg_match('~^(https?://|//)~', $url) && !str_starts_with($url, '/')) {
        $url = BASE_URL . '/' . ltrim($url, '/');
    }
    header("Location: " . $url);
    exit();
}

function showAlert($message, $type = 'danger') {
    $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    echo "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
        $safeMessage
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

// === CSRF ===
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

// === Password Validation ===
function validatePassword($password, $enabled = true) {
    if (!$enabled) return [];
    $errors = [];
    if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
    if (!preg_match('/[A-Z]/', $password)) $errors[] = "Password must contain at least one uppercase letter.";
    if (!preg_match('/[a-z]/', $password)) $errors[] = "Password must contain at least one lowercase letter.";
    if (!preg_match('/[0-9]/', $password)) $errors[] = "Password must contain at least one number.";
    return $errors;
}

// === Akses Role ===
function userCanAccess($allowedRoles = ['admin']) {
    if (!isset($_SESSION['user_id'])) return false;
    $userRole = $_SESSION['role'] ?? '';
    return in_array($userRole, $allowedRoles);
}

function showAccessDenied($allowedRoles = ['admin']) {
    $roleLabels = getRoleLabels();
    $allowedLabels = array_map(fn($r) => $roleLabels[$r] ?? $r, $allowedRoles);
    $allowedText = implode(' atau ', $allowedLabels);

    include __DIR__ . '/../views/header.php';
    include __DIR__ . '/../views/topnav.php';
    ?>
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/../views/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="alert alert-danger">
                    <h4>⛔ Akses Ditolak</h4>
                    <p>Halaman ini hanya dapat diakses oleh: <strong><?= htmlspecialchars($allowedText) ?></strong>.</p>
                    <p>Anda login sebagai <strong><?= htmlspecialchars(getRoleLabel($_SESSION['role'] ?? 'user')) ?></strong>.</p>
                    <a href="../<?= htmlspecialchars($_SESSION['role'] ?? 'login') ?>/index.php" class="btn btn-primary">Kembali ke Dashboard</a>
                </div>
            </main>
        </div>
    </div>
    <?php
    include __DIR__ . '/../views/footer.php';
    exit();
}

function requireRoleAccess($allowedRoles = ['admin'], $redirectUrl = null) {
    if (!userCanAccess($allowedRoles)) {
        if ($redirectUrl) {
            redirect($redirectUrl);
        } else {
            showAccessDenied($allowedRoles);
        }
    }
}

// === Konfigurasi Menu ===
function loadMenuConfig() {
    $configFile = __DIR__ . '/../config/menu.json';
    if (file_exists($configFile)) {
        $jsonContent = file_get_contents($configFile);
        return json_decode($jsonContent, true) ?: [];
    }
    return [];
}

function getRoleLabel($role) {
    $menuConfig = loadMenuConfig();
    return $menuConfig['roles'][$role]['label'] ?? $role;
}

function getRoleLabels() {
    $menuConfig = loadMenuConfig();
    $labels = [];
    foreach ($menuConfig['roles'] as $role => $config) {
        $labels[$role] = $config['label'];
    }
    return $labels;
}

function getAllowedRolesForModule($moduleName) {
    $menuConfig = loadMenuConfig();
    return $menuConfig['modules'][$moduleName]['allowed_roles'] ?? ['admin'];
}

function userCanAccessModule($moduleName) {
    if (!isset($_SESSION['user_id'])) return false;
    $userRole = $_SESSION['role'] ?? '';
    $allowedRoles = getAllowedRolesForModule($moduleName);
    return in_array($userRole, $allowedRoles);
}

function requireModuleAccess($moduleName, $redirectUrl = null) {
    $allowedRoles = getAllowedRolesForModule($moduleName);
    if (!userCanAccessModule($moduleName)) {
        if ($redirectUrl) {
            redirect($redirectUrl);
        } else {
            showAccessDenied($allowedRoles);
        }
    }
}

// === Helper URL ===
function base_url($path = '') {
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

// === Dropdown & DB Helper ===
function dropdownFromTable($table, $value_field = 'id', $label_field = 'name', $selected = '', $name = '', $placeholder = '-- Pilih --', $order_by = '', $where = '') {
    global $connection;
    $table = str_replace('`', '', $table);
    $value_field = str_replace('`', '', $value_field);
    $label_field = str_replace('`', '', $label_field);
    if ($order_by) $order_by = str_replace('`', '', $order_by);

    $sql = "SELECT `$value_field`, `$label_field` FROM `$table`";
    if ($where) $sql .= " WHERE $where";
    $sql .= $order_by ? " ORDER BY `$order_by`" : " ORDER BY `$label_field` ASC";

    $result = mysqli_query($connection, $sql);
    $html = '<select name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" class="form-control">';
    $html .= '<option value="">' . htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') . '</option>';
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $value = htmlspecialchars($row[$value_field], ENT_QUOTES, 'UTF-8');
            $label = htmlspecialchars($row[$label_field], ENT_QUOTES, 'UTF-8');
            $selected_attr = ($row[$value_field] == $selected) ? 'selected' : '';
            $html .= "<option value=\"$value\" $selected_attr>$label</option>";
        }
    } else {
        $html .= '<option value="">-- Tidak ada data --</option>';
    }
    $html .= '</select>';
    return $html;
}

function getFieldValue($table, $field, $where_field, $where_value) {
    global $connection;
    $table = str_replace('`', '', $table);
    $field = str_replace('`', '', $field);
    $where_field = str_replace('`', '', $where_field);

    $sql = "SELECT `$field` FROM `$table` WHERE `$where_field` = ? LIMIT 1";
    $stmt = mysqli_prepare($connection, $sql);
    if (!$stmt) {
        error_log("SQL prepare error: " . mysqli_error($connection));
        return null;
    }
    $type = is_int($where_value) || is_float($where_value) ? 'd' : 's';
    mysqli_stmt_bind_param($stmt, $type, $where_value);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_row($result);
    mysqli_stmt_close($stmt);
    return $row ? $row[0] : null;
}

function updateMasterTotalFromDetail($connection, $detail_table, $sum_field, $detail_fk_field, $master_table, $master_pk_field, $master_total_field, $master_id) {
    $detail_table = str_replace('`', '', $detail_table);
    $sum_field = str_replace('`', '', $sum_field);
    $detail_fk_field = str_replace('`', '', $detail_fk_field);
    $master_table = str_replace('`', '', $master_table);
    $master_pk_field = str_replace('`', '', $master_pk_field);
    $master_total_field = str_replace('`', '', $master_total_field);

    // Hitung total
    $sql_sum = "SELECT COALESCE(SUM(`$sum_field`), 0) AS total FROM `$detail_table` WHERE `$detail_fk_field` = ?";
    $stmt_sum = mysqli_prepare($connection, $sql_sum);
    if (!$stmt_sum) {
        error_log("updateMasterTotalFromDetail (SUM) prepare error: " . mysqli_error($connection));
        return false;
    }
    $type = is_int($master_id) || is_float($master_id) ? 'd' : 's';
    mysqli_stmt_bind_param($stmt_sum, $type, $master_id);
    mysqli_stmt_execute($stmt_sum);
    $result = mysqli_stmt_get_result($stmt_sum);
    $row = mysqli_fetch_assoc($result);
    $total = (float)($row['total'] ?? 0.0);
    mysqli_stmt_close($stmt_sum);

    // Update master
    $sql_update = "UPDATE `$master_table` SET `$master_total_field` = ? WHERE `$master_pk_field` = ?";
    $stmt_update = mysqli_prepare($connection, $sql_update);
    if (!$stmt_update) {
        error_log("updateMasterTotalFromDetail (UPDATE) prepare error: " . mysqli_error($connection));
        return false;
    }
    mysqli_stmt_bind_param($stmt_update, "d$type", $total, $master_id);
    $success = mysqli_stmt_execute($stmt_update);
    mysqli_stmt_close($stmt_update);
    return $success;
}

// ==================================================================================
// 🔥 FUNGSI UPLOAD SESUAI PDF "UPLOAD GAMBAR"
// ==================================================================================

/**
 * Handle upload gambar sesuai instruksi PDF.
 * 
 * @param array $file $_FILES['foto']
 * @return string '' jika tidak diupload atau gagal, nama file jika sukses
 */
function handle_file_upload($file) {
    // Jika tidak ada file yang diupload
    if (!isset($file['name']) || empty($file['name'])) {
        return ''; // Tidak ada file
    }

    // Path ke folder uploads/produk/
    $target_dir = __DIR__ . '/../uploads/produk/';

    // Buat folder jika belum ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // Validasi error upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return '';
    }

    // Ekstensi file
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Validasi tipe file
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed_types)) {
        return '';
    }

    // Validasi ukuran (max 2MB)
    if ($file['size'] > 2097152) {
        return '';
    }

    // Pastikan benar-benar gambar
    if (!getimagesize($file['tmp_name'])) {
        return '';
    }

    // Generate nama file unik
    $filename = 'produk_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $target_path = $target_dir . $filename;

    // Pindahkan file
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return $filename;
    }

    return '';
}