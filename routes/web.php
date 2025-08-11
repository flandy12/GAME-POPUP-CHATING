<?php

use App\Events\MessageSent;
use App\Models\MasterMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/form', function () {
    return view('form');
})->name('form');

Route::post('/form', function (Request $request) {
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'message' => 'required|string|max:5000',
    ]);

    // Simpan ke DB
    $message = MasterMessage::create($data);
    
    // Broadcast event ke Echo (Redis)
    MessageSent::dispatch($message->name, $message->message);

    return redirect()->route('form')->with('success', 'Your message has been sent successfully.');
})->name('form.submit');


Route::get('/messages', function () {
    // Ambil 20 pesan terakhir, urut dari yang paling baru ke lama
    $messages = MasterMessage::orderBy('created_at', 'desc')->take(20)->get();

    return response()->json($messages);
});

Route::post('/search', function (Request $request) {
    $search = $request->input('search');
    $results = MasterMessage::where('name', 'like', "%{$search}%")
        ->orWhere('email', 'like', "%{$search}%")
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json($results);
});

