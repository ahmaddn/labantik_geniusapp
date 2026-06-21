<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch } from "vue";
import { usePage, useForm, router } from "@inertiajs/vue3";
import ConfirmDialog from "@/Components/UI/ConfirmDialog.vue";
import Toast from "@/Components/UI/Toast.vue";
import Button from "@/Components/UI/Button.vue";
import FileUpload from "@/Components/UI/Forms/FileUpload.vue";
import {
    Palette,
    Volume2,
    Images,
    Star,
    Trash2,
    ArrowLeft,
    Plus,
    Music,
} from "lucide-vue-next";

const props = defineProps({
    template: Object,
});

const page = usePage();
const successMessage = ref("");
const showSuccess = ref(false);
const toastType = ref("success");
const showDeleteBgDialog = ref(false);
const showDeleteMascotDialog = ref(false);
const selectedBgId = ref(null);
const selectedMascotId = ref(null);

// Form untuk tambah background/mascot baru
const bgForm = useForm({ backgrounds: [] });
const mascotForm = useForm({ mascots: [] });
const previewBgs = ref([]);
const previewMascots = ref([]);

const showToast = (message, type = "success") => {
    successMessage.value = message;
    toastType.value = type;
    showSuccess.value = true;
    setTimeout(() => (showSuccess.value = false), 2500);
};

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) showToast(flash.success, "success");
        if (flash?.error) showToast(flash.error, "error");
    },
);

const goBack = () => router.visit(route("admin.templates.index"));

// Hapus background
const confirmDeleteBg = (id) => {
    selectedBgId.value = id;
    showDeleteBgDialog.value = true;
};

const deleteBackground = () => {
    useForm({}).delete(
        route("admin.templates.backgrounds.destroy", selectedBgId.value),
        {
            onSuccess: () => {
                showDeleteBgDialog.value = false;
            },
            onError: () => showToast("Gagal menghapus background.", "error"),
        },
    );
};

// Hapus mascot
const confirmDeleteMascot = (id) => {
    selectedMascotId.value = id;
    showDeleteMascotDialog.value = true;
};

const deleteMascot = () => {
    useForm({}).delete(
        route("admin.templates.mascots.destroy", selectedMascotId.value),
        {
            onSuccess: () => {
                showDeleteMascotDialog.value = false;
            },
            onError: () => showToast("Gagal menghapus maskot.", "error"),
        },
    );
};

// Upload background baru
const handleBgUpload = (event) => {
    const files = Array.from(event.target.files);
    files.forEach((file) => {
        previewBgs.value.push({
            id: Date.now() + Math.random(),
            name: file.name,
        });
        bgForm.backgrounds.push(file);
    });
};

const uploadBackgrounds = () => {
    bgForm
        .transform((data) => ({ ...data, _method: "PUT" }))
        .post(route("admin.templates.update", props.template.id), {
            forceFormData: true,
            onSuccess: () => {
                previewBgs.value = [];
                bgForm.backgrounds = [];
            },
            onError: (e) => showToast(Object.values(e)[0], "error"),
        });
};

// Upload mascot baru
const handleMascotUpload = (event) => {
    const files = Array.from(event.target.files);
    files.forEach((file) => {
        previewMascots.value.push({
            id: Date.now() + Math.random(),
            name: file.name,
        });
        mascotForm.mascots.push(file);
    });
};

const uploadMascots = () => {
    mascotForm
        .transform((data) => ({ ...data, _method: "PUT" }))
        .post(route("admin.templates.update", props.template.id), {
            forceFormData: true,
            onSuccess: () => {
                previewMascots.value = [];
                mascotForm.mascots = [];
            },
            onError: (e) => showToast(Object.values(e)[0], "error"),
        });
};

// Helper ambil nama file dari path
const filename = (path) => path?.split("/").pop() ?? "-";

// Helper storage URL
const storageUrl = (path) => `/storage/${path}`;
</script>

<template>
    <AppLayout>
        <!-- Hero Header Area -->
        <div class="relative w-full h-48 md:h-64 bg-slate-800 rounded-b-3xl overflow-hidden shadow-lg mb-8">
            <!-- Cover Background -->
            <img v-if="template.backgrounds && template.backgrounds.length > 0" 
                 :src="storageUrl(template.backgrounds[0].image)" 
                 class="absolute inset-0 w-full h-full object-cover opacity-60" />
            <div v-else class="absolute inset-0 bg-gradient-to-r from-blue-600 to-indigo-700 opacity-80"></div>
            
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent"></div>

            <!-- Header Content -->
            <div class="absolute bottom-0 left-0 right-0 p-6 md:p-8 flex items-end justify-between">
                <div class="flex items-center gap-4">
                    <button @click="goBack" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm p-3 rounded-2xl transition-all border border-white/30">
                        <ArrowLeft class="text-white w-6 h-6" />
                    </button>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <span class="bg-blue-500 text-white text-xs px-3 py-1 rounded-full font-bold uppercase tracking-wider">Template</span>
                            <span class="text-white/80 text-sm"><Images class="w-4 h-4 inline mr-1"/> {{ template.backgrounds.length }} BG &middot; <Star class="w-4 h-4 inline mr-1"/> {{ template.mascots.length }} Maskot</span>
                        </div>
                        <h1 class="text-3xl md:text-4xl font-heading font-black text-white drop-shadow-md">
                            {{ template.name }}
                        </h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="px-4 md:px-8 max-w-7xl mx-auto pb-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- Left Column (Backsound & Backgrounds) -->
                <div class="lg:col-span-7 space-y-8">
                    
                    <!-- Backsound Section -->
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 lg:p-8 relative overflow-hidden group hover:shadow-md transition-shadow">
                        <div class="absolute top-0 right-0 p-8 opacity-5">
                            <Volume2 class="w-32 h-32" />
                        </div>
                        
                        <div class="flex items-center gap-3 mb-6 relative z-10">
                            <div class="bg-indigo-50 p-2.5 rounded-xl text-indigo-600">
                                <Volume2 class="w-6 h-6" />
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">Musik Latar</h2>
                        </div>

                        <div class="relative z-10">
                            <div v-if="template.backsound" class="bg-gray-50 p-5 rounded-2xl border border-gray-100">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="bg-indigo-100 p-2 rounded-full">
                                        <Music class="text-indigo-600 w-4 h-4" />
                                    </div>
                                    <span class="text-sm font-semibold text-gray-700 truncate flex-1">
                                        {{ filename(template.backsound) }}
                                    </span>
                                </div>
                                <audio controls class="w-full h-10 outline-none">
                                    <source :src="storageUrl(template.backsound)" />
                                </audio>
                            </div>
                            <div v-else class="flex flex-col items-center justify-center py-10 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                                <Volume2 class="w-12 h-12 text-gray-300 mb-3" />
                                <p class="text-sm font-medium text-gray-500">Belum ada musik latar</p>
                                <p class="text-xs text-gray-400 mt-1">Tambahkan lewat form edit template</p>
                            </div>
                        </div>
                    </div>

                    <!-- Backgrounds Section -->
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 lg:p-8">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="bg-emerald-50 p-2.5 rounded-xl text-emerald-600">
                                    <Images class="w-6 h-6" />
                                </div>
                                <h2 class="text-xl font-bold text-gray-800">Latar Belakang</h2>
                            </div>
                        </div>

                        <!-- Upload New BG -->
                        <div class="mb-8 p-5 bg-emerald-50/50 rounded-2xl border border-emerald-100 border-dashed">
                            <FileUpload
                                label="Unggah Gambar Latar (Bisa lebih dari 1)"
                                accept="image/*"
                                :multiple="true"
                                button-color="emerald"
                                @change="handleBgUpload"
                            />
                            <p class="text-xs text-emerald-600 mt-3 flex items-center"><Star class="w-3 h-3 mr-1"/> Rekomendasi resolusi: 16:9 (contoh: 1280x720 px)</p>
                            
                            <div v-if="previewBgs.length > 0" class="mt-4 p-4 bg-white rounded-xl border border-emerald-100 shadow-sm">
                                <p class="text-xs font-bold text-gray-500 mb-3 uppercase tracking-wide">Antrean Unggah:</p>
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <span v-for="bg in previewBgs" :key="bg.id" class="text-xs font-medium text-emerald-700 bg-emerald-100 px-3 py-1.5 rounded-lg border border-emerald-200 truncate max-w-xs">
                                        {{ bg.name }}
                                    </span>
                                </div>
                                <Button variant="primary" size="md" class="w-full justify-center !bg-emerald-500 hover:!bg-emerald-600 !border-emerald-600 !border-b-emerald-700" @click="uploadBackgrounds">
                                    <span class="flex items-center"><ArrowLeft class="w-4 h-4 mr-2 rotate-90" /> Simpan Latar Belakang</span>
                                </Button>
                            </div>
                        </div>

                        <!-- BG Grid -->
                        <div v-if="template.backgrounds.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div v-for="bg in template.backgrounds" :key="bg.id" class="group relative rounded-2xl overflow-hidden bg-gray-100 aspect-video shadow-sm border border-gray-200">
                                <img :src="storageUrl(bg.image)" :alt="bg.name" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                                
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                
                                <div class="absolute bottom-0 left-0 right-0 p-4 translate-y-4 group-hover:translate-y-0 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                    <p class="text-white text-sm font-semibold truncate mb-2 drop-shadow-md">{{ bg.name }}</p>
                                    <button @click="confirmDeleteBg(bg.id)" class="w-full py-2 bg-red-500 hover:bg-red-600 text-white text-xs font-bold rounded-xl transition-colors flex items-center justify-center gap-1.5 shadow-md">
                                        <Trash2 class="w-3.5 h-3.5" /> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-10 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                            <Images class="w-10 h-10 text-gray-300 mx-auto mb-3" />
                            <p class="text-gray-500 font-medium">Belum ada gambar latar</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column (Mascots) -->
                <div class="lg:col-span-5">
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 lg:p-8 sticky top-24">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="bg-amber-50 p-2.5 rounded-xl text-amber-500">
                                <Star class="w-6 h-6" />
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">Koleksi Maskot</h2>
                        </div>

                        <!-- Upload New Mascot -->
                        <div class="mb-8 p-5 bg-amber-50/50 rounded-2xl border border-amber-100 border-dashed">
                            <FileUpload
                                label="Unggah Maskot Baru"
                                accept="image/*"
                                :multiple="true"
                                button-color="yellow"
                                @change="handleMascotUpload"
                            />
                            <p class="text-xs text-amber-600 mt-3 flex items-center"><Star class="w-3 h-3 mr-1"/> Resolusi optimal: 1:1 (Minimal 500x500 px)</p>
                            
                            <div v-if="previewMascots.length > 0" class="mt-4 p-4 bg-white rounded-xl border border-amber-100 shadow-sm">
                                <p class="text-xs font-bold text-gray-500 mb-3 uppercase tracking-wide">Antrean Unggah:</p>
                                <div class="flex flex-col gap-2 mb-4">
                                    <span v-for="m in previewMascots" :key="m.id" class="text-xs font-medium text-amber-700 bg-amber-100 px-3 py-1.5 rounded-lg border border-amber-200 truncate">
                                        {{ m.name }}
                                    </span>
                                </div>
                                <Button variant="warning" size="md" class="w-full justify-center shadow-sm" @click="uploadMascots">
                                    <span class="flex items-center"><ArrowLeft class="w-4 h-4 mr-2 rotate-90" /> Simpan Maskot</span>
                                </Button>
                            </div>
                        </div>

                        <!-- Mascot Grid -->
                        <div v-if="template.mascots.length > 0" class="grid grid-cols-2 gap-4">
                            <div v-for="mascot in template.mascots" :key="mascot.id" class="group relative rounded-2xl overflow-hidden bg-gradient-to-b from-amber-50 to-white aspect-square shadow-sm border border-amber-100 flex items-center justify-center p-4 hover:border-amber-300 transition-colors">
                                <img :src="storageUrl(mascot.image)" :alt="mascot.name_pose" class="w-full h-full object-contain filter drop-shadow-md transition-transform duration-500 group-hover:scale-110" />
                                
                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col items-center justify-center p-3">
                                    <p class="text-white text-xs font-bold text-center mb-3 line-clamp-2 drop-shadow-md">{{ mascot.name_pose }}</p>
                                    <button @click="confirmDeleteMascot(mascot.id)" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-bold rounded-xl transition-transform hover:scale-105 shadow-lg flex items-center gap-1">
                                        <Trash2 class="w-3.5 h-3.5" /> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                            <Star class="w-10 h-10 text-gray-300 mx-auto mb-3" />
                            <p class="text-gray-500 font-medium">Belum ada maskot</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Confirm Delete Background -->
        <ConfirmDialog
            :show="showDeleteBgDialog"
            title="Hapus latar belakang ini?"
            message="File gambar akan ikut terhapus dari server secara permanen."
            @confirm="deleteBackground"
            @cancel="showDeleteBgDialog = false"
        />

        <!-- Confirm Delete Mascot -->
        <ConfirmDialog
            :show="showDeleteMascotDialog"
            title="Hapus maskot ini?"
            message="File gambar akan ikut terhapus dari server secara permanen."
            @confirm="deleteMascot"
            @cancel="showDeleteMascotDialog = false"
        />

        <!-- Toast -->
        <Toast
            :show="showSuccess"
            :message="successMessage"
            :type="toastType || 'success'"
            @close="showSuccess = false"
        />
    </AppLayout>
</template>
