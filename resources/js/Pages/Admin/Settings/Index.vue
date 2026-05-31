z<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref } from "vue";
import { useForm, router, usePage } from "@inertiajs/vue3";
import InputField from "@/Components/UI/Forms/InputField.vue";
import TextAreaField from "@/Components/UI/Forms/TextAreaField.vue";
import FileDropzone from "@/Components/UI/Forms/FileDropzone.vue";
import Button from "@/Components/UI/Button.vue";
import Card from "@/Components/UI/Card.vue";
import Toast from "@/Components/UI/Toast.vue";
import { 
    Settings, 
    Save, 
    Image as ImageIcon, 
    X, 
    Trash2, 
    Plus, 
    Music, 
    Upload, 
    Volume2, 
    CheckCircle2, 
    Play, 
    Pause,
    MessageSquare,
    User,
    Power
} from "lucide-vue-next";

const props = defineProps({
    settings: { type: Object, default: () => ({}) },
    bgms: { type: Array, default: () => [] }
});

const page = usePage();
const toastVisible = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

const showToast = (message, type = 'success') => {
    toastMessage.value = message;
    toastType.value = type;
    toastVisible.value = true;
    setTimeout(() => { toastVisible.value = false; }, 3000);
};

const form = useForm({
    _method: 'put',
    platform_name: props.settings?.platform_name || '',
    platform_subtitle: props.settings?.platform_subtitle || '',
    platform_logo: null,
    platform_mascot: null,
    platform_mascot_pose: props.settings?.platform_mascot_pose || '',
    platform_mascot_dialog: props.settings?.platform_mascot_dialog || [],
    bgm_enabled: props.settings?.bgm_enabled ?? true,
});

const logoPreview = ref(props.settings?.platform_logo ? `/storage/${props.settings.platform_logo}` : null);
const mascotPreview = ref(props.settings?.platform_mascot ? `/storage/${props.settings.platform_mascot}` : null);

const addDialog = () => {
    if (!form.platform_mascot_dialog) form.platform_mascot_dialog = [];
    form.platform_mascot_dialog.push('');
};

const removeDialog = (index) => {
    form.platform_mascot_dialog.splice(index, 1);
};

const handleLogoUpload = (file) => {
    form.platform_logo = file;
    if (file) {
        logoPreview.value = URL.createObjectURL(file);
    } else {
        logoPreview.value = props.settings?.platform_logo ? `/storage/${props.settings.platform_logo}` : null;
    }
};

const handleMascotUpload = (file) => {
    form.platform_mascot = file;
    if (file) {
        mascotPreview.value = URL.createObjectURL(file);
    } else {
        mascotPreview.value = props.settings?.platform_mascot ? `/storage/${props.settings.platform_mascot}` : null;
    }
};

const saveSettings = () => {
    form.post(route('admin.settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            showToast('Pengaturan berhasil disimpan!');
            if (form.platform_logo) form.platform_logo = null;
            if (form.platform_mascot) form.platform_mascot = null;
        }
    });
};

const deleteLogo = () => {
    router.delete(route('admin.settings.logo.delete'), { 
        preserveScroll: true,
        onSuccess: () => {
            logoPreview.value = null;
            form.platform_logo = null;
            showToast('Logo berhasil dihapus!');
        }
    });
};

const deleteMascot = () => {
    router.delete(route('admin.settings.mascot.delete'), { 
        preserveScroll: true,
        onSuccess: () => {
            mascotPreview.value = null;
            form.platform_mascot = null;
            showToast('Maskot berhasil dihapus!');
        }
    });
};

// BGM Management
const bgmForm = useForm({
    bgm_file: null,
});

const uploadBgm = () => {
    if(!bgmForm.bgm_file) return;
    bgmForm.post(route('admin.settings.bgm.upload'), {
        preserveScroll: true,
        onSuccess: () => {
            bgmForm.reset();
            showToast('BGM berhasil diunggah!');
        }
    });
};

const deleteBgm = (id) => {
    router.delete(route('admin.settings.bgm.delete', id), { 
        preserveScroll: true,
        onSuccess: () => showToast('BGM berhasil dihapus!')
    });
};

const setActiveBgm = (id) => {
    router.post(route('admin.settings.bgm.active', id), {}, { 
        preserveScroll: true,
        onSuccess: () => showToast('BGM aktif berhasil diatur!')
    });
};

const clearActiveBgm = () => {
    router.post(route('admin.settings.bgm.clear'), {}, { 
        preserveScroll: true,
        onSuccess: () => showToast('BGM aktif berhasil dibatalkan!')
    });
};

const activeAudio = ref(null);
const playingId = ref(null);

const toggleAudio = (bgm) => {
    if (playingId.value === bgm.id) {
        activeAudio.value.pause();
        playingId.value = null;
    } else {
        if (activeAudio.value) activeAudio.value.pause();
        activeAudio.value = new Audio(bgm.url);
        activeAudio.value.play();
        playingId.value = bgm.id;
        activeAudio.value.onended = () => { playingId.value = null; };
    }
};
</script>

<template>
    <AppLayout>
        <div class="p-5 max-w-5xl mx-auto space-y-8">
            <!-- Header -->
            <div class="bg-white rounded-3xl border-4 border-blue-200 shadow-playful p-6 flex items-center gap-4">
                <div class="bg-blue-100 p-3 rounded-2xl border-2 border-blue-300">
                    <Settings class="text-blue-600 w-6 h-6" />
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-heading font-bold text-gray-800">
                        Pengaturan Platform
                    </h1>
                    <p class="text-sm text-gray-500">Atur tampilan dan fitur utama platform GENIUSS</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8">
                <!-- Identitas Platform -->
                <Card border-color="blue" class="p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <ImageIcon class="w-5 h-5 text-blue-500" /> Identitas Platform
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <InputField 
                                label="Nama Platform" 
                                v-model="form.platform_name" 
                                placeholder="GENIUSS Web Education" 
                                border-color="blue"
                            />
                            <InputField 
                                label="Sub-judul Platform" 
                                v-model="form.platform_subtitle" 
                                placeholder="Media Pembelajaran Interaktif" 
                                border-color="blue"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Logo Platform</label>
                            <FileDropzone 
                                accept="image/*" 
                                v-model="form.platform_logo"
                                @update:modelValue="handleLogoUpload"
                            />
                            <div v-if="logoPreview" class="mt-4 relative border-2 border-gray-200 rounded-xl p-2 max-w-[200px] inline-block">
                                <img :src="logoPreview" class="h-24 w-auto object-contain mx-auto" />
                                <button type="button" @click="deleteLogo" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 shadow-md hover:bg-red-600">
                                    <X class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Maskot Platform -->
                <Card border-color="purple" class="p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <User class="w-5 h-5 text-purple-500" /> Maskot Platform
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Gambar Maskot Utama</label>
                            <FileDropzone 
                                accept="image/*" 
                                v-model="form.platform_mascot"
                                @update:modelValue="handleMascotUpload"
                            />
                            <div v-if="mascotPreview" class="mt-4 relative border-2 border-gray-200 rounded-xl p-2 max-w-[200px] inline-block bg-gray-50">
                                <img :src="mascotPreview" class="h-32 w-auto object-contain mx-auto" />
                                <button type="button" @click="deleteMascot" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 shadow-md hover:bg-red-600">
                                    <X class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <InputField 
                                label="Pose / Nama Maskot" 
                                v-model="form.platform_mascot_pose" 
                                placeholder="Misal: Si Budi (Halo)" 
                                border-color="purple"
                            />
                            
                            <div class="mt-4">
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block text-sm font-bold text-gray-700 flex items-center gap-1">
                                        <MessageSquare class="w-4 h-4 text-gray-500" /> Dialog Maskot (Acak)
                                    </label>
                                    <Button variant="light" size="sm" @click="addDialog" class="!px-2 !py-1 text-xs">
                                        <Plus class="w-3 h-3 mr-1" /> Tambah
                                    </Button>
                                </div>
                                <div class="space-y-3">
                                    <div v-for="(dialog, idx) in form.platform_mascot_dialog" :key="idx" class="flex gap-2 items-center">
                                        <InputField 
                                            v-model="form.platform_mascot_dialog[idx]" 
                                            placeholder="Tulis dialog..." 
                                            class="flex-1"
                                        />
                                        <button type="button" @click="removeDialog(idx)" class="text-red-400 hover:text-red-600">
                                            <Trash2 class="w-5 h-5" />
                                        </button>
                                    </div>
                                    <div v-if="!form.platform_mascot_dialog || form.platform_mascot_dialog.length === 0" class="text-sm text-gray-400 italic text-center py-2">
                                        Belum ada dialog maskot
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Audio & BGM -->
                <Card border-color="orange" class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                            <Music class="w-5 h-5 text-orange-500" /> Latar Musik (BGM)
                        </h2>
                        
                        <!-- Toggle Status BGM -->
                        <div class="flex items-center gap-3 bg-orange-50 p-2 px-4 rounded-xl border border-orange-200">
                            <span class="text-sm font-bold text-gray-700 flex items-center gap-1">
                                <Power class="w-4 h-4 text-orange-600" /> Aktifkan BGM
                            </span>
                            <button
                                type="button"
                                @click="form.bgm_enabled = !form.bgm_enabled"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
                                :class="form.bgm_enabled ? 'bg-green-500' : 'bg-gray-300'"
                            >
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                    :class="form.bgm_enabled ? 'translate-x-6' : 'translate-x-1'" />
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div>
                            <h3 class="font-bold text-gray-700 mb-3">Upload BGM Baru</h3>
                            <div class="flex gap-3 items-start">
                                <div class="flex-1">
                                    <FileDropzone 
                                        accept="audio/mpeg,audio/wav,audio/ogg" 
                                        v-model="bgmForm.bgm_file"
                                    />
                                    <p class="text-xs text-gray-400 mt-2 text-center">Format didukung: MP3, WAV, OGG (Maks 10MB)</p>
                                </div>
                                <Button 
                                    variant="primary" 
                                    @click="uploadBgm" 
                                    :disabled="!bgmForm.bgm_file || bgmForm.processing"
                                >
                                    <Upload class="w-4 h-4 mr-2" /> Upload
                                </Button>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="font-bold text-gray-700">Daftar BGM Tersedia</h3>
                                <Button variant="light" size="sm" @click="clearActiveBgm" class="!text-xs text-orange-600 border-orange-200">
                                    Kosongkan BGM Aktif
                                </Button>
                            </div>
                            <div class="space-y-3 max-h-60 overflow-y-auto pr-2 custom-scroll">
                                <div v-for="bgm in bgms" :key="bgm.id" 
                                    class="flex items-center justify-between p-3 rounded-xl border-2 transition-all"
                                    :class="settings.bgm_file === bgm.file_path ? 'border-orange-400 bg-orange-50 shadow-sm' : 'border-gray-100 bg-white hover:border-gray-300'"
                                >
                                    <div class="flex items-center gap-3">
                                        <button @click="toggleAudio(bgm)" class="w-8 h-8 rounded-full flex items-center justify-center bg-gray-100 text-gray-600 hover:bg-orange-100 hover:text-orange-600 transition-colors">
                                            <Pause v-if="playingId === bgm.id" class="w-4 h-4" />
                                            <Play v-else class="w-4 h-4 ml-0.5" />
                                        </button>
                                        <div>
                                            <p class="text-sm font-bold text-gray-800 line-clamp-1">{{ bgm.name }}</p>
                                            <span v-if="settings.bgm_file === bgm.file_path" class="text-xs font-bold text-orange-600 flex items-center gap-1">
                                                <Volume2 class="w-3 h-3" /> Dipakai
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button v-if="settings.bgm_file !== bgm.file_path" @click="setActiveBgm(bgm.id)" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors">
                                            Gunakan
                                        </button>
                                        <button @click="deleteBgm(bgm.id)" class="p-1.5 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                                <div v-if="bgms.length === 0" class="text-center py-6 border-2 border-dashed border-gray-200 rounded-xl">
                                    <p class="text-sm text-gray-400">Belum ada BGM yang diunggah.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Simpan Button -->
                <div class="flex justify-end pt-4 pb-10">
                    <Button variant="primary" size="lg" :icon="Save" @click="saveSettings" :disabled="form.processing">
                        Simpan Semua Pengaturan
                    </Button>
                </div>
            </div>
        </div>

        <Toast :show="toastVisible" :message="toastMessage" :type="toastType" />
    </AppLayout>
</template>

<style scoped>
.custom-scroll::-webkit-scrollbar {
    width: 6px;
}
.custom-scroll::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}
.custom-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.custom-scroll::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
