<?php
$required_role = 'mahasiswa';
include '../includes/db_connect.php';
include '../includes/check_auth.php';

// Mengambil nama pengguna saat ini dari database berdasarkan ID akun di sesi.
$stmt = $pdo->prepare("SELECT nama_pengguna FROM Pengguna WHERE id_akun = ?");
$stmt->execute([$_SESSION['id_akun']]);
$user = $stmt->fetch();
$nama_user = $user['nama_pengguna'] ?? 'Pengguna';

// Mengambil parameter filter dari URL (kategori dan status favorit).
$filter_kategori = $_GET['kategori'] ?? null;
$show_favorit = isset($_GET['favorit']) && $_GET['favorit'] == '1';
$id_akun = $_SESSION['id_akun'];

// Daftar kategori yang tersedia dan inisialisasi judul halaman.
$kategoris = ['Mental Health', 'Self Development', 'Stress Management', 'Healthy Habits'];
$title = "Semua Artikel Edukasi";

// Jika menampilkan favorit ($show_favorit), gunakan INNER JOIN ke tabel Artikel_Favorit.
if ($show_favorit) {
    $sql = "
        SELECT K.id_artikel, K.judul_artikel, K.kategori, K.gambar, K.tanggal_diperbarui,
               1 AS is_favorit
        FROM Artikel K
        INNER JOIN Artikel_Favorit F ON K.id_artikel = F.id_artikel
        WHERE F.id_akun = :id_akun";
    $params = [':id_akun' => $id_akun];
    $title = "Artikel Favorit Anda";
} 
// Jika menampilkan semua artikel, gunakan LEFT JOIN untuk mengecek status favorit (is_favorit).
else {
    $sql = "
        SELECT K.id_artikel, K.judul_artikel, K.kategori, K.gambar, K.tanggal_diperbarui,
               CASE WHEN F.id_artikel IS NOT NULL THEN 1 ELSE 0 END AS is_favorit
        FROM Artikel K
        LEFT JOIN Artikel_Favorit F ON K.id_artikel = F.id_artikel AND F.id_akun = :id_akun
        WHERE 1=1";
    $params = [':id_akun' => $id_akun];
}

// Menambahkan kondisi filter kategori ke query jika ada dan valid.
if ($filter_kategori && in_array($filter_kategori, $kategoris)) {
    $sql .= " AND K.kategori = :kategori";
    $params[':kategori'] = $filter_kategori;
    $title = ($show_favorit ? "Favorit " : "") . "Kategori: " . $filter_kategori;
}

// Menambahkan klausa ORDER BY (diurutkan berdasarkan tanggal terbaru).
$sql .= " ORDER BY K.tanggal_diperbarui DESC";

// Menjalankan query yang sudah dibentuk dan mengambil hasilnya. Menangani exception database.
try {
    $stmt_artikel = $pdo->prepare($sql);
    $stmt_artikel->execute($params);
    $artikels = $stmt_artikel->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $artikels = [];
    $error_message = "Terjadi kesalahan database.";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konten Edukasi - Mahasiswa</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body style="background-color: #f2f2f2;">

    <nav class="navbar navbar-expand-lg bg-white fixed-top shadow-sm border-bottom">
        <div class="container-fluid px-4">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="konseling.php">Konseling Online</a></li>
                    <li class="nav-item"><a class="nav-link" href="dailyreflection.php">Daily Reflection</a></li>
                    <li class="nav-item"><a class="nav-link" href="assessmenttest.php">Assessment Test</a></li>
                    <li class="nav-item"><a class="nav-link active" href="kontenedukasi.php">Konten Edukasi</a></li>
                    <li class="nav-item"><a class="nav-link" href="laporkankasus.php">Laporkan Kasus</a></li>
                    <li class="nav-item"><a class="nav-link" href="moodtracker.php">Mood Tracker</a></li>
                    <li class="nav-item"><a class="nav-link" href="wellnessmission.php">Wellness Mission</a></li>
                </ul>
                <ul class="navbar-nav align-items-center">
                    <a class="nav-link text-danger fw-semibold d-flex align-items-center" href="../logout.php">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container-fluid py-5" style="margin-top: 40px;">
        <div class="p-5 bg-white rounded shadow-sm">

            <div class="d-flex flex-wrap align-items-center gap-2 mb-4 border-bottom pb-3">
                <a href="kontenedukasi.php"
                    class="btn btn-sm filter-btn <?= (!$filter_kategori && !$show_favorit) ? 'active' : ''; ?>">
                    Semua Artikel
                </a>

                <?php foreach ($kategoris as $kategori) : ?>
                    <a href="?kategori=<?= urlencode($kategori); ?>"
                        class="btn btn-sm filter-btn <?= ($filter_kategori == $kategori) ? 'active' : ''; ?>">
                        <?= htmlspecialchars($kategori); ?>
                    </a>
                <?php endforeach; ?>

                <a href="?favorit=<?= $show_favorit ? '0' : '1'; ?>"
                    class="ms-auto btn btn-sm rounded-circle p-2 shadow-sm border-0"
                    title="<?= $show_favorit ? 'Tampilkan Semua Artikel' : 'Lihat Favorit'; ?>">
                    <i class="fa-heart fa-2x <?= $show_favorit ? 'fas text-danger' : 'far'; ?>"></i>
                </a>
            </div>

            <h4 class="fw-bold text-dark"><?= htmlspecialchars($title); ?></h4>

            <?php if (isset($error_message)) : ?>
                <div class="alert alert-danger"><?= $error_message; ?></div>
            <?php endif; ?>

            <?php if (empty($artikels)) : ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> Yah..belum ada artikelnya.
                </div>
            <?php else : ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mt-2">
                    <?php foreach ($artikels as $artikel) :
                        $is_favorit = $artikel['is_favorit'] == 1;
                    ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden">

                                <img src="<?= htmlspecialchars('../' . $artikel['gambar'] ?? '../assets/default-img.jpg'); ?>"
                                    class="card-img-top" alt="Artikel"
                                    style="height: 196px; object-fit: cover;">

                                <button class="btn btn-sm position-absolute top-0 end-0 m-2 rounded-circle toggle-favorit"
                                    style="z-index: 2; width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;background: rgba(255,255,255,0.85);"
                                    data-id="<?= $artikel['id_artikel']; ?>"
                                    data-favorit="<?= $is_favorit ? '1' : '0'; ?>">
                                    <i class="<?= $is_favorit ? 'fas fa-heart text-danger' : 'far fa-heart text-dark'; ?>"></i>
                                </button>

                                <div class="card-body">
                                    <h5 class="fw-bold">
                                        <a href="detail_artikel.php?id=<?= $artikel['id_artikel']; ?>" class="text-dark text-decoration-none">
                                            <?= htmlspecialchars($artikel['judul_artikel']); ?>
                                        </a>
                                    </h5>

                                    <p class="small text-muted mt-2">
                                        <span class="fw-semibold"><?= htmlspecialchars($artikel['kategori']); ?></span> -
                                        <?= date('F d, Y', strtotime($artikel['tanggal_diperbarui'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content text-center rounded-4 shadow-lg border-0">
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-0 px-4">
                        <i id="modalIcon" class="fas fa-check-circle fa-3x mb-3" style="color: #6D94C5;"></i>
                        <h5 id="modalTitle" class="fw-bold">Status Aksi</h5>
                        <p id="modalBodyText" class="text-muted small mb-3">Pesan akan ditampilkan di sini.</p>
                        <button type="button" class="btn btn-sm btn-primary w-100" data-bs-dismiss="modal" style="background-color: #6D94C5; border-color: #6D94C5;">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Menampilkan modal notifikasi (sukses/gagal)
        function showStatusModal(isSuccess, title, message) {
            var icon = $('#modalIcon');
            var iconColor = isSuccess ? '#6D94C5' : '#dc3545';

            $('#modalTitle').text(title);
            $('#modalBodyText').text(message);

            if (isSuccess) {
                icon.removeClass('fa-exclamation-triangle').addClass('fa-check-circle');
            } else {
                icon.removeClass('fa-check-circle').addClass('fa-exclamation-triangle');
            }
            icon.css('color', iconColor);

            var myModal = new bootstrap.Modal(document.getElementById('statusModal'));
            myModal.show();
        }

        // Handler klik untuk tombol 'toggle-favorit' (menggunakan AJAX)
        $('.toggle-favorit').click(function(e) {
            e.preventDefault();

            var btn = $(this);
            var id = btn.data('id');
            var status = btn.data('favorit');
            var action = status == 0 ? 'add' : 'remove';
            var icon = btn.find('i');

            btn.prop('disabled', true).css('opacity', '.6');

            // Mengirim permintaan POST AJAX ke toggle_favorit.php
            $.post('toggle_favorit.php', {
                    id_artikel: id,
                    action: action
                }, function(res) {
                    if (res.status === 'success') {
                        var s = res.new_status;
                        btn.data('favorit', s);

                        if (s === 1) {
                            // Perbarui tampilan jika berhasil ditambahkan
                            icon.removeClass('far fa-heart text-dark').addClass('fas fa-heart text-danger');
                            showStatusModal(true, 'Berhasil Disimpan', 'Artikel berhasil disimpan ke favorit');

                        } else {
                            // Perbarui tampilan jika berhasil dihapus
                            icon.removeClass('fas fa-heart text-danger').addClass('far fa-heart text-dark');
                            showStatusModal(true, 'Berhasil Dihapus', 'Artikel berhasil dihapus dari favorit');

                            // Jika pengguna sedang di halaman favorit, hapus card artikel secara visual
                            if (window.location.search.includes('favorit=1')) {
                                btn.closest('.col').fadeOut(300, function() {
                                    $(this).remove();
                                    // Jika tidak ada artikel lagi setelah dihapus, muat ulang halaman untuk menampilkan pesan "belum ada artikel"
                                    if ($('.col').length === 0) location.reload();
                                });
                            }
                        }
                    } else {
                        // Tampilkan pesan error dari server
                        showStatusModal(false, 'Aksi Gagal', res.message);
                    }
                }, 'json')
                .fail(function() {
                    // Tampilkan pesan error koneksi
                    showStatusModal(false, 'Kesalahan Koneksi', 'Gagal terhubung ke server. Mohon coba lagi.');
                })
                .always(function() {
                    // Selalu aktifkan kembali tombol setelah permintaan selesai
                    btn.prop('disabled', false).css('opacity', '1');
                });
        });
    </script>
</body>

</html>
