const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');
const toggleSidebar = document.getElementById('toggleSidebar');
const profileModal = document.getElementById('profileModal');
const profileView = document.getElementById('profileView');
const profileEdit = document.getElementById('profileEdit');
const sidebarProfilePic = document.getElementById('sidebarProfilePic');
const modalProfilePic = document.getElementById('modalProfilePic');
// const heroProfilePic = document.getElementById('heroProfilePic'); // Uncomment if using in hero section

toggleSidebar.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('expanded');
});

function toggleProfileModal() {
    profileModal.classList.toggle('active');
    mainContent.classList.toggle('blurred');
    if (!profileModal.classList.contains('active')) {
        profileView.classList.remove('hidden');
        profileEdit.classList.add('hidden');
    }
}

function toggleEditMode() {
    profileView.classList.toggle('hidden');
    profileEdit.classList.toggle('hidden');
}

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-slide-in');
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.animate-slide-in').forEach(card => {
    observer.observe(card);
});