export default {
  failed: 'Aksi gagal',
  success: 'Aksi berhasil',
  nav: {
    home: 'Beranda',
    posts: 'Dasbor Postingan',
    comments: 'Dasbor Komentar',
    tags: 'Dasbor Tag',
    signOut: 'Keluar',
    signIn: 'Masuk',
    register: 'Daftar'
  },
  home: {
    subtitle: 'Demo aplikasi reaktif real-time berkinerja tinggi.',
    syncTitle: 'Sinkronisasi Real-Time',
    syncDesc: 'Rasakan sinkronisasi instan antar-klien yang didukung oleh SSE (Server-Sent Events) dan Mercure.',
    authTitle: 'Autentikasi Aman',
    authDesc: 'Proses registrasi lengkap, validasi konfirmasi, dan alur autentikasi aman berbasis token JWT.',
    btnDashboard: 'Ke Dasbor',
    btnGetStarted: 'Mulai Sekarang',
    architectureTitle: 'Arsitektur Broadcast Real-time',
    noteTitle: 'Catatan Mode Aktif:',
    noteDesc: 'Aplikasi contoh ini saat ini dikonfigurasi menggunakan Metode Direct (Tanpa Queue) agar perubahan data tersinkronisasi 100% instan secara lokal. Untuk lingkungan produksi dengan beban tinggi (busy site), Anda sangat dianjurkan beralih menggunakan sistem Queue.',
    runQueueTitle: 'Cara Menjalankan Queue Worker (Jika Mode Queue Diaktifkan):',
    runQueueDesc: 'Buka jendela terminal baru di direktori root project Anda dan jalankan perintah berikut:',
    directTitle: 'Metode Direct (Tanpa Queue)',
    directPros: 'Transmisi data instan (0-10ms), tidak memerlukan pemrosesan latar belakang tambahan di development.',
    directCons: 'Menambah beban kerja web server secara sinkron, memblokir request utama saat jaringan Mercure sibuk/lambat, berpotensi menurunkan throughput server di trafik tinggi.',
    queueTitle: 'Metode Queue (Antrean Latar Belakang)',
    queuePros: 'Beban web server sangat ringan, request selesai <1ms, toleransi kegagalan tinggi (bisa retry otomatis jika Mercure down).',
    queueCons: 'Memerlukan proses latar belakang (worker) aktif, terdapat delay pemrosesan di lokal jika menggunakan database queue (kecuali dikonfigurasi menggunakan Redis).',
    prosLabel: 'Kelebihan:',
    consLabel: 'Kekurangan:'
  }
}
