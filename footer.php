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

            // Toggle the current submenu
            submenu.classList.toggle('show');
            chevron.classList.toggle('rotated');

            // Close other submenus
            document.querySelectorAll('.submenu').forEach(menu => {
            if (menu.id !== `${id}-submenu` && menu.classList.contains('show')) {
                    menu.classList.remove('show');
                    const otherChevron = document.getElementById(menu.id.replace('-submenu', '-chevron'));
                    if (otherChevron) otherChevron.classList.remove('rotated');
                }
            });
        }

        // Initialize submenus based on current page
        document.addEventListener('DOMContentLoaded', function() {
            // Get current page from URL
            const urlParams = new URLSearchParams(window.location.search);
            const currentPage = urlParams.get('page');
            
            // Open relevant submenu if needed
            if (currentPage) {
                const parentItems = {
                    'view-results': 'results',
                    'semester-results': 'results',
                    'generate-reports': 'reports',
                    'export-results': 'reports',
                    'submit-complaint': 'complaints',
                    'complaint-status': 'complaints',
                    'assign-seats': 'room-management',
                    'view-allocations': 'room-management'
                };
                
                if (parentItems[currentPage]) {
                    const submenu = document.getElementById(`${parentItems[currentPage]}-submenu`);
                    const chevron = document.getElementById(`${parentItems[currentPage]}-chevron`);
                    if (submenu && chevron) {
                        submenu.classList.add('show');
                        chevron.classList.add('rotated');
                    }
                }
            }
        });
    </script>
</body>
</html>