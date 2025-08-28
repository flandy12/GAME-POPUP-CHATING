<!doctype html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Monash University</title>
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('/images/logo.png') }}">
    <script src="https://cdn.tiny.cloud/1/eurlu7d7btago4qbkngk9koxh3cn62potiv7f1ryk6kmosf7/tinymce/7/tinymce.min.js"
        referrerpolicy="origin"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const photoInput = document.getElementById("photo");
            const photoPreviewModal = document.getElementById("photoPreviewModal");
            const cropperModal = document.getElementById("cropperModal");
            const cancelCrop = document.getElementById("cancelCrop");
            const saveCrop = document.getElementById("saveCrop");
            const mergedImage = document.getElementById("mergedImage");
            const canvas = document.getElementById("frameCanvas");
            const ctx = canvas.getContext("2d");

            const form = document.getElementById('contact-form');
            const topPadding = 150;
            const photoAreaHeight = 310;
            const messageAreaStart = topPadding + photoAreaHeight;

            let photo = new Image(); // foto hasil upload user
            let frameImage = new Image(); // frame default
            frameImage.src = "/images/frame-04.png"; // ganti dengan asset() jika Laravel

            let cropper = null;

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

            // render frame default saat pertama kali diload
            frameImage.onload = function() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(frameImage, 0, 0, canvas.width, canvas.height);
            };

            // pilih foto -> buka modal crop
            photoInput.addEventListener("change", e => {
                const file = e.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = ev => {
                    photoPreviewModal.src = ev.target.result;
                    cropperModal.classList.remove("hidden");

                    photoPreviewModal.onload = () => {
                        if (cropper) cropper.destroy();
                        cropper = new Cropper(photoPreviewModal, {
                            aspectRatio: 4 / 4,
                            viewMode: 1,
                            autoCropArea: 1,
                            responsive: true
                        });
                    };
                };
                reader.readAsDataURL(file);
            });

            // cancel crop
            cancelCrop.addEventListener("click", () => {
                if (cropper) cropper.destroy();
                cropperModal.classList.add("hidden");
                photoInput.value = "";
            });

            // save crop
            saveCrop.addEventListener("click", () => {
                if (!cropper) return;
                const croppedCanvas = cropper.getCroppedCanvas({
                    width: 600,
                    height: 900
                });

                // jadikan sebagai foto utama
                photo.src = croppedCanvas.toDataURL("image/png");

                // simpan ke hidden input
                mergedImage.value = photo.src;

                cropper.destroy();
                cropperModal.classList.add("hidden");

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
            });

            function wrapText(ctx, text, x, y, maxWidth, lineHeight) {
                let line = '';

                for (let i = 0; i < text.length; i++) {
                    const testLine = line + text[i];
                    const testWidth = ctx.measureText(testLine).width;

                    if (testWidth > 240 && line !== '') {
                        ctx.fillText(line, x, y);
                        line = text[i]; // mulai baris baru
                        y += lineHeight;
                    } else {
                        line = testLine;
                    }
                }

                if (line !== '') {
                    ctx.fillText(line, x, y);
                }
            }

            function draw() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                let imageBottom = 0; // posisi bawah foto crop

                // gambar foto
                if (photo && photo.complete && photo.naturalWidth > 0) {
                    const drawWidth = photo.width * state.scale;
                    const drawHeight = photo.height * state.scale;

                    // pastikan foto tetap di canvas
                    const x = Math.min(Math.max(state.x, 0), canvas.width - drawWidth);
                    const y = Math.min(Math.max(state.y, 0), canvas.height - drawHeight);

                    ctx.drawImage(photo, x, y, drawWidth, drawHeight);

                    // catat posisi bawah foto
                    imageBottom = y + drawHeight;
                }

                // gambar frame
                if (frameImage && frameImage.complete) {
                    ctx.drawImage(frameImage, 0, 0, canvas.width, canvas.height);
                }

                // ambil teks dari TinyMCE
                const messageValue = tinymce.get('message')?.getContent({
                    format: 'text'
                }).trim() || '';
                if (messageValue) {
                    const fontSize = Math.max(14, Math.floor(canvas.width * 0.04));
                    ctx.font = `${fontSize}px Arial`;
                    ctx.fillStyle = "black";
                    ctx.textAlign = "center";

                    const padding = 50; // padding kiri-kanan supaya tidak mepet frame
                    const maxWidth = canvas.width - (padding * 2);
                    const lineHeight = fontSize * 1.4;

                    // posisi teks = 40px di bawah foto
                    let textY = imageBottom + 40;

                    // batas bawah frame (jangan melebihi canvas)
                    const bottomLimit = canvas.height - 40;
                    if (textY + lineHeight > bottomLimit) {
                        textY = bottomLimit - lineHeight; // geser ke atas agar muat
                    }

                    wrapText(ctx, messageValue, canvas.width / 2, textY, maxWidth, lineHeight);
                }
            }

            // drag
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

            // zoom
            canvas.addEventListener('wheel', e => {
                e.preventDefault();
                const zoom = e.deltaY < 0 ? 1.1 : 0.9;
                let newScale = state.scale * zoom;
                state.scale = Math.min(Math.max(newScale, state.minScale), state.maxScale);
                draw();
            });

            tinymce.init({
                selector: '#message',
                plugins: 'emoticons',
                toolbar: 'emoticons charmap',
                menubar: false,
                height: 200,
                setup: function(editor) {
                    editor.on('keydown', function(e) {
                        const content = editor.getContent({
                            format: 'text'
                        });
                        if (content.length >= 170 && e.key.length === 1 && !e.ctrlKey && !e
                            .metaKey) {
                            e.preventDefault(); // cegah input lebih dari 170
                            alert('Maksimal 170 karakter!');
                        }
                    });

                    editor.on('input', function() {
                        const content = editor.getContent({
                            format: 'text'
                        });
                        if (content.length > 170) {
                            editor.setContent(content.substring(0, 170));
                            alert('Maksimal 170 karakter!');
                        }
                    });
                    // Trigger redraw ketika ada perubahan di editor
                    editor.on('input KeyUp change', () => {
                        draw();
                    });

                    // Validasi + generate image saat form submit
                    form.addEventListener('submit', function(e) {
                        editor.save(); // update <textarea> hidden
                        draw();

                        const textValue = editor.getContent({
                            format: 'text'
                        }).trim();
                        if (!textValue) {
                            e.preventDefault();
                            alert('Message is required.');
                            editor.focus();
                            return;
                        }

                        // Simpan hasil canvas ke input hidden
                        const mergedData = canvas.toDataURL('image/png');
                        mergedImage.value = mergedData;
                    });
                }
            });

        });
    </script>

    @vite('resources/css/app.css')
</head>

<body class="bg-[#e3e8f8] w-full">
    <div class="container mx-auto xl:w-[1200px] text-2xl flex flex-col items-center xl:flex-row gap-5 p-10 flex-col-2">
        <!-- Preview Utama -->
        <div class="mb-5 mx-auto text-center p-4 flex justify-center flex-col w-full"
            style="width: -webkit-fill-available;">
            <label class="block text-gray-600 mb-5 font-semibold">Preview dengan Frame</label>
            <div
                class="border rounded-lg w-full bg-gray-100 flex items-center justify-center aspect-[2/3] relative overflow-hidden">
                <!-- hasil crop + frame ditampilkan disini -->
                <!-- Canvas utama -->
                <canvas id="frameCanvas" width="600" height="900"></canvas>
            </div>

            <small class="text-gray-500 mt-5 font-semibold">Geser & zoom foto agar pas dengan frame</small>
        </div>

        <!-- Modal -->
        <div id="cropperModal"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-[#00000029] bg-opacity-75 black opse bg-opacity-50">
            <!-- Konten modal -->
            <div class="flex justify-center items-center h-full">
                <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6 relative">

                    <!-- Tombol close pojok kanan -->
                    <button id="cancelCrop" class="absolute top-3 right-3 text-gray-500 hover:text-red-500">
                        ✕
                    </button>

                    <!-- Judul -->
                    <h2 class="text-lg font-semibold mb-4 text-center">Crop Gambar</h2>

                    <!-- Area gambar -->
                    <div class="w-full flex justify-center mb-4">
                        <img id="photoPreviewModal" class="max-h-[400px] rounded-lg border" />
                    </div>

                    <!-- Tombol aksi -->
                    <div class="flex justify-end gap-3">
                        <button id="cancelCrop" class="px-4 py-2 rounded-lg bg-gray-300 hover:bg-gray-400">
                            Batal
                        </button>
                        <button id="saveCrop" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                            Simpan
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- Form -->
        <form id="contact-form" class="w-full p-4 mb-10 mx-auto h-fit bg-white rounded-lg shadow-lg"
            action="{{ route('form.submit') }}" enctype="multipart/form-data" method="POST">

            @csrf

            <div class="mb-5">
                <label for="photo" class="block mb-2 font-medium text-gray-600">Upload Foto</label>
                <input type="file" name="photo" id="photo" accept="image/*"
                    class="bg-gray-50 border border-gray-300 text-gray-600 rounded-lg block w-full p-2.5" required>
            </div>

            <!-- hidden input hasil crop+frame -->
            <input type="hidden" name="merged_image" id="mergedImage">

            <div class="mb-5">
                <label for="name" class="block mb-2 font-medium text-gray-600">Your name</label>
                <input type="text" name="name" id="name"
                    class="bg-gray-50 border border-gray-300 text-gray-600 rounded-lg block w-full p-2.5"
                    placeholder="name" required />
            </div>

            <div class="mb-5">
                <label for="email" class="block mb-2 font-medium text-gray-600">Your email</label>
                <input type="email" name="email" id="email"
                    class="bg-gray-50 border border-gray-300 text-gray-600 rounded-lg block w-full p-2.5"
                    placeholder="name@flowbite.com" required />
            </div>

            <div class="mb-5">
                <label for="message" class="block mb-2 font-medium text-gray-600">Your message</label>
                <textarea id="message" name="message" rows="4"
                    class="bg-gray-50 border border-gray-300 text-gray-600 rounded-lg block w-full p-2.5"
                    placeholder="Write your message here..."></textarea>
            </div>

            <button type="submit"
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg w-full sm:w-auto px-5 py-2.5 text-center">
                Submit
            </button>
        </form>
    </div>
</body>

</html>
