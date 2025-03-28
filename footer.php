        <footer class="copyright">
            <p>All rights reserved &copy; <span id="year"></span></p>
        </footer>
    </div>
    <script>
        document.getElementById('year').innerHTML = new Date().getFullYear()
    </script>
    <!--  custom js links  -->
    <script src="assets/js/header.js"></script>

    <script>
    function toggleSubmenu(id) {
        const submenu = document.getElementById(`${id}-submenu`);
        const chevron = document.getElementById(`${id}-chevron`);
        
        submenu.classList.toggle('show');
        chevron.classList.toggle('rotated');
        
        // Close other submenus
        document.querySelectorAll('.submenu').forEach(menu => {
            if (menu.id !== `${id}-submenu` && menu.classList.contains('show')) {
                menu.classList.remove('show');
                document.getElementById(menu.id.replace('-submenu', '-chevron'))?.classList.remove('rotated');
            }
        });
    }

    // Initialize submenus on page load
    document.addEventListener('DOMContentLoaded', function() {
        const currentPage = basename(window.location.pathname);
        const parentMenus = {
            'view-results.php': 'results',
            'semester-results.php': 'results',
            'generate-reports.php': 'reports',
            'export-results.php': 'reports',
            'submit-complaint.php': 'complaints',
            'complaint-status.php': 'complaints',
            'assign-seats.php': 'room-management',
            'view-allocations.php': 'room-management',
            'departments.php': 'room-management'
        };

        if (parentMenus[currentPage]) {
            const submenu = document.getElementById(`${parentMenus[currentPage]}-submenu`);
            const chevron = document.getElementById(`${parentMenus[currentPage]}-chevron`);
            if (submenu && chevron) {
                submenu.classList.add('show');
                chevron.classList.add('rotated');
            }
        }
    });

// Helper function to get base filename
function basename(path) {
    return path.split('/').pop().split('?')[0];
}

if ( window.history.replaceState ) {
  window.history.replaceState( null, null, window.location.href );
}
</script>
</body>
</html>