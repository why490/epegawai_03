<?php
require_once 'config.php';
require_login();

// Get all holidays data
try {
    $stmt = $pdo->prepare("SELECT * FROM holidays WHERE YEAR(tanggal) = YEAR(CURDATE()) ORDER BY tanggal ASC");
    $stmt->execute();
    $holidays_data = $stmt->fetchAll();
} catch (PDOException $e) {
    $holidays_data = [];
}

// Generate individual events per day for holidays
$calendar_events = [];
foreach ($holidays_data as $holiday) {
    $title = isset($holiday['nama_libur']) ? htmlspecialchars($holiday['nama_libur']) : htmlspecialchars($holiday['keterangan']);
    $jenis = isset($holiday['jenis_libur']) ? htmlspecialchars($holiday['jenis_libur']) : '';
    $keterangan = isset($holiday['keterangan']) ? htmlspecialchars($holiday['keterangan']) : '';
    
    $calendar_events[] = [
        'id' => $holiday['id'],
        'title' => $title,
        'start' => $holiday['tanggal'],
        'description' => $keterangan,
        'jenis' => $jenis,
        'allDay' => true,
        'backgroundColor' => 'rgba(239, 68, 68, 0.85)',
        'borderColor' => 'rgba(239, 68, 68, 1)'
    ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender Hari Libur - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- FullCalendar CSS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <style>
        #calendar {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .fc {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .fc-toolbar-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #667eea;
        }
        .fc .fc-button-primary,
        .fc-button-primary {
            background: white;
            border: 2px solid #667eea;
            border-radius: 10px;
            font-weight: 600;
            padding: 8px 20px;
            color: #667eea;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
            transition: all 0.3s ease;
        }
        .fc .fc-button-primary:hover,
        .fc-button-primary:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        .fc .fc-button-primary.fc-button-active,
        .fc-button-primary.fc-button-active {
            background: #667eea;
            color: white;
            border-color: #667eea;
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        .fc .fc-button-primary:not(:disabled):active,
        .fc-button-primary:not(:disabled):active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(102, 126, 234, 0.3);
        }
        .fc .fc-button-primary:disabled,
        .fc-button-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .fc-daygrid-day-number {
            font-weight: 600;
            color: #667eea;
        }
        .fc-col-header-cell {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            font-weight: 600;
            color: #764ba2;
            padding: 10px;
        }
        .fc-daygrid-day {
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }
        .fc-daygrid-day:hover {
            background: rgba(102, 126, 234, 0.05);
        }
        .fc-day-today {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%) !important;
        }
        .fc-event {
            border: none;
            border-radius: 6px;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .fc-event:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h1 class="mb-0">
                            <i class="fas fa-calendar-day me-2"></i>Kalender Hari Libur
                        </h1>
                        <p class="mb-0">Jadwal hari libur nasional dan daerah</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#holidayModal" onclick="resetHolidayForm()">
                            <i class="fas fa-plus me-2"></i>Tambah Hari Libur
                        </button>
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

            <!-- Calendar -->
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Holiday Add/Edit Modal -->
    <div class="modal fade" id="holidayModal" tabindex="-1" aria-labelledby="holidayModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15); border-radius: 16px;">
                <div class="modal-header" style="border: none; padding: 24px 24px 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px 16px 0 0;">
                    <h5 class="modal-title" id="holidayModalLabel" style="color: white; font-weight: 600; margin: 0;">
                        <i class="fas fa-calendar-plus me-2"></i>Tambah Hari Libur
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                </div>
                <div class="modal-body" style="padding: 20px;">
                    <form id="holidayForm">
                        <input type="hidden" id="holiday_id" name="id">
                        <div class="mb-3">
                            <label for="modal_tanggal" class="form-label" style="font-weight: 500; font-size: 0.9rem; color: #495057;">
                                <i class="fas fa-calendar me-2 text-primary"></i>Tanggal
                            </label>
                            <input type="date" class="form-control" id="modal_tanggal" name="tanggal" required
                                   style="border-radius: 8px; border: 2px solid #e9ecef; padding: 6px 12px; font-size: 0.9rem;">
                        </div>
                        <div class="mb-3">
                            <label for="modal_nama_libur" class="form-label" style="font-weight: 500; font-size: 0.9rem; color: #495057;">
                                <i class="fas fa-tag me-2 text-primary"></i>Nama Libur
                            </label>
                            <input type="text" class="form-control" id="modal_nama_libur" name="nama_libur" required placeholder="Contoh: Hari Kemerdekaan"
                                   style="border-radius: 8px; border: 2px solid #e9ecef; padding: 6px 12px; font-size: 0.9rem;">
                        </div>
                        <div class="mb-3">
                            <label for="modal_jenis_libur" class="form-label" style="font-weight: 500; font-size: 0.9rem; color: #495057;">
                                <i class="fas fa-layer-group me-2 text-primary"></i>Jenis Libur
                            </label>
                            <select class="form-select" id="modal_jenis_libur" name="jenis_libur" required
                                    style="border-radius: 8px; border: 2px solid #e9ecef; padding: 6px 12px; font-size: 0.9rem;">
                                <option value="">Pilih Jenis Libur</option>
                                <option value="Nasional">Nasional</option>
                                <option value="Daerah">Daerah</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="modal_tahun" class="form-label" style="font-weight: 500; font-size: 0.9rem; color: #495057;">
                                <i class="fas fa-clock me-2 text-primary"></i>Tahun
                            </label>
                            <input type="number" class="form-control" id="modal_tahun" name="tahun" required
                                   style="border-radius: 8px; border: 2px solid #e9ecef; padding: 6px 12px; font-size: 0.9rem;">
                        </div>
                        <div class="mb-3">
                            <label for="modal_keterangan" class="form-label" style="font-weight: 500; font-size: 0.9rem; color: #495057;">
                                <i class="fas fa-info-circle me-2 text-primary"></i>Keterangan
                            </label>
                            <textarea class="form-control" id="modal_keterangan" name="keterangan" rows="2" placeholder="Opsional..."
                                      style="border-radius: 8px; border: 2px solid #e9ecef; padding: 6px 12px; font-size: 0.9rem; resize: none;"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="border: none; padding: 0 20px 20px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            style="border-radius: 8px; padding: 6px 16px; font-weight: 500; font-size: 0.9rem;">
                        <i class="fas fa-times me-2"></i>Batal
                    </button>
                    <button type="button" class="btn btn-primary" onclick="saveHoliday()"
                            style="border-radius: 8px; padding: 6px 16px; font-weight: 500; font-size: 0.9rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
    #holidayModal .form-control:focus, #holidayModal .form-select:focus {
        border-color: #667eea !important;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15) !important;
    }
    #holidayModal .form-control, #holidayModal .form-select {
        transition: all 0.3s ease;
    }
    #holidayModal .form-control:hover, #holidayModal .form-select:hover {
        border-color: #667eea;
    }
    </style>

    <!-- Holiday Detail Modal -->
    <div class="modal fade" id="holidayDetailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-day me-2"></i>Detail Hari Libur
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Hari Libur:</label>
                        <p id="holidayName" class="form-control-plaintext"></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jenis:</label>
                        <p id="holidayJenis" class="form-control-plaintext"></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal:</label>
                        <p id="holidayDate" class="form-control-plaintext"></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan:</label>
                        <p id="holidayDescription" class="form-control-plaintext"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <a href="holidays.php" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Kelola Hari Libur
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
    function resetHolidayForm() {
        document.getElementById('holidayForm').reset();
        document.getElementById('holiday_id').value = '';
        document.getElementById('modal_tahun').value = new Date().getFullYear();
        document.getElementById('holidayModalLabel').textContent = 'Tambah Hari Libur';
    }

    function saveHoliday() {
        const form = document.getElementById('holidayForm');
        const id = document.getElementById('holiday_id').value;
        const formData = new FormData(form);
        const url = id ? 'holidays.php?action=edit&id=' + id : 'holidays.php?action=add';

        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('holidayModal')).hide();
                window.location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            alert('Gagal menyimpan data: ' + error);
        });
    }
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        
        const events = [
            <?php foreach ($calendar_events as $event): ?>
            {
                id: '<?php echo $event['id']; ?>',
                title: '<?php echo $event['title']; ?>',
                start: '<?php echo $event['start']; ?>',
                description: '<?php echo $event['description']; ?>',
                jenis: '<?php echo $event['jenis']; ?>',
                allDay: true,
                backgroundColor: '<?php echo $event['backgroundColor']; ?>',
                borderColor: '<?php echo $event['borderColor']; ?>'
            },
            <?php endforeach; ?>
        ];

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                week: 'Minggu',
                day: 'Hari'
            },
            events: events,
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                const event = info.event;
                
                // Set modal content
                document.getElementById('holidayName').textContent = event.title;
                document.getElementById('holidayJenis').textContent = event.extendedProps.jenis || '-';
                document.getElementById('holidayDate').textContent = new Date(event.start).toLocaleDateString('id-ID', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                document.getElementById('holidayDescription').textContent = event.extendedProps.description || '-';
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('holidayDetailModal'));
                modal.show();
            },
            height: 'auto'
        });

        calendar.render();
    });
    </script>
</body>
</html>
