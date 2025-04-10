        <footer class="copyright">
            <p>All rights reserved &copy; <span id="year"></span></p>
        </footer>
    </div>
    <script>
        document.getElementById('year').innerHTML = new Date().getFullYear()
    </script>

<!-- JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>

    <!-- bootstrap css -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <!-- vue link -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/vue@3.2.47/dist/vue.global.js"></script> -->

    <!--  custom js links  -->
    <!-- <script src="assets/js/header.js"></script> -->

    <!-- sweet alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>


    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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
                'new-results': 'results',
                'semester-results': 'results',
                'student-results': 'results',
                'course': 'course',
                'course_units': 'course',
                'generate-reports': 'reports',
                'export-results': 'reports',
                'submit-complaint': 'complaints',
                'complaint-status': 'complaints',
                'assign-seats': 'room-management',
                'view-allocations': 'room-management',
                'departments': 'room-management'
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

    <!-- alert modal messages -->
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
                    timer: 5000, //3000
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

    <!-- for notifications -->
    <script>
        function markSingleNotificationAsRead(notificationId, element) {
            event.preventDefault();
            const originalHref = element.getAttribute('href');
            
            fetch('mark_notification_read.php?id=' + notificationId)
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // Update the UI immediately
                        element.classList.remove('fw-bold');
                        
                        // Update badge count
                        const badge = document.querySelector('.notification-badge');
                        if (badge) {
                            if (data.unread_count > 0) {
                                badge.textContent = data.unread_count;
                            } else {
                                badge.remove();
                            }
                        }
                        
                        // Navigate to the page
                        window.location.href = originalHref;
                    } else {
                        console.error('Failed to mark notification as read');
                        window.location.href = originalHref;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = originalHref;
                });
        }

        // Mark all related notifications as read when landing on a page
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if(urlParams.has('mark_all_read')) {
                // Remove the parameter from URL without reload
                history.replaceState(null, null, window.location.pathname);
                
                // Update UI for all notifications on this page
                document.querySelectorAll('.bg-notification').forEach(el => {
                    el.classList.remove('bg-notification');
                    el.classList.add('bg-read');
                });
                
                // Update badge count
                const badge = document.querySelector('.notification-badge');
                if (badge) {
                    badge.remove();
                }
            }
        });
    </script>

    <!-- tables  -->

</body>
</html>