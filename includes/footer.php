    <footer class="bg-dark text-white py-8 mt-auto">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">Apotera</h3>
                    <p class="text-gray-300">Platform kesehatan terpercaya untuk konsultasi dokter dan pembelian obat.</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Layanan</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="consultation.php" class="hover:text-primary">Konsultasi</a></li>
                        <li><a href="health-store.php" class="hover:text-primary">Toko Kesehatan</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Cek Kesehatan</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="bmi-calculator.php" class="hover:text-primary">BMI Calculator</a></li>
                        <li><a href="depression-test.php" class="hover:text-primary">Cek Depresi</a></li>
                        <li><a href="heart-risk.php" class="hover:text-primary">Risiko Jantung</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Hubungi Kami</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li>Email: info@apotera.com</li>
                        <li>Telepon: (021) 12345678</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-6 text-center text-gray-400">
                <p>&copy; 2025 Apotera. All rights reserved.</p>
            </div>
        </div>
    </footer>
</div> <!-- Close page-wrapper div -->

<script>
    // Mobile menu toggle
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    mobileMenuButton.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
    });

    // Mobile dropdown toggle
    const mobileDropdownButton = document.getElementById('mobile-dropdown-button');
    const mobileDropdownContent = document.getElementById('mobile-dropdown-content');

    mobileDropdownButton.addEventListener('click', () => {
        mobileDropdownContent.classList.toggle('hidden');
    });

    // Cart dropdown functionality
    document.addEventListener('DOMContentLoaded', function() {
        const cartDropdown = document.getElementById('cart-dropdown');
        const cartPreview = cartDropdown ? cartDropdown.querySelector('.cart-preview') : null;
        const cartLink = cartDropdown ? cartDropdown.querySelector('a') : null;

        if (cartLink && cartPreview) {
            // Toggle cart dropdown
            cartLink.addEventListener('click', function(e) {
                e.preventDefault();
                cartPreview.classList.toggle('hidden');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (cartDropdown && !cartDropdown.contains(e.target)) {
                    cartPreview.classList.add('hidden');
                }
            });

            // Prevent dropdown from closing when clicking inside it
            cartPreview.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    });
</script>
</body>
</html>