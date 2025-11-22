document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('logout-confirm-modal');
    const confirmBtn = document.getElementById('modal-confirm-btn');
    const cancelBtn = document.getElementById('modal-cancel-btn');
    const logoutButtons = document.querySelectorAll('.logout-btn');

    if (!modal || !confirmBtn || !cancelBtn || logoutButtons.length === 0) {
        return; // Do nothing if modal elements are not found
    }

    const showModal = () => {
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('visible'), 10);
    };

    const hideModal = () => {
        modal.classList.remove('visible');
        setTimeout(() => (modal.style.display = 'none'), 300);
    };

    logoutButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent default action
            showModal();
        });
    });

    cancelBtn.addEventListener('click', hideModal);
    confirmBtn.addEventListener('click', () => window.location.href = '?logout=1');
});