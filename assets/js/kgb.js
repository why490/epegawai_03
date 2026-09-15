// Format Rupiah untuk Gaji Pokok Lama
var gaji_pokok_lama = document.getElementById('gaji_pokok_lama');
if(gaji_pokok_lama) {
    gaji_pokok_lama.addEventListener('keyup', function(e){
        gaji_pokok_lama.value = formatRupiah(this.value, 'Rp ');
    });
}

// Format Rupiah untuk Gaji Pokok Baru
var gaji_pokok_baru = document.getElementById('gaji_pokok_baru');
if(gaji_pokok_baru) {
    gaji_pokok_baru.addEventListener('keyup', function(e){
        gaji_pokok_baru.value = formatRupiah(this.value, 'Rp ');
    });
}

// Auto-calculate Kenaikan Gaji Yang Akan Datang (2 years after mulai tanggal)
var mulai_tanggal = document.getElementById('mulai_tanggal');
var kenaikan_gaji_yad = document.getElementById('kenaikan_gaji_yad');
if(mulai_tanggal && kenaikan_gaji_yad) {
    mulai_tanggal.addEventListener('change', function(e){
        if(this.value) {
            var date = new Date(this.value);
            date.setFullYear(date.getFullYear() + 2);
            var year = date.getFullYear();
            var month = String(date.getMonth() + 1).padStart(2, '0');
            var day = String(date.getDate()).padStart(2, '0');
            kenaikan_gaji_yad.value = year + '-' + month + '-' + day;
        } else {
            kenaikan_gaji_yad.value = '';
        }
    });
}

// Auto-fill NIP, Jabatan, and Golongan when Pegawai is selected
var id_pegawai = document.getElementById('id_pegawai');
var nip = document.getElementById('nip');
var jabatan = document.getElementById('jabatan');
var golongan_saat_ini = document.getElementById('golongan_saat_ini');
if(id_pegawai && nip && jabatan && golongan_saat_ini) {
    id_pegawai.addEventListener('change', function(e){
        var selectedOption = this.options[this.selectedIndex];
        nip.value = selectedOption.getAttribute('data-nip') || '';
        jabatan.value = selectedOption.getAttribute('data-jabatan') || '';
        golongan_saat_ini.value = selectedOption.getAttribute('data-golongan') || '';
        
        // Fetch last KGB data for this pegawai
        var pegawaiId = this.value;
        if(pegawaiId) {
            console.log('Fetching KGB data for pegawai ID:', pegawaiId);
            fetch('get_last_kgb.php?id_pegawai=' + pegawaiId)
                .then(response => {
                    console.log('Fetch response:', response);
                    return response.json();
                })
                .then(data => {
                    console.log('KGB data received:', data);
                    if(data.success) {
                        // Auto-fill Dasar SK Terakhir fields
                        document.getElementById('gaji_pokok_lama').value = data.data.gaji_pokok_lama || '';
                        document.getElementById('oleh_pejabat').value = data.data.oleh_pejabat || '';
                        document.getElementById('nomor_surat_sk').value = data.data.nomor_surat_sk || '';
                        document.getElementById('tanggal_surat_sk').value = data.data.tanggal_surat_sk || '';
                        document.getElementById('tanggal_mulai_berlaku').value = data.data.tanggal_mulai_berlaku || '';
                        document.getElementById('masa_kerja_golongan').value = data.data.masa_kerja_golongan || '';
                        console.log('Fields filled successfully');
                    } else {
                        console.log('No previous KGB data found');
                    }
                })
                .catch(error => {
                    console.error('Error fetching last KGB data:', error);
                });
        }
    });
}

// Auto-fill Jabatan Pejabat when Pejabat is selected
var pejabat_tanda_tangan = document.getElementById('pejabat_tanda_tangan');
var jabatan_pejabat = document.getElementById('jabatan_pejabat');
if(pejabat_tanda_tangan && jabatan_pejabat) {
    pejabat_tanda_tangan.addEventListener('change', function(e){
        var selectedOption = this.options[this.selectedIndex];
        jabatan_pejabat.value = selectedOption.getAttribute('data-jabatan') || '';
    });
}

/* Fungsi formatRupiah */
function formatRupiah(angka, prefix){
    var number_string = angka.replace(/[^,\d]/g, '').toString(),
    split   		= number_string.split(','),
    sisa     		= split[0].length % 3,
    rupiah     		= split[0].substr(0, sisa),
    ribuan     		= split[0].substr(sisa).match(/\d{3}/gi);

    // tambahkan titik jika yang di input sudah menjadi angka ribuan
    if(ribuan){
        var separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    return prefix == undefined ? rupiah : (rupiah ? 'Rp ' + rupiah : '');
}
