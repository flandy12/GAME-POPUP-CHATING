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
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (
        import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
});
document.addEventListener('DOMContentLoaded', () => {
    const searchForm = document.getElementById('searchForm');
    const wrapperChat = document.getElementById('wrapper-chating');
    const defaultModal = document.getElementById('default-modal');
    const valueSearch = document.getElementById('value-search');
    const closeBtn = document.getElementById('close-btn');
    const chatGrid = document.getElementById('chat-grid');

    wrapperChat.classList.add('hidden');

    // ===== Global Variables =====
    const rows = 5; // jumlah baris bubble
    const cols = 5; // jumlah kolom bubble
    let dataIndex = 0;

    const data = [{
            name: "User 1",
            message: "Halo 👋"
        },
        {
            name: "User 2",
            message: "Laravel power!"
        },
        {
            name: "User 3",
            message: "Mantap 🔥"
        },
        {
            name: "User 4",
            message: "Selamat malam 🌙"
        },
        {
            name: "User 5",
            message: "Lagi sibuk 😅"
        }
    ];

    // ========= Drag Modal Chat =========
    const modal = document.getElementById("chat-modal");
    const dragHandle = modal.querySelector(".drag-handle");
    let isDragging = false,
        offsetX, offsetY;

    dragHandle.addEventListener("mousedown", (e) => {
        isDragging = true;
        offsetX = e.clientX - modal.offsetLeft;
        offsetY = e.clientY - modal.offsetTop;
        modal.style.position = "absolute";
        modal.style.zIndex = 1000;
        document.body.style.userSelect = "none";
    });

    document.addEventListener("mousemove", (e) => {
        if (isDragging) {
            modal.style.left = (e.clientX - offsetX) + "px";
            modal.style.top = (e.clientY - offsetY) + "px";
        }
    });

    document.addEventListener("mouseup", () => {
        isDragging = false;
        document.body.style.userSelect = "auto";
    });

    // ========= Init Grid Bubble =========
    function initGrid() {
        chatGrid.innerHTML = "";
        chatGrid.classList.add("grid", "gap-2");
        chatGrid.style.gridTemplateColumns = `repeat(${cols}, minmax(0, 1fr))`;
        chatGrid.style.gridTemplateRows = `repeat(${rows}, minmax(0, 1fr))`;

        for (let i = 0; i < rows * cols; i++) {
            const slot = document.createElement("div");
            slot.className = "bubble-slot flex items-center justify-center min-h-[60px]";
            chatGrid.appendChild(slot);
        }
    }

    function spawnBubble() {
        const slots = Array.from(chatGrid.querySelectorAll(".bubble-slot"));
        const emptySlots = slots.filter(s => s.innerHTML.trim() === "");
        if (emptySlots.length === 0) return;

        const targetSlot = emptySlots[Math.floor(Math.random() * emptySlots.length)];
        const msg = data[dataIndex % data.length];
        dataIndex++;

        targetSlot.innerHTML = `
<div class="bubble transform translate-x-[-100%] opacity-0 transition-all duration-700 ease-out 
            bg-blue-200 p-3 rounded-lg shadow-md text-sm max-w-[120px] break-words">
    <p class="font-semibold capitalize">${msg.name}</p>
    <p class="text-gray-700 capitalize">${msg.message}</p>
</div>
`;

        setTimeout(() => {
            const bubble = targetSlot.querySelector(".bubble");

            bubble.classList.remove("translate-x-[-100%]", "opacity-0");
            bubble.classList.add("translate-x-0", "opacity-100");

        }, 50);
    }

    // jalankan bubble tiap 1.5 detik
    setInterval(spawnBubble, 1500);

    searchForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const searchInput = document.getElementById('default-search').value;

        if (searchInput) {
            fetch(`/search`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]')
                            .content
                    },
                    body: JSON.stringify({
                        search: searchInput
                    })
                })
                .then(response => response.json())
                .then(result => {
                    wrapperChat.innerHTML = ''; // kosongkan dulu

                    if (!result || result.length === 0) {
                        // kalau tidak ada hasil, sembunyikan modal & wrapper
                        wrapperChat.classList.add('hidden');
                        defaultModal.classList.add('hidden');
                        defaultModal.classList.remove('flex');

                        // optional: tampilkan pesan
                        return;
                    }

                    // jika ada hasil, tampilkan modal & wrapper
                    wrapperChat.classList.replace('hidden', 'w-full');
                    defaultModal.classList.remove('hidden');
                    defaultModal.classList.add('flex');

                    const data = result.map(item => ({
                        id: item.id,
                        name: item.name,
                        email: item.email ?? '-',
                        message: item.message ?? ''
                    }));

                    data.forEach((msg, i) => {
                        setTimeout(() => {
                            const row = document.createElement(
                                'button');
                            row.setAttribute('onclick',
                                `showProfileImage(${msg.id})`);
                            row.setAttribute('data-target', msg.id);
                            row.className = "cursor-pointer w-full";

                            row.innerHTML = `
                                    <div class="block">
                                        <div class="flex items-start gap-2.5 bg-gray-100 hover:bg-blue-200 rounded-lg p-3 shadow-sm">
                                            <img class="w-8 h-8 rounded-full" 
                                                src="https://ui-avatars.com/api/?name=${encodeURIComponent(msg.name)}&background=random" 
                                                alt="${msg.name}">
                                            <div class="flex flex-col gap-1 w-full">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm font-semibold text-gray-900 capitalize">${msg.name}</span>
                                                    <span class="text-xs text-gray-500 capitalize">${msg.email}</span>
                                                </div>
                                                <div class="text-sm text-gray-700 text-left mt-3 capitalize whitespace-normal break-words max-w-[450px]">${msg.message}</div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            wrapperChat.appendChild(row);
                        }, i * 200);
                    });

                    valueSearch.innerHTML = searchInput;
                })

                .catch(error => {
                    wrapperChat.classList.replace('w-full', 'hidden');
                    console.error('Error:', error);
                });
        } else {
            defaultModal.classList.add('hidden');
            defaultModal.classList.remove('flex');
            document.getElementById('image-modal').classList.add('hidden');
            document.getElementById('modal-box').classList.add('hidden');
        }
    });

    // ========= Close Search Modal =========
    closeBtn.addEventListener('click', function () {
        defaultModal.classList.add('hidden');
        defaultModal.classList.remove('flex');
    });

    // ========= Close Image Modal =========
    document.getElementById('close-modal').addEventListener('click', () => {
        document.getElementById('image-modal').classList.add('hidden');
    });

    // ========= Drag Image Modal =========
    (function () {
        const modalBox = document.getElementById("modal-box");
        let isDragging = false,
            offsetX, offsetY;

        modalBox.addEventListener("mousedown", (e) => {
            isDragging = true;
            offsetX = e.clientX - modalBox.offsetLeft;
            offsetY = e.clientY - modalBox.offsetTop;
            modalBox.style.position = "absolute";
            modalBox.style.zIndex = 1000;
        });

        document.addEventListener("mousemove", (e) => {
            if (isDragging) {
                modalBox.style.left = (e.clientX - offsetX) + "px";
                modalBox.style.top = (e.clientY - offsetY) + "px";
            }
        });

        document.addEventListener("mouseup", () => {
            isDragging = false;
        });
    })();
});

// ========= Show Profile Image =========
window.showProfileImage = (id) => {
    const defaultModal = document.getElementById('default-modal');

    defaultModal.classList.add('hidden');
    fetch(`/user/${id}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
        })
        .then(response => response.json())
        .then(data => {
            const baseUrl = window.location.origin;
            const modal = document.getElementById('image-modal');
            const img = document.getElementById('profile-full-image');

            img.src = `${baseUrl}/storage/${data.merged_image}`;
            modal.classList.remove('hidden');
            document.getElementById('image-modal').classList.remove('hidden');
            document.getElementById('modal-box').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
        });
};

document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.chat-body');
    const cols = 3; // 3 kolom
    const rows = 2; // 2 baris
    const delay = 4000; // update setiap 2 detik
    const maxBubbles = cols * rows; // total 6 bubble
    let pesanIndex = 0;
    let pesanArray = [];
    let pesanLoop = [];
    let bubbleElements = [];

    // CSS penting agar posisi benar
    // container.style.position = 'relative';
    container.style.height = '300px'; // tinggi container agar cukup 2 baris
    // container.style.width = '100%';

    // Ambil data awal dari DB
    fetch('/messages')
        .then(res => res.json())
        .then(data => {
            data.reverse().forEach(msg => {
                const cleanText = stripHTML(msg.message);
                pesanArray.push({
                    text: cleanText,
                    name: msg.name
                });
            });
            pesanLoop = [...pesanArray];

            // Buat 6 bubble kosong dulu
            for (let i = 0; i < maxBubbles; i++) {
                const bubble = addBubble('...', 'Loading');
                bubbleElements.push(bubble);
            }
        });

    // Fungsi buat bubble
    function addBubble(text, name) {
        const bubble = document.createElement('div');
        bubble.className = 'bubble fade-in';
        // bubble.style.position = 'absolute';
        bubble.style.padding = '10px';
        bubble.style.color = '#fff';
        bubble.style.borderRadius = '8px';
        bubble.style.background = '#3498db';
        bubble.style.width = '150px';
        bubble.style.wordWrap = 'break-word';
        bubble.style.textAlign = 'center';

        const nameElement = document.createElement('strong');
        nameElement.style.display = 'block';
        nameElement.style.marginBottom = '10px';
        nameElement.textContent = name;

        const textElement = document.createElement('span');
        textElement.classList.add('bubble-text');
        textElement.textContent = text;

        bubble.appendChild(nameElement);
        bubble.appendChild(textElement);
        container.appendChild(bubble);
        repositionBubbles();

        return bubble;
    }

    // Atur posisi 3 kolom × 2 baris
    function repositionBubbles() {
        const containerWidth = container.clientWidth;
        const colWidth = containerWidth / cols;
        const rowHeight = 150; // tambahkan lebih besar agar tidak numpuk

        bubbleElements.forEach((b, index) => {
            const col = index % cols;
            const row = Math.floor(index / cols);

            b.style.left = `${col * colWidth + 10}px`;
            b.style.top = `${row * rowHeight + 10}px`;
        });
    }

    // Update isi bubble setiap 2 detik
    function updateBubbles() {
        if (pesanLoop.length === 0) return;

        for (let i = 0; i < bubbleElements.length; i++) {
            const msg = pesanLoop[pesanIndex % pesanLoop.length];
            const bubble = bubbleElements[i];

            // Tambahkan class fade-out
            bubble.classList.remove('fade-in');
            bubble.classList.add('fade-out');

            setTimeout(() => {
                bubble.innerHTML = `
                    <strong>${msg.name}</strong>
                    <span class="bubble-text">${msg.text}</span>
                `;
                bubble.classList.remove('fade-out');
                bubble.classList.add('fade-in');
            }, 500); // fade-out selesai dalam 0.5s

            pesanIndex++;
        }
    }

    setInterval(updateBubbles, delay);

    window.addEventListener('resize', repositionBubbles);

    function stripHTML(html) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        return tempDiv.textContent || tempDiv.innerText || "";
    }
});
