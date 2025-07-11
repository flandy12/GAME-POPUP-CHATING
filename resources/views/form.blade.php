@extends('layouts.app')

@section('content')
<div class="flex justify-center items-center h-screen relative">
    
    <form id="contact-form" class="max-w-sm w-[500px] p-4 mx-auto bg-white rounded-lg shadow-lg" action="{{ route('form.submit') }}" method="POST">
        @if(session('success'))
        <div id="success-alert" class="flex items-center p-4 mb-4 text-sm text-green-800 border border-green-300 rounded-lg bg-green-50 " role="alert">
            <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
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

        <!-- @if (session('success')) -->
           
        <!-- @endif -->
    </form>
</div>
@endsection

@section('scripts')
<script src="https://cdn.tiny.cloud/1/eurlu7d7btago4qbkngk9koxh3cn62potiv7f1ryk6kmosf7/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>

<script>
    tinymce.init({
        selector: '#message',
        plugins: 'emoticons',
        toolbar: 'emoticons charmap',
        menubar: false,
        height: 200,
        setup: function (editor) {
            const form = document.getElementById('contact-form');

            form.addEventListener('submit', function (e) {
                editor.save(); // Sync isi editor ke textarea

                const messageValue = editor.getContent({ format: 'text' }).trim();

                if (!messageValue) {
                    e.preventDefault();
                    alert('Message is required.');
                    editor.focus();
                }
            });
        }
    });

    setTimeout(() => {
            const alert = document.getElementById('success-alert');
            if (alert) {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = 0;
                setTimeout(() => alert.remove(), 500); // Remove after fade out
            }
        }, 1000); // 10 detik
</script>
@endsection