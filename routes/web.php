<?php

use App\Events\MessageSent;
use App\Models\MasterMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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
        'name'    => 'required|string|max:255',
        // 'email'   => 'nullable|email|max:255',
        'institution' => 'required|string|max:255',
        'message' => 'required|string|max:5000',
        'merged_image' => 'required|string', // hasil canvas (base64)
    ]);

    if (!empty($data['merged_image'])) {
        // decode base64 jadi file
        $image = str_replace('data:image/png;base64,', '', $data['merged_image']);
        $image = str_replace(' ', '+', $image);
        $imageName = 'framed_' . time() . '.png';

        Storage::disk('public')->put('uploads/' . $imageName, base64_decode($image));

        // simpan path ke DB, bukan base64
        $data['merged_image'] = 'uploads/' . $imageName;
    }

    // Simpan ke DB langsung dari $data
    $message = MasterMessage::create($data);

    // Broadcast event
    MessageSent::dispatch($message->name, $message->message);

    return redirect()
        ->route('form')
        ->with('success', 'Your message has been sent successfully.');
        
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


Route::get('/user/{id}', function($id) {
    $results = MasterMessage::findOrFail($id);

    return response()->json($results);
});