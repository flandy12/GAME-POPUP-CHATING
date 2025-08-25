@extends('layouts.app')
@section('content')
    <div class="grid grid-cols-2 gap-5">
        <div class="w-full">
            <div class="flex justify-start items-center h-screen relative">
                <div class="chat-container relative bg-white shadow-lg overflow-hidden p-4 w-full h-full">
                    <img src="{{ asset('/images/logo.png') }}"class="h-20 text-center mx-auto" />
                    <h1 class="uppercase text-white font-bold text-2xl text-center mb-5 font-default">Manifesto for a better
                        indonesia
                    </h1>
                    <!-- Bubble container -->
                    <div id="chat-grid" class="chat-body relative w-full h-full space-x-4 space-y-4">
                    </div>
                </div>

                <!-- Main modal -->
                <div id="default-modal"
                    class="hidden z-[99] fixed inset-0 items-center justify-end w-full h-full right-5 top-5 ">

                    <div id="chat-modal" class="relative w-full max-w-2xl h-full md:h-auto p-4 cursor-move">
                        <div class="relative bg-white text-black rounded-lg shadow-lg h-[400px] flex flex-col">
                            <div
                                class="drag-handle flex items-center justify-between p-4 border-b border-gray-200 sticky top-0 bg-white z-10">
                                <h3 class="text-xl font-semibold text-gray-900">
                                    Chat Results <span id="value-search"></span>
                                </h3>
                                <button id="close-btn">❌</button>
                            </div>
                            <div class="flex-1 overflow-y-auto p-4 space-y-4" id="wrapper-chating"></div>
                            <!-- Modal untuk full image -->
                        </div>
                    </div>
                </div>

                <!-- Modal Wrapper -->
                <div id="image-modal" class="hidden fixed inset-0  flex items-center justify-start z-50 ">
                    <div id="modal-box" class="relative bg-white p-3 rounded-lg shadow-lg cursor-move">
                        <!-- Tombol Close -->
                        <button id="close-modal" class="absolute top-2 right-2 text-red-500 font-bold">X</button>
                        <!-- Image -->
                        <img id="profile-full-image" src="" class="max-w-[90vw] max-h-[80vh] rounded-lg">
                    </div>
                </div>

            </div>
        </div>
        <!-- Search form -->
        <div class="flex items-center justify-center min-h-screen">
            <form id="searchForm" class="w-[400px] z-50 shadow-lg">
                @csrf
                <label for="default-search" class="sr-only">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                        </svg>
                    </div>
                    <input type="text" id="default-search" name="search"
                        class="block w-full p-4 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Search ..." />
                    <button type="submit"
                        class="text-white absolute right-2.5 bottom-2.5 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2">
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
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
            const cols = 20; // jumlah kolom bubble
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
                console.log("Spawn bubble..."); // cek jalan
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
                    if (bubble) {
                        bubble.classList.remove("translate-x-[-100%]", "opacity-0");
                        bubble.classList.add("translate-x-0", "opacity-100");
                    }
                }, 50);
            }


            // jalankan bubble tiap 1.5 detik
            setInterval(spawnBubble, 1500);

            // ========= Search Form =========
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const searchInput = document.getElementById('default-search').value;

                fetch(`/search`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            search: searchInput
                        })
                    })
                    .then(response => response.json())
                    .then(result => { // ✅ ganti "data" jadi "result"
                        wrapperChat.classList.replace('hidden', 'w-full');
                        defaultModal.classList.remove('hidden');
                        defaultModal.classList.add('flex');

                        wrapperChat.innerHTML = '';

                        if (result.length === 0) {
                            wrapperChat.innerHTML =
                                `<p class="text-gray-500 text-sm text-center">Tidak ada hasil ditemukan.</p>`;
                            return;
                        }

                        // map data sesuai kebutuhan
                        const data = result.map(item => ({
                            id: item.id,
                            name: item.name,
                            email: item.email ?? '-', // fallback kalau email kosong
                            message: item.message ?? '' // fallback kalau message kosong
                        }));

                        data.forEach((msg, i) => {
                            setTimeout(() => {
                                const row = document.createElement('button');
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
                                <div class="text-sm text-gray-700 text-left mt-3 capitalize">${msg.message}</div>
                            </div>
                        </div>
                    </div>
                `;
                                wrapperChat.appendChild(row);
                            }, i * 200); // delay animasi
                        });

                        valueSearch.innerHTML = searchInput;
                    })
                    .catch(error => {
                        wrapperChat.classList.replace('w-full', 'hidden');
                        console.error('Error:', error);
                    });
            });

            // ========= Close Search Modal =========
            closeBtn.addEventListener('click', function() {
                defaultModal.classList.add('hidden');
                defaultModal.classList.remove('flex');
            });

            // ========= Close Image Modal =========
            document.getElementById('close-modal').addEventListener('click', () => {
                document.getElementById('image-modal').classList.add('hidden');
            });

            // ========= Drag Image Modal =========
            (function() {
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
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        };
    </script>
@endsection
