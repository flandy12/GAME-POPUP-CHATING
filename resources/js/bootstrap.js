// /**
//  * We'll load the axios HTTP library which allows us to easily issue requests
//  * to our Laravel back-end. This library automatically handles sending the
//  * CSRF token as a header based on the value of the "XSRF" token cookie.
//  */

// import axios from 'axios';
// window.axios = axios;

// window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// /**
//  * Echo exposes an expressive API for subscribing to channels and listening
//  * for events that are broadcast by Laravel. Echo and event broadcasting
//  * allows your team to easily build robust real-time web applications.
//  */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_APP_KEY,
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT, // Needed if using SSL/HTTPS
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    disableStats: true,
    enabledTransports: ['ws', 'wss'], // Specify WebSocket transports
});

document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.chat-body');
    // const notifSound = new Audio('/sounds/notif.mp3');
    const randomMessages = [
        "Halo!", "Apa kabar?", "Selamat datang!",
        "Bagaimana hari mu?", "Testing bubble...",
        "Pesan acak nih!", "Mantap!", "Laravel power!"
    ];

    // Ambil pesan dari DB, simpan di pesanDariDB untuk tidak bentrok
    let pesanDariDB = [];

    fetch('/messages')
    .then(res => res.json())
    .then(data => {
        pesanDariDB = data.reverse(); // simpan data dan tampilkan
        pesanDariDB.forEach((msg, i) => {
            setTimeout(() => {
                // Buang tag HTML sebelum dikirim ke addBubble
                const cleanText = stripHTML(msg.message);
                addBubble(cleanText, msg.name, true);
            }, i * 1000); // 500ms jeda antar bubble
        });
    });

    let pauseRandom = false;

    function addBubble(text,name, isEcho = false) {
        const bubble = document.createElement('div');
        bubble.className = 'bubble';
        bubble.innerHTML = `
            <strong>${name}</strong><br>
            ${text}
        `;

        bubble.style.left = '50%';
        bubble.style.top = '50%';

        if(container) {
            container.appendChild(bubble);

            setTimeout(() => {
                const bubbleWidth = bubble.offsetWidth;
                const bubbleHeight = bubble.offsetHeight;
                const containerWidth = container.offsetWidth;
                const containerHeight = container.offsetHeight;

                const offset = 20;
                const halfWidth = bubbleWidth / 2;
                const halfHeight = bubbleHeight / 2;

                const minLeft = halfWidth + offset;
                const maxLeft = containerWidth - halfWidth - offset;
                const minTop = halfHeight + offset;
                const maxTop = containerHeight - halfHeight - offset;

                let left = Math.random() * (maxLeft - minLeft) + minLeft;
                let top = Math.random() * (maxTop - minTop) + minTop;

                left = Math.max(minLeft, Math.min(left, maxLeft));
                top = Math.max(minTop, Math.min(top, maxTop));

                bubble.style.left = `${left}px`;
                bubble.style.top = `${top}px`;

                if (left > containerWidth / 2) {
                    bubble.classList.add('right');
                } else {
                    bubble.classList.remove('right');
                }
            }, 50);

            // Auto-remove HANYA jika bukan dari Echo
            if (!isEcho) {
                setTimeout(() => bubble.remove(), 10000);
            }
        }
    }

    function generateRandomBubble() {
        if (pauseRandom) return;

        const randomText = randomMessages[Math.floor(Math.random() * randomMessages.length)];
        addBubble(randomText, 'admin');
    }

    // Jalankan setiap 2 detik
    setInterval(generateRandomBubble, 2000);

    // Tambahkan satu saat halaman dimuat
    addBubble("Selamat datang!", 'admin');

    // Pesan dari Laravel Echo
    window.Echo.channel('chat')
        .listen('.message.sent', (e) => {

            addBubble(stripHTML(e.message),e.name, true);
            // notifSound.play().catch(() => {});

            // Pause pesan acak selama 10 detik
            pauseRandom = true;
            setTimeout(() => pauseRandom = false, 10000);
        });
});


function stripHTML(html) {
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    return tempDiv.textContent || tempDiv.innerText || "";
}