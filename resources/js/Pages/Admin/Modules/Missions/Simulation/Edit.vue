<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, onMounted } from "vue";
import { router, usePage, useForm } from "@inertiajs/vue3";
import InputField from "@/Components/UI/Forms/InputField.vue";
import TextareaField from "@/Components/UI/Forms/TextAreaField.vue";
import SelectField from "@/Components/UI/Forms/SelectField.vue";
import FileDropzone from "@/Components/UI/Forms/FileDropzone.vue";
import Button from "@/Components/UI/Button.vue";
import Toast from "@/Components/UI/Toast.vue";
import Card from "@/Components/UI/Card.vue";
import {
    ArrowLeft,
    Check,
    Plus,
    Trash2,
    SlidersHorizontal,
    GitCompare,
    MousePointerClick,
    FileText,
    Image as ImageIcon,
    Hash
} from "lucide-vue-next";

const props = defineProps({
    module: { type: Object, required: true },
    mission: { type: Object, required: true },
    configs: { type: Object, required: true },
});

const page = usePage();
const activeTab = ref('slider');

const successMessage = ref("");
const showSuccess = ref(false);
const toastType = ref("success");

const showToast = (message, type = "success") => {
    successMessage.value = message;
    toastType.value = type;
    showSuccess.value = true;
    setTimeout(() => (showSuccess.value = false), 2500);
};

// -------------------------------------------------------------
// SLIDER CONFIG
// -------------------------------------------------------------
const mapSliderLevels = (levels) => {
    return (levels || []).map(level => ({
        id: level.id,
        level_name: level.level_name || '',
        status: level.status || 'aman',
        animation_effect: level.animation_effect || 'none',
        narration: level.narration || '',
        metric_value: level.metric_value || '',
        existing_image: level.image || null,
        image: null,
        _preview: level.image ? `/storage/${level.image}` : null
    }));
};

const sliderForm = useForm({
    _method: 'put',
    config_type: 'slider',
    title: props.configs.slider?.title || '',
    x_axis_label: props.configs.slider?.x_axis_label || '',
    conclusion_text: props.configs.slider?.conclusion_text || '',
    variables: props.configs.slider?.variables || [],
    levels: mapSliderLevels(props.configs.slider?.levels)
});

const addVariable = () => {
    sliderForm.variables.push({
        name: '',
        min_label: '',
        max_label: ''
    });
};

const removeVariable = (index) => {
    sliderForm.variables.splice(index, 1);
};

const addSliderLevel = () => {
    sliderForm.levels.push({
        id: null,
        level_name: '',
        status: 'aman',
        animation_effect: 'none',
        narration: '',
        metric_value: '',
        image: null,
        _preview: null
    });
};

const removeSliderLevel = (index) => {
    sliderForm.levels.splice(index, 1);
};

// Handle file in watchers or inline
// Dihapus fungsi handleSliderImage karena menggunakan v-model dari FileDropzone

const saveSlider = () => {
    if (sliderForm.levels.length === 0) {
        showToast("Tambahkan minimal 1 level terlebih dahulu!", "error");
        return;
    }
    
    // Validasi inputan kosong
    for (const level of sliderForm.levels) {
        if (!level.level_name) {
            showToast("Nama Level tidak boleh kosong!", "error");
            return;
        }
        if (!level.image && !level._preview && typeof level.image !== 'string') {
            showToast(`Gambar untuk level "${level.level_name}" wajib diisi!`, "error");
            return;
        }
    }

    sliderForm.post(route('admin.modules.missions.simulation.update', [props.module.id, props.mission.id]), {
        preserveScroll: true,
        onSuccess: () => showToast("Konfigurasi Slider disimpan!"),
        onError: () => showToast("Gagal menyimpan Slider. Periksa form Anda.", "error")
    });
};

// -------------------------------------------------------------
// COMPARISON CONFIG
// -------------------------------------------------------------
const mapComparisons = (comparisons) => {
    return (comparisons || []).map(comp => ({
        id: comp.id,
        explanation: comp.explanation || '',
        items: (comp.items || []).map(item => ({
            toggle_name: item.toggle_name || '',
            label: item.label || '',
            narration: item.narration || '',
            existing_image: item.image || null,
            image: null,
            _preview: item.image ? `/storage/${item.image}` : null
        }))
    }));
};

const comparisonForm = useForm({
    _method: 'put',
    config_type: 'comparison',
    page_title: props.configs.comparisons?.[0]?.title || '',
    comparisons: mapComparisons(props.configs.comparisons)
});

const addComparison = () => {
    comparisonForm.comparisons.push({
        id: null,
        explanation: '',
        items: []
    });
};

const removeComparison = (index) => {
    comparisonForm.comparisons.splice(index, 1);
};

const addComparisonItem = (compIdx) => {
    comparisonForm.comparisons[compIdx].items.push({
        toggle_name: '',
        label: '',
        narration: '',
        existing_image: null,
        image: null,
        _preview: null
    });
};

const removeComparisonItem = (compIdx, itemIdx) => {
    comparisonForm.comparisons[compIdx].items.splice(itemIdx, 1);
};

const saveComparison = () => {
    if (comparisonForm.comparisons.length === 0) {
        showToast("Tambahkan minimal 1 grup perbandingan terlebih dahulu!", "error");
        return;
    }

    for (const comp of comparisonForm.comparisons) {
        if (!comp.items || comp.items.length === 0) {
            showToast("Setiap perbandingan harus memiliki minimal 1 item (tampilan)!", "error");
            return;
        }
        for (const item of comp.items) {
            if (!item.toggle_name) {
                showToast("Nama Toggle (tombol) tidak boleh kosong!", "error");
                return;
            }
        }
    }

    comparisonForm.post(route('admin.modules.missions.simulation.update', [props.module.id, props.mission.id]), {
        preserveScroll: true,
        onSuccess: () => showToast("Konfigurasi Comparison disimpan!"),
        onError: () => showToast("Gagal menyimpan Comparison.", "error")
    });
};

// -------------------------------------------------------------
// CLICKABLE CONFIG
// -------------------------------------------------------------
const mapClickables = (clickables) => {
    return (clickables || []).map(obj => ({
        id: obj.id,
        name: obj.name || '',
        impact_text: obj.impact_text || '',
        is_positive: obj.is_positive ?? 1,
        existing_image: obj.image || null,
        image: null,
        _preview: obj.image ? `/storage/${obj.image}` : null
    }));
};

const clickableForm = useForm({
    _method: 'put',
    config_type: 'clickable',
    page_title: props.configs.clickable_objects?.[0]?.title || '',
    clickables: mapClickables(props.configs.clickable_objects)
});

const addClickable = () => {
    clickableForm.clickables.push({
        id: null,
        name: '',
        impact_text: '',
        is_positive: 1,
        image: null,
        _preview: null
    });
};

const removeClickable = (index) => {
    clickableForm.clickables.splice(index, 1);
};

// Handle clickable image inline in template

const saveClickable = () => {
    if (clickableForm.clickables.length === 0) {
        showToast("Tambahkan minimal 1 objek terlebih dahulu!", "error");
        return;
    }

    for (const obj of clickableForm.clickables) {
        if (!obj.name) {
            showToast("Nama objek tidak boleh kosong!", "error");
            return;
        }
    }

    clickableForm.post(route('admin.modules.missions.simulation.update', [props.module.id, props.mission.id]), {
        preserveScroll: true,
        onSuccess: () => showToast("Konfigurasi Objek Klik disimpan!"),
        onError: () => showToast("Gagal menyimpan Objek Klik.", "error")
    });
};

// -------------------------------------------------------------
// DECISION CONFIG
// -------------------------------------------------------------
const mapDecisions = (decisions) => {
    return (decisions || []).map(dec => ({
        id: dec.id,
        title: dec.title || '',
        initial_state_title: dec.initial_state_title || '',
        future_state_title: dec.future_state_title || '',
        existing_initial_image: dec.initial_state_image || null,
        initial_state_image: null,
        _preview_initial: dec.initial_state_image ? `/storage/${dec.initial_state_image}` : null,
        character_image: dec.character_image || null,
        options: (dec.options || []).map(opt => ({
            id: opt.id,
            button_label: opt.button_label || '',
            button_color: opt.button_color || 'green',
            feedback_message: opt.feedback_message || '',
            existing_future_image: opt.future_state_image || null,
            future_state_image: null,
            _preview_future: opt.future_state_image ? `/storage/${opt.future_state_image}` : null,
        }))
    }));
};

const decisionForm = useForm({
    _method: 'put',
    config_type: 'decision',
    decisions: mapDecisions(props.configs.decisions)
});

const addDecision = () => {
    decisionForm.decisions.push({
        id: null,
        title: '',
        initial_state_title: '',
        future_state_title: '',
        initial_state_image: null,
        _preview_initial: null,
        character_image: null,
        options: []
    });
};

const removeDecision = (index) => {
    decisionForm.decisions.splice(index, 1);
};

const addDecisionOption = (decIdx) => {
    decisionForm.decisions[decIdx].options.push({
        id: null,
        button_label: '',
        button_color: 'green',
        feedback_message: '',
        future_state_image: null,
        _preview_future: null
    });
};

const removeDecisionOption = (decIdx, optIdx) => {
    decisionForm.decisions[decIdx].options.splice(optIdx, 1);
};

const saveDecision = () => {
    if (decisionForm.decisions.length === 0) {
        showToast("Tambahkan minimal 1 simulasi keputusan terlebih dahulu!", "error");
        return;
    }

    for (const dec of decisionForm.decisions) {
        if (!dec.options || dec.options.length < 2) {
            showToast("Setiap simulasi keputusan harus memiliki minimal 2 opsi!", "error");
            return;
        }
    }

    decisionForm.post(route('admin.modules.missions.simulation.update', [props.module.id, props.mission.id]), {
        preserveScroll: true,
        onSuccess: () => showToast("Konfigurasi Keputusan disimpan!"),
        onError: () => showToast("Gagal menyimpan Keputusan.", "error")
    });
};

</script>

<template>
    <AppLayout>
        <div class="p-5 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-3xl border-4 border-gray-200 shadow-playful p-6 mb-8 flex items-center gap-4">
                <button
                    @click="router.visit(route('admin.modules.missions.show', [module.id, mission.id]))"
                    class="bg-blue-100 p-3 rounded-2xl border-2 border-blue-300 hover:bg-blue-200 transition-all"
                >
                    <ArrowLeft class="text-blue-500 w-5 h-5" />
                </button>
                <div>
                    <h1 class="text-2xl md:text-3xl font-heading font-bold text-gray-800">
                        Konfigurasi Simulasi
                    </h1>
                    <p class="text-sm text-gray-500">Misi: {{ mission.name }}</p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex flex-wrap gap-2 mb-6">
                <button @click="activeTab = 'slider'" :class="['px-5 py-3 rounded-xl font-bold flex items-center gap-2 border-2', activeTab === 'slider' ? 'bg-blue-500 text-white border-blue-600 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50']">
                    <SlidersHorizontal class="w-5 h-5" /> Slider
                </button>
                <button @click="activeTab = 'comparison'" :class="['px-5 py-3 rounded-xl font-bold flex items-center gap-2 border-2', activeTab === 'comparison' ? 'bg-orange-500 text-white border-orange-600 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50']">
                    <GitCompare class="w-5 h-5" /> Perbandingan
                </button>
                <button @click="activeTab = 'clickable'" :class="['px-5 py-3 rounded-xl font-bold flex items-center gap-2 border-2', activeTab === 'clickable' ? 'bg-green-500 text-white border-green-600 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50']">
                    <MousePointerClick class="w-5 h-5" /> Objek Klik
                </button>
                <button @click="activeTab = 'decision'" :class="['px-5 py-3 rounded-xl font-bold flex items-center gap-2 border-2', activeTab === 'decision' ? 'bg-purple-500 text-white border-purple-600 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50']">
                    <Hash class="w-5 h-5" /> Keputusan
                </button>
            </div>

            <div class="bg-white rounded-3xl border-4 border-gray-200 shadow-playful p-6">
                
                <!-- SLIDER TAB -->
                <div v-if="activeTab === 'slider'">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Simulasi Slider</h2>
                    <div class="space-y-4 mb-6">
                        <InputField label="Judul Halaman Simulasi" v-model="sliderForm.title" placeholder="Misal: Simulasi Perubahan Debit Air" />
                        <InputField label="Teks Kesimpulan (Jika siswa benar)" v-model="sliderForm.conclusion_text" placeholder="Misal: Luar biasa! Kamu berhasil menyeimbangkan ekosistem." />
                        
                        <div class="flex justify-between items-center mt-6 mb-2">
                            <h3 class="font-bold text-lg text-gray-700">Variabel Penggeser (Slider)</h3>
                            <Button variant="outline" size="sm" :icon="Plus" @click="addVariable">Tambah Variabel</Button>
                        </div>
                        
                        <div v-if="sliderForm.variables.length === 0" class="text-center py-4 bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl text-gray-500">
                            Belum ada variabel penggeser. Klik "Tambah Variabel" untuk menambahkan.
                        </div>

                        <div v-for="(variable, vIdx) in sliderForm.variables" :key="vIdx" class="p-4 border-2 border-indigo-100 bg-indigo-50/50 rounded-xl relative mb-4">
                            <button @click="removeVariable(vIdx)" class="absolute top-4 right-4 text-red-500 hover:text-red-700" title="Hapus Variabel"><Trash2 class="w-5 h-5"/></button>
                            <h4 class="font-bold text-indigo-800 mb-3">Variabel {{ vIdx + 1 }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <InputField label="Nama Variabel" v-model="variable.name" placeholder="Misal: Intensitas Suhu" />
                                <InputField label="Label Kiri (Minimal)" v-model="variable.min_label" placeholder="Misal: Dingin" />
                                <InputField label="Label Kanan (Maksimal)" v-model="variable.max_label" placeholder="Misal: Panas" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center mb-4 mt-6">
                        <h3 class="font-bold text-lg text-gray-700">Level / Tahapan</h3>
                        <Button variant="primary" size="sm" :icon="Plus" @click="addSliderLevel">Tambah Level</Button>
                    </div>

                    <div class="space-y-6">
                        <div v-for="(level, idx) in sliderForm.levels" :key="idx" class="p-4 border-2 border-blue-100 bg-blue-50/50 rounded-2xl relative">
                            <button @click="removeSliderLevel(idx)" class="absolute top-4 right-4 text-red-500 hover:text-red-700"><Trash2 class="w-5 h-5"/></button>
                            <h4 class="font-bold text-blue-800 mb-4">Level {{ idx + 1 }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <InputField label="Nama Level (contoh: Tahap Awal, Level 1)" v-model="level.level_name" required placeholder="Misal: Level 1" />
                                <InputField label="Keterangan Tambahan Level (opsional)" v-model="level.metric_value" placeholder="Misal: Suhu 30C / Kelembaban 80%" />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Status Level</label>
                                    <select v-model="level.status" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        <option value="aman">Aman / Normal</option>
                                        <option value="waspada">Waspada</option>
                                        <option value="bahaya">Bahaya</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Efek Animasi Overlay</label>
                                    <select v-model="level.animation_effect" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        <option value="none">Tidak ada efek</option>
                                        <option value="rain_light">Gerimis</option>
                                        <option value="rain_heavy">Hujan Deras</option>
                                        <option value="snow">Salju</option>
                                        <option value="bubbles">Gelembung Air</option>
                                        <option value="fire_sparks">Percikan Api</option>
                                        <option value="wind_leaves">Daun Berterbangan</option>
                                        <option value="dust">Polusi / Debu</option>
                                        <option value="sunbeams">Cahaya Cerah (Sunbeams)</option>
                                        <option value="earthquake">Guncangan Layar (Gempa)</option>
                                    </select>
                                </div>
                            </div>
                            <TextareaField label="Narasi" v-model="level.narration" class="mt-4" placeholder="Misal: Pada level ini, matahari bersinar sangat terik..." />
                            
                            <div class="mt-4">
                                <FileDropzone 
                                    label="Gambar Background Level" 
                                    accept="image/*" 
                                    v-model="level.image"
                                    @update:modelValue="(file) => { if(file) level._preview = URL.createObjectURL(file); else level._preview = null; }" 
                                />
                                <div v-if="level._preview || level.existing_image" class="mt-3">
                                    <p class="text-xs text-gray-500 mb-1 font-bold">Preview Gambar:</p>
                                    <img :src="level._preview || `/storage/${level.existing_image}`" class="w-32 h-32 object-cover rounded-xl border-2 border-gray-200 shadow-sm" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <Button variant="primary" :icon="Check" :disabled="sliderForm.processing" @click="saveSlider">Simpan Slider</Button>
                    </div>
                </div>

                <!-- COMPARISON TAB -->
                <div v-if="activeTab === 'comparison'">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h2 class="text-xl font-bold text-gray-800">Simulasi Perbandingan</h2>
                        <Button variant="primary" size="sm" :icon="Plus" @click="addComparison">Tambah Perbandingan</Button>
                    </div>

                    <div class="mb-6">
                        <InputField label="Judul Halaman Simulasi" v-model="comparisonForm.page_title" placeholder="Misal: Perbandingan Kondisi Lingkungan" />
                    </div>

                    <div class="space-y-6">
                        <div v-for="(comp, idx) in comparisonForm.comparisons" :key="idx" class="p-5 border-2 border-orange-100 bg-orange-50/50 rounded-2xl relative">
                            <button @click="removeComparison(idx)" class="absolute top-4 right-4 text-red-500 hover:text-red-700"><Trash2 class="w-5 h-5"/></button>
                            <h4 class="font-bold text-orange-800 mb-4">Grup Perbandingan {{ idx + 1 }}</h4>
                            
                            <TextareaField label="Penjelasan/Kesimpulan Keseluruhan" v-model="comp.explanation" class="mb-4" placeholder="Penjelasan tentang perbandingan ini..." />

                            <div class="flex justify-between items-center mb-4 mt-6">
                                <h5 class="font-bold text-lg text-gray-700">Daftar Item (Tampilan)</h5>
                                <Button variant="outline" size="sm" :icon="Plus" @click="addComparisonItem(idx)">Tambah Tampilan</Button>
                            </div>

                            <div v-if="!comp.items || comp.items.length === 0" class="text-center py-4 bg-white border-2 border-dashed border-gray-200 rounded-xl text-gray-500 mb-4">
                                Belum ada item. Klik "Tambah Tampilan" untuk menambahkan.
                            </div>

                            <div class="space-y-4">
                                <div v-for="(item, itemIdx) in comp.items" :key="itemIdx" class="p-4 border border-gray-200 bg-white rounded-xl relative">
                                    <button @click="removeComparisonItem(idx, itemIdx)" class="absolute top-4 right-4 text-red-500 hover:text-red-700"><Trash2 class="w-4 h-4"/></button>
                                    <h6 class="font-bold text-gray-700 mb-3">Tampilan {{ itemIdx + 1 }}</h6>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <InputField label="Nama Tombol Toggle" v-model="item.toggle_name" placeholder="Misal: Tampilkan Akar" required />
                                        <InputField label="Label Gambar" v-model="item.label" placeholder="Misal: Hutan Lebat" />
                                    </div>
                                    <TextareaField label="Narasi" v-model="item.narration" class="mt-4" placeholder="Misal: Pohon-pohon memiliki akar yang kuat..." />
                                    
                                    <div class="mt-4">
                                        <FileDropzone 
                                            label="Gambar Tampilan" 
                                            accept="image/*" 
                                            v-model="item.image"
                                            @update:modelValue="(file) => { if(file) item._preview = URL.createObjectURL(file); else item._preview = null; }" 
                                        />
                                        <div v-if="item._preview || item.existing_image" class="mt-3">
                                            <p class="text-xs text-gray-500 mb-1 font-bold">Preview:</p>
                                            <img :src="item._preview || `/storage/${item.existing_image}`" class="w-32 h-32 object-cover rounded-xl border-2 border-gray-200 shadow-sm" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <Button variant="primary" :icon="Check" :disabled="comparisonForm.processing" @click="saveComparison">Simpan Perbandingan</Button>
                    </div>
                </div>

                <!-- CLICKABLE TAB -->
                <div v-if="activeTab === 'clickable'">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h2 class="text-xl font-bold text-gray-800">Simulasi Objek Klik</h2>
                        <Button variant="primary" size="sm" :icon="Plus" @click="addClickable">Tambah Objek</Button>
                    </div>

                    <div class="mb-6">
                        <InputField label="Judul Halaman Simulasi" v-model="clickableForm.page_title" placeholder="Misal: Solusi untuk Menyelamatkan Sungai" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="(obj, idx) in clickableForm.clickables" :key="idx" class="p-4 border-2 border-green-100 bg-green-50/50 rounded-2xl relative">
                            <button @click="removeClickable(idx)" class="absolute top-4 right-4 text-red-500 hover:text-red-700"><Trash2 class="w-5 h-5"/></button>
                            <h4 class="font-bold text-green-800 mb-4">Objek {{ idx + 1 }}</h4>
                            
                            <InputField label="Nama Benda" v-model="obj.name" required placeholder="Misal: Sampah Plastik" />
                            
                            <div class="mt-3">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Sifat Objek (Dampak)</label>
                                <select v-model="obj.is_positive" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <option :value="1">Positif / Baik</option>
                                    <option :value="0">Negatif / Buruk</option>
                                </select>
                            </div>
                            <TextareaField label="Teks Penjelasan / Dampak" v-model="obj.impact_text" class="mt-3" placeholder="Misal: Plastik membutuhkan ratusan tahun untuk terurai..." />
                            
                            <div class="mt-3">
                                <FileDropzone 
                                    label="Gambar Objek (PNG)" 
                                    accept="image/png,image/gif" 
                                    v-model="obj.image"
                                    @update:modelValue="(file) => { if(file) obj._preview = URL.createObjectURL(file); else obj._preview = null; }" 
                                />
                                <div v-if="obj._preview || obj.existing_image" class="mt-3">
                                    <p class="text-xs text-gray-500 mb-1 font-bold">Preview:</p>
                                    <img :src="obj._preview || `/storage/${obj.existing_image}`" class="w-16 h-16 object-cover rounded-xl border-2 border-gray-200 shadow-sm" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <Button variant="primary" :icon="Check" :disabled="clickableForm.processing" @click="saveClickable">Simpan Objek</Button>
                    </div>
                </div>

                <!-- DECISION TAB -->
                <div v-if="activeTab === 'decision'">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h2 class="text-xl font-bold text-gray-800">Simulasi Keputusan</h2>
                        <Button variant="primary" size="sm" :icon="Plus" @click="addDecision">Tambah Simulasi</Button>
                    </div>

                    <div class="space-y-6">
                        <div v-for="(dec, idx) in decisionForm.decisions" :key="idx" class="p-5 border-2 border-purple-100 bg-purple-50/50 rounded-2xl relative">
                            <button @click="removeDecision(idx)" class="absolute top-4 right-4 text-red-500 hover:text-red-700"><Trash2 class="w-5 h-5"/></button>
                            <h4 class="font-bold text-purple-800 mb-4">Simulasi Keputusan {{ idx + 1 }}</h4>
                            
                            <InputField label="Judul Simulasi" v-model="dec.title" class="mb-4" placeholder="Misal: Aktivitas Interaktif 3 - Simulasi Keputusan" />

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <InputField label="Judul Status Awal" v-model="dec.initial_state_title" placeholder="Misal: HARI INI" />
                                <InputField label="Judul Status Masa Depan" v-model="dec.future_state_title" placeholder="Misal: 1 BULAN LAGI" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div>
                                    <FileDropzone 
                                        label="Gambar Status Awal (Hari Ini)" 
                                        accept="image/*" 
                                        v-model="dec.initial_state_image"
                                        :previewUrl="dec._preview_initial || (dec.existing_initial_image ? `/storage/${dec.existing_initial_image}` : null)"
                                        @update:modelValue="(file) => { if(file) dec._preview_initial = URL.createObjectURL(file); else { dec._preview_initial = null; dec.existing_initial_image = null; dec.remove_initial_image = true; } }" 
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Maskot dari Template (Opsional)</label>
                                    <select v-model="dec.character_image" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        <option :value="null">Gunakan Maskot Default Geniuss</option>
                                        <option v-for="mascot in module.template?.mascots || []" :key="mascot.id" :value="mascot.image">
                                            {{ mascot.name_pose || 'Pose Maskot' }}
                                        </option>
                                    </select>
                                    <div v-if="dec.character_image" class="mt-3">
                                        <p class="text-xs text-gray-500 mb-1 font-bold">Preview:</p>
                                        <img :src="`/storage/${dec.character_image}`" class="w-24 h-24 object-contain rounded-xl border-2 border-gray-200 shadow-sm" />
                                    </div>
                                    <p class="text-xs text-gray-400 mt-2">Memilih maskot yang sesuai dengan modul ini.</p>
                                </div>
                            </div>

                            <div class="flex justify-between items-center mb-4 mt-6">
                                <h5 class="font-bold text-lg text-gray-700">Daftar Opsi Keputusan</h5>
                                <Button variant="outline" size="sm" :icon="Plus" @click="addDecisionOption(idx)">Tambah Opsi</Button>
                            </div>

                            <div v-if="!dec.options || dec.options.length === 0" class="text-center py-4 bg-white border-2 border-dashed border-gray-200 rounded-xl text-gray-500 mb-4">
                                Belum ada opsi. Klik "Tambah Opsi" untuk menambahkan (minimal 2).
                            </div>

                            <div class="space-y-4">
                                <div v-for="(opt, optIdx) in dec.options" :key="optIdx" class="p-4 border border-gray-200 bg-white rounded-xl relative">
                                    <button @click="removeDecisionOption(idx, optIdx)" class="absolute top-4 right-4 text-red-500 hover:text-red-700"><Trash2 class="w-4 h-4"/></button>
                                    <h6 class="font-bold text-gray-700 mb-3">Opsi {{ optIdx + 1 }}</h6>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <InputField label="Label Tombol" v-model="opt.button_label" placeholder="Misal: Bersihkan Sampah" required />
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-2">Warna Tombol</label>
                                            <select v-model="opt.button_color" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                                <option value="green">Hijau (Sukses/Baik)</option>
                                                <option value="yellow">Kuning (Peringatan/Netral)</option>
                                                <option value="red">Merah (Bahaya/Buruk)</option>
                                                <option value="blue">Biru (Info)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <TextareaField label="Pesan Maskot (Feedback)" v-model="opt.feedback_message" class="mt-4" placeholder="Misal: Ketika manusia bertindak, sistem-sistem membaik." />
                                    
                                    <div class="mt-4">
                                        <FileDropzone 
                                            label="Gambar Hasil (Masa Depan)" 
                                            accept="image/*" 
                                            v-model="opt.future_state_image"
                                            :previewUrl="opt._preview_future || (opt.existing_future_image ? `/storage/${opt.existing_future_image}` : null)"
                                            @update:modelValue="(file) => { if(file) opt._preview_future = URL.createObjectURL(file); else { opt._preview_future = null; opt.existing_future_image = null; opt.remove_future_image = true; } }" 
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <Button variant="primary" :icon="Check" :disabled="decisionForm.processing" @click="saveDecision">Simpan Keputusan</Button>
                    </div>
                </div>
            </div>
        </div>
        <Toast :show="showSuccess" :message="successMessage" :type="toastType" />
    </AppLayout>
</template>
