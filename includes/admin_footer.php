        </main>
    </div> <!-- Close main-content div -->

    <footer class="bg-dark text-white py-6 mt-8">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; 2025 Doktera Admin Panel. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Toggle sidebar
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        const toggleButton = document.getElementById('sidebar-toggle');
        
        toggleButton.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar-collapsed');
            mainContent.classList.toggle('sidebar-collapsed');
        });

        // Handle dropdown for health assessments
        const healthAssessmentLink = document.querySelector('.dropdown > a');
        healthAssessmentLink.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdownMenu = this.nextElementSibling;
            dropdownMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>