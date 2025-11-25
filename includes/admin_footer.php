        </main>
    </div> <!-- Close main-content div -->

    <footer class="bg-dark text-white py-6 mt-8">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; 2025 Doktera Admin Panel. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const navbarMenu = document.querySelector('.navbar-menu');

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function() {
                navbarMenu.classList.toggle('show');
            });
        }

        // Handle dropdown for health assessments
        const dropdownToggles = document.querySelectorAll('.dropdown > a');
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const dropdownMenu = this.nextElementSibling;
                const allDropdownMenus = document.querySelectorAll('.dropdown-menu');

                // Close all other dropdowns
                allDropdownMenus.forEach(menu => {
                    if (menu !== dropdownMenu) {
                        menu.style.display = 'none';
                    }
                });

                // Toggle current dropdown
                if (window.innerWidth < 1024) {
                    // On mobile, toggle the dropdown
                    const isOpen = dropdownMenu.style.display === 'block' || dropdownMenu.classList.contains('show');
                    dropdownMenu.style.display = isOpen ? 'none' : 'block';
                } else {
                    // On desktop, use hover behavior
                    const computedStyle = window.getComputedStyle(dropdownMenu);
                    dropdownMenu.style.display = computedStyle.display === 'block' ? 'none' : 'block';
                }
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                const allDropdownMenus = document.querySelectorAll('.dropdown-menu');
                allDropdownMenus.forEach(menu => {
                    menu.style.display = 'none';
                });
            }
        });
    </script>
</body>
</html>