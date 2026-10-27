---
name: anti-ai-ui-slop
description: Gunakan skill ini setiap kali merancang, memperbaiki, atau merefaktor tampilan frontend (UI/UX) web agar terhindar dari gaya AI slop yang kaku dan generik. Wajib mengikuti DESIGN.md dan melaporkan setiap perubahan kode, perbaikan bug, serta penambahan fitur yang dilakukan.
---

# Panduan Desain Anti-AI Slop

## 1. Prinsip Utama Desain

- Hindari desain generik AI, seperti gradasi ungu-biru yang tidak relevan, glassmorphism berlebihan, dekorasi tanpa fungsi, dan penggunaan card secara monoton.
- Utamakan desain yang rapi, proporsional, bersih, dan memiliki identitas visual yang jelas.
- Setiap elemen visual harus memiliki tujuan dan mendukung kebutuhan pengguna.
- Hindari layout yang terasa seperti template generik.
- Jangan mengubah desain yang sudah baik tanpa alasan yang jelas.
- Utamakan fungsionalitas, keterbacaan, konsistensi, dan kemudahan penggunaan.
- Jangan mengorbankan pengalaman pengguna hanya demi tampilan yang lebih dekoratif.

## 2. Design System Wajib — DESIGN.md

File `DESIGN.md` di root proyek merupakan sumber utama spesifikasi desain dan wajib diperiksa sebelum membuat, memperbaiki, atau merefaktor UI.

### 2.1 Aturan umum

- Baca `DESIGN.md` sebelum mengubah tampilan frontend.
- Jangan membuat palet warna, typography system, spacing scale, radius, atau design tokens baru jika spesifikasinya sudah tersedia.
- Jangan mengganti identitas visual tanpa instruksi eksplisit dari pengguna.
- Periksa konfigurasi aktual seperti CSS variables, Tailwind CSS, layout, dan komponen sebelum menerapkan spesifikasi.
- Jangan menganggap semua nilai dalam dokumentasi sudah diterapkan dalam kode.
- Jika dokumentasi dan implementasi aktual berbeda, identifikasi perbedaannya dan prioritaskan konsistensi dengan design system yang telah digunakan.
- Jika terdapat konflik yang signifikan atau keputusan desain yang belum jelas, jelaskan kepada pengguna sebelum mengubahnya.
- Jangan mengubah design system secara global hanya untuk memperbaiki satu halaman.

### 2.2 Color palette

Gunakan palet Technolife Corporate yang tercantum dalam `DESIGN.md`.

Token utama meliputi:

- `primary`: `#B1002C`
- `primary-container`: `#DC143C`
- `background`: `#FBF9F8`
- `surface`: `#FBF9F8`
- `surface-container-lowest`: `#FFFFFF`
- `on-surface`: `#1B1C1C`
- `on-surface-variant`: `#5C3F3F`
- `secondary`: `#5F5E5E`
- `tertiary`: `#006622`
- `error`: `#BA1A1A`

Aturan penerapan:

- Gunakan warna primer untuk CTA utama dan status aktif yang relevan.
- Gunakan warna surface untuk latar halaman dan area konten.
- Gunakan warna teks sesuai token yang tersedia.
- Gunakan warna success, warning, dan error secara konsisten sesuai maknanya.
- Jangan membuat warna merek baru tanpa kebutuhan yang jelas.
- Hindari gradasi dan glassmorphism yang tidak terdapat dalam design system.
- Gunakan token yang tersedia daripada mengulang nilai hexadecimal di banyak tempat.
- Pastikan kontras teks dan latar belakang tetap memadai.

Catatan: daftar di atas merupakan ringkasan token utama. Untuk token lengkap, termasuk varian surface dan warna pendukung, selalu rujuk `DESIGN.md`.

Jika warna pada dokumentasi berbeda dengan konfigurasi aktual proyek, jangan mengganti semuanya secara otomatis. Laporkan perbedaannya dan pertahankan konsistensi implementasi yang ada sampai keputusan desain ditentukan.

## 3. Typography

Gunakan sistem tipografi berikut sesuai `DESIGN.md`.

### 3.1 Heading

- Font family: Poppins.
- Font weight: 600 atau SemiBold.
- H1 desktop: 48px, line-height 1.2.
- H1 mobile: 32px, line-height 1.2.
- H2 desktop: 36px, line-height 1.3.
- H2 mobile: 28px, line-height 1.3.
- H3: 28px, line-height 1.4.
- H4: 22px, line-height 1.4.

### 3.2 Body dan label

- Font family: Inter.
- Body: 16px, regular, line-height 160%.
- Caption: 14px, regular, line-height 160%.
- Label: 14px, weight 600, letter spacing 0.02em.

### 3.3 Aturan penerapan

- Gunakan Poppins untuk heading dan Inter untuk isi, label, navigasi, serta informasi umum.
- Pertahankan hierarki visual yang jelas.
- Hindari terlalu banyak ukuran dan jenis font dalam satu halaman.
- Pastikan heading tidak terpotong atau menghasilkan line break yang buruk pada perangkat mobile.
- Jangan mengganti font tanpa alasan dan persetujuan pengguna.
- Periksa apakah font sudah tersedia dan dimuat dengan benar sebelum menambahkan dependensi baru.

## 4. Layout dan Spacing

Ikuti sistem layout dan spacing dari `DESIGN.md`.

- Gunakan sistem grid berbasis kelipatan 8px.
- Spacing yang tersedia:
  - `xs`: 4px.
  - `sm`: 8px.
  - `md`: 16px.
  - `lg`: 24px.
  - `xl`: 32px.
  - `xxl`: 48px.
- Maksimum lebar konten: 1280px.
- Gutter desktop: 24px.
- Margin mobile: 16px.
- Gunakan grid 12 kolom untuk desktop, 8 kolom untuk tablet, dan 4 kolom untuk mobile sesuai kebutuhan layout.
- Berikan ruang antarseksi yang cukup, misalnya `py-24` jika sesuai dengan struktur halaman.
- Jangan membuat tampilan terlalu padat atau terlalu renggang.
- Pertahankan alignment, jarak, dan proporsi yang konsisten.
- Hindari penggunaan angka spacing acak jika token yang sesuai sudah tersedia.

Nilai tersebut merupakan acuan desain. Implementasikan dengan sintaks yang sesuai dengan framework dan konfigurasi proyek, tanpa menganggap nama token otomatis tersedia sebagai utility class.

## 5. Elevation, Shadow, dan Shapes

### 5.1 Elevation

- Utamakan permukaan datar untuk latar dan area konten.
- Gunakan elevasi ringan untuk card dan input yang memerlukan pemisahan visual.
- Level 1: border 1px `#EAEAEA` dan shadow `0px 4px 12px rgba(0, 0, 0, 0.05)`.
- Level 2: shadow `0px 8px 24px rgba(0, 0, 0, 0.08)` untuk hover atau modal yang membutuhkan penekanan.
- Jangan menggunakan shadow berlebihan pada semua elemen.
- Jangan menambahkan efek visual hanya untuk membuat UI terlihat kompleks.

### 5.2 Border radius

Ikuti skala radius dalam `DESIGN.md`:

- `sm`: 0.25rem.
- `DEFAULT`: 0.5rem.
- `md`: 0.75rem.
- `lg`: 1rem.
- `xl`: 1.5rem.
- `full`: 9999px.

Gunakan radius utama 12px atau 0.75rem untuk komponen besar sesuai spesifikasi.

Jangan mengubah radius seluruh komponen hanya untuk menyesuaikan satu halaman.

## 6. Komponen UI

### 6.1 Buttons

- Primary button: latar crimson, teks putih, radius 12px.
- Tinggi tombol besar: 48px.
- Tinggi tombol medium: 40px.
- Secondary button: latar transparan, border crimson 1.5px, teks crimson.
- Hover primary: gelapkan warna sekitar 10% jika sesuai dengan token dan implementasi.
- Hover secondary: gunakan tint crimson ringan.
- Pastikan setiap tombol memiliki tujuan dan aksi yang jelas.
- Jangan membuat tombol yang terlihat aktif tetapi tidak memiliki fungsi.
- Pertahankan loading, disabled, success, dan error state sesuai kebutuhan.

### 6.2 Cards

- Gunakan latar putih, border tipis, dan radius 12px sesuai design system.
- Gunakan shadow ringan jika diperlukan.
- Gunakan elevasi yang lebih tinggi pada hover hanya jika memberikan umpan balik yang berguna.
- Jangan menjadikan setiap bagian halaman sebagai card.
- Jika tabel, daftar, atau layout terbuka lebih sesuai, gunakan pola tersebut.

### 6.3 Input fields

- Gunakan surface atau latar putih sesuai konteks.
- Gunakan border netral dan radius 12px.
- Gunakan warna crimson untuk focus state dengan indikator yang terlihat.
- Pastikan label, placeholder, validasi, dan pesan kesalahan mudah dipahami.
- Jangan mengandalkan placeholder sebagai satu-satunya label.
- Jangan menghapus validasi yang sudah ada saat memperbaiki tampilan formulir.

### 6.4 Lists dan navigation

- Gunakan Inter Regular 16px untuk item navigasi sesuai spesifikasi.
- Gunakan SemiBold dan indikator crimson untuk navigasi aktif.
- Pertahankan struktur navigasi yang sudah dikenal pengguna.
- Pastikan menu mobile dapat digunakan dengan baik.
- Jangan mengubah route atau tujuan navigasi tanpa alasan yang jelas.

### 6.5 Icons dan imagery

- Gunakan ikon dengan gaya visual dan ketebalan garis yang konsisten, sekitar 1.5–2px.
- Gunakan warna netral atau primary sesuai konteks.
- Gunakan foto berkualitas tinggi dengan pencahayaan alami untuk kebutuhan fotografi korporat.
- Hindari ilustrasi dekoratif yang tidak relevan.
- Jangan mengganti aset merek, logo, atau gambar yang sudah disediakan pengguna tanpa persetujuan.

## 7. Responsivitas dan Aksesibilitas

- Pastikan semua halaman bekerja pada desktop, tablet, dan mobile.
- Jangan sekadar mengecilkan layout desktop untuk membuat versi mobile.
- Periksa overflow horizontal, teks terpotong, tombol terlalu kecil, serta elemen yang bertumpuk.
- Pastikan form, tabel, modal, dropdown, dan navigasi dapat digunakan pada layar kecil.
- Gunakan semantic HTML dan elemen interaktif yang sesuai.
- Pastikan focus state, label input, navigasi keyboard, dan kontras warna memadai.
- Hormati preferensi `prefers-reduced-motion` jika animasi digunakan.
- Jangan menghapus fitur responsif yang sudah berfungsi saat memperbaiki komponen lain.

## 8. Animasi dan Interaksi

- Gunakan animasi seperlunya untuk memberikan umpan balik.
- Hindari animasi berlebihan, parallax yang tidak dibutuhkan, dan transisi yang memperlambat interaksi.
- Pastikan setiap elemen interaktif memiliki aksi nyata.
- Tangani loading, empty state, success state, error state, dan disabled state sesuai kebutuhan.
- Jangan membuat data contoh seolah-olah merupakan data nyata.
- Jangan mengubah perilaku fitur hanya demi efek visual.

## 9. Aturan Sebelum Mengubah Kode

Sebelum melakukan perubahan, AI Agent wajib:

1. Memeriksa struktur proyek dan teknologi yang digunakan.
2. Membaca `DESIGN.md` dan file instruksi proyek yang relevan.
3. Membaca file kode yang akan diubah.
4. Memahami fungsi dan alur kode yang sudah ada.
5. Mengidentifikasi komponen, halaman, route, controller, model, database, dan API yang berpotensi terdampak.
6. Mengidentifikasi fitur lama yang harus dipertahankan.
7. Menentukan file yang benar-benar perlu diubah atau ditambahkan.
8. Menghindari penulisan ulang seluruh file jika perbaikan lokal sudah cukup.
9. Mempertahankan konvensi dan arsitektur proyek yang sudah digunakan.
10. Memeriksa kemungkinan regresi sebelum menyelesaikan pekerjaan.

Jika kebutuhan belum jelas dan keputusan berpotensi mengubah arsitektur, data, atau perilaku fitur secara signifikan, tanyakan terlebih dahulu.

Untuk tugas kecil yang sudah jelas, kerjakan langsung tanpa pertanyaan yang tidak diperlukan.

## 10. Wajib Melaporkan Setiap Perubahan dan Penambahan Fitur

Setiap prompt coding yang dikerjakan wajib diikuti laporan perubahan yang sesuai dengan pekerjaan sebenarnya.

Aturan ini berlaku untuk:

- Perbaikan UI/UX.
- Penambahan fitur baru.
- Perbaikan bug.
- Perubahan alur pengguna.
- Perubahan dashboard admin.
- Perubahan backend, database, route, dan API.
- Refaktor kode.
- Perbaikan responsivitas.
- Perubahan konfigurasi dan dependensi.

### 10.1 Sebelum pengerjaan

- Pahami tujuan pengguna.
- Untuk tugas kompleks atau berisiko, sampaikan rencana perubahan secara singkat.
- Identifikasi fitur yang akan ditambahkan, diperbaiki, dan dipertahankan.
- Jangan mengklaim sudah mengubah file sebelum implementasi dilakukan.

### 10.2 Saat pengerjaan

- Catat file yang dibuat, diubah, atau dihapus.
- Pastikan perubahan sesuai dengan permintaan.
- Jelaskan kebutuhan migration, konfigurasi, dependensi, atau perubahan struktur data jika ada.
- Pertahankan kode yang tidak berkaitan dengan permintaan.
- Jangan menghapus fitur yang sudah ada tanpa memeriksa dampaknya.
- Jangan menambahkan fitur signifikan di luar permintaan tanpa menjelaskan kebutuhan dan meminta persetujuan.
- Jangan menjalankan operasi destruktif terhadap database atau data pengguna tanpa persetujuan.

### 10.3 Laporan setelah pengerjaan

Setelah tugas selesai, gunakan format berikut sesuai relevansinya.

#### A. Ringkasan perubahan

Jelaskan apa yang diperbaiki atau ditambahkan dan tujuan perubahannya.

#### B. Daftar file terdampak

Untuk setiap file, tuliskan:

- Path file.
- Status: dibuat, diubah, atau dihapus.
- Fungsi file.
- Ringkasan perubahan.

#### C. Detail implementasi

Jelaskan cara kerja fitur baru atau perbaikan dengan bahasa yang mudah dipahami.

Jika relevan, jelaskan hubungan frontend, backend, database, dan alur pengguna atau admin.

#### D. Dampak terhadap fitur lama

Jelaskan fitur yang tetap dipertahankan, perilaku yang berubah, dan risiko kompatibilitas yang ditemukan.

#### E. Perubahan tambahan

Sebutkan migration, route, API, dependensi, konfigurasi `.env`, atau perintah yang perlu dijalankan jika memang ada.

#### F. Hasil pengujian

Tuliskan pengujian yang benar-benar dilakukan dan hasilnya.

Jika pengujian belum dilakukan, nyatakan secara eksplisit.

#### G. Langkah selanjutnya

Berikan instruksi menjalankan, mengonfigurasi, atau memverifikasi perubahan jika diperlukan.

### 10.4 Kejujuran laporan

- Jangan mengklaim file berubah jika belum ada perubahan.
- Jangan mengklaim fitur berhasil jika belum diverifikasi.
- Jangan menyatakan seluruh pengujian lulus jika hanya sebagian yang dijalankan.
- Jangan menyembunyikan error atau regresi yang ditemukan.
- Jika tidak ada kode yang berubah, jelaskan bahwa pekerjaan hanya berupa analisis atau saran.
- Jangan mengulang seluruh isi kode dalam laporan jika ringkasan perubahan sudah cukup.

## 11. Komentar Kode dan Kemudahan Pemeliharaan

- Tambahkan komentar pada logika bisnis yang kompleks atau keputusan implementasi yang tidak langsung jelas.
- Berikan penjelasan relevan pada validasi, transaksi, autentikasi, otorisasi, dan pengelolaan data.
- Hindari komentar pada setiap baris jika hanya mengulang isi kode.
- Gunakan nama variabel, fungsi, class, dan komponen yang deskriptif.
- Ikuti konvensi framework dan struktur proyek.
- Jika pengguna meminta komentar setiap baris atau penjelasan per baris, penuhi permintaan tersebut pada bagian yang diminta.
- Pastikan komentar tetap sesuai dengan implementasi aktual setelah perubahan.

## 12. Keamanan dan Integritas Sistem

- Jangan mengekspos API key, password, token, atau kredensial sensitif.
- Jangan menonaktifkan validasi backend demi membuat frontend terlihat berfungsi.
- Validasi data penting harus dilakukan di backend.
- Jangan mempercayai nominal pembayaran atau status transaksi yang hanya berasal dari browser.
- Pertahankan autentikasi, otorisasi, dan proteksi keamanan yang sudah tersedia.
- Hindari migration yang merusak data lama.
- Jangan menghapus data produksi atau menjalankan reset database tanpa persetujuan pengguna.
- Jangan mengubah konfigurasi sensitif tanpa memahami dampaknya.
- Jangan memasukkan rahasia ke dalam Git atau laporan perubahan.

## 13. Aturan untuk Proyek yang Sudah Berjalan

Jika mengerjakan proyek yang sudah memiliki halaman dan fitur aktif:

- Pelajari implementasi yang ada sebelum melakukan refaktor.
- Pertahankan pola desain dan komponen yang telah digunakan.
- Hindari redesign total jika pengguna hanya meminta perbaikan tertentu.
- Jangan mengubah halaman lain yang tidak berkaitan.
- Periksa kemungkinan dampak perubahan terhadap route, form, autentikasi, transaksi, dan database.
- Jika menemukan masalah lain di luar lingkup permintaan, laporkan terlebih dahulu daripada langsung mengubahnya.
- Pastikan perubahan baru tidak merusak fungsi yang sudah berjalan.

## 14. Prinsip Akhir

Prioritaskan desain yang berkarakter, fungsional, konsisten, responsif, aman, dan mudah dipelihara.

Jangan sekadar membuat tampilan terlihat modern. Pastikan desain mengikuti `DESIGN.md`, kebutuhan pengguna, dan arsitektur proyek.

Setiap tugas harus menghasilkan implementasi sesuai permintaan dan laporan yang membantu pengguna memahami:

- Apa yang berubah.
- File mana yang terdampak.
- Fitur apa yang ditambahkan atau diperbaiki.
- Bagaimana perubahan bekerja.
- Apa dampaknya terhadap fitur lama.
- Bagaimana hasilnya diverifikasi.

**AI Agent tidak cukup hanya menghasilkan kode. AI Agent wajib menjaga konsistensi design system, mempertahankan fitur yang tidak berkaitan, dan menjelaskan perubahan yang benar-benar dilakukan secara transparan.**
