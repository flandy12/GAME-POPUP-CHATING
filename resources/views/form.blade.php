@extends('layouts.app')

@section('content')
    <div class="container mx-auto xl:w-[1200px] text-2xl flex flex-col xl:flex-row gap-5 p-10 flex-col-2">
        <div class="mb-5 mx-auto text-center p-4 flex justify-center flex-col w-full"  style="width: -webkit-fill-available;">
            <label class="block e5t-gray-600  mb-5 font-semibold">Preview dengan Frame</label>

            <div class="border rounded-lg w-full bg-gray-100 flex items-center justify-center aspect-[2/3]">
                <canvas id="frameCanvas" height="500" class="w-full h-full"></canvas>
            </div>

            <small class="text-gray-500 mt-5 font-semibold">Geser & zoom foto agar pas dengan frame</small>
        </div>

        @if (session('success'))
        @endif

        <form id="contact-form" class="w-full p-4 mb-10 mx-auto bg-white rounded-lg shadow-lg "
            action="{{ route('form.submit') }}" enctype="multipart/form-data" method="POST">

            @if (session('success'))
                <div id="success-alert"
                    class="flex items-center p-4 mb-4 text-green-800 border border-green-300 rounded-lg bg-green-50 "
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
                <label for="photo" class="block mb-2 font-medium text-gray-600">Upload Foto</label>
                <input type="file" name="photo" id="photo" accept="image/*"
                    class="bg-gray-50 border border-gray-300 text-gray-600 rounded-lg block w-full p-2.5" required>
            </div>

            <!-- hidden input hasil gabungan -->
            <input type="hidden" name="merged_image" id="mergedImage">

            <div class="mb-5">
                <label for="name" class="block mb-2 font-medium text-gray-600">Your name</label>
                <input type="text" name="name" id="name"
                    class="bg-gray-50 border border-gray-300 text-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                    placeholder="name" required />
            </div>

            <div class="mb-5">
                <label for="email" class="block mb-2 font-medium text-gray-600">Your email</label>
                <input type="email" name="email" id="email"
                    class="bg-gray-50 border  border-gray-300 text-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                    placeholder="name@flowbite.com" required />
            </div>

            <div class="mb-5">
                <label for="message" class="block mb-2 font-medium text-gray-600">Your message</label>
                <textarea id="message" name="message" rows="4"
                    class="bg-gray-50 border border-gray-300 text-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                    placeholder="Write your message here..."></textarea>
            </div>


            <button type="submit"
                class="text-white  bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg w-full sm:w-auto px-5 py-2.5 text-center">
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
            const messageAreaStart = topPadding + photoAreaHeight;

            let photo = new Image();
            let frame = new Image();
            frame.src = "{{ asset('images/frame-04.png') }}";
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

                // Foto
                if (photo && photo.complete && photo.naturalWidth > 0) {
                    const drawWidth = photo.width * state.scale;
                    const drawHeight = photo.height * state.scale;
                    ctx.drawImage(photo, state.x, state.y, drawWidth, drawHeight);
                }

                // Frame
                if (frame && frame.complete) {
                    ctx.drawImage(frame, 0, 0, canvas.width, canvas.height);
                }

                // Pesan dari TinyMCE
                const messageValue = tinymce.get('message')?.getContent({
                    format: 'text'
                }).trim() || '';

                if (messageValue) {
                    const fontSize = Math.max(14, Math.floor(canvas.width * 0.04));
                    ctx.font = `${fontSize}px Arial`;
                    ctx.fillStyle = "black";
                    ctx.textAlign = "center";

                    // Padding kiri/kanan 70px
                    const paddingLeft = 70;
                    const paddingRight = 70;
                    const maxWidth = canvas.width - paddingLeft - paddingRight;
                    const lineHeight = fontSize * 1.4;

                    // Posisi Y mulai di bawah area foto
                    const startY = messageAreaStart + 0;

                    wrapText(ctx, messageValue, canvas.width / 2, startY, maxWidth, lineHeight);
                }
            }

            // Load foto dari input
            photoInput.addEventListener('change', e => {
                const file = e.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = ev => {
                    photo.onload = () => {
                        const fitScale = Math.max(
                            canvas.width / photo.width,
                            photoAreaHeight / photo.height
                        );

                        state.scale = fitScale * 0.52;
                        state.x = (canvas.width - photo.width * state.scale) / 2;
                        state.y = topPadding + (photoAreaHeight - photo.height * state.scale) / 2;

                        draw();
                    };
                    photo.src = ev.target.result;
                };
                reader.readAsDataURL(file);
            });

            // Drag
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

            // Zoom
            canvas.addEventListener('wheel', e => {
                e.preventDefault();
                const zoom = e.deltaY < 0 ? 1.1 : 0.9;
                let newScale = state.scale * zoom;
                state.scale = Math.min(Math.max(newScale, state.minScale), state.maxScale);
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
                    const redraw = () => draw();
                    editor.on('input', redraw);
                    editor.on('KeyUp', redraw);

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
