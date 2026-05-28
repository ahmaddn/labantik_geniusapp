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
    Image as ImageIcon
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
const sliderForm = useForm({
    _method: 'put',
    config_type: 'slider',
    x_axis_label: props.configs.slider?.x_axis_label || '',
    conclusion_text: props.configs.slider?.conclusion_text || '',
    levels: props.configs.slider?.levels || []
});

const addSliderLevel = () => {
    sliderForm.levels.push({
        id: null,
        level_name: '',
        narration: '',
        water_debit: '',
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
const comparisonForm = useForm({
    _method: 'put',
    config_type: 'comparison',
    comparisons: props.configs.comparisons || []
});

const addComparison = () => {
    comparisonForm.comparisons.push({
        id: null,
        left_label: '',
        right_label: '',
        left_narration: '',
        right_narration: '',
        explanation: '',
        left_image: null,
        right_image: null,
        _preview_left: null,
        _preview_right: null
    });
};

const removeComparison = (index) => {
    comparisonForm.comparisons.splice(index, 1);
};

// Handle comparison image inline in template

const saveComparison = () => {
    if (comparisonForm.comparisons.length === 0) {
        showToast("Tambahkan minimal 1 perbandingan terlebih dahulu!", "error");
        return;
    }

    for (const comp of comparisonForm.comparisons) {
        if (!comp.left_label || !comp.right_label) {
            showToast("Label Kiri dan Label Kanan tidak boleh kosong!", "error");
            return;
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
const clickableForm = useForm({
    _method: 'put',
    config_type: 'clickable',
    clickables: props.configs.clickable_objects || []
});

const addClickable = () => {
    clickableForm.clickables.push({
        id: null,
        name: '',
        pos_x: '',
        pos_y: '',
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
// SCENARIO CONFIG
// -------------------------------------------------------------
const scenarioForm = useForm({
    _method: 'put',
    config_type: 'scenario',
    scenarios: props.configs.scenarios || []
});

const addScenario = () => {
    scenarioForm.scenarios.push({
        id: null,
        context: '',
        correct_option: '',
        image: null,
        _preview: null,
        options: []
    });
};

const removeScenario = (index) => {
    scenarioForm.scenarios.splice(index, 1);
};

// Handle scenario image inline in template

const addScenarioOption = (sIndex) => {
    scenarioForm.scenarios[sIndex].options.push({
        id: null,
        label: '',
        text: '',
        feedback: ''
    });
};
const removeScenarioOption = (sIndex, oIndex) => {
    scenarioForm.scenarios[sIndex].options.splice(oIndex, 1);
};

const saveScenario = () => {
    if (scenarioForm.scenarios.length === 0) {
        showToast("Tambahkan minimal 1 skenario terlebih dahulu!", "error");
        return;
    }

    for (const scn of scenarioForm.scenarios) {
        if (!scn.context) {
            showToast("Konteks / Cerita Kasus tidak boleh kosong!", "error");
            return;
        }
        if (scn.options.length === 0) {
            showToast("Setiap kasus harus memiliki minimal 1 opsi pilihan!", "error");
            return;
        }
    }

    scenarioForm.post(route('admin.modules.missions.simulation.update', [props.module.id, props.mission.id]), {
        preserveScroll: true,
        onSuccess: () => showToast("Konfigurasi Skenario disimpan!"),
        onError: (errs) => {
            console.log(errs);
            showToast("Gagal menyimpan Skenario.", "error")
        }
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
                <button @click="activeTab = 'scenario'" :class="['px-5 py-3 rounded-xl font-bold flex items-center gap-2 border-2', activeTab === 'scenario' ? 'bg-purple-500 text-white border-purple-600 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50']">
                    <FileText class="w-5 h-5" /> Studi Kasus
                </button>
            </div>

            <div class="bg-white rounded-3xl border-4 border-gray-200 shadow-playful p-6">
                
                <!-- SLIDER TAB -->
                <div v-if="activeTab === 'slider'">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Simulasi Slider</h2>
                    <div class="space-y-4 mb-6">
                        <InputField label="Teks Kesimpulan (Jika siswa benar)" v-model="sliderForm.conclusion_text" placeholder="Misal: Luar biasa! Kamu berhasil menyeimbangkan ekosistem." />
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
                                <InputField label="Keterangan Tambahan Level (opsional)" v-model="level.water_debit" placeholder="Misal: Suhu Panas / Cuaca Cerah" />
                            </div>
                            <TextareaField label="Narasi" v-model="level.narration" class="mt-4" placeholder="Misal: Pada level ini, matahari bersinar sangat terik..." />
                            
                            <div class="mt-4">
                                <FileDropzone 
                                    label="Gambar Background Level" 
                                    accept="image/*" 
                                    v-model="level.image"
                                    @update:modelValue="(file) => { if(file) level._preview = URL.createObjectURL(file); else level._preview = null; }" 
                                />
                                <div v-if="level._preview || (level.image && typeof level.image === 'string')" class="mt-3">
                                    <p class="text-xs text-gray-500 mb-1 font-bold">Preview Gambar:</p>
                                    <img :src="level._preview || `/storage/${level.image}`" class="w-32 h-32 object-cover rounded-xl border-2 border-gray-200 shadow-sm" />
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

                    <div class="space-y-6">
                        <div v-for="(comp, idx) in comparisonForm.comparisons" :key="idx" class="p-5 border-2 border-orange-100 bg-orange-50/50 rounded-2xl relative">
                            <button @click="removeComparison(idx)" class="absolute top-4 right-4 text-red-500 hover:text-red-700"><Trash2 class="w-5 h-5"/></button>
                            <h4 class="font-bold text-orange-800 mb-4">Item Perbandingan {{ idx + 1 }}</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Kiri -->
                                <div class="space-y-4 border-r-0 md:border-r-2 border-orange-200 pr-0 md:pr-4">
                                    <h5 class="font-bold text-gray-600">Sisi Kiri</h5>
                                    <InputField label="Label Kiri" v-model="comp.left_label" placeholder="Misal: Hutan Gundul" />
                                    <TextareaField label="Narasi Kiri" v-model="comp.left_narration" placeholder="Misal: Pohon-pohon ditebang sembarangan menyebabkan..." />
                                    <div>
                                        <FileDropzone 
                                            label="Gambar Kiri" 
                                            accept="image/*" 
                                            v-model="comp.left_image"
                                            @update:modelValue="(file) => { if(file) comp._preview_left = URL.createObjectURL(file); else comp._preview_left = null; }" 
                                        />
                                        <div v-if="comp._preview_left || (comp.left_image && typeof comp.left_image === 'string')" class="mt-3">
                                            <p class="text-xs text-gray-500 mb-1 font-bold">Preview Kiri:</p>
                                            <img :src="comp._preview_left || `/storage/${comp.left_image}`" class="w-32 h-32 object-cover rounded-xl border-2 border-gray-200 shadow-sm" />
                                        </div>
                                    </div>
                                </div>
                                <!-- Kanan -->
                                <div class="space-y-4">
                                    <h5 class="font-bold text-gray-600">Sisi Kanan</h5>
                                    <InputField label="Label Kanan" v-model="comp.right_label" placeholder="Misal: Hutan Rimbun" />
                                    <TextareaField label="Narasi Kanan" v-model="comp.right_narration" placeholder="Misal: Banyak hewan berlindung di bawah pepohonan..." />
                                    <div>
                                        <FileDropzone 
                                            label="Gambar Kanan" 
                                            accept="image/*" 
                                            v-model="comp.right_image"
                                            @update:modelValue="(file) => { if(file) comp._preview_right = URL.createObjectURL(file); else comp._preview_right = null; }" 
                                        />
                                        <div v-if="comp._preview_right || (comp.right_image && typeof comp.right_image === 'string')" class="mt-3">
                                            <p class="text-xs text-gray-500 mb-1 font-bold">Preview Kanan:</p>
                                            <img :src="comp._preview_right || `/storage/${comp.right_image}`" class="w-32 h-32 object-cover rounded-xl border-2 border-gray-200 shadow-sm" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <TextareaField label="Penjelasan Kesimpulan" v-model="comp.explanation" class="mt-4" placeholder="Misal: Perbedaan utamanya adalah ketersediaan resapan air..." />
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="(obj, idx) in clickableForm.clickables" :key="idx" class="p-4 border-2 border-green-100 bg-green-50/50 rounded-2xl relative">
                            <button @click="removeClickable(idx)" class="absolute top-4 right-4 text-red-500 hover:text-red-700"><Trash2 class="w-5 h-5"/></button>
                            <h4 class="font-bold text-green-800 mb-4">Objek {{ idx + 1 }}</h4>
                            
                            <InputField label="Nama Benda" v-model="obj.name" required placeholder="Misal: Sampah Plastik" />
                            <div class="grid grid-cols-2 gap-2 mt-3">
                                <InputField label="Posisi X (%) - Kiri ke Kanan" v-model="obj.pos_x" placeholder="Misal: 45" />
                                <InputField label="Posisi Y (%) - Atas ke Bawah" v-model="obj.pos_y" placeholder="Misal: 60" />
                            </div>
                            <p class="text-xs text-blue-600 mt-2 mb-4 bg-blue-50 p-2.5 rounded-lg border border-blue-100 leading-relaxed">
                                <strong>💡 Petunjuk Posisi (Isi angka 0 sampai 100):</strong><br/>
                                &bull; <strong>Posisi X</strong> menentukan letak benda dari ujung Kiri (0%) ke Kanan (100%).<br/>
                                &bull; <strong>Posisi Y</strong> menentukan letak benda dari paling Atas (0%) ke Bawah (100%).<br/>
                                <em>Misal: X=50 dan Y=50 berarti benda diletakkan persis di tengah layar.</em>
                            </p>
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
                                <div v-if="obj._preview || (obj.image && typeof obj.image === 'string')" class="mt-3">
                                    <p class="text-xs text-gray-500 mb-1 font-bold">Preview:</p>
                                    <img :src="obj._preview || `/storage/${obj.image}`" class="w-16 h-16 object-cover rounded-xl border-2 border-gray-200 shadow-sm" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <Button variant="primary" :icon="Check" :disabled="clickableForm.processing" @click="saveClickable">Simpan Objek</Button>
                    </div>
                </div>

                <!-- SCENARIO TAB -->
                <div v-if="activeTab === 'scenario'">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h2 class="text-xl font-bold text-gray-800">Simulasi Studi Kasus</h2>
                        <Button variant="primary" size="sm" :icon="Plus" @click="addScenario">Tambah Skenario</Button>
                    </div>

                    <div class="space-y-6">
                        <div v-for="(scn, idx) in scenarioForm.scenarios" :key="idx" class="p-5 border-2 border-purple-100 bg-purple-50/50 rounded-2xl relative">
                            <button @click="removeScenario(idx)" class="absolute top-4 right-4 text-red-500 hover:text-red-700"><Trash2 class="w-5 h-5"/></button>
                            <h4 class="font-bold text-purple-800 mb-4">Kasus {{ idx + 1 }}</h4>
                            
                            <TextareaField label="Konteks / Cerita Kasus" v-model="scn.context" required placeholder="Misal: Warga membuang limbah ke sungai. Apa yang harus dilakukan?" />
                            
                            <div class="mt-3 mb-4">
                                <FileDropzone 
                                    label="Gambar Skenario Kasus" 
                                    accept="image/*" 
                                    v-model="scn.image"
                                    @update:modelValue="(file) => { if(file) scn._preview = URL.createObjectURL(file); else scn._preview = null; }" 
                                />
                                <div v-if="scn._preview || (scn.image && typeof scn.image === 'string')" class="mt-3">
                                    <p class="text-xs text-gray-500 mb-1 font-bold">Preview:</p>
                                    <img :src="scn._preview || `/storage/${scn.image}`" class="w-32 h-32 object-cover rounded-xl border-2 border-gray-200 shadow-sm" />
                                </div>
                            </div>

                            <div class="mt-6 border-t-2 border-purple-200 pt-4">
                                <div class="flex justify-between items-center mb-4">
                                    <h5 class="font-bold text-gray-700">Opsi Pilihan</h5>
                                    <Button variant="secondary" size="sm" @click="addScenarioOption(idx)">Tambah Opsi</Button>
                                </div>
                                <div class="space-y-3">
                                    <div v-for="(opt, oIdx) in scn.options" :key="oIdx" class="flex gap-3 items-start bg-white p-3 rounded-xl border border-gray-200">
                                        <div class="w-16">
                                            <InputField label="Label" v-model="opt.label" placeholder="A, B.." />
                                        </div>
                                        <div class="flex-1">
                                            <InputField label="Teks Pilihan" v-model="opt.text" placeholder="Misal: Melapor ke aparat" />
                                        </div>
                                        <div class="flex-1">
                                            <InputField label="Feedback (Dampak)" v-model="opt.feedback" placeholder="Misal: Langkah tepat! Aparat akan..." />
                                        </div>
                                        <button @click="removeScenarioOption(idx, oIdx)" class="mt-8 text-red-400 hover:text-red-600"><Trash2 class="w-4 h-4"/></button>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <InputField label="Label Opsi yang Benar" v-model="scn.correct_option" placeholder="Contoh: A" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <Button variant="primary" :icon="Check" :disabled="scenarioForm.processing" @click="saveScenario">Simpan Skenario</Button>
                    </div>
                </div>

            </div>
        </div>
        <Toast :show="showSuccess" :message="successMessage" :type="toastType" />
    </AppLayout>
</template>
