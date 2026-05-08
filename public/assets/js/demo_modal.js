document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('demo-modal');
    const content = document.getElementById('demo-modal-content');
    const closeBtn = document.getElementById('close-demo-modal');

    if (!modal || !content || !closeBtn) return;

    const getCookie = (name) => {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    };

    const setCookie = (name, value, days) => {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = `expires=${date.toUTCString()}`;
        document.cookie = `${name}=${value}; ${expires}; path=/`;
    };

    // Show modal if not seen yet
    if (!getCookie('demo_modal_seen')) {
        setTimeout(() => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.add('show');
            }, 10);
        }, 500);
    }

    // Close modal function
    const closeModal = () => {
        content.classList.remove('show');
        setTimeout(() => {
            modal.classList.replace('flex', 'hidden');
            // Fallback for safety
            modal.style.display = 'none';
            setCookie('demo_modal_seen', 'true', 30);
        }, 300);
    };

    closeBtn.addEventListener('click', closeModal);
});

