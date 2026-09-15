// Common JavaScript functions for ePegawai Application

// Auto-refresh dashboard every 30 seconds (for index.php)
function autoRefreshDashboard() {
    setInterval(function() {
        // Only refresh if page is visible
        if (!document.hidden) {
            location.reload();
        }
    }, 30000);
}

// Add smooth scrolling for anchor links
function initSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
}

// Auto-focus username field on login page
function initAutoFocusUsername() {
    document.addEventListener('DOMContentLoaded', function() {
        const usernameField = document.getElementById('username');
        if (usernameField) {
            usernameField.focus();
        }
    });
}

// Clear password field when username changes (login page)
function initClearPasswordOnUsernameChange() {
    const usernameField = document.getElementById('username');
    const passwordField = document.getElementById('password');
    
    if (usernameField && passwordField) {
        usernameField.addEventListener('input', function() {
            passwordField.value = '';
        });
    }
}

// Confirm delete function for cuti
function confirmDelete(cutiId, pegawaiName) {
    if (confirm('Apakah Anda yakin ingin menghapus pengajuan cuti "' + pegawaiName + '"?')) {
        window.location.href = 'cuti.php?action=delete&id=' + cutiId;
    }
}

// Confirm delete function for pegawai
function confirmDeletePegawai(pegawaiId, pegawaiName) {
    if (confirm('Apakah Anda yakin ingin menghapus data pegawai "' + pegawaiName + '"?')) {
        window.location.href = 'data_pegawai.php?action=delete&id=' + pegawaiId;
    }
}

// View details function for pegawai
function viewDetails(pegawaiId) {
    window.location.href = 'data_pegawai.php?action=view&id=' + pegawaiId;
}

// Confirm delete function for surat tugas
function confirmDeleteSurat(suratId, pegawaiName) {
    if (confirm('Apakah Anda yakin ingin menghapus surat tugas "' + pegawaiName + '"?')) {
        window.location.href = 'surat_tugas.php?action=delete&id=' + suratId;
    }
}

// Confirm delete function for user
function confirmDeleteUser(userId, userName) {
    if (confirm('Apakah Anda yakin ingin menghapus user "' + userName + '"?')) {
        window.location.href = 'manage_users.php?action=delete&id=' + userId;
    }
}

// Initialize common functions on page load
document.addEventListener('DOMContentLoaded', function() {
    initSmoothScrolling();
    initAutoFocusUsername();
    initClearPasswordOnUsernameChange();
    
    // Check if we're on dashboard page and auto-refresh
    if (window.location.pathname.endsWith('index.php') || window.location.pathname.endsWith('/')) {
        autoRefreshDashboard();
    }
    
    // Initialize password strength checker if exists
    if (document.getElementById('password')) {
        initPasswordStrengthChecker();
    }
    
    // Initialize user form validation if exists
    if (document.getElementById('userForm')) {
        initUserFormValidation();
    }
    
    // Initialize pegawai form validation if exists
    if (document.getElementById('pegawaiForm')) {
        initPegawaiFormValidation();
    }
    
    // Initialize cuti form if exists
    if (document.getElementById('cutiForm')) {
        initCutiForm();
    }
});

// Password strength checker
function initPasswordStrengthChecker() {
    const passwordField = document.getElementById('password');
    const strengthBar = document.getElementById('passwordStrength');
    
    if (passwordField && strengthBar) {
        passwordField.addEventListener('input', function() {
            const password = this.value;
            
            if (password.length === 0) {
                strengthBar.className = 'password-strength';
                strengthBar.style.width = '0';
                return;
            }
            
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            
            // Character type checks
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            // Update strength bar
            strengthBar.className = 'password-strength';
            
            if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
            } else if (strength <= 4) {
                strengthBar.classList.add('strength-medium');
            } else {
                strengthBar.classList.add('strength-strong');
            }
        });
    }
}

// User form validation
function initUserFormValidation() {
    const form = document.getElementById('userForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Check password match
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
                return false;
            }
            
            // Check password strength for new users
            const isNewUser = !document.getElementById('username').hasAttribute('readonly');
            if (isNewUser && password.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter!');
                return false;
            }
        });
    }
}

// Pegawai form validation
function initPegawaiFormValidation() {
    const form = document.getElementById('pegawaiForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const nip = document.getElementById('nip').value;
            const nama = document.getElementById('nama').value;
            const jenisKelamin = document.getElementById('jenis_kelamin').value;
            const statusKepegawaian = document.getElementById('status_kepegawaian').value;
            const statusPegawai = document.getElementById('status_pegawai').value;
            
            // Basic validation
            if (!nip || !nama || !jenisKelamin || !statusKepegawaian || !statusPegawai) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi!');
                return false;
            }
            
            // NIP validation (basic)
            if (nip.length < 10) {
                e.preventDefault();
                alert('NIP minimal 10 karakter!');
                return false;
            }
            
            // Phone validation (if filled)
            const noHp = document.getElementById('no_hp').value;
            if (noHp && !/^[0-9+\-\s]+$/.test(noHp)) {
                e.preventDefault();
                alert('Format nomor HP tidak valid!');
                return false;
            }
            
            // Email validation (if filled)
            const email = document.getElementById('email').value;
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                e.preventDefault();
                alert('Format email tidak valid!');
                return false;
            }
        });
        
        // Auto-format NIP
        const nipField = document.getElementById('nip');
        if (nipField) {
            nipField.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
        
        // Auto-format phone
        const noHpField = document.getElementById('no_hp');
        if (noHpField) {
            noHpField.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9+\-\s]/g, '');
            });
        }
    }
}

// Cuti form initialization
function initCutiForm() {
    const idPegawai = document.getElementById('id_pegawai');
    const pegawaiInfo = document.getElementById('pegawaiInfo');
    
    // Update pegawai info when selected
    if (idPegawai && pegawaiInfo) {
        idPegawai.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (selectedOption.value) {
                const nip = selectedOption.dataset.nip;
                const nama = selectedOption.dataset.nama;
                const alamat = selectedOption.dataset.alamat || '';
                const noHp = selectedOption.dataset.noHp || '';
                const status = selectedOption.dataset.status;
                const cutiN = selectedOption.dataset.cutiN || 12;
                const cutiNMinus1 = selectedOption.dataset.cutiNMinus1 || 0;
                const cutiNMinus2 = selectedOption.dataset.cutiNMinus2 || 0;
                
                pegawaiInfo.innerHTML = `
                    <strong>NIP:</strong> ${nip}<br>
                    <strong>Nama:</strong> ${nama}<br>
                    <strong>Status:</strong> ${status}
                `;
                
                // Auto-fill alamat_cuti and no_telepon_cuti fields
                const alamatCuti = document.getElementById('alamat_cuti');
                const noTeleponCuti = document.getElementById('no_telepon_cuti');
                
                if (alamatCuti && alamat) {
                    alamatCuti.value = alamat;
                }
                if (noTeleponCuti && noHp) {
                    noTeleponCuti.value = noHp;
                }
                
                // Update leave quota display
                const quotaN = document.getElementById('quota-n');
                const quotaNMinus1 = document.getElementById('quota-n-minus-1');
                const quotaNMinus2 = document.getElementById('quota-n-minus-2');
                const totalQuota = document.getElementById('total-quota');
                
                if (quotaN) quotaN.textContent = cutiN;
                if (quotaNMinus1) quotaNMinus1.textContent = cutiNMinus1;
                if (quotaNMinus2) quotaNMinus2.textContent = cutiNMinus2;
                if (totalQuota) totalQuota.textContent = parseInt(cutiN) + parseInt(cutiNMinus1) + parseInt(cutiNMinus2);
            } else {
                pegawaiInfo.innerHTML = '<strong>Tip:</strong> Pilih pegawai untuk melihat informasi lengkap';
                
                // Reset leave quota display
                const quotaN = document.getElementById('quota-n');
                const quotaNMinus1 = document.getElementById('quota-n-minus-1');
                const quotaNMinus2 = document.getElementById('quota-n-minus-2');
                const totalQuota = document.getElementById('total-quota');
                
                if (quotaN) quotaN.textContent = '12';
                if (quotaNMinus1) quotaNMinus1.textContent = '0';
                if (quotaNMinus2) quotaNMinus2.textContent = '0';
                if (totalQuota) totalQuota.textContent = '12';
            }
        });
        
        // Trigger change event if a pegawai is already selected (for edit mode)
        if (idPegawai.value) {
            idPegawai.dispatchEvent(new Event('change'));
        }
    }
    
    // Atasan dropdown handler
    const atasanId = document.getElementById('atasan_id');
    if (atasanId) {
        atasanId.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const preview = document.getElementById('atasanPreview');
            const namaField = document.getElementById('atasan_nama');
            const nipField = document.getElementById('atasan_nip');
            
            if (selectedOption.value && preview && namaField && nipField) {
                namaField.value = selectedOption.dataset.nama;
                nipField.value = selectedOption.dataset.nip;
                preview.innerHTML = `<i class="fas fa-check text-success"></i> ${selectedOption.dataset.nama} (${selectedOption.dataset.nip})`;
                preview.className = 'form-text text-success';
            } else if (preview && namaField && nipField) {
                namaField.value = '';
                nipField.value = '';
                preview.innerHTML = 'Pilih atasan untuk auto-fill';
                preview.className = 'form-text text-muted';
            }
        });
    }
    
    // Pejabat dropdown handler
    const pejabatId = document.getElementById('pejabat_id');
    if (pejabatId) {
        pejabatId.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const preview = document.getElementById('pejabatPreview');
            const namaField = document.getElementById('pejabat_nama');
            const nipField = document.getElementById('pejabat_nip');
            
            if (selectedOption.value && preview && namaField && nipField) {
                namaField.value = selectedOption.dataset.nama;
                nipField.value = selectedOption.dataset.nip;
                preview.innerHTML = `<i class="fas fa-check text-success"></i> ${selectedOption.dataset.nama} (${selectedOption.dataset.nip})`;
                preview.className = 'form-text text-success';
            } else if (preview && namaField && nipField) {
                namaField.value = '';
                nipField.value = '';
                preview.innerHTML = 'Pilih pejabat untuk auto-fill';
                preview.className = 'form-text text-muted';
            }
        });
    }
    
    // Toggle quota fields for Cuti Tahunan
    const jenisCuti = document.getElementById('jenis_cuti');
    const quotaFields = document.getElementById('quota-fields');
    if (jenisCuti && quotaFields) {
        jenisCuti.addEventListener('change', function() {
            const jenis = this.value;
            
            if (jenis === 'Cuti Tahunan') {
                quotaFields.style.display = 'block';
                // Set default values if empty
                const n2 = document.getElementById('cuti_n_minus_2');
                const n1 = document.getElementById('cuti_n_minus_1');
                const n = document.getElementById('cuti_n');
                if (n2 && !n2.value) n2.value = 6;
                if (n1 && !n1.value) n1.value = 6;
                if (n && !n.value) n.value = 12;
            } else {
                quotaFields.style.display = 'none';
            }
            calculateCutiDays();
        });
    }
    
    // Calculate leave duration
    const tanggalMulai = document.getElementById('tanggal_mulai');
    const tanggalSelesai = document.getElementById('tanggal_selesai');
    
    if (tanggalMulai && tanggalSelesai) {
        tanggalMulai.addEventListener('change', calculateCutiDays);
        tanggalSelesai.addEventListener('change', calculateCutiDays);
        
        // Recalculate when jenis_cuti changes
        if (jenisCuti) {
            jenisCuti.addEventListener('change', calculateCutiDays);
        }
        
        // Update quota total when quota fields change
        ['cuti_n_minus_2', 'cuti_n_minus_1', 'cuti_n'].forEach(function(id) {
            const field = document.getElementById(id);
            if (field) field.addEventListener('input', calculateCutiDays);
        });
    }
    
    // Form validation
    const cutiForm = document.getElementById('cutiForm');
    if (cutiForm) {
        cutiForm.addEventListener('submit', function(e) {
            const idPegawaiVal = document.getElementById('id_pegawai').value;
            const jenisCutiVal = document.getElementById('jenis_cuti').value;
            const tanggalMulaiVal = document.getElementById('tanggal_mulai').value;
            const tanggalSelesaiVal = document.getElementById('tanggal_selesai').value;
            const alasanCuti = document.getElementById('alasan_cuti').value;
            
            // Basic validation
            if (!idPegawaiVal || !jenisCutiVal || !tanggalMulaiVal || !tanggalSelesaiVal || !alasanCuti) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi!');
                return false;
            }
            
            // Date validation
            const start = new Date(tanggalMulaiVal);
            const end = new Date(tanggalSelesaiVal);
            
            if (end < start) {
                e.preventDefault();
                alert('Tanggal selesai tidak boleh kurang dari tanggal mulai!');
                return false;
            }
            
            // Calculate days
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            
            // Validate quota for Cuti Tahunan
            if (jenisCutiVal === 'Cuti Tahunan') {
                const n2 = parseInt(document.getElementById('quota-n-minus-2')?.textContent) || 0;
                const n1 = parseInt(document.getElementById('quota-n-minus-1')?.textContent) || 0;
                const n = parseInt(document.getElementById('quota-n')?.textContent) || 12;
                const totalQuota = n2 + n1 + n;

                // Get the displayed working days from lama_cuti element
                const lamaCutiText = document.getElementById('lama_cuti')?.textContent || '';
                const workingDaysMatch = lamaCutiText.match(/(\d+)/);
                const workingDays = workingDaysMatch ? parseInt(workingDaysMatch[1]) : diffDays;

                if (workingDays > totalQuota) {
                    e.preventDefault();
                    alert(`Lama cuti (${workingDays} hari kerja) melebihi total jatah (${totalQuota} hari)! Silakan sesuaikan.`);
                    return false;
                }
            }

            const displayedDaysText = document.getElementById('lama_cuti')?.textContent || '';
            const displayedDaysMatch = displayedDaysText.match(/(\d+)/);
            const displayedWorkingDays = displayedDaysMatch ? parseInt(displayedDaysMatch[1]) : diffDays;
            
            if (jenisCutiVal === 'Cuti Melahirkan' && displayedWorkingDays > 90) {
                e.preventDefault();
                if (!confirm('Cuti melahirkan melebihi 90 hari kerja. Apakah Anda yakin ingin melanjutkan?')) {
                    return false;
                }
            }
        });
    }
}

// Global variable to store holidays
let holidayDates = [];

// Fetch holidays from database
async function fetchHolidaysFromDB(year) {
    try {
        const response = await fetch(`holidays.php?api=holidays&year=${year}`);
        holidayDates = await response.json();
        console.log('Holidays loaded for year', year, ':', holidayDates);
    } catch (error) {
        console.error('Error fetching holidays:', error);
        holidayDates = [];
    }
}

// Calculate leave days function
async function calculateCutiDays() {
    const startDate = document.getElementById('tanggal_mulai');
    const endDate = document.getElementById('tanggal_selesai');
    const lamaCutiElement = document.getElementById('lama_cuti');
    const quotaTotalElement = document.getElementById('quota-total');
    const jenisCuti = document.getElementById('jenis_cuti');

    if (!startDate || !endDate || !lamaCutiElement) return;

    const startVal = startDate.value;
    const endVal = endDate.value;

    if (startVal && endVal) {
        const start = new Date(startVal);
        const end = new Date(endVal);

        if (end >= start) {
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

            // Cuti Melahirkan counts all days including weekends and holidays
            if (jenisCuti && jenisCuti.value === 'Cuti Melahirkan') {
                lamaCutiElement.innerHTML = `<strong>${diffDays}</strong> hari`;
            } else {
                // For other leave types (especially Cuti Tahunan), count only working days
                const year = start.getFullYear();
                await fetchHolidaysFromDB(year);

                let workingDays = 0;
                for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                    const dayOfWeek = d.getDay();
                    const dateStr = d.toISOString().split('T')[0]; // Format: YYYY-MM-DD

                    // 0 = Sunday, 6 = Saturday
                    const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
                    const isHoliday = holidayDates.includes(dateStr);

                    if (!isWeekend && !isHoliday) {
                        workingDays++;
                    }
                }

                lamaCutiElement.innerHTML = `<strong>${workingDays}</strong> hari kerja (total ${diffDays} hari)`;
            }

            // Check quota if Cuti Tahunan
            if (quotaTotalElement && jenisCuti && jenisCuti.value === 'Cuti Tahunan') {
                // Get quota from displayed spans (updated when pegawai is selected)
                const n2 = parseInt(document.getElementById('quota-n-minus-2')?.textContent) || 0;
                const n1 = parseInt(document.getElementById('quota-n-minus-1')?.textContent) || 0;
                const n = parseInt(document.getElementById('quota-n')?.textContent) || 12;
                const totalQuota = n2 + n1 + n;
                quotaTotalElement.textContent = totalQuota + ' hari';

                // Remove existing warning
                const existingWarning = document.getElementById('quota-warning');
                if (existingWarning) {
                    existingWarning.remove();
                }

                if (workingDays > totalQuota) {
                    lamaCutiElement.parentElement.classList.add('text-danger');
                    quotaTotalElement.parentElement.classList.add('text-danger');
                    
                    // Add warning message
                    const warningDiv = document.createElement('div');
                    warningDiv.id = 'quota-warning';
                    warningDiv.className = 'alert alert-warning mt-2';
                    warningDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i><strong>Peringatan:</strong> Jumlah hari cuti (' + workingDays + ' hari) melebihi sisa kuota cuti (' + totalQuota + ' hari)!';
                    lamaCutiElement.parentElement.appendChild(warningDiv);
                } else {
                    lamaCutiElement.parentElement.classList.remove('text-danger');
                    quotaTotalElement.parentElement.classList.remove('text-danger');
                }
            } else if (quotaTotalElement) {
                quotaTotalElement.textContent = '-';
                // Remove warning if exists
                const existingWarning = document.getElementById('quota-warning');
                if (existingWarning) {
                    existingWarning.remove();
                }
            }
        } else {
            lamaCutiElement.textContent = 'Tanggal tidak valid';
        }
    } else {
        lamaCutiElement.textContent = '0';
        if (quotaTotalElement) quotaTotalElement.textContent = '-';
    }
}
