<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Support\Facades\Storage; // Untuk membaca file KTM privat

class BookingController extends Controller
{
    // ═══════════════════════════════════════════════
    //  index() — Daftar Semua Booking
    // ═══════════════════════════════════════════════
    /**
     * Menampilkan daftar booking dengan fitur filter status dan pencarian keyword.
     *
     * Query parameter yang didukung:
     *  - ?status=pending/confirmed/cancelled   — filter berdasarkan status booking
     *  - ?search=keyword                       — cari berdasarkan kode/nama/telepon
     *  - ?ktm=pending/approved/rejected        — filter berdasarkan status KTM
     */
    public function index(Request $request)
    {
        // Ambil parameter filter dari URL
        $status = $request->get('status');
        $search = $request->get('search');
        $ktmFilter = $request->get('ktm'); // Filter status KTM (baru)

        // Mulai query dengan eager load relasi room dan items
        $query = Booking::with(['room', 'items.product'])->latest();

        // ── Filter berdasarkan status booking ───────────────────────────────
        if ($status) {
            $query->where('status', $status);
        }

        // ── Filter berdasarkan status KTM (baru) ────────────────────────────
        // Hanya tampilkan booking mahasiswa yang KTM-nya perlu perhatian
        if ($ktmFilter) {
            $query->where('visitor_category', 'mahasiswa')
                ->where('ktm_status', $ktmFilter);
        }

        // ── Pencarian keyword (baru) ─────────────────────────────────────────
        // Mencari di tiga kolom: kode booking, nama pelanggan, nomor telepon
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'LIKE', "%{$search}%")
                ->orWhere('customer_name', 'LIKE', "%{$search}%")
                ->orWhere('customer_phone', 'LIKE', "%{$search}%");
            });
        }

        // Paginate 10 data per halaman, pertahankan query parameter saat paginasi
        $bookings = $query->paginate(10)->withQueryString();

        // Hitung jumlah KTM yang menunggu verifikasi (untuk badge notifikasi)
        // Digunakan untuk menampilkan badge di tab/menu admin
        $pendingKtmCount = Booking::where('visitor_category', 'mahasiswa')
                                ->where('ktm_status', 'pending')
                                ->count();

        return view('admin.bookings.index', compact('bookings', 'status', 'search', 'ktmFilter', 'pendingKtmCount'));
    }

    // ═══════════════════════════════════════════════
    //  show() — Detail Booking
    // ═══════════════════════════════════════════════
    /**
     * Menampilkan detail lengkap satu booking termasuk info KTM.
     */
    public function show(Booking $booking)
    {
        // Load semua relasi yang diperlukan sekaligus (eager loading)
        $booking->load(['room', 'items.product', 'payment']);
        return view('admin.bookings.show', compact('booking'));
    }

    // ═══════════════════════════════════════════════
    //  update() — Update Status Booking
    // ═══════════════════════════════════════════════
    /**
     * Mengubah status booking (digunakan oleh tombol di tabel daftar booking).
     * Method ini dipertahankan dari implementasi asli.
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed'
        ]);

        $booking->update([
            'status' => $request->status
        ]);

        return back()->with('success', 'Status booking berhasil diperbarui.');
    }

    // Alias untuk kompatibilitas dengan Route::resource yang memanggil update()
    public function update(Request $request, Booking $booking)
    {
        return $this->updateStatus($request, $booking);
    }

    // ═══════════════════════════════════════════════
    //  viewKtm() — Tampilkan/Download File KTM (Admin Only)
    // ═══════════════════════════════════════════════
    /**
     * Mengirimkan file KTM ke browser admin untuk dilihat.
     *
     * Kenapa tidak pakai URL langsung?
     *  -> KTM disimpan di storage/app/ktm/ yang TIDAK publik.
     *     Satu-satunya cara mengakses adalah melalui controller ini,
     *     yang dilindungi middleware 'auth' di routes/web.php.
     *
     * Ini mencegah publik mengakses foto KTM langsung via URL.
     */
    public function viewKtm(Booking $booking)
    {
        // Pastikan booking ini adalah mahasiswa dan punya KTM
        if (!$booking->ktm_path || !Storage::disk('local')->exists($booking->ktm_path)) {
            abort(404, 'File KTM tidak ditemukan.');
        }

        // Ambil konten file dari storage privat
        $file = Storage::disk('local')->get($booking->ktm_path);
        
        // Tentukan MIME type berdasarkan ekstensi file
        $extension = pathinfo($booking->ktm_path, PATHINFO_EXTENSION);
        $mimeTypes = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'pdf'  => 'application/pdf',
        ];
        $mimeType = $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';

        // Kirim file ke browser dengan header yang tepat
        // 'inline' = tampilkan di browser, bukan download
        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="KTM-' . $booking->booking_code . '.' . $extension . '"');
    }

    // ═══════════════════════════════════════════════
    //  approveKtm() — Setujui KTM Mahasiswa
    // ═══════════════════════════════════════════════
    /**
     * Admin menyetujui KTM yang diunggah mahasiswa.
     * Status KTM diubah dari 'pending' menjadi 'approved'.
     *
     * @param  Booking $booking — booking yang KTM-nya akan disetujui
     */
    public function approveKtm(Booking $booking)
    {
        // Pastikan booking ini memang mahasiswa dan ada KTM-nya
        if ($booking->visitor_category !== 'mahasiswa' || !$booking->ktm_path) {
            return back()->with('error', 'Booking ini tidak memerlukan verifikasi KTM.');
        }

        // Update status KTM menjadi approved
        $booking->update([
            'ktm_status'        => 'approved',
            'ktm_reject_reason' => null, // Hapus alasan penolakan jika sebelumnya ditolak
        ]);

        return back()->with('success', 'KTM mahasiswa berhasil disetujui.');
    }

    // ═══════════════════════════════════════════════
    //  rejectKtm() — Tolak KTM Mahasiswa
    // ═══════════════════════════════════════════════
    /**
     * Admin menolak KTM yang diunggah mahasiswa.
     * Penolakan WAJIB disertai alasan agar mahasiswa tahu apa yang harus diperbaiki.
     *
     * @param  Request $request — mengandung field 'reject_reason'
     * @param  Booking $booking — booking yang KTM-nya akan ditolak
     */
    public function rejectKtm(Request $request, Booking $booking)
    {
        // Validasi: alasan penolakan wajib diisi dan minimal 10 karakter
        $request->validate([
            'reject_reason' => 'required|string|min:10|max:500',
        ], [
            'reject_reason.required' => 'Alasan penolakan wajib diisi.',
            'reject_reason.min'      => 'Alasan penolakan minimal 10 karakter.',
        ]);

        // Update status KTM dan simpan alasan penolakan
        $booking->update([
            'ktm_status'        => 'rejected',
            'ktm_reject_reason' => $request->reject_reason,
        ]);

        return back()->with('success', 'KTM telah ditolak. Pelanggan dapat mengunggah ulang KTM.');
    }

    // ═══════════════════════════════════════════════
    //  destroy() — Hapus Data Booking
    // ═══════════════════════════════════════════════
    /**
     * Menghapus data booking dari database.
     * File KTM juga dihapus dari storage jika ada.
     */
    public function destroy(Booking $booking)
    {
        // Hapus file KTM dari storage sebelum hapus record booking
        if ($booking->ktm_path && Storage::disk('local')->exists($booking->ktm_path)) {
            Storage::disk('local')->delete($booking->ktm_path);
        }

        $booking->delete();
        return back()->with('success', 'Data booking berhasil dihapus.');
    }
}