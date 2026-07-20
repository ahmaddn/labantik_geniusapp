# Catatan Perubahan Aplikasi - Labantik Genius

Berikut adalah daftar perubahan dan pembaruan sistem pembelajaran interaktif pada aplikasi **Labantik Genius - Web Education** yang ditulis dalam bahasa yang mudah dipahami:

---

### 📅 Laporan Analisis Belajar Siswa (Student Report) & Pembaruan Tampilan Auth (15 Juli 2026)
* **Visualisasi Grafik Analisis Nilai (Student Report Dashboard):**
  * *Di mana letaknya:* Menu Laporan Siswa (Student Report) di Dashboard Admin/Guru.
  * *Penjelasan:* Menambahkan visualisasi grafik interaktif (Grafik Batang skor kuis, Grafik Garis tren perkembangan nilai, dan Grafik Donat distribusi nilai) untuk memudahkan Guru memantau tingkat pemahaman siswa secara sekilas.
* **Penyaringan Riwayat Pengerjaan Kuis (Module History Tabs):**
  * *Di mana letaknya:* Detail Laporan Siswa per modul.
  * *Penjelasan:* Riwayat kuis siswa kini dibagi rapi ke dalam tiga kategori tab: Pretest, Kuis Misi, dan Posttest. Masing-masing tab menyajikan informasi lengkap seperti skor, waktu pengerjaan, dan tanggal penyelesaian kuis.
* **Detail Jawaban & Penyesuaian Nilai Manual:**
  * *Di mana letaknya:* Baris tindakan tabel riwayat kuis pada Laporan Siswa.
  * *Penjelasan:* Guru sekarang dapat melihat rincian setiap lembar jawaban siswa (tombol "Detail") serta mengubah/menyesuaikan nilai siswa secara manual (tombol "Nilai") apabila diperlukan.
* **Latar Belakang Ilustrasi Halaman Autentikasi (AuthLayout):**
  * *Di mana letaknya:* Sisi kiri halaman Login & Registrasi pada perangkat desktop.
  * *Penjelasan:* Mengganti latar belakang warna biru polos dengan ilustrasi visual yang interaktif (`auth-bg.png` / `auth.png`) untuk memberikan kesan modern dan ramah pengguna.

---

### 📅 Target Modul Berdasarkan Kelas & Tab Penyaring Siswa (12 Juli 2026)
* **Pengaturan Target Kelas Modul (Panel Admin):**
  * *Di mana letaknya:* Modal tambah/edit modul di dashboard Admin.
  * *Penjelasan:* Guru/Admin dapat menetapkan target modul ke satu atau beberapa kelas paralel tertentu secara mudah (misalnya hanya untuk Kelas 1A dan 1B) melalui pilihan checkbox. Modul yang tidak dihubungkan ke kelas mana pun akan otomatis berstatus modul umum.
* **Lencana Visual Kelas (Panel Admin):**
  * *Di mana letaknya:* Daftar kartu modul di dashboard Admin.
  * *Penjelasan:* Menambahkan lencana (badge) biru penunjuk kelas target agar Guru dapat langsung memantau modul mana saja yang aktif untuk kelas tertentu secara visual.
* **Tab Switcher Penyaring Modul (Dashboard Siswa):**
  * *Di mana letaknya:* Layar Playground siswa (Materi Pembelajaran).
  * *Penjelasan:* Siswa kini dapat menyaring modul secara instan menggunakan tombol Tab 3D yang membal (chunky style) untuk memilih kategori: Semua Modul, Modul khusus Kelas mereka saja, atau Modul Umum (suplemen belajar untuk semua kelas).
* **Pembaruan Layout Kuis Pilihan Ganda (A-E):**
  * *Di mana letaknya:* Halaman kuis pilihan ganda siswa.
  * *Penjelasan:* Tombol opsi A-E diperbarui dengan ketebalan 3D warna-warni yang cerah, font bulat Nunito ramah anak, dan efek animasi klik yang lebih lembut.
* **Penyempurnaan Kuis Benar / Salah:**
  * *Di mana letaknya:* Halaman kuis Benar/Salah.
  * *Penjelasan:* Pemisahan skema warna yang tegas antara tombol hijau mint untuk "Benar" dan merah koral untuk "Salah", lengkap dengan ikon centang & silang untuk mempermudah anak-anak.
* **Redesain Slider Simulasi (Double Slider):**
  * *Di mana letaknya:* Elemen pengatur nilai pada simulasi sains.
  * *Penjelasan:* Menghilangkan penumpukan angka yang membingungkan siswa. Angka nilai aktif kini melayang di lencana tersendiri, dengan tombol peg tebal 3D yang mudah digeser oleh anak SD.
* **Penyempurnaan Efek Cuaca (Hujan & Salju):**
  * *Di mana letaknya:* Animasi simulasi cuaca.
  * *Penjelasan:* Memperbaiki partikel hujan/salju yang sempat membeku di bagian atas layar agar langsung meluncur jatuh dengan transisi jatuh yang sangat halus.
* **Pengembalian Layout Flowchart Refleksi Geser (Horizontal Scroll):**
  * *Di mana letaknya:* Layar refleksi akhir simulasi.
  * *Penjelasan:* Mengembalikan alur langkah refleksi ke mode geser ke samping (scroll) agar mendukung langkah yang dinamis dengan rapi tanpa menumpuk di layar.

---

### 📅 Rincian Hasil Kuis Lengkap & Genius Badge (1 Juli 2026)
* **Pecahan Nilai per Kategori Soal (Breakdown Kategori):**
  * *Di mana letaknya:* Halaman Laporan Hasil Kuis (Pretest, Misi Belajar, Posttest).
  * *Penjelasan:* Sekarang siswa dapat melihat pembagian nilai dan keakuratan jawaban (benar/salah) berdasarkan jenis soal yang dikerjakan (pilihan ganda, benar/salah, studi kasus, esai, atau tarik-lepas) untuk mempermudah evaluasi belajar.
* **Rincian Nilai Tiap Misi di Hasil Akhir Modul:**
  * *Di mana letaknya:* Halaman hasil akhir modul.
  * *Penjelasan:* Menampilkan rekapitulasi performa dan nilai secara mendetail untuk setiap misi yang telah diselesaikan dalam modul tersebut.
* **Lencana Kejeniusan (Genius Badge):**
  * *Di mana letaknya:* Bagian atas layar hasil kelulusan siswa.
  * *Penjelasan:* Memunculkan lencana bertuliskan "Genius" dengan animasi menarik ketika siswa berhasil menyelesaikan kuis dengan nilai sempurna atau performa luar biasa sebagai bentuk apresiasi tambahan.

---

### 📅 Umpan Balik Esai, Maskot Melayang (Sticky), & Suara Klik Baru (30 Juni 2026)
* **Penilaian Uji Pemahaman Singkat (Bukan Benar/Salah):**
  * *Di mana letaknya:* Halaman kuis isian/esai singkat siswa dan halaman laporan hasil nilai.
  * *Penjelasan:* Jawaban esai siswa tidak lagi dinilai secara kaku sebagai "Benar" atau "Kurang Tepat/Salah". Di layar kuis, sistem selalu menerima jawaban dengan pesan ramah *"Jawabanmu Berhasil Dikirim!"*. Di halaman detail hasil kuis, kartu soal akan otomatis berwarna hijau sukses dengan tanda centang hijau (bukan silang merah), serta menyajikan *"Referensi Jawaban"* berwarna biru (jika disediakan Guru) tanpa menampilkan baris kosong.
* **Maskot Geni Mengikuti Layar (Sticky):**
  * *Di mana letaknya:* Halaman belajar siswa (Playground).
  * *Penjelasan:* Maskot burung biru Geni kini akan tetap berada di layar (ikut turun) saat siswa menggeser layar ke bawah, agar tidak terpotong atau menghilang dari pandangan siswa.
* **Suara Klik Tombol Baru (Lebih Bervariasi):**
  * *Di mana letaknya:* Seluruh tombol di halaman belajar siswa.
  * *Penjelasan:* Mengeklik tombol apa saja di halaman belajar kini mengeluarkan suara klik ganda gelembung air (*double-bubble chirp*) yang khas dan baru untuk memberikan respon yang menyenangkan dan berbeda dari suara bawaan kuis.

---

### 📅 Pembaruan Halaman Belajar, Kalimat Maskot, & Suara Panduan (29 Juni 2026)
* **Warna Layar Berubah Otomatis (Perubahan Tampilan):**
  * *Di mana letaknya:* Halaman belajar siswa (Playground).
  * *Penjelasan:* Layar akan otomatis berubah warna: **Biru** untuk halaman membaca materi, **Hijau** saat membuka simulasi/game interaktif, dan **Kuning** saat mengisi soal kuis. Hal ini membantu siswa fokus pada aktivitasnya.
* **Kalimat Maskot Bisa Ditulis Sendiri (Bahasa Interaktif):**
  * *Di mana letaknya:* Menu Kuis/Materi di Dashboard Guru/Admin.
  * *Penjelasan:* Guru dapat menulis sendiri teks kalimat ucapan maskot (Geni) agar ucapan yang muncul di layar siswa lebih komunikatif, bervariasi, dan bergantian secara acak.
* **Panduan Suara (Voice Over) di Misi Belajar:**
  * *Di mana letaknya:* Bagian bawah layar misi belajar siswa (misalnya di Misi 1).
  * *Penjelasan:* Guru dapat mengunggah rekaman suara arahan. Siswa tinggal klik tombol putar (Play) atau sesuaikan volume untuk mendengarkan penjelasannya.
* **Penyimpanan Nilai Otomatis:**
  * *Di mana letaknya:* Sistem database otomatis.
  * *Penjelasan:* Progres dan nilai misi siswa akan otomatis tersimpan aman di database saat misi selesai dikerjakan. Siswa tidak perlu khawatir nilainya hilang.

---

### 📅 Laporan Nilai Akhir & Hiasan Kertas Warna-Warni (28 Juni 2026)
* **Rincian Hasil Ujian & Kuis (Feedback Kuis):**
  * *Di mana letaknya:* Layar laporan nilai sesaat setelah kuis/evaluasi selesai dikerjakan.
  * *Penjelasan:* Menampilkan nilai akhir, jumlah jawaban benar, jumlah jawaban salah, serta keterangannya agar siswa langsung tahu hasilnya secara detail.
* **Hiasan Kertas Warna-Warni (Selebrasi Konfeti):**
  * *Di mana letaknya:* Muncul otomatis di layar hasil kelulusan siswa.
  * *Penjelasan:* Semburan kertas warna-warni (konfeti) yang interaktif akan muncul sebagai hadiah visual saat siswa berhasil lulus agar belajar tidak membosankan.

---

### 📅 Memasukkan Materi Massal & Pembuatan Soal Praktis (27 Juni 2026)
* **Unggah Materi Massal Lewat File Excel (CSV):**
  * *Di mana letaknya:* Menu Materi pada Dashboard Guru.
  * *Penjelasan:* Guru tidak perlu mengetik satu-satu materi di aplikasi, melainkan bisa langsung mengunggah file Excel berisi kumpulan materi sekaligus untuk menghemat waktu.
* **Form Edit Soal Kuis Guru:**
  * *Di mana letaknya:* Menu Kuis pada Dashboard Guru.
  * *Penjelasan:* Menyediakan form pengisian soal yang mudah dipahami Guru untuk membuat soal pilihan ganda, benar/salah, maupun soal geser gambar.

---

### 📅 Efek Suara Menarik & Umpan Balik Geser Gambar (26 Juni 2026)
* **Efek Suara Tombol & Jawaban:**
  * *Di mana letaknya:* Saat siswa mengeklik tombol atau menjawab pertanyaan.
  * *Penjelasan:* Menambahkan efek suara menyenangkan agar suasana belajar kuis menjadi seru, interaktif, dan menyenangkan.
* **Tanda Penilaian Instan di Soal Geser Gambar (Drag & Drop):**
  * *Di mana letaknya:* Halaman kuis tarik-lepas (mengelompokkan gambar).
  * *Penjelasan:* Ketika gambar diletakkan di kotak jawaban, otomatis akan muncul tanda centang hijau (benar) atau tanda silang merah (salah) di atas gambar tersebut untuk respon cepat.
