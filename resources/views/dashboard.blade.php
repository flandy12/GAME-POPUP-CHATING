@extends('layouts.app')
@section('content')
    <div class="grid grid-cols-2 gap-5">
        <div class="w-full">
            <div class="flex justify-start items-center h-screen relative">
                <div class="chat-container relative bg-white shadow-lg overflow-hidden p-4 h-full aspect-[3/4]">
                    <img src="{{ asset('/images/logo.png') }}"class="h-20 text-center mx-auto" />
                    <h1 class="uppercase text-white font-boldtext-2xl text-center mb-2 font-default">Manifesto for a better
                        indonesia
                    </h1>
                    <!-- Bubble container -->
                    <div id="chat-grid" class="chat-body relative">
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
