<?php
$required_role = 'mahasiswa';
include '../includes/db_connect.php'; 
include '../includes/check_auth.php';

$id_artikel = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Memeriksa validitas ID artikel
if (!$id_artikel) {
    header('Location: kontenedukasi.php?error=' . urlencode('Artikel tidak ditemukan.'));
    exit();
}

// Mengambil ID akun pengguna dari sesi yang sedang aktif
$id_akun = $_SESSION['id_akun'];

// Menyusun query SQL untuk mengambil detail artikel dan status favorit
$sql = "
    SELECT 
        K.id_artikel, 
        K.judul_artikel, 
        K.kategori, 
        K.isi,
        K.gambar, 
        K.sumber, 
        DATE(K.tanggal_diperbarui) AS tanggal_update,
        CASE 
            WHEN F.id_artikel IS NOT NULL THEN 1 
            ELSE 0 
        END AS is_favorit
    FROM Artikel K
    LEFT JOIN Artikel_Favorit F 
        ON K.id_artikel = F.id_artikel AND F.id_akun = :id_akun
    WHERE K.id_artikel = :id_artikel
";

try {
    // Menyiapkan dan menjalankan statement SQL
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_akun' => $id_akun, ':id_artikel' => $id_artikel]);
    $artikel = $stmt->fetch();
} catch (PDOException $e) {
    // Menangani error jika terjadi kesalahan database saat fetching data
    header('Location: kontenedukasi.php?error=' . urlencode('Kesalahan database saat mengambil data artikel.'));
    exit();
}

// Memeriksa apakah artikel ditemukan di database
if (!$artikel) {
    header('Location: kontenedukasi.php?error=' . urlencode('Artikel tidak ditemukan.'));
    exit();
}

// Menentukan path gambar artikel, menggunakan default jika gambar tidak tersedia
$gambar_path = !empty($artikel['gambar'])
    ? '../' . $artikel['gambar']
    : '../assets/default-img.jpg';

// Menetapkan judul halaman
$title = $artikel['judul_artikel'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Artikel: <?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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

            <article>
                <div class="d-flex align-items-center gap-3 mb-4">
                    <a href="kontenedukasi.php" class="btn btn-primary rounded-circle shadow-sm"
                        style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; background-color: #6D94C5; border-color: var(--primary-blue);"
                        title="Kembali ke Daftar Artikel">
                        <i class="fas fa-arrow-left"></i>
                    </a>

                    <span class="btn btn-primary shadow-sm"
                        style="pointer-events: none; background-color: #6D94C5; border-color: var(--primary-blue); font-weight: 600;">
                        Isi Artikel
                    </span>
                </div>
                <h1 class="mb-3 fw-bold text-primary-blue"><?= htmlspecialchars($artikel['judul_artikel']) ?></h1>

                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                    <div>
                        <span
                            class="badge rounded-pill me-2"
                            style="background-color: #6D94C5 !important; color: white !important; font-size: 1.0rem !important;">
                            <?= htmlspecialchars($artikel['kategori']) ?>
                        </span>
                        <small class="text-muted">Diperbarui:
                            <?= htmlspecialchars($artikel['tanggal_update']) ?></small>
                    </div>

                    <button class="btn btn-sm toggle-favorit" 
                        data-id="<?= $artikel['id_artikel'] ?>"
                        data-favorit="<?= $artikel['is_favorit'] ?>" 
                        title="Tambahkan ke Favorit"
                        style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.75); border: none; backdrop-filter: blur(4px);">
                        <i
                            class="<?= $artikel['is_favorit'] == 1 ? 'fas fa-heart text-danger' : 'far fa-heart text-dark' ?> fa-lg"></i>
                    </button>
                </div>

                <div class="mb-4 text-center">
                    <img src="<?= htmlspecialchars($gambar_path) ?>"
                        class="img-fluid rounded-3"
                        alt="<?= htmlspecialchars($artikel['judul_artikel']) ?>"
                        style="max-height: 400px; width: 100%; object-fit: cover;">
                </div>

                <section class="article-content mb-5" style="line-height: 1.8;">
                    <p><?= nl2br(htmlspecialchars($artikel['isi'])) ?></p>
                </section>

                <footer class="text-end border-top pt-3">
                    <p class="mb-0 text-muted">Sumber:
                        <?= !empty($artikel['sumber']) ? htmlspecialchars($artikel['sumber']) : 'Tidak disebutkan' ?>
                    </p>
                </footer>

            </article>
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
        // Mendefinisikan fungsi untuk menampilkan modal status
        function showStatusModal(isSuccess, title, message) {
            var icon = $('#modalIcon');
            var iconColor = isSuccess ? '#6D94C5' : '#dc3545';

            $('#modalTitle').text(title);
            $('#modalBodyText').text(message);

            // Mengganti ikon berdasarkan status sukses atau gagal
            if (isSuccess) {
                icon.removeClass('fa-exclamation-triangle').addClass('fa-check-circle');
            } else {
                icon.removeClass('fa-check-circle').addClass('fa-exclamation-triangle');
            }
            icon.css('color', iconColor);

            // Menampilkan modal
            var myModal = new bootstrap.Modal(document.getElementById('statusModal'));
            myModal.show();
        }

        // Memasang event listener pada tombol favorit
        $('.toggle-favorit').on('click', function(e) {
            e.preventDefault();
            var btn = $(this);
            var id = btn.data('id');
            var status = btn.data('favorit');
            // Menentukan aksi: 'add' jika status 0 (belum favorit), 'remove' jika status 1 (sudah favorit)
            var action = status == 0 ? 'add' : 'remove';
            var icon = btn.find('i');

            // Menonaktifkan tombol sementara proses AJAX berjalan
            btn.prop('disabled', true).css('opacity', '.6');

            // Mengirim permintaan AJAX ke toggle_favorit.php
            $.post('toggle_favorit.php', {
                    id_artikel: id,
                    action: action
                }, function(res) {
                    // Memproses respons dari server
                    if (res.status === 'success') {
                        var s = res.new_status;
                        // Memperbarui data-favorit pada tombol
                        btn.data('favorit', s);

                        if (s === 1) {
                            // Mengganti ikon menjadi terisi (favorit)
                            icon.removeClass('far text-dark').addClass('fas text-danger');
                            showStatusModal(true, 'Berhasil Disimpan', 'Artikel berhasil disimpan ke favorit');
                        } else {
                            // Mengganti ikon menjadi kosong (tidak favorit)
                            icon.removeClass('fas text-danger').addClass('far text-dark');
                            showStatusModal(true, 'Berhasil Dihapus', 'Artikel berhasil dihapus dari favorit');
                            if (window.location.search.includes('favorit=1')) {
                            }
                        }
                    } else {
                        // Menampilkan pesan error dari server
                        showStatusModal(false, 'Aksi Gagal', res.message);
                    }
                }, 'json')
                // Menangani kegagalan koneksi AJAX
                .fail(function() {
                    showStatusModal(false, 'Kesalahan Koneksi', 'Gagal terhubung ke server. Mohon coba lagi.');
                })
                // Menjalankan kode ini terlepas dari hasil permintaan (selalu)
                .always(function() {
                    // Mengaktifkan kembali tombol
                    btn.prop('disabled', false).css('opacity', '1');
                });
        });
    </script>
</body>

</html>
