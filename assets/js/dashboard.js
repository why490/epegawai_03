// Dashboard Charts
document.addEventListener('DOMContentLoaded', function() {
    // Cuti Trend Chart (Line Chart)
    const cutiTrendCtx = document.getElementById('cutiTrendChart');
    if (cutiTrendCtx) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const cutiMonthJenisRaw = cutiTrendCtx.dataset.cutiMonthJenis;
        
        const totalCuti = new Array(12).fill(0);
        const jenisCutiData = {};
        
        if (cutiMonthJenisRaw) {
            const data = JSON.parse(cutiMonthJenisRaw);
            data.forEach(item => {
                totalCuti[item.month - 1] += item.count;
                
                if (!jenisCutiData[item.jenis_cuti]) {
                    jenisCutiData[item.jenis_cuti] = new Array(12).fill(0);
                }
                jenisCutiData[item.jenis_cuti][item.month - 1] = item.count;
            });
        }
        
        // Modern color palette with gradient-like colors
        const colorPalette = [
            { border: '#10b981', bg: 'rgba(16, 185, 129, 0.15)' },  // Emerald
            { border: '#f59e0b', bg: 'rgba(245, 158, 11, 0.15)' },  // Amber
            { border: '#ef4444', bg: 'rgba(239, 68, 68, 0.15)' },  // Red
            { border: '#06b6d4', bg: 'rgba(6, 182, 212, 0.15)' },  // Cyan
            { border: '#8b5cf6', bg: 'rgba(139, 92, 246, 0.15)' }   // Violet
        ];
        
        const datasets = [{
            label: 'Total Cuti',
            data: totalCuti,
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#6366f1',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6
        }];
        
        let colorIndex = 0;
        Object.keys(jenisCutiData).forEach(jenis => {
            if (colorIndex < colorPalette.length) {
                datasets.push({
                    label: jenis,
                    data: jenisCutiData[jenis],
                    borderColor: colorPalette[colorIndex].border,
                    backgroundColor: colorPalette[colorIndex].bg,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: colorPalette[colorIndex].border,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                });
                colorIndex++;
            }
        });
        
        new Chart(cutiTrendCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12,
                                weight: '500'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 12
                        },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: true
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                }
            }
        });
    }

    // Pegawai per Jabatan Chart
    const pegawaiJabatanCtx = document.getElementById('pegawaiJabatanChart');
    if (pegawaiJabatanCtx) {
        const jabatanLabelsRaw = pegawaiJabatanCtx.dataset.jabatanLabels;
        const jabatanCountsRaw = pegawaiJabatanCtx.dataset.jabatanCounts;
        
        const jabatanLabels = jabatanLabelsRaw ? JSON.parse(jabatanLabelsRaw) : [];
        const jabatanCounts = jabatanCountsRaw ? JSON.parse(jabatanCountsRaw) : [];
        
        // Elegant modern color palette
        const colors = [
            { bg: 'rgba(59, 130, 246, 0.8)', border: 'rgba(59, 130, 246, 1)' },
            { bg: 'rgba(99, 102, 241, 0.8)', border: 'rgba(99, 102, 241, 1)' },
            { bg: 'rgba(139, 92, 246, 0.8)', border: 'rgba(139, 92, 246, 1)' },
            { bg: 'rgba(236, 72, 153, 0.8)', border: 'rgba(236, 72, 153, 1)' },
            { bg: 'rgba(239, 68, 68, 0.8)', border: 'rgba(239, 68, 68, 1)' },
            { bg: 'rgba(249, 115, 22, 0.8)', border: 'rgba(249, 115, 22, 1)' },
            { bg: 'rgba(245, 158, 11, 0.8)', border: 'rgba(245, 158, 11, 1)' },
            { bg: 'rgba(16, 185, 129, 0.8)', border: 'rgba(16, 185, 129, 1)' },
            { bg: 'rgba(6, 182, 212, 0.8)', border: 'rgba(6, 182, 212, 1)' },
            { bg: 'rgba(14, 165, 233, 0.8)', border: 'rgba(14, 165, 233, 1)' }
        ];
        
        const backgroundColors = jabatanLabels.map((_, i) => colors[i % colors.length].bg);
        const borderColors = jabatanLabels.map((_, i) => colors[i % colors.length].border);
        
        new Chart(pegawaiJabatanCtx, {
            type: 'bar',
            data: {
                labels: jabatanLabels,
                datasets: [{
                    label: 'Jumlah Pegawai',
                    data: jabatanCounts,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 2,
                    borderRadius: 4,
                    barPercentage: 0.7
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        cornerRadius: 8,
                        padding: 12,
                        titleFont: {
                            size: 13,
                            weight: '600'
                        },
                        bodyFont: {
                            size: 12
                        },
                        callbacks: {
                            label: function(context) {
                                return context.parsed.x + ' pegawai';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.06)',
                            drawBorder: false
                        }
                    },
                    y: {
                        ticks: {
                            font: {
                                size: 12,
                                weight: '500'
                            }
                        },
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    }
                }
            }
        });
    }
});
