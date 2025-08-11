@extends('layouts.app')
@section('content')
<div class="container mx-auto">
    <div class="flex justify-center items-center h-screen relative">
        <div class="chat-container relative bg-white rounded-lg shadow-lg overflow-hidden p-4 max-w-[1210px] max-h-[540px] w-full h-full">

            <!-- Bubble container -->
            <div class="chat-body relative w-full h-full overflow-hidden rounded-lg"></div>

            <!-- Search form -->
            <form id="searchForm" class="absolute bottom-4 left-1/2 transform -translate-x-1/2 w-[400px] z-50 shadow-lg">
                @csrf
                <label for="default-search" class="sr-only">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
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


        <!-- Main modal -->
        <div id="default-modal" tabindex="-1" aria-hidden="true"
            class="hidden z-[99] fixed inset-0 items-center justify-end w-full h-full bg-black/50">

            <div class="relative w-full max-w-2xl h-full md:h-auto p-4">
                <!-- Modal content -->
                <div class="relative bg-white text-black rounded-lg shadow-lg h-[400px] flex flex-col">
                    <!-- Modal header -->
                    <div class="flex items-center justify-between p-4 border-b border-gray-200 sticky top-0 bg-white z-10">
                        <h3 class="text-xl font-semibold text-gray-900">
                            Chat Results <span id="value-search"></span>
                        </h3>
                        <button type="button"
                            class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex items-center justify-center"
                            data-modal-hide="default-modal" id="close-btn">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    </div>

                    <!-- Modal body -->
                    <div class="flex-1 overflow-y-auto p-4 space-y-4" id="wrapper-chating">
                        <!-- Isi chat atau konten di sini -->
                    </div>
                </div>
            </div>
        </div>

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
        wrapperChat.classList.add('hidden');

        searchForm.addEventListener('submit', function(e) {
            e.preventDefault(); // cegah reload
            const searchInput = document.getElementById('default-search').value;

            fetch(`/search`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        search: searchInput,
                    })
                })
                .then(response => response.json())
                .then(data => {
                    wrapperChat.classList.replace('hidden', 'w-full');
                    defaultModal.classList.remove('hidden');
                    defaultModal.classList.add('flex');

                    wrapperChat.innerHTML = ''; // bersihkan sebelum isi baru
                    if (data.length === 0) {
                        wrapperChat.innerHTML = `<p class="text-gray-500 text-sm text-center">Tidak ada hasil ditemukan.</p>`;
                        return;
                    }
                    data.forEach(msg => {

                        wrapperChat.innerHTML += `
                        <div class="flex items-start gap-2.5 bg-gray-100 rounded-lg p-3 shadow-sm">
                            <img class="w-8 h-8 rounded-full" src="https://ui-avatars.com/api/?name=${encodeURIComponent(msg.name)}&background=random" alt="${msg.name}">
                            <div class="flex flex-col gap-1 w-full max-w-[320px]">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-gray-900">${msg.name}</span>
                                    <span class="text-xs text-gray-500">${msg.email}</span>
                                </div>
                                <div class="text-sm text-gray-700">${msg.message}</div>
                            </div>
                        </div>
                    `;
                    });

                    valueSearch.innerHTML = searchInput;
                    
                })
                .catch(error => {
                    wrapperChat.classList.replace('w-full', 'hidden');
                    console.error('Error:', error);
                });
        });

        const closeBtn = document.getElementById('close-btn');
        closeBtn.addEventListener('click', function() {
            defaultModal.classList.add('hidden');
            defaultModal.classList.remove('flex');
        })
    });
</script>
@endsection