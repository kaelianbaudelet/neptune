document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('demo-modal');
    const content = document.getElementById('demo-modal-content');
    const closeBtn = document.getElementById('close-demo-modal');

    console.log('Demo modal script loaded. APP_DEMO is true.');
    if (!modal || !content) {
        console.error('Modal or content elements not found!');
        return;
    }

    // Function to get cookie
    const getCookie = (name) => {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    };

    // Function to set cookie
    const setCookie = (name, value, days) => {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = `expires=${date.toUTCString()}`;
        document.cookie = `${name}=${value}; ${expires}; path=/`;
    };

    // Check if cookie exists
    if (!getCookie('demo_modal_seen')) {
        // Show modal with a slight delay for better transition
        setTimeout(() => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            // Trigger animation
            setTimeout(() => {
                content.classList.add('show');
            }, 10);
        }, 500);
    } else {
        console.log('Demo modal hidden because "demo_modal_seen" cookie is set.');
    }

    // Close modal and set cookie
    closeBtn.addEventListener('click', () => {
        content.classList.remove('show');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            setCookie('demo_modal_seen', 'true', 30); // Valid for 30 days
        }, 300);
    });
});
