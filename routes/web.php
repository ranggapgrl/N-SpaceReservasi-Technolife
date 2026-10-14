<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\BlockedDateController; // Tambahan: kelola tanggal diblokir

/*
|--------------------------------------------------------------------------
| Web Routes — N-Space Booking
|--------------------------------------------------------------------------
|
| Route dikelompokkan berdasarkan fungsinya:
|  1. Halaman publik (landing, booking flow)
|  2. Checkout dan pembayaran
|  3. Status booking tanpa login
|  4. Admin authentication
|  5. Admin dashboard (dilindungi middleware 'auth')
|
| Cara membaca route:
|  Route::get('/url', [ControllerClass::class, 'methodName'])->name('nama.route')
|  - GET  = hanya untuk menampilkan halaman (tidak mengubah data)
|  - POST = mengirim data dari form
|  - PATCH/PUT = memperbarui sebagian data
|  - DELETE = menghapus data
*/

// ── Halaman Utama ──────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index']);

// ── Booking Flow (Alur Reservasi Pelanggan) ────────────────────────────────

// Menampilkan form wizard booking (Langkah 1–4)
Route::get('/booking', [BookingController::class, 'index'])->name('booking.index');

// Mengecek ketersediaan ruangan via AJAX (dipanggil saat pilih tanggal)
Route::post('/booking/check-availability', [BookingController::class, 'checkAvailability'])->name('booking.check');

// Memproses form booking (POST karena ada upload file KTM)
// PENTING: Form harus menggunakan enctype="multipart/form-data"
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');

// Halaman sukses setelah pembayaran (parameter = booking_code atau id)
Route::get('/booking/success/{booking}', [BookingController::class, 'success'])->name('booking.success');

// Upload ulang KTM yang ditolak admin (tidak perlu login, diverifikasi via booking_code + telepon)
Route::post('/booking/reupload-ktm', [BookingController::class, 'reuploadKtm'])->name('booking.reupload_ktm');

// ── Checkout dan Pembayaran ────────────────────────────────────────────────

// Halaman simulasi pembayaran (fallback jika Midtrans tidak tersedia)
Route::get('/checkout/simulate-payment/{bookingCode}', [CheckoutController::class, 'simulatePayment'])->name('checkout.simulate');

// Proses pembayaran simulasi
Route::post('/checkout/process-payment/{bookingCode}', [CheckoutController::class, 'processPayment'])->name('checkout.process');

// ── Cek Status Booking (Tanpa Login) ─────────────────────────────────────────
Route::get('/booking/check', [BookingController::class, 'checkStatus'])->name('booking.check_status');

// ── Admin Authentication ───────────────────────────────────────────────────
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login'); // Alias wajib untuk middleware auth
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// ── Admin Dashboard & Management (Dilindungi Middleware 'auth') ────────────
// Middleware 'auth' memastikan hanya admin yang sudah login yang bisa mengakses
// Semua route di dalam grup ini memiliki prefix '/admin' dan name 'admin.'
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {

    // Dashboard utama admin
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // ── Manajemen Ruangan ──────────────────────────────────────────────────
    Route::resource('rooms', RoomController::class);
    
    // ── Manajemen Booking ──────────────────────────────────────────────────
    // Route::resource menghasilkan: index, create, store, show, edit, update, destroy
    Route::resource('bookings', AdminBookingController::class);

    // Route tambahan untuk verifikasi KTM (di luar standar resource)
    // Menggunakan PATCH karena hanya mengubah sebagian data (ktm_status)
    Route::patch('bookings/{booking}/ktm/approve', [AdminBookingController::class, 'approveKtm'])
         ->name('bookings.ktm.approve');
    Route::patch('bookings/{booking}/ktm/reject', [AdminBookingController::class, 'rejectKtm'])
         ->name('bookings.ktm.reject');

    // Route untuk melihat file KTM privat (hanya admin yang bisa akses)
    // Menggunakan GET karena hanya membaca/menampilkan file
    Route::get('bookings/{booking}/ktm/view', [AdminBookingController::class, 'viewKtm'])
         ->name('bookings.ktm.view');

    // ── Manajemen Produk ───────────────────────────────────────────────────
    Route::resource('products', ProductController::class);

    // ── Kelola Tanggal Pemesanan (Blokir/Buka Tanggal) ────────────────────
    // GET    admin/blocked-dates         → daftar tanggal yang diblokir
    // POST   admin/blocked-dates         → tambah tanggal baru yang diblokir
    // DELETE admin/blocked-dates/{id}    → buka kembali tanggal yang sudah diblokir
    Route::get('blocked-dates', [BlockedDateController::class, 'index'])->name('blocked-dates.index');
    Route::post('blocked-dates', [BlockedDateController::class, 'store'])->name('blocked-dates.store');
    Route::delete('blocked-dates/{blockedDate}', [BlockedDateController::class, 'destroy'])->name('blocked-dates.destroy');
});