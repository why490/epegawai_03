// Live search with debounce
let searchTimeout;

function initLiveSearch() {
    const searchInput = document.querySelector('input[name="search"]');
    
    // Refocus search input after page load and move cursor to end
    if (searchInput && searchInput.value) {
        searchInput.focus();
        searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
    }
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                const form = document.querySelector('.search-box form');
                if (form) {
                    form.submit();
                }
            }, 300); // 300ms delay
        });
    }
}

function initLiveSearchWithFilters(selectors) {
    const searchInput = document.querySelector('input[name="search"]');
    
    // Refocus search input after page load and move cursor to end
    if (searchInput && searchInput.value) {
        searchInput.focus();
        searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
    }
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                const form = document.querySelector('.search-box form');
                if (form) {
                    form.submit();
                }
            }, 300); // 300ms delay
        });
    }
    
    // Auto-submit on filter change
    selectors.forEach(selector => {
        const selectElement = document.querySelector(selector);
        if (selectElement) {
            selectElement.addEventListener('change', function() {
                const form = document.querySelector('.search-box form');
                if (form) {
                    form.submit();
                }
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Check which page we're on and initialize accordingly
    const path = window.location.pathname;
    
    if (path.includes('data_pegawai.php')) {
        initLiveSearchWithFilters([
            'select[name="kepegawaian"]',
            'select[name="status"]',
            'select[name="golongan"]'
        ]);
    } else if (path.includes('cuti.php')) {
        initLiveSearchWithFilters([
            'select[name="status"]',
            'select[name="jenis"]'
        ]);
    } else if (path.includes('surat_tugas.php')) {
        initLiveSearchWithFilters([
            'select[name="status"]'
        ]);
    } else if (path.includes('holidays.php')) {
        initLiveSearchWithFilters([
            'select[name="jenis"]'
        ]);
    } else if (path.includes('kgb.php')) {
        initLiveSearch();
    }
});
