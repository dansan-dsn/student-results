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
        const toggleSubmenu = (menu) =>{

        const submenu = document.getElementById(menu + '-submenu');
        const chevron = document.getElementById(menu + '-chevron');
        
            if (submenu.style.display === 'none') {
                submenu.style.display = 'block';
                chevron.classList.add('bx-chevron-up');
                chevron.classList.remove('bx-chevron-down');
            } else {
                submenu.style.display = 'none';
                chevron.classList.add('bx-chevron-down');
                chevron.classList.remove('bx-chevron-up');
            }
        }
    </script>
</body>
</html>