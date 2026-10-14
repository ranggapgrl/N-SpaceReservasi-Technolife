/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        // --- BASE & BACKGROUND ---
        'n-bg': '#FAFAF9',       // Mempertahankan warna putih tulang sebelumnya (n-white)
        'n-white': '#FAFAF9',
        'n-border': '#E5E7EB',   // Abu-abu terang untuk garis batas kartu
        'n-gray': '#9CA3AF',     // Abu-abu netral untuk teks sekunder

        // --- TEKS UTAMA ---
        // Diambil dari warna teks "study space" pada logo
        'n-charcoal': '#4F555A', 

        // --- BRAND COLORS (NEEDS STUDY SPACE) ---
        // Diambil dari warna buku biru di logo
        'n-primary': '#00A9E0',      
        'n-primary-dark': '#0087B3', // Biru sedikit gelap untuk efek hover tombol
        
        // Diambil dari lipatan buku kuning di logo
        'n-accent': '#F6C026',       
        'n-accent-dark': '#D9A015',

        // --- LEGACY COLORS ---
        // Dibiarkan agar halaman lama (jika ada) tidak error
        'n-brown': '#45312C',
        'n-beige': '#E5E0D8',
      },
      fontFamily: {
        serif: ['"Playfair Display"', 'serif'],
        sans: ['Poppins', 'sans-serif'],
      },
    },
  },
  plugins: [],
}