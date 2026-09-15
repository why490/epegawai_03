<?php
// Fetch pegawai data for dropdown
$pegawai_query = "SELECT id, nama, nip, jabatan, golongan_ruangan FROM pegawai WHERE status_pegawai = 'aktif' ORDER BY nama ASC";
$pegawai_result = $pdo->query($pegawai_query);
$pegawai_list = [];
if ($pegawai_result) {
    while ($row = $pegawai_result->fetch()) {
        $pegawai_list[] = $row;
    }
}

// Check if editing
$is_edit = isset($kgb) && $kgb;
?>
<div class="row">
    <div class="col-12">
        <h4 class="mb-3"><?php echo $is_edit ? 'Edit Kenaikan Gaji Berkala' : 'Form Kenaikan Gaji Berkala'; ?></h4>
    </div>
</div>

<form method="POST" action="kgb.php">
    <?php if ($is_edit): ?>
        <input type="hidden" name="id" value="<?php echo $kgb['id']; ?>">
    <?php endif; ?>
                    <div class="row">
                        <!-- Header Fields -->
                        <div class="col-md-4 mb-3">
                            <label for="nomor_surat" class="form-label">Nomor Surat <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nomor_surat" name="nomor_surat" placeholder="Masukkan nomor surat" required value="<?php echo $is_edit ? htmlspecialchars($kgb['nomor_surat']) : ''; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="lampiran" class="form-label">Lampiran</label>
                            <input type="text" class="form-control" id="lampiran" name="lampiran" placeholder="Contoh: 1 lembar" value="<?php echo $is_edit ? htmlspecialchars($kgb['lampiran']) : ''; ?>">
                            <small class="text-muted">isi "-" bila tidak ada lampiran</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tanggal_surat" class="form-label">Tanggal Surat <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_surat" name="tanggal_surat" required value="<?php echo $is_edit ? $kgb['tanggal_surat'] : ''; ?>">
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row">
                        <!-- Data Pegawai -->
                        <div class="col-12 mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-user me-2"></i>Data Pegawai
                            </h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="id_pegawai" class="form-label">Pilih Pegawai <span class="text-danger">*</span></label>
                            <select class="form-select" id="id_pegawai" name="id_pegawai" required>
                                <option value="">-- Pilih Pegawai --</option>
                                <?php foreach ($pegawai_list as $pegawai): ?>
                                    <option value="<?php echo $pegawai['id']; ?>" 
                                            data-nip="<?php echo htmlspecialchars($pegawai['nip'] ?? ''); ?>"
                                            data-jabatan="<?php echo htmlspecialchars($pegawai['jabatan'] ?? ''); ?>"
                                            data-golongan="<?php echo htmlspecialchars($pegawai['golongan_ruangan'] ?? ''); ?>"
                                            <?php echo $is_edit && $kgb['id_pegawai'] == $pegawai['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($pegawai['nama']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nip" class="form-label">NIP</label>
                            <input type="text" class="form-control" id="nip" name="nip" placeholder="NIP akan muncul otomatis" readonly value="<?php echo $is_edit ? htmlspecialchars($kgb['nip']) : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jabatan" class="form-label">Jabatan</label>
                            <input type="text" class="form-control" id="jabatan" name="jabatan" placeholder="Jabatan akan muncul otomatis" readonly value="<?php echo $is_edit ? htmlspecialchars($kgb['jabatan']) : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="golongan_saat_ini" class="form-label">Golongan Saat Ini</label>
                            <input type="text" class="form-control" id="golongan_saat_ini" name="golongan_saat_ini" placeholder="Golongan akan muncul otomatis" readonly value="<?php echo $is_edit ? htmlspecialchars($kgb['golongan_saat_ini']) : ''; ?>">
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row">
                        <!-- Dasar SK Terakhir -->
                        <div class="col-12 mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-file-alt me-2"></i>Dasar SK Terakhir Tentang Gaji/Pangkat
                            </h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="gaji_pokok_lama" class="form-label">Gaji Pokok Lama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="gaji_pokok_lama" name="gaji_pokok_lama" placeholder="Contoh: Rp 2.500.000" required value="<?php echo $is_edit ? htmlspecialchars($kgb['gaji_pokok_lama']) : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="oleh_pejabat" class="form-label">Oleh Pejabat <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="oleh_pejabat" name="oleh_pejabat" placeholder="Nama pejabat" required value="<?php echo $is_edit ? htmlspecialchars($kgb['oleh_pejabat']) : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nomor_surat_sk" class="form-label">Nomor Surat <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nomor_surat_sk" name="nomor_surat_sk" placeholder="Masukkan nomor surat" required value="<?php echo $is_edit ? htmlspecialchars($kgb['nomor_surat_sk']) : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_surat_sk" class="form-label">Tanggal Surat <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_surat_sk" name="tanggal_surat_sk" required value="<?php echo $is_edit ? $kgb['tanggal_surat_sk'] : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_mulai_berlaku" class="form-label">Tanggal Mulai Berlakunya Gaji Tersebut <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_mulai_berlaku" name="tanggal_mulai_berlaku" required value="<?php echo $is_edit ? $kgb['tanggal_mulai_berlaku'] : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="masa_kerja_golongan" class="form-label">Masa Kerja Golongan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="masa_kerja_golongan" name="masa_kerja_golongan" placeholder="Contoh: 5 Tahun 2 Bulan" required value="<?php echo $is_edit ? htmlspecialchars($kgb['masa_kerja_golongan']) : ''; ?>">
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row">
                        <!-- Data Kenaikan Gaji -->
                        <div class="col-12 mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-chart-line me-2"></i>Data Kenaikan Gaji
                            </h5>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="gaji_pokok_baru" class="form-label">Gaji Pokok Baru <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="gaji_pokok_baru" name="gaji_pokok_baru" placeholder="Contoh: Rp 2.500.000" required value="<?php echo $is_edit ? htmlspecialchars($kgb['gaji_pokok_baru']) : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="mulai_tanggal" class="form-label">Mulai Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="mulai_tanggal" name="mulai_tanggal" required value="<?php echo $is_edit ? $kgb['mulai_tanggal'] : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="berdasarkan_masa_kerja" class="form-label">Berdasarkan Masa Kerja <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="berdasarkan_masa_kerja" name="berdasarkan_masa_kerja" placeholder="Contoh: 5 Tahun 2 Bulan" required value="<?php echo $is_edit ? htmlspecialchars($kgb['berdasarkan_masa_kerja']) : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="kenaikan_gaji_yad" class="form-label">Kenaikan Gaji Yang Akan Datang <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="kenaikan_gaji_yad" name="kenaikan_gaji_yad" placeholder="Otomatis 2 tahun setelah mulai tanggal" readonly value="<?php echo $is_edit ? $kgb['kenaikan_gaji_yad'] : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="dalam_golongan" class="form-label">Dalam Golongan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="dalam_golongan" name="dalam_golongan" placeholder="Contoh: III/d" required value="<?php echo $is_edit ? htmlspecialchars($kgb['dalam_golongan']) : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pejabat_tanda_tangan" class="form-label">Pejabat yang Menandatangani <span class="text-danger">*</span></label>
                            <select class="form-select" id="pejabat_tanda_tangan" name="pejabat_tanda_tangan" required>
                                <option value="">-- Pilih Pejabat --</option>
                                <?php foreach ($pegawai_list as $pegawai): ?>
                                    <option value="<?php echo htmlspecialchars($pegawai['nama']); ?>" 
                                            data-jabatan="<?php echo htmlspecialchars($pegawai['jabatan'] ?? ''); ?>"
                                            <?php echo $is_edit && $kgb['pejabat_tanda_tangan'] == $pegawai['nama'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($pegawai['nama']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jabatan_pejabat" class="form-label">Jabatan Pejabat <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="jabatan_pejabat" name="jabatan_pejabat" placeholder="Otomatis terisi" readonly value="<?php echo $is_edit ? htmlspecialchars($kgb['jabatan_pejabat']) : ''; ?>">
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button type="submit" name="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Simpan
                                </button>
                                <a href="kgb.php" class="btn btn-danger">
                                    <i class="fas fa-times me-2"></i>Batal
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/script.js"></script>
<script src="assets/js/kgb.js"></script>
