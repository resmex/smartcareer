document.addEventListener('DOMContentLoaded', function () {
    // Sidebar Toggle
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const toggleBtn = document.getElementById('toggleSidebar');

    toggleBtn.addEventListener('click', function () {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
    });

    // Real-time Filtering
    const filterForms = document.querySelectorAll('.filter-form');
    
    filterForms.forEach(form => {
        const section = form.getAttribute('data-section');
        const inputs = form.querySelectorAll('input[type="text"]');
        const selects = form.querySelectorAll('select');

        // Debounce function to limit frequent updates
        function debounce(func, wait) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        // Update URL and reload page
        function updateFilters() {
            const params = new URLSearchParams(new FormData(form)).toString();
            window.location.href = `${window.location.pathname}?${params}`;
        }

        // Input event (typing)
        inputs.forEach(input => {
            input.addEventListener('input', debounce(updateFilters, 500)); // 500ms debounce
        });

        // Select change event (dropdowns)
        selects.forEach(select => {
            select.addEventListener('change', updateFilters);
        });
    });
});