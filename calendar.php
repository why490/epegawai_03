<?php
require_once 'config.php';
require_login();

// Get all cuti data for calendar
try {
    $stmt = $pdo->prepare("
        SELECT c.*, p.nama as nama_pegawai 
        FROM cuti c 
        JOIN pegawai p ON c.id_pegawai = p.id 
        WHERE c.status_cuti = 'Disetujui'
        ORDER BY c.tanggal_mulai DESC
    ");
    $stmt->execute();
    $cuti_data = $stmt->fetchAll();
} catch (PDOException $e) {
    $cuti_data = [];
}

// Get holidays
try {
    $stmt = $pdo->prepare("SELECT tanggal FROM holidays WHERE YEAR(tanggal) = YEAR(CURDATE())");
    $stmt->execute();
    $holidays = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $holidays = [];
}

// Function to check if date is holiday or weekend
function isHolidayOrWeekend($date, $holidays) {
    $dayOfWeek = date('w', strtotime($date));
    if ($dayOfWeek == 0 || $dayOfWeek == 6) return true; // Sunday or Saturday
    if (in_array($date, $holidays)) return true;
    return false;
}

// Generate individual events per day
$calendar_events = [];
foreach ($cuti_data as $cuti) {
    $current = strtotime($cuti['tanggal_mulai']);
    $end = strtotime($cuti['tanggal_selesai']);
    
    while ($current <= $end) {
        $date_str = date('Y-m-d', $current);
        if (!isHolidayOrWeekend($date_str, $holidays)) {
            $calendar_events[] = [
                'title' => htmlspecialchars($cuti['nama_pegawai']),
                'start' => $date_str,
                'allDay' => true,
                'backgroundColor' => $cuti['jenis_cuti'] == 'Cuti Tahunan' ? 'rgba(102, 126, 234, 0.85)' : ($cuti['jenis_cuti'] == 'Cuti Sakit' ? 'rgba(239, 68, 68, 0.85)' : ($cuti['jenis_cuti'] == 'Cuti Melahirkan' ? 'rgba(236, 72, 153, 0.85)' : 'rgba(16, 185, 129, 0.85)')),
                'borderColor' => $cuti['jenis_cuti'] == 'Cuti Tahunan' ? 'rgba(102, 126, 234, 1)' : ($cuti['jenis_cuti'] == 'Cuti Sakit' ? 'rgba(239, 68, 68, 1)' : ($cuti['jenis_cuti'] == 'Cuti Melahirkan' ? 'rgba(236, 72, 153, 1)' : 'rgba(16, 185, 129, 1)')),
                'url' => 'cuti.php?action=view&id=' . $cuti['id']
            ];
        }
        $current = strtotime('+1 day', $current);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender Cuti - <?php echo APP_NAME; ?></title>
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
            background: white !important;
            border: 2px solid #667eea !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
            padding: 8px 20px !important;
            color: #667eea !important;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2) !important;
            transition: all 0.3s ease !important;
        }
        .fc .fc-button-primary:hover,
        .fc-button-primary:hover {
            background: #667eea !important;
            color: white !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4) !important;
        }
        .fc .fc-button-primary.fc-button-active,
        .fc-button-primary.fc-button-active {
            background: #667eea !important;
            color: white !important;
            border-color: #667eea !important;
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4) !important;
        }
        .fc .fc-button-primary:not(:disabled):active,
        .fc-button-primary:not(:disabled):active {
            transform: translateY(0) !important;
            box-shadow: 0 2px 10px rgba(102, 126, 234, 0.3) !important;
        }
        .fc .fc-button-primary:disabled,
        .fc-button-primary:disabled {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
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
                            <i class="fas fa-calendar-alt me-2"></i>Kalender Cuti
                        </h1>
                        <p class="mb-0">Jadwal cuti pegawai yang disetujui</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <small class="text-white">
                            <i class="fas fa-clock me-1"></i><?php echo date('d/m/Y H:i'); ?>
                        </small>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        
        const events = [
            <?php foreach ($calendar_events as $event): ?>
            {
                title: '<?php echo $event['title']; ?>',
                start: '<?php echo $event['start']; ?>',
                allDay: true,
                backgroundColor: '<?php echo $event['backgroundColor']; ?>',
                borderColor: '<?php echo $event['borderColor']; ?>',
                url: '<?php echo $event['url']; ?>'
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
                if (info.event.url) {
                    window.open(info.event.url);
                }
            },
            height: 'auto'
        });

        calendar.render();
    });
    </script>
</body>
</html>
