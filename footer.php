        <footer class="copyright">
            <p>All rights reserved &copy; <span id="year"></span></p>
        </footer>
    </div>
    <script>
        document.getElementById('year').innerHTML = new Date().getFullYear()
    </script>
    <!--  custom js links  -->
    <script src="assets/js/header.js"></script>

    <!-- sweet alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

    <!-- aside menu and submenu -->
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
            'course.php': 'course',
            'course_units.php': 'course',
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

<script>
    
    // success and error alert
    document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const message = urlParams.get('message');

    if (status && message) {
        const iconColor = status === "success" ? "#3e8e41" : "#ff0000";
        Swal.fire({
            position: 'top-end',
            icon: status === 'success' ? 'success' : 'error',
            title: status === 'success' ? 'Success!' : 'Error!',
            text: message,
            showConfirmButton: false,
            timer: 3000,
            toast: true, // Enable toast mode
            customClass: {
                popup: 'small-toast' // Custom class for the toast
            },
            background: '#22252a',
            color: '#fff', 
            iconColor: iconColor // Set icon color based on success or error,
        }).then(() => {
            const cleanUrl = window.location.origin + window.location.pathname;
            window.history.replaceState(null, null, cleanUrl);
        });
    }
});

</script>

</body>
</html>