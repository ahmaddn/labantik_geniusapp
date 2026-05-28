<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref } from "vue";
import { 
    Home, 
    Palette, 
    GraduationCap, 
    BookOpen, 
    Users, 
    BarChart, 
    Info,
    HelpCircle,
    CheckCircle2,
    ArrowRight,
    PlayCircle,
    FileText,
    MousePointerClick,
    Settings,
    Download
} from "lucide-vue-next";

const guides = ref([
    { id: "dashboard", title: "Dashboard Utama", icon: Home },
    { id: "templates", title: "Template Desain", icon: Palette },
    { id: "modules", title: "Modul Pembelajaran", icon: GraduationCap },
    { id: "classes", title: "Manajemen Kelas", icon: BookOpen },
    { id: "users", title: "Manajemen Pengguna", icon: Users },
    { id: "reports", title: "Laporan & Riwayat", icon: BarChart }
]);

const activeTab = ref("modules"); 
</script>

<template>
    <AppLayout>
        <div class="p-5 max-w-6xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-3xl border-4 border-gray-200 shadow-playful p-6 mb-8 flex items-center gap-4">
                <div class="bg-blue-100 p-4 rounded-2xl border-2 border-blue-300">
                    <HelpCircle class="text-blue-500 w-8 h-8" />
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-heading font-bold text-gray-800">
                        Pusat Bantuan & Tutorial
                    </h1>
                    <p class="text-gray-500">
                        Panduan langkah demi langkah yang sangat mudah diikuti untuk setiap menu.
                    </p>
                </div>
            </div>

            <!-- Intro Alert -->
            <div class="bg-yellow-50 border-2 border-yellow-200 p-5 rounded-2xl mb-8 flex gap-4 items-start shadow-sm">
                <Info class="text-yellow-500 w-6 h-6 shrink-0 mt-0.5" />
                <div class="text-sm text-yellow-800 leading-relaxed">
                    <p class="font-bold text-base mb-1">Selamat Datang di Admin Panel!</p>
                    <p>Halaman ini adalah pusat kendali bagi Bapak/Ibu Guru. Di sini Anda bertugas menyiapkan materi pelajaran, soal tes, dan permainan interaktif yang nantinya akan dimainkan oleh siswa di aplikasi Playground.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <!-- Navigasi Kiri (Desktop) -->
                <div class="md:col-span-4 space-y-3 hidden md:block">
                    <h3 class="font-bold text-gray-400 mb-4 px-2 uppercase tracking-wider text-xs">Pilih Menu Tutorial</h3>
                    
                    <button 
                        v-for="guide in guides" 
                        :key="guide.id"
                        @click="activeTab = guide.id"
                        class="w-full text-left px-5 py-4 rounded-2xl transition-all border-2 flex items-center gap-3"
                        :class="activeTab === guide.id 
                            ? 'bg-blue-500 text-white border-blue-600 shadow-md font-bold' 
                            : 'bg-white text-gray-600 border-gray-100 hover:border-blue-200 hover:bg-blue-50'"
                    >
                        <component :is="guide.icon" class="w-5 h-5 shrink-0" :class="activeTab === guide.id ? 'text-blue-100' : 'text-gray-400'" />
                        <span class="truncate">{{ guide.title }}</span>
                    </button>
                </div>

                <!-- Navigasi Atas (Tablet/Mobile) -->
                <div class="md:hidden flex flex-wrap gap-2 pb-2">
                    <button 
                        v-for="guide in guides" 
                        :key="'mob-'+guide.id"
                        @click="activeTab = guide.id"
                        class="px-4 py-2 rounded-xl border-2 flex items-center gap-2 transition-all"
                        :class="activeTab === guide.id 
                            ? 'bg-blue-500 text-white border-blue-600 shadow-sm font-bold' 
                            : 'bg-white text-gray-600 border-gray-100 hover:bg-gray-50'"
                    >
                        <component :is="guide.icon" class="w-4 h-4" />
                        {{ guide.title }}
                    </button>
                </div>

                <!-- Area Konten Utama -->
                <div class="md:col-span-8 bg-white rounded-3xl border-4 border-gray-100 shadow-sm p-6 md:p-8 animate-fade-in">
                    
                    <!-- 1. DASHBOARD -->
                    <div v-show="activeTab === 'dashboard'">
                        <div class="flex items-center gap-3 mb-8 border-b border-gray-100 pb-4">
                            <div class="p-2 bg-blue-100 rounded-xl"><Home class="w-6 h-6 text-blue-600" /></div>
                            <h2 class="text-2xl font-bold text-gray-800">Tutorial: Dashboard</h2>
                        </div>
                        
                        <p class="text-gray-600 mb-6 text-lg">Dashboard adalah halaman ringkasan. Anda tidak perlu mengatur apapun di sini, cukup melihat dan memantau data.</p>
                        
                        <div class="space-y-4">
                            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-5 flex gap-4 items-start">
                                <div class="bg-white p-2 rounded-lg shadow-sm border border-gray-100 shrink-0"><BarChart class="w-6 h-6 text-blue-500"/></div>
                                <div>
                                    <h4 class="font-bold text-gray-800 text-lg mb-1">Kartu Statistik & Grafik</h4>
                                    <p class="text-gray-600 leading-relaxed">Melihat total keseluruhan modul, siswa, kelas. Anda juga bisa melihat grafik nilai rata-rata untuk mengetahui seberapa baik performa belajar siswa secara umum.</p>
                                </div>
                            </div>
                            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-5 flex gap-4 items-start">
                                <div class="bg-white p-2 rounded-lg shadow-sm border border-gray-100 shrink-0"><Users class="w-6 h-6 text-orange-500"/></div>
                                <div>
                                    <h4 class="font-bold text-gray-800 text-lg mb-1">Aktivitas Terbaru</h4>
                                    <p class="text-gray-600 leading-relaxed">Melihat siapa siswa yang baru saja mendaftar atau baru saja menyelesaikan ujian (kuis) beserta skornya secara langsung (real-time).</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. TEMPLATES -->
                    <div v-show="activeTab === 'templates'">
                        <div class="flex items-center gap-3 mb-8 border-b border-gray-100 pb-4">
                            <div class="p-2 bg-pink-100 rounded-xl"><Palette class="w-6 h-6 text-pink-600" /></div>
                            <h2 class="text-2xl font-bold text-gray-800">Tutorial: Template Desain</h2>
                        </div>
                        
                        <p class="text-gray-600 mb-6 text-lg">Siapkan gambar latar belakang (background) dan karakter pendamping (maskot) di sini sebelum Anda membuat modul pembelajaran.</p>
                        
                        <div class="space-y-6">
                            <!-- Step 1 -->
                            <div class="flex gap-4">
                                <div class="w-10 h-10 shrink-0 bg-pink-100 text-pink-600 rounded-full flex items-center justify-center font-bold text-lg border-2 border-pink-200">1</div>
                                <div class="flex-1">
                                    <h4 class="text-xl font-bold text-gray-800 mb-2">Mengunggah Background</h4>
                                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 text-gray-600 space-y-3">
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-pink-500 shrink-0 mt-1"/> <div>Klik tombol <span class="bg-blue-500 text-white px-2 py-0.5 rounded text-sm font-bold">Tambah Background</span></div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-pink-500 shrink-0 mt-1"/> <div>Beri nama latar (contoh: "Pemandangan Sungai").</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-pink-500 shrink-0 mt-1"/> <div>Pilih file gambar dari komputer Anda.</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-pink-500 shrink-0 mt-1"/> <div>Klik <span class="font-bold text-blue-600">Simpan</span>.</div></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Step 2 -->
                            <div class="flex gap-4">
                                <div class="w-10 h-10 shrink-0 bg-pink-100 text-pink-600 rounded-full flex items-center justify-center font-bold text-lg border-2 border-pink-200">2</div>
                                <div class="flex-1">
                                    <h4 class="text-xl font-bold text-gray-800 mb-2">Mengunggah Maskot</h4>
                                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 text-gray-600 space-y-3">
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-pink-500 shrink-0 mt-1"/> <div>Pindah ke Tab <span class="font-bold text-gray-800 border-b-2 border-gray-800">Maskot</span> di bagian atas tabel.</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-pink-500 shrink-0 mt-1"/> <div>Klik <span class="bg-blue-500 text-white px-2 py-0.5 rounded text-sm font-bold">Tambah Maskot</span>.</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-pink-500 shrink-0 mt-1"/> <div>Unggah gambar. Sangat disarankan gambar <strong>PNG Transparan</strong> tanpa latar putih.</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-pink-500 shrink-0 mt-1"/> <div>Klik <span class="font-bold text-blue-600">Simpan</span>.</div></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. MODULES (PALING PENTING) -->
                    <div v-show="activeTab === 'modules'">
                        <div class="flex items-center gap-3 mb-8 border-b border-gray-100 pb-4">
                            <div class="p-2 bg-purple-100 rounded-xl"><GraduationCap class="w-6 h-6 text-purple-600" /></div>
                            <h2 class="text-2xl font-bold text-gray-800">Tutorial: Modul Pembelajaran</h2>
                        </div>
                        
                        <div class="bg-purple-50 border border-purple-100 rounded-2xl p-4 mb-8 flex items-start gap-3">
                            <Info class="w-5 h-5 text-purple-600 shrink-0 mt-0.5"/>
                            <p class="text-purple-800 text-sm">Ini adalah menu inti pembuatan pelajaran. Prosesnya berurutan: <br/><strong>Bikin Modul &rarr; Isi Pretest &rarr; Bikin Misi &rarr; Isi Materi &rarr; Atur Animasi Simulasi.</strong></p>
                        </div>

                        <div class="space-y-8">
                            <!-- Step 1 -->
                            <div class="flex gap-4">
                                <div class="w-10 h-10 shrink-0 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center font-bold text-lg border-2 border-purple-200">1</div>
                                <div class="flex-1">
                                    <h4 class="text-xl font-bold text-gray-800 mb-2">Membuat Modul Utama</h4>
                                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 text-gray-600 space-y-3">
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-purple-400 shrink-0 mt-1"/> <div>Klik tombol <span class="bg-blue-500 text-white px-2 py-0.5 rounded text-sm font-bold">Tambah Modul</span>.</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-purple-400 shrink-0 mt-1"/> <div>Ketik Judul Tema (contoh: "Kelestarian Sungai").</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-purple-400 shrink-0 mt-1"/> <div>Pilih Background dan Maskot (yang dibuat di menu Template).</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-purple-400 shrink-0 mt-1"/> <div>Klik Simpan.</div></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2 -->
                            <div class="flex gap-4">
                                <div class="w-10 h-10 shrink-0 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center font-bold text-lg border-2 border-purple-200">2</div>
                                <div class="flex-1">
                                    <h4 class="text-xl font-bold text-gray-800 mb-2">Menyiapkan Pretest / Posttest</h4>
                                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 text-gray-600 space-y-3">
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-purple-400 shrink-0 mt-1"/> <div>Klik ikon <span class="text-blue-500 font-bold bg-blue-50 px-2 py-1 rounded">Mata (Detail)</span> di baris modul tersebut.</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-purple-400 shrink-0 mt-1"/> <div>Di bagian paling atas, klik <span class="font-bold border border-gray-300 px-2 rounded">Kelola Soal</span> pada kartu Pretest.</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-purple-400 shrink-0 mt-1"/> <div>Klik <span class="font-bold">Tambah Pertanyaan</span>. Anda bisa memilih Pilihan Ganda atau Jawaban Singkat.</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-purple-400 shrink-0 mt-1"/> <div><span class="text-sm bg-yellow-100 px-2 rounded">Catatan:</span> Untuk jawaban singkat, tuliskan <strong>Kata Kunci Benar</strong> (misal: "sampah,plastik").</div></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3 -->
                            <div class="flex gap-4">
                                <div class="w-10 h-10 shrink-0 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center font-bold text-lg border-2 border-purple-200">3</div>
                                <div class="flex-1">
                                    <h4 class="text-xl font-bold text-gray-800 mb-2">Membuat Misi (Sub-Tema)</h4>
                                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 text-gray-600 space-y-3">
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-purple-400 shrink-0 mt-1"/> <div>Kembali ke halaman Detail Modul, gulir/scroll ke bawah ke bagian <strong>Daftar Misi</strong>.</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-purple-400 shrink-0 mt-1"/> <div>Klik tombol <span class="bg-blue-500 text-white px-2 py-0.5 rounded text-sm font-bold">Tambah Misi Baru</span>. Isi judul dan urutannya.</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-purple-400 shrink-0 mt-1"/> <div>Setelah tersimpan, klik ikon <span class="text-blue-500 font-bold bg-blue-50 px-2 py-1 rounded">Mata (Detail)</span> pada misi tersebut.</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-purple-400 shrink-0 mt-1"/> <div>Klik <span class="bg-green-500 text-white px-2 py-0.5 rounded text-sm font-bold">Tambah Materi</span> untuk mengetik materi pelajaran atau menyisipkan <strong>Link YouTube</strong>.</div></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 4 -->
                            <div class="flex gap-4">
                                <div class="w-10 h-10 shrink-0 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center font-bold text-lg border-2 border-purple-200">4</div>
                                <div class="flex-1">
                                    <h4 class="text-xl font-bold text-gray-800 mb-2 flex items-center gap-2">Konfigurasi Simulasi <span class="bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded-lg">Fitur Spesial</span></h4>
                                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-5 text-gray-600">
                                        <p class="mb-4 text-sm bg-white p-3 rounded-lg border border-gray-100">Fitur ini mengubah pengaturan Anda menjadi permainan animasi di layar HP/Laptop siswa.</p>
                                        <p class="mb-3 font-bold text-gray-800">Langkahnya:</p>
                                        <div class="space-y-3 mb-4">
                                            <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-purple-400 shrink-0 mt-1"/> <div>Di halaman Detail Misi, temukan dan klik tombol <span class="bg-cyan-500 text-white px-2 py-0.5 rounded text-sm font-bold">Konfigurasi Simulasi</span>.</div></div>
                                            <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-purple-400 shrink-0 mt-1"/> <div>Anda akan melihat 4 pilihan tab/menu permainan:</div></div>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <!-- Slider -->
                                            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                                                <h5 class="font-bold text-blue-600 flex items-center gap-2 mb-2 text-base"><Settings class="w-5 h-5"/> Slider (Perubahan Kondisi)</h5>
                                                <p class="text-sm text-gray-600 mb-3 leading-relaxed"><strong>Fungsi:</strong> Menunjukkan proses perubahan atau tahapan suatu kejadian. Siswa dapat menggeser tuas (*slider*) ke kanan/kiri untuk melihat gambar yang berubah secara interaktif.</p>
                                                <div class="bg-blue-50 p-2.5 rounded-lg border border-blue-100 text-sm text-blue-800 leading-relaxed mb-3">
                                                    <strong>💡 Contoh:</strong><br/>
                                                    Level 1 (Lingkungan Bersih) &rarr; Level 2 (Banyak Sampah) &rarr; Level 3 (Banjir Bandang).
                                                </div>
                                                <p class="text-sm text-gray-600 leading-relaxed">
                                                    <strong>Cara Membuat:</strong><br/>
                                                    1. Klik tombol <strong>Tambah Level Baru</strong>.<br/>
                                                    2. Isi <strong>Nama Level</strong> (misal: Tahap 1) dan unggah Gambar-nya.<br/>
                                                    3. Buat minimal 2 level agar tuas (*slider*) bisa berfungsi.
                                                </p>
                                            </div>

                                            <!-- Perbandingan -->
                                            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                                                <h5 class="font-bold text-orange-600 flex items-center gap-2 mb-2 text-base"><FileText class="w-5 h-5"/> Perbandingan</h5>
                                                <p class="text-sm text-gray-600 mb-3 leading-relaxed"><strong>Fungsi:</strong> Membandingkan dua keadaan yang berbeda secara berdampingan (kiri dan kanan), sehingga siswa bisa langsung menganalisis perbedaannya.</p>
                                                <div class="bg-orange-50 p-2.5 rounded-lg border border-orange-100 text-sm text-orange-800 leading-relaxed mb-3">
                                                    <strong>💡 Contoh:</strong><br/>
                                                    Sisi Kiri (Gambar Paru-paru Perokok) <strong>vs</strong> Sisi Kanan (Gambar Paru-paru Sehat).
                                                </div>
                                                <p class="text-sm text-gray-600 leading-relaxed">
                                                    <strong>Cara Membuat:</strong><br/>
                                                    1. Klik tombol <strong>Tambah Perbandingan</strong>.<br/>
                                                    2. Unggah gambar dan isi teks untuk <strong>Sisi Kiri</strong>.<br/>
                                                    3. Unggah gambar dan isi teks untuk <strong>Sisi Kanan</strong>.<br/>
                                                    4. (Opsional) Ketikkan teks kesimpulan perbandingannya.
                                                </p>
                                            </div>

                                            <!-- Objek Klik -->
                                            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                                                <h5 class="font-bold text-green-600 flex items-center gap-2 mb-2 text-base"><MousePointerClick class="w-5 h-5"/> Objek Klik</h5>
                                                <p class="text-sm text-gray-600 mb-3 leading-relaxed"><strong>Fungsi:</strong> Melatih ketelitian siswa mencari benda tersembunyi. Saat benda diklik, akan muncul penjelasan apakah benda itu berdampak Positif/Negatif.</p>
                                                <div class="bg-green-50 p-2.5 rounded-lg border border-green-100 text-sm text-green-800 leading-relaxed mb-3">
                                                    <strong>💡 Contoh:</strong><br/>
                                                    Meminta siswa mencari "Genangan Air" (Sarang Nyamuk) pada gambar halaman rumah.
                                                </div>
                                                <p class="text-sm text-gray-600 leading-relaxed">
                                                    <strong>Cara Membuat:</strong><br/>
                                                    1. Klik <strong>Tambah Objek Baru</strong>.<br/>
                                                    2. Isi Nama Benda dan pilih dampaknya (Positif/Negatif).<br/>
                                                    3. Atur <strong>Posisi X</strong> (Kiri-Kanan) dan <strong>Posisi Y</strong> (Atas-Bawah) dengan persentase 0 - 100.<br/>
                                                    4. Unggah gambar benda (sangat disarankan gambar transparan/PNG).
                                                </p>
                                            </div>

                                            <!-- Studi Kasus -->
                                            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                                                <h5 class="font-bold text-purple-600 flex items-center gap-2 mb-2 text-base"><PlayCircle class="w-5 h-5"/> Studi Kasus</h5>
                                                <p class="text-sm text-gray-600 mb-3 leading-relaxed"><strong>Fungsi:</strong> Memberikan simulasi pengambilan keputusan. Anda mengetik cerita, dan siswa harus memilih opsi A/B/C lalu melihat dampaknya.</p>
                                                <div class="bg-purple-50 p-2.5 rounded-lg border border-purple-100 text-sm text-purple-800 leading-relaxed mb-3">
                                                    <strong>💡 Contoh Kasus:</strong> "Temanmu membuang sampah sembarangan".<br/>
                                                    Pilihan A (Dibiarkan saja), Pilihan B (Ditegur & Diajak membuang ke tempatnya).
                                                </div>
                                                <p class="text-sm text-gray-600 leading-relaxed">
                                                    <strong>Cara Membuat:</strong><br/>
                                                    1. Klik <strong>Tambah Skenario Kasus</strong>.<br/>
                                                    2. Ketikkan Cerita/Konteks Kasus dan unggah gambarnya.<br/>
                                                    3. Klik <strong>Tambah Opsi</strong> untuk membuat pilihan (A, B, C, dst) beserta jawaban/dampaknya.<br/>
                                                    4. Tentukan huruf label mana yang merupakan opsi paling benar.
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-4 pt-4 border-t border-gray-200 flex items-center gap-2 text-sm text-red-600 font-bold">
                                            <Info class="w-4 h-4"/> Jangan lupa selalu klik "Simpan" di setiap tab setelah melakukan perubahan!
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- 4. CLASSES -->
                    <div v-show="activeTab === 'classes'">
                        <div class="flex items-center gap-3 mb-8 border-b border-gray-100 pb-4">
                            <div class="p-2 bg-green-100 rounded-xl"><BookOpen class="w-6 h-6 text-green-600" /></div>
                            <h2 class="text-2xl font-bold text-gray-800">Tutorial: Manajemen Kelas</h2>
                        </div>
                        
                        <p class="text-gray-600 mb-6 text-lg">Buatlah kelas-kelas terlebih dahulu di sini sebelum Anda mendaftarkan akun siswa.</p>
                        
                        <div class="space-y-6">
                            <div class="flex gap-4">
                                <div class="w-10 h-10 shrink-0 bg-green-100 text-green-600 rounded-full flex items-center justify-center font-bold text-lg border-2 border-green-200">1</div>
                                <div class="flex-1">
                                    <h4 class="text-xl font-bold text-gray-800 mb-2">Cara Menambah Kelas</h4>
                                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 text-gray-600 space-y-3">
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-green-500 shrink-0 mt-1"/> <div>Klik tombol <span class="bg-blue-500 text-white px-2 py-0.5 rounded text-sm font-bold">Tambah Kelas</span> di pojok kanan atas.</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-green-500 shrink-0 mt-1"/> <div>Ketikkan Nama Kelas (Contoh: "Kelas 5A" atau "SDN 1 Sukamaju").</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-green-500 shrink-0 mt-1"/> <div>Ketikkan Deskripsi (opsional, boleh dikosongkan).</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-green-500 shrink-0 mt-1"/> <div>Klik <span class="font-bold text-blue-600">Simpan</span>.</div></div>
                                    </div>
                                    <div class="mt-4 bg-yellow-50 p-3 rounded-xl border border-yellow-100 text-sm text-yellow-800">
                                        <strong>Tips:</strong> Disarankan untuk mengedit nama kelas saja daripada menghapusnya (jika salah ketik). Menghapus kelas akan berdampak pada profil siswa yang ada di dalamnya.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 5. USERS -->
                    <div v-show="activeTab === 'users'">
                        <div class="flex items-center gap-3 mb-8 border-b border-gray-100 pb-4">
                            <div class="p-2 bg-orange-100 rounded-xl"><Users class="w-6 h-6 text-orange-600" /></div>
                            <h2 class="text-2xl font-bold text-gray-800">Tutorial: Manajemen Pengguna</h2>
                        </div>
                        
                        <p class="text-gray-600 mb-6 text-lg">Tempat untuk mendaftarkan akun untuk Guru pendamping lain atau untuk murid-murid Anda.</p>
                        
                        <div class="space-y-6">
                            <div class="flex gap-4">
                                <div class="w-10 h-10 shrink-0 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center font-bold text-lg border-2 border-orange-200">1</div>
                                <div class="flex-1">
                                    <h4 class="text-xl font-bold text-gray-800 mb-2">Mendaftarkan Siswa Baru</h4>
                                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 text-gray-600 space-y-3">
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-orange-500 shrink-0 mt-1"/> <div>Klik <span class="bg-blue-500 text-white px-2 py-0.5 rounded text-sm font-bold">Tambah Pengguna</span>.</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-orange-500 shrink-0 mt-1"/> <div>Masukkan <strong>Nama Lengkap</strong> dan <strong>Email</strong> (Boleh email buatan, misal: andi@kelas5.com).</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-orange-500 shrink-0 mt-1"/> <div>Ketik <strong>Password</strong> sementara. (Catat dan berikan ke siswa tersebut).</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-orange-500 shrink-0 mt-1"/> <div>Pada menu Role (Peran), pilih <strong>"Siswa"</strong>.</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-orange-500 shrink-0 mt-1"/> <div>Akan muncul menu dropdown kelas, <strong>Pilih Kelas</strong> siswa tersebut.</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-orange-500 shrink-0 mt-1"/> <div>Klik Simpan.</div></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex gap-4">
                                <div class="w-10 h-10 shrink-0 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center font-bold text-lg border-2 border-orange-200">2</div>
                                <div class="flex-1">
                                    <h4 class="text-xl font-bold text-gray-800 mb-2">Import Data Siswa Masal (Excel)</h4>
                                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 text-gray-600 space-y-3">
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-orange-500 shrink-0 mt-1"/> <div>Klik tombol <span class="bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded text-sm font-bold">Import Excel</span>.</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-orange-500 shrink-0 mt-1"/> <div>Klik <strong>Unduh Template Excel</strong>, lalu isi kolom Nama dan Email (opsional) di file tersebut.</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-orange-500 shrink-0 mt-1"/> <div>Pilih kelas tujuan di menu pilihan kelas.</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-orange-500 shrink-0 mt-1"/> <div>Unggah file Excel yang sudah diisi, lalu klik <strong>Mulai Import</strong>. Username akan otomatis dibuatkan dari nama depan siswa!</div></div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="w-10 h-10 shrink-0 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center font-bold text-lg border-2 border-orange-200">3</div>
                                <div class="flex-1">
                                    <h4 class="text-xl font-bold text-gray-800 mb-2">Jika Siswa Lupa Password</h4>
                                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 text-gray-600 space-y-3">
                                        <p class="m-0 leading-relaxed">Cukup cari nama siswa di tabel pencarian, klik tombol <span class="font-bold text-yellow-600 bg-yellow-50 px-2 py-1 rounded">Edit (Ikon Pensil)</span>, lalu ketikkan password baru di kolom Password, lalu Simpan. Sangat mudah!</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 6. REPORTS -->
                    <div v-show="activeTab === 'reports'">
                        <div class="flex items-center gap-3 mb-8 border-b border-gray-100 pb-4">
                            <div class="p-2 bg-teal-100 rounded-xl"><BarChart class="w-6 h-6 text-teal-600" /></div>
                            <h2 class="text-2xl font-bold text-gray-800">Tutorial: Laporan & Riwayat</h2>
                        </div>
                        
                        <p class="text-gray-600 mb-6 text-lg">Lihat hasil ujian, pantau perkembangan belajar, dan unduh laporan nilai siswa dalam bentuk Microsoft Excel di sini.</p>
                        
                        <div class="space-y-6">
                            <div class="flex gap-4">
                                <div class="w-10 h-10 shrink-0 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center font-bold text-lg border-2 border-teal-200">1</div>
                                <div class="flex-1">
                                    <h4 class="text-xl font-bold text-gray-800 mb-2">Melihat Nilai Per Modul</h4>
                                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 text-gray-600 space-y-3">
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-teal-500 shrink-0 mt-1"/> <div>Di layar awal, klik tombol <span class="bg-white border border-gray-300 font-bold px-2 py-0.5 rounded text-sm">Lihat Riwayat</span> pada modul yang diinginkan.</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-teal-500 shrink-0 mt-1"/> <div>Anda akan melihat tabel berisi nama siswa, rata-rata skor mereka, dan persentase seberapa jauh mereka telah belajar.</div></div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="w-10 h-10 shrink-0 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center font-bold text-lg border-2 border-teal-200">2</div>
                                <div class="flex-1">
                                    <h4 class="text-xl font-bold text-gray-800 mb-2">Mengunduh ke Excel (Export XLSX)</h4>
                                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 text-gray-600 space-y-3">
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-teal-500 shrink-0 mt-1"/> <div>Buka halaman Lihat Riwayat modul.</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-teal-500 shrink-0 mt-1"/> <div>Di kanan atas tabel, klik tombol <span class="bg-green-500 text-white font-bold px-2 py-0.5 rounded text-sm inline-flex items-center gap-1"><Download class="w-3 h-3"/> Export Excel</span>.</div></div>
                                        <div class="flex items-start gap-3 leading-relaxed"><ArrowRight class="w-4 h-4 text-teal-500 shrink-0 mt-1"/> <div>File akan otomatis terunduh. Laporan nilai ini sudah diformat lengkap dan bisa langsung dicetak!</div></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.4s ease-out forwards;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
