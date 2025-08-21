@extends('layouts.app')

@section('content')
    <div class=" mx-auto xl:w-[1200px]">
        <div class="mb-5 mx-auto text-center p-4 mx-auto max-w-md">
            <label class="block mb-2 text-sm font-medium text-gray-600">Preview dengan Frame</label>
            <div class="border rounded-lg overflow-hidden w-[400px] bg-gray-100 flex items-center justify-cente text-center">
                <canvas id="frameCanvas" width="400" height="600"></canvas>
            </div>
            <small class="text-gray-500">Geser & zoom foto agar pas dengan frame</small>
        </div>
        <!-- @if (session('success'))
    @endif -->
        <form id="contact-form" class="p-4 mb-10 mx-auto max-w-md bg-white w-full rounded-lg shadow-lg"
            action="{{ route('form.submit') }}" enctype="multipart/form-data" method="POST">

            @if (session('success'))
                <div id="success-alert"
                    class="flex items-center p-4 mb-4 text-sm text-green-800 border border-green-300 rounded-lg bg-green-50 "
                    role="alert">
                    <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>
                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-medium">Success!</span> {{ session('success') }}
                        <!-- {{ session('success') }} -->
                    </div>
                </div>
            @endif

            @csrf

            <div class="mb-5">
                <label for="photo" class="block mb-2 text-sm font-medium text-gray-600">Upload Foto</label>
                <input type="file" name="photo" id="photo" accept="image/*"
                    class="bg-gray-50 border border-gray-300 text-gray-600 text-sm rounded-lg block w-full p-2.5" required>
            </div>

            <!-- hidden input hasil gabungan -->
            <input type="hidden" name="merged_image" id="mergedImage">

            <div class="mb-5">
                <label for="name" class="block mb-2 text-sm font-medium text-gray-600">Your name</label>
                <input type="text" name="name" id="name"
                    class="bg-gray-50 border border-gray-300 text-gray-600 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                    placeholder="name" required />
            </div>

            <div class="mb-5">
                <label for="email" class="block mb-2 text-sm font-medium text-gray-600">Your email</label>
                <input type="email" name="email" id="email"
                    class="bg-gray-50 border border-gray-300 text-gray-600 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                    placeholder="name@flowbite.com" required />
            </div>

            <div class="mb-5">
                <label for="message" class="block mb-2 text-sm font-medium text-gray-600">Your message</label>
                <textarea id="message" name="message" rows="4"
                    class="bg-gray-50 border border-gray-300 text-gray-600 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                    placeholder="Write your message here..."></textarea>
            </div>


            <button type="submit"
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">
                Submit
            </button>

        </form>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.tiny.cloud/1/eurlu7d7btago4qbkngk9koxh3cn62potiv7f1ryk6kmosf7/tinymce/7/tinymce.min.js"
        referrerpolicy="origin"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const canvas = document.getElementById('frameCanvas');
            const ctx = canvas.getContext('2d');
            const photoInput = document.getElementById('photo');
            const form = document.getElementById('contact-form');
            const hiddenImage = document.getElementById('mergedImage');

            const topPadding = 100;
            const photoAreaHeight = 310;
            const messageAreaStart = topPadding + photoAreaHeight; // 500

            let photo = new Image();
            let frame = new Image();
            frame.src = "{{ asset('images/frame-04.png') }}"; // frame PNG transparan
            frame.onload = () => draw();

            let state = {
                scale: 1,
                x: 0,
                y: 0,
                dragging: false,
                offsetX: 0,
                offsetY: 0,
                minScale: 0.2,
                maxScale: 3
            };

            function wrapText(ctx, text, x, y, maxWidth, lineHeight) {
                const words = text.split(' ');
                let line = '';
                for (let n = 0; n < words.length; n++) {
                    const testLine = line + words[n] + ' ';
                    const metrics = ctx.measureText(testLine);
                    const testWidth = metrics.width;
                    if (testWidth > maxWidth && n > 0) {
                        ctx.fillText(line, x, y);
                        line = words[n] + ' ';
                        y += lineHeight;
                    } else {
                        line = testLine;
                    }
                }
                ctx.fillText(line, x, y);
            }

            function draw() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                if (photo.src) {
                    const drawWidth = photo.width * state.scale;
                    const drawHeight = photo.height * state.scale;
                    ctx.drawImage(photo, state.x, state.y, drawWidth, drawHeight);
                }

                if (frame.complete) ctx.drawImage(frame, 0, 0, canvas.width, canvas.height);

                const messageValue = tinymce.get('message')?.getContent({
                    format: 'text'
                }).trim() || '';

                if (messageValue) {
                    ctx.font = "14px Arial";
                    ctx.fillStyle = "black";
                    ctx.textAlign = "center";

                    const paddingLeft = 80;
                    const paddingRight = 80;
                    const bottomPadding = 50;
                    const maxWidth = canvas.width - paddingLeft - paddingRight;
                    const lineHeight = 25;
                    // posisi Y: mulai dari area message + padding bottom
                    const startY = messageAreaStart + 45;
                    wrapText(ctx, messageValue, canvas.width / 2, startY, maxWidth, lineHeight);
                }

                // garis bantu (hapus kalau tidak mau kelihatan)
                ctx.strokeStyle = "rgba(0,0,0,0.2)";
                ctx.beginPath();
                ctx.moveTo(0, topPadding);
                ctx.lineTo(canvas.width, topPadding);
                ctx.moveTo(0, messageAreaStart);
                ctx.lineTo(canvas.width, messageAreaStart);
                ctx.stroke();
            }

            // Load foto dari input
            photoInput.addEventListener('change', e => {
                const file = e.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = ev => {
                    photo.onload = () => {
                        // hitung skala supaya foto muat dalam area
                        const fitScale = Math.max(
                            canvas.width / photo.width,
                            photoAreaHeight / photo.height
                        );

                        // langsung kecilkan 50%
                        state.scale = fitScale * 0.52;

                        // posisi tengah
                        state.x = (canvas.width - photo.width * state.scale) / 2;
                        state.y = topPadding + (photoAreaHeight - photo.height * state.scale) / 2;

                        draw();
                    };
                    photo.src = ev.target.result;
                };
                reader.readAsDataURL(file);
            });

            // Dragging
            canvas.addEventListener('mousedown', e => {
                state.dragging = true;
                state.offsetX = e.offsetX - state.x;
                state.offsetY = e.offsetY - state.y;
            });

            canvas.addEventListener('mousemove', e => {
                if (state.dragging) {
                    state.x = e.offsetX - state.offsetX;
                    state.y = e.offsetY - state.offsetY;
                    draw();
                }
            });
            window.addEventListener('mouseup', () => state.dragging = false);

            // Zoom scroll
            canvas.addEventListener('wheel', e => {
                e.preventDefault();
                const zoom = e.deltaY < 0 ? 1.1 : 0.9;
                let newScale = state.scale * zoom;
                if (newScale < state.minScale) newScale = state.minScale;
                if (newScale > state.maxScale) newScale = state.maxScale;
                state.scale = newScale;
                draw();
            });

            // TinyMCE
            tinymce.init({
                selector: '#message',
                plugins: 'emoticons',
                toolbar: 'emoticons charmap',
                menubar: false,
                height: 200,
                setup: function(editor) {
                    form.addEventListener('submit', function(e) {
                        editor.save();
                        draw();

                        const mergedData = canvas.toDataURL('image/png');
                        hiddenImage.value = mergedData;

                        const messageValue = editor.getContent({
                            format: 'text'
                        }).trim();
                        if (!messageValue) {
                            e.preventDefault();
                            alert('Message is required.');
                            editor.focus();
                            return;
                        }
                    });
                }
            });

            // Auto hide alert success
            setTimeout(() => {
                const alert = document.getElementById('success-alert');
                if (alert) {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = 0;
                    setTimeout(() => alert.remove(), 500);
                }
            }, 10000);
        });
    </script>
@endsection
