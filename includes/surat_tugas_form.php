<?php
require_once 'config.php';
require_login();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($surat_tugas) ? 'Edit' : 'Buat'; ?> Surat Tugas - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h1 class="mb-0">
                            <i class="fas fa-file-alt me-1"></i>
                            <?php echo isset($surat_tugas) ? 'Edit' : 'Buat'; ?> Surat Tugas
                        </h1>
                        <p class="mb-0"><?php echo isset($surat_tugas) ? 'Perbarui data surat tugas' : 'Buat surat tugas baru'; ?></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="surat_tugas.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <?php if ($message = get_flash_message('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($message = get_flash_message('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Surat Tugas Form -->
            <div class="content-card">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-section">
                                <h5>Informasi Surat</h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nomor_surat_tugas" class="form-label">Nomor Surat Tugas</label>
                                            <input type="text" class="form-control" id="nomor_surat_tugas" name="nomor_surat_tugas"
                                                   value="<?php echo isset($surat_tugas) ? htmlspecialchars($surat_tugas['nomor_surat_tugas']) : ''; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="berdasarkan_surat" class="form-label">Berdasarkan Surat</label>
                                            <input type="text" class="form-control" id="berdasarkan_surat" name="berdasarkan_surat"
                                                   value="<?php echo isset($surat_tugas) ? htmlspecialchars($surat_tugas['berdasarkan_surat']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nomor_surat" class="form-label">Nomor Surat</label>
                                            <input type="text" class="form-control" id="nomor_surat" name="nomor_surat" 
                                                   value="<?php echo isset($surat_tugas) ? htmlspecialchars($surat_tugas['nomor_surat']) : ''; ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="hari" class="form-label">Hari</label>
                                            <input type="text" class="form-control" id="hari" name="hari" 
                                                   value="<?php echo isset($surat_tugas) ? htmlspecialchars($surat_tugas['hari']) : ''; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="tanggal_surat" class="form-label">Tanggal Surat</label>
                                            <input type="date" class="form-control" id="tanggal_surat" name="tanggal_surat" 
                                                   value="<?php echo isset($surat_tugas) ? htmlspecialchars($surat_tugas['tanggal_surat']) : ''; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="tanggal_surat_tugas" class="form-label">Tanggal Surat Tugas</label>
                                            <input type="date" class="form-control" id="tanggal_surat_tugas" name="tanggal_surat_tugas" 
                                                   value="<?php echo isset($surat_tugas) ? htmlspecialchars($surat_tugas['tanggal_surat_tugas']) : ''; ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="tentang" class="form-label">Tentang</label>
                                    <textarea class="form-control" id="tentang" name="tentang" rows="2" required><?php echo isset($surat_tugas) ? htmlspecialchars($surat_tugas['tentang']) : ''; ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="lokasi_tugas" class="form-label">Lokasi Tugas</label>
                                    <textarea class="form-control" id="lokasi_tugas" name="lokasi_tugas" rows="2" required><?php echo isset($surat_tugas) ? htmlspecialchars($surat_tugas['tempat_tugas']) : ''; ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="pejabat_tanda_tangan" class="form-label">Pejabat Penanda Tangan</label>
                                    <select class="form-select" id="pejabat_tanda_tangan" name="pejabat_tanda_tangan" required>
                                        <option value="">Pilih Pejabat</option>
                                        <?php
                                        $stmt = $pdo->prepare("SELECT id, nama, nip, jabatan, golongan_ruangan FROM pegawai WHERE status_pegawai = 'aktif' ORDER BY nama");
                                        $stmt->execute();
                                        $pegawai_list = $stmt->fetchAll();
                                        foreach ($pegawai_list as $p):
                                        ?>
                                            <option value="<?php echo $p['id']; ?>" 
                                                    <?php echo isset($surat_tugas) && $surat_tugas['pejabat_tanda_tangan'] == $p['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($p['nama']); ?> - <?php echo htmlspecialchars($p['jabatan']); ?> (<?php echo htmlspecialchars($p['nip']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="form-section">
                                <h5>Pegawai Bertugas</h5>
                                
                                <div class="mb-3">
                                    <label for="pegawai_bertugas" class="form-label">Tambah Pegawai Bertugas</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <select class="form-select" id="pegawai_bertugas">
                                                <option value="">Pilih Pegawai</option>
                                                <?php
                                                foreach ($pegawai_list as $p):
                                                ?>
                                                    <option value="<?php echo $p['id']; ?>" 
                                                            data-nip="<?php echo htmlspecialchars($p['nip']); ?>"
                                                            data-nama="<?php echo htmlspecialchars($p['nama']); ?>"
                                                            data-jabatan="<?php echo htmlspecialchars($p['jabatan']); ?>"
                                                            data-golongan="<?php echo htmlspecialchars($p['golongan_ruangan'] ?? '-'); ?>">
                                                        <?php echo htmlspecialchars($p['nama']); ?> - <?php echo htmlspecialchars($p['nip']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="date" class="form-control" id="tanggal_mulai_tugas" placeholder="Tanggal Mulai">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="date" class="form-control" id="tanggal_selesai_tugas" placeholder="Tanggal Selesai">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-primary w-100" onclick="addPegawaiBertugas()">
                                                <i class="fas fa-plus me-2"></i>Tambah
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered" id="pegawai_bertugas_table">
                                        <thead>
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="20%">Nama Pegawai</th>
                                                <th width="15%">NIP</th>
                                                <th width="20%">Jabatan</th>
                                                <th width="15%">Golongan Ruang</th>
                                                <th width="15%">Tanggal Melaksanakan Tugas</th>
                                                <th width="10%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="pegawai_bertugas_body">
                                            <?php
                                            if (isset($surat_tugas) && $surat_tugas['id']):
                                                $stmt = $pdo->prepare("
                                                    SELECT * FROM surat_tugas_pegawai 
                                                    WHERE id_surat_tugas = ? 
                                                    ORDER BY id
                                                ");
                                                $stmt->execute([$surat_tugas['id']]);
                                                $pegawai_tugas = $stmt->fetchAll();
                                                $no = 1;
                                                foreach ($pegawai_tugas as $pt):
                                            ?>
                                                <tr data-id="<?php echo $pt['id']; ?>" data-pegawai-id="<?php echo $pt['id_pegawai']; ?>">
                                                    <td><?php echo $no++; ?></td>
                                                    <td><?php echo htmlspecialchars($pt['nama_pegawai']); ?></td>
                                                    <td><?php echo htmlspecialchars($pt['nip']); ?></td>
                                                    <td><?php echo htmlspecialchars($pt['jabatan']); ?></td>
                                                    <td><?php echo htmlspecialchars($pt['golongan_ruang']); ?></td>
                                                    <td><?php echo format_date($pt['tanggal_mulai_tugas']); ?> - <?php echo format_date($pt['tanggal_selesai_tugas']); ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="removePegawaiBertugas(this)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan
                            </button>
                            <a href="surat_tugas.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
        let pegawaiCount = <?php echo isset($surat_tugas) && $surat_tugas['id'] ? count($pegawai_tugas ?? []) : 0; ?>;

        function addPegawaiBertugas() {
            const pegawaiSelect = document.getElementById('pegawai_bertugas');
            const tanggalMulai = document.getElementById('tanggal_mulai_tugas').value;
            const tanggalSelesai = document.getElementById('tanggal_selesai_tugas').value;
            
            if (!pegawaiSelect.value) {
                alert('Pilih pegawai terlebih dahulu!');
                return;
            }
            
            if (!tanggalMulai || !tanggalSelesai) {
                alert('Isi tanggal mulai dan selesai tugas!');
                return;
            }

            const selectedOption = pegawaiSelect.options[pegawaiSelect.selectedIndex];
            const pegawaiId = pegawaiSelect.value;
            const nip = selectedOption.getAttribute('data-nip');
            const nama = selectedOption.getAttribute('data-nama');
            const jabatan = selectedOption.getAttribute('data-jabatan');
            const golongan = selectedOption.getAttribute('data-golongan');

            const tbody = document.getElementById('pegawai_bertugas_body');
            const row = document.createElement('tr');
            row.setAttribute('data-pegawai-id', pegawaiId);
            
            pegawaiCount++;
            row.innerHTML = `
                <td>${pegawaiCount}</td>
                <td>${nama}</td>
                <td>${nip}</td>
                <td>${jabatan}</td>
                <td>${golongan}</td>
                <td>${tanggalMulai} - ${tanggalSelesai}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removePegawaiBertugas(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            
            tbody.appendChild(row);

            // Reset form
            pegawaiSelect.value = '';
            document.getElementById('tanggal_mulai_tugas').value = '';
            document.getElementById('tanggal_selesai_tugas').value = '';
        }

        function removePegawaiBertugas(btn) {
            const row = btn.closest('tr');
            row.remove();
            
            // Renumber rows
            const tbody = document.getElementById('pegawai_bertugas_body');
            const rows = tbody.querySelectorAll('tr');
            rows.forEach((row, index) => {
                row.cells[0].textContent = index + 1;
            });
            pegawaiCount = rows.length;
        }

        // Add hidden input to store pegawai data
        document.querySelector('form').addEventListener('submit', function(e) {
            const tbody = document.getElementById('pegawai_bertugas_body');
            const rows = tbody.querySelectorAll('tr');
            
            if (rows.length === 0) {
                e.preventDefault();
                alert('Tambahkan minimal satu pegawai bertugas!');
                return;
            }

            rows.forEach((row, index) => {
                const pegawaiId = row.getAttribute('data-pegawai-id');
                const nama = row.cells[1].textContent;
                const nip = row.cells[2].textContent;
                const jabatan = row.cells[3].textContent;
                const golongan = row.cells[4].textContent;
                const tanggal = row.cells[5].textContent;
                const [tanggalMulai, tanggalSelesai] = tanggal.split(' - ');

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `pegawai_bertugas[${index}][id_pegawai]`;
                input.value = pegawaiId;
                this.appendChild(input);

                const input2 = document.createElement('input');
                input2.type = 'hidden';
                input2.name = `pegawai_bertugas[${index}][nama_pegawai]`;
                input2.value = nama;
                this.appendChild(input2);

                const input3 = document.createElement('input');
                input3.type = 'hidden';
                input3.name = `pegawai_bertugas[${index}][nip]`;
                input3.value = nip;
                this.appendChild(input3);

                const input4 = document.createElement('input');
                input4.type = 'hidden';
                input4.name = `pegawai_bertugas[${index}][jabatan]`;
                input4.value = jabatan;
                this.appendChild(input4);

                const input5 = document.createElement('input');
                input5.type = 'hidden';
                input5.name = `pegawai_bertugas[${index}][golongan_ruang]`;
                input5.value = golongan;
                this.appendChild(input5);

                const input6 = document.createElement('input');
                input6.type = 'hidden';
                input6.name = `pegawai_bertugas[${index}][tanggal_mulai_tugas]`;
                input6.value = tanggalMulai;
                this.appendChild(input6);

                const input7 = document.createElement('input');
                input7.type = 'hidden';
                input7.name = `pegawai_bertugas[${index}][tanggal_selesai_tugas]`;
                input7.value = tanggalSelesai;
                this.appendChild(input7);
            });
        });
    </script>
</body>
</html>
