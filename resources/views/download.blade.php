<!doctype html>
<html>

<head>
    <title>Download Gambar</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#006DAE] flex items-center justify-center h-screen">

    <!-- Modal -->
    <div id="downloadModal" class="fixed inset-0 flex items-center justify-center bg-black/20">
        <div class="bg-white rounded-xl shadow-lg max-w-sm w-full p-6 text-center">
            <img src="{{ asset('storage/' . $mergedImage) }}" alt="Download Icon" class="mx-auto mb-4 h-24">
            <h2 class="text-lg font-semibold mb-4">Download Gambar</h2>
            <p class="text-gray-600 mb-6">Apakah kamu ingin mendownload gambar hasil crop?</p>
            <div class="flex justify-center gap-4">
                <button id="cancelDownload" class="px-4 py-2 rounded-lg bg-gray-300 hover:bg-gray-400">
                    Tidak
                </button>
                <button id="confirmDownload" class="px-4 py-2 rounded-lg bg-[#006DAE] text-white hover:bg-[#005B8C]">
                    Ya, Download
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('downloadModal');
            const mergedImage = "{{ $mergedImage }}";

            document.getElementById('cancelDownload').onclick = () => {
                modal.style.display = 'none';
                // Optional: redirect ke halaman lain atau close
                window.location.href = '/'; // contoh redirect ke homepage
            };

            document.getElementById('confirmDownload').onclick = () => {
                // buat link sementara untuk download
                const link = document.createElement('a');
                link.href = `/storage/${mergedImage}`;
                link.download = mergedImage;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                modal.style.display = 'none';
                // Optional: redirect ke halaman lain setelah download
                window.location.href = '/';
            };
        });
    </script>
</body>

</html>
