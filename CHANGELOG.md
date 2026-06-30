# Catatan Perubahan (Changelog) - Sejak 26 Juni 2026

Berikut adalah penjelasan rinci mengenai pembaruan sistem dan antarmuka interaktif pada aplikasi **Labantik Genius - Web Education**:

---

### 📅 29 Juni 2026
#### **1. Progresi Misi, Dialog Maskot, & Voiceover**
* **Diferensiasi Tampilan Belajar Siswa:** Antarmuka playground siswa saat memuat **Materi** (bertema biru tenang), **Simulasi** (bertema hijau segar), maupun **Kuis** (bertema kuning emas) sekarang telah dipisahkan secara visual lengkap dengan efek partikel latar belakang yang dinamis sesuai tema untuk memberikan pengalaman belajar yang lebih terfokus.
* **Dialog Maskot Kustom (Random):** Admin/Guru sekarang dapat menginput kalimat kustom berbaris-baris (`custom_dialogues`) untuk masing-masing kuis atau materi. Kalimat ini akan ditampilkan secara bergantian/acak (*random*) pada gelembung ucapan maskot bersama dengan pose maskot yang dinamis.
* **Panduan Suara (Voiceover Misi):** Siswa dapat memutar rekaman instruksi suara (*voiceover*) pendukung lewat panel kontrol audio (play, pause, volume slider) di area bawah kuis misi.
* **Sinkronisasi Progres Misi:** Pencatatan otomatis pencapaian dan status progresi belajar siswa langsung di database saat misi diselesaikan.
* **Penyempurnaan Audio:** Pembersihan alokasi memori pemutar musik/suara saat navigasi untuk menghindari penumpukan suara.
* **Layout Pretest/Posttest Baru: ** Memperkenalkan pembungkus layout halaman evaluasi yang lebih teratur dan indah.

---

### 📅 28 Juni 2026
#### **2. Dashboard Hasil Kuis & Efek Animasi Interaktif**
* **Hasil Kuis Berkelanjutan (Feedback):** Setiap menyelesaikan kuis bentuk apa pun (Pretest, Posttest, Kuis Misi) siswa langsung disuguhi halaman rekapitulasi nilai detail.
* **Tampilan Lebih Menarik & Tidak Monoton:** Selebrasi kelulusan misi diramaikan dengan semburan konfeti berwarna (*canvas-confetti*) serta antarmuka statistik skor yang atraktif.
* **Perbaikan Bug:** Memperbaiki pergeseran klik area objek simulasi dan penanganan data misi kosong.

---

### 📅 27 Juni 2026
#### **3. Impor CSV & Manajemen Kuis Lanjutan**
* **Kelola Kuis Multi-tipe:** Form edit soal baru untuk pilihan ganda, benar/salah, dan drag-drop.
* **Impor Materi CSV:** Pengunggahan materi massal via dashboard admin.
* **Navigasi Kuis Siswa:** Transisi responsif dan penyimpanan sementara pilihan kuis siswa.

---

### 📅 26 Juni 2026
#### **4. Efek Suara (SFX) & Tarik-Lepas (Drag & Drop)**
* **Pemicu SFX Interaktif:** Memasang composable `useSfx` untuk memutar audio umpan balik secara instan sewaktu klik tombol atau menjawab soal.
* **Umpan Balik Kuis Drag & Drop:** Menampilkan indikator kebenaran (badge centang/silang) langsung pada objek yang ditarik secara dinamis untuk memberikan respon cepat.
