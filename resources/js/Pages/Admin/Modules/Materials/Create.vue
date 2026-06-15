<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import InputField from "@/Components/UI/Forms/InputField.vue";
import TextareaField from "@/Components/UI/Forms/TextAreaField.vue";
import SelectField from "@/Components/UI/Forms/SelectField.vue";
import Button from "@/Components/UI/Button.vue";
import Toast from "@/Components/UI/Toast.vue";
import Card from "@/Components/UI/Card.vue";
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import {
    FileEdit,
    Star,
    Plus,
    ArrowLeft,
    Check,
    List,
    FileText,
    Pencil,
    Trash2,
    AlertTriangle,
    Inbox,
    X,
    Image as ImageIcon,
    Video as VideoIcon,
    Loader2,
} from "lucide-vue-next";
import FileDropzone from "@/Components/UI/Forms/FileDropzone.vue";

const props = defineProps({
    module: { type: Object, required: true },
    mission: { type: Object, required: true },
    mascots: { type: Array, default: () => [] },
});

const wizardStep = ref(1);
const successMessage = ref("");
const showSuccess = ref(false);
const toastType = ref("success");
const cardVariant = ref("playful");

// Media type toggle: 'image' | 'video'
const mediaType = ref("image");

const materialForm = ref({
    title: "",
    description: "",
    content: "",
    youtube_link: "",
    mascot_id: null,
    image: null,
    layout_type: "default",
    conceptual_data: {
        topLeft: "",
        topRight: "",
        bottomLeft: "",
        bottomRight: "",
        variables: [],
        levels: []
    }
});

// Helper methods for Variables & Levels
const addVariable = () => {
    materialForm.value.conceptual_data.variables.push({
        name: '',
        min_label: '',
        max_label: ''
    });
};

const removeVariable = (index) => {
    materialForm.value.conceptual_data.variables.splice(index, 1);
};

const addSliderLevel = () => {
    materialForm.value.conceptual_data.levels.push({
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
    materialForm.value.conceptual_data.levels.splice(index, 1);
};

const mediaPreview = ref(null);
const materials = ref([]);

const mascotOptions = computed(() => {
    if (!props.mascots || props.mascots.length === 0) return [];
    return props.mascots.map((m) => ({ value: m.id, label: m.name_pose }));
});
const getSelectedMascot = (mascotId) =>
    props.mascots.find((m) => m.id == mascotId) || null;

const showToast = (message, type = "success") => {
    successMessage.value = message;
    toastType.value = type;
    showSuccess.value = true;
    setTimeout(() => (showSuccess.value = false), 2500);
};

// Switch media type — clear file sebelumnya
const switchMediaType = (type) => {
    mediaType.value = type;
    materialForm.value.image = null;
    mediaPreview.value = null;
};

// Handle file (gambar atau video) — keduanya ke field `image`
const handleMediaChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        materialForm.value.image = file;
        mediaPreview.value = URL.createObjectURL(file);
    }
};
const removeMedia = () => {
    materialForm.value.image = null;
    mediaPreview.value = null;
};

const validateForm = () => {
    if (!materialForm.value.title.trim()) {
        showToast("Judul material harus diisi!", "warning");
        return false;
    }
    if (materialForm.value.layout_type === "default" && !materialForm.value.content.trim()) {
        showToast("Konten material harus diisi!", "warning");
        return false;
    }
    return true;
};

const prevStep = () => {
    wizardStep.value = 1;
};

// Tambah ke list (belum submit ke server)
const addMaterial = () => {
    if (!validateForm()) return;
    
    let finalContent = materialForm.value.content;
    if (materialForm.value.layout_type === 'conceptual_systematic') {
        finalContent = JSON.stringify(materialForm.value.conceptual_data);
    }

    materials.value.push({
        ...materialForm.value,
        conceptual_data: JSON.parse(JSON.stringify(materialForm.value.conceptual_data)), // deep clone values
        content: finalContent,
        id: Date.now(),
        mediaType: mediaType.value,
        mediaPreview: mediaPreview.value,
    });
    // Restore file references since stringify drops them
    const addedMat = materials.value[materials.value.length - 1];
    if (addedMat.layout_type === 'conceptual_systematic' && addedMat.conceptual_data?.levels) {
        addedMat.conceptual_data.levels.forEach((lvl, lvlIdx) => {
            lvl.image = materialForm.value.conceptual_data.levels[lvlIdx].image;
        });
    }
    
    materialForm.value = {
        title: "",
        description: "",
        content: "",
        youtube_link: "",
        mascot_id: null,
        image: null,
        layout_type: "default",
        conceptual_data: {
            topLeft: "",
            topRight: "",
            bottomLeft: "",
            bottomRight: "",
            variables: [],
            levels: []
        }
    };
    mediaType.value = "image";
    mediaPreview.value = null;
    showToast("Material ditambahkan ke list.", "success");
};

const removeMaterial = (id) => {
    materials.value = materials.value.filter((m) => m.id !== id);
    showToast("Material dihapus dari list.", "success");
};

const editMaterial = (material) => {
    materialForm.value = { ...material };
    if (material.layout_type === 'conceptual_systematic') {
        try {
            materialForm.value.conceptual_data = JSON.parse(material.content);
        } catch(e) {}
    }
    mediaType.value = material.mediaType || "image";
    mediaPreview.value = material.mediaPreview;
    materials.value = materials.value.filter((m) => m.id !== material.id);
    wizardStep.value = 1;
};

// Final save — kirim ke controller store()
const finalSave = () => {
    if (materials.value.length === 0) {
        showToast("Tambahkan minimal 1 material!", "warning");
        return;
    }

    const formData = new FormData();
    materials.value.forEach((material, index) => {
        formData.append(`materials[${index}][title]`, material.title);
        formData.append(
            `materials[${index}][description]`,
            material.description || "",
        );
        formData.append(`materials[${index}][content]`, material.content);
        if (material.youtube_link) {
            formData.append(`materials[${index}][youtube_link]`, material.youtube_link);
        }
        formData.append(
            `materials[${index}][mascot_id]`,
            material.mascot_id || "",
        );
        formData.append(
            `materials[${index}][layout_type]`,
            material.layout_type || "default",
        );
        if (material.image) {
            formData.append(`materials[${index}][image]`, material.image);
        }
        if (material.layout_type === 'conceptual_systematic' && material.conceptual_data?.levels) {
            material.conceptual_data.levels.forEach((lvl, lvlIdx) => {
                if (lvl.image) {
                    formData.append(`materials_levels_images[${index}][${lvlIdx}]`, lvl.image);
                }
            });
        }
    });

    router.post(
        route("admin.modules.missions.materials.store", [
            props.module.id,
            props.mission.id,
        ]),
        formData,
        {
            onSuccess: () => {
                showToast("Semua material berhasil disimpan.", "success");
                setTimeout(() => {
                    router.visit(
                        route("admin.modules.missions.show", [
                            props.module.id,
                            props.mission.id,
                        ]),
                    );
                }, 1500);
            },
            onError: (errors) => {
                showToast(
                    "Gagal menyimpan: " + Object.values(errors).join(", "),
                    "error",
                );
            },
        },
    );
};

const toggleCardVariant = () => {
    cardVariant.value = cardVariant.value === "playful" ? "normal" : "playful";
};
</script>

<template>
    <AppLayout>
        <div class="p-5">
            <!-- ===== HEADER ===== -->
            <div
                :class="[
                    'rounded-3xl p-5 mb-8',
                    cardVariant === 'playful'
                        ? 'bg-white border-4 border-blue-200 shadow-playful'
                        : 'bg-white border border-gray-200 shadow-md',
                ]"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div
                            :class="[
                                cardVariant === 'playful'
                                    ? 'bg-blue-100 p-3 rounded-2xl border-2 border-blue-300'
                                    : 'bg-blue-50 p-2 rounded-lg',
                            ]"
                        >
                            <FileEdit class="text-blue-600 w-6 h-6" />
                        </div>
                        <div>
                            <h1
                                class="text-2xl md:text-3xl font-heading font-bold text-gray-800"
                            >
                                Buat Material untuk {{ mission.name }}
                            </h1>
                            <p class="text-sm text-gray-500">
                                Modul: {{ module.name }}
                            </p>
                        </div>
                    </div>
                    <Button
                        :variant="
                            cardVariant === 'playful' ? 'secondary' : 'light'
                        "
                        size="md"
                        :icon="Star"
                        @click="toggleCardVariant"
                    >
                        {{ cardVariant === "playful" ? "Playful" : "Normal" }}
                    </Button>
                </div>
            </div>

            <!-- ===== WIZARD PROGRESS ===== -->
            <div class="mb-8">
                <div class="flex items-center justify-center gap-4">
                    <div class="flex items-center">
                        <div
                            :class="[
                                'w-10 h-10 rounded-full flex items-center justify-center font-bold',
                                wizardStep === 1
                                    ? 'bg-blue-500 text-white'
                                    : 'bg-gray-300 text-gray-600',
                            ]"
                        >
                            1
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700"
                            >Form Material</span
                        >
                    </div>
                    <div class="h-1 w-20 bg-gray-300"></div>
                    <div class="flex items-center">
                        <div
                            :class="[
                                'w-10 h-10 rounded-full flex items-center justify-center font-bold',
                                wizardStep === 2
                                    ? 'bg-blue-500 text-white'
                                    : 'bg-gray-300 text-gray-600',
                            ]"
                        >
                            2
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700"
                            >Review & Simpan</span
                        >
                    </div>
                </div>
            </div>

            <!-- ===== STEP 1: Form Material ===== -->
            <div v-if="wizardStep === 1">
                <Card
                    :variant="cardVariant"
                    title="Form Material"
                    subtitle="Isi informasi material pembelajaran"
                    :icon="FileEdit"
                    icon-color="blue"
                    border-color="blue"
                    :hoverable="false"
                >
                    <div class="space-y-5">
                        <InputField
                            label="Judul Material"
                            v-model="materialForm.title"
                            placeholder="Contoh: Pengenalan Fotosintesis"
                            required
                        />

                        <!-- Layout Type Toggle -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-3">Tipe Layout Materi</label>
                            <div class="flex flex-wrap gap-4">
                                <label class="flex items-center gap-2 cursor-pointer p-3 border-2 rounded-xl transition-all"
                                       :class="materialForm.layout_type === 'default' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50'">
                                    <input type="radio" v-model="materialForm.layout_type" value="default" class="hidden" />
                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                                         :class="materialForm.layout_type === 'default' ? 'border-blue-500' : 'border-gray-300'">
                                        <div v-if="materialForm.layout_type === 'default'" class="w-2.5 h-2.5 bg-blue-500 rounded-full"></div>
                                    </div>
                                    <span class="font-bold text-gray-700">Reguler (Teks/Video)</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer p-3 border-2 rounded-xl transition-all"
                                       :class="materialForm.layout_type === 'conceptual_systematic' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50'">
                                    <input type="radio" v-model="materialForm.layout_type" value="conceptual_systematic" class="hidden" />
                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                                         :class="materialForm.layout_type === 'conceptual_systematic' ? 'border-blue-500' : 'border-gray-300'">
                                        <div v-if="materialForm.layout_type === 'conceptual_systematic'" class="w-2.5 h-2.5 bg-blue-500 rounded-full"></div>
                                    </div>
                                    <span class="font-bold text-gray-700">Konseptual Sistematis</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer p-3 border-2 rounded-xl transition-all"
                                       :class="materialForm.layout_type === 'video_only' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50'">
                                    <input type="radio" v-model="materialForm.layout_type" value="video_only" class="hidden" />
                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                                         :class="materialForm.layout_type === 'video_only' ? 'border-blue-500' : 'border-gray-300'">
                                        <div v-if="materialForm.layout_type === 'video_only'" class="w-2.5 h-2.5 bg-blue-500 rounded-full"></div>
                                    </div>
                                    <span class="font-bold text-gray-700">Hanya Video YouTube</span>
                                    </label>
                                </div>
                        </div>

                        <TextareaField
                            label="Deskripsi Singkat"
                            v-model="materialForm.description"
                            placeholder="Deskripsi singkat tentang material ini..."
                            :rows="3"
                        />
                        <!-- Default Content -->
                        <template v-if="materialForm.layout_type === 'default'">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Konten Material</label>
                                <div class="border rounded-md bg-white">
                                    <QuillEditor
                                        v-model:content="materialForm.content"
                                        contentType="html"
                                        theme="snow"
                                        placeholder="Tulis konten pembelajaran di sini..."
                                        class="h-64"
                                    />
                                </div>
                            </div>
                            <InputField
                                label="Link YouTube Embed (Opsional)"
                                v-model="materialForm.youtube_link"
                                placeholder="Contoh: https://www.youtube.com/embed/..."
                                type="url"
                            />
                            <div class="mt-2 text-xs text-blue-800 bg-blue-50 p-2 rounded border border-blue-200">
                                <p class="font-bold flex items-center gap-1 mb-1"><AlertTriangle class="w-3 h-3" /> Info Link Embed:</p>
                                <ul class="list-disc pl-4 space-y-0.5">
                                    <li>Di video YouTube, klik <b>Bagikan</b> &gt; <b>Sematkan</b>.</li>
                                    <li>Copy link di atribut <code>src="..."</code> (berawalan <code>/embed/</code>).</li>
                                </ul>
                            </div>
                        </template>

                        <!-- Conceptual Systematic Content -->
                        <!-- Conceptual Systematic Content -->
                        <template v-else-if="materialForm.layout_type === 'conceptual_systematic'">
                            <div class="bg-gray-50 p-5 rounded-2xl border-2 border-gray-200 space-y-6">
                                <h3 class="font-bold text-gray-800 text-lg border-b pb-2">Konfigurasi Konseptual Sistematis</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <TextareaField label="Teks Kiri Atas" v-model="materialForm.conceptual_data.topLeft" :rows="2" placeholder="Contoh: Curah hujan adalah..." />
                                    <TextareaField label="Teks Kanan Atas" v-model="materialForm.conceptual_data.topRight" :rows="2" placeholder="Contoh: Semakin tinggi curah hujan..." />
                                    <TextareaField label="Teks Kiri Bawah" v-model="materialForm.conceptual_data.bottomLeft" :rows="2" placeholder="Contoh: Semakin banyak air..." />
                                    <TextareaField label="Teks Kanan Bawah" v-model="materialForm.conceptual_data.bottomRight" :rows="2" placeholder="Contoh: Banyaknya air mengalir..." />
                                </div>
                                
                                <!-- Variables list (Sliders) -->
                                <div class="border-t pt-4">
                                    <div class="flex justify-between items-center mb-4">
                                        <h4 class="font-bold text-gray-700">Variabel Penggeser (Slider)</h4>
                                        <Button type="button" variant="outline" size="sm" :icon="Plus" @click="addVariable">Tambah Variabel</Button>
                                    </div>
                                    
                                    <div v-if="materialForm.conceptual_data.variables.length === 0" class="text-center py-4 bg-white border-2 border-dashed border-gray-200 rounded-xl text-gray-500 mb-4">
                                        Belum ada variabel penggeser. Klik "Tambah Variabel" untuk menambahkan.
                                    </div>

                                    <div v-for="(variable, vIdx) in materialForm.conceptual_data.variables" :key="'v-'+vIdx" class="p-4 border-2 border-indigo-100 bg-white rounded-xl relative mb-4">
                                        <button type="button" @click="removeVariable(vIdx)" class="absolute top-4 right-4 text-red-500 hover:text-red-700" title="Hapus Variabel"><Trash2 class="w-5 h-5"/></button>
                                        <h5 class="font-bold text-indigo-800 mb-3">Variabel {{ vIdx + 1 }}</h5>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <InputField label="Nama Variabel" v-model="variable.name" placeholder="Misal: Intensitas Suhu" />
                                            <InputField label="Label Kiri (Minimal)" v-model="variable.min_label" placeholder="Misal: Dingin" />
                                            <InputField label="Label Kanan (Maksimal)" v-model="variable.max_label" placeholder="Misal: Panas" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Levels list -->
                                <div class="border-t pt-4">
                                    <div class="flex justify-between items-center mb-4">
                                        <h4 class="font-bold text-gray-700">Level / Tahapan</h4>
                                        <Button type="button" variant="primary" size="sm" :icon="Plus" @click="addSliderLevel">Tambah Level</Button>
                                    </div>

                                    <div v-if="materialForm.conceptual_data.levels.length === 0" class="text-center py-4 bg-white border-2 border-dashed border-gray-200 rounded-xl text-gray-500 mb-4">
                                        Belum ada level. Klik "Tambah Level" untuk menambahkan.
                                    </div>

                                    <div class="space-y-6">
                                        <div v-for="(level, idx) in materialForm.conceptual_data.levels" :key="'lvl-'+idx" class="p-4 border-2 border-blue-100 bg-white rounded-2xl relative">
                                            <button type="button" @click="removeSliderLevel(idx)" class="absolute top-4 right-4 text-red-500 hover:text-red-700"><Trash2 class="w-5 h-5"/></button>
                                            <h5 class="font-bold text-blue-800 mb-4">Level {{ idx + 1 }}</h5>
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
                                                    label="Gambar Background Level (Opsional - Fallback ke Gambar Utama)" 
                                                    accept="image/*" 
                                                    v-model="level.image"
                                                    @update:modelValue="(file) => { if(file) level._preview = URL.createObjectURL(file); else level._preview = null; }" 
                                                />
                                                <div v-if="level._preview" class="mt-3 relative inline-block">
                                                    <p class="text-xs text-gray-500 mb-1 font-bold">Preview Gambar:</p>
                                                    <img :src="level._preview" class="w-32 h-32 object-cover rounded-xl border-2 border-gray-200 shadow-sm" />
                                                    <button type="button" @click="level.image = null; level._preview = null;" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 shadow">
                                                        <X class="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Video Only Content -->
                        <template v-else-if="materialForm.layout_type === 'video_only'">
                            <div class="bg-blue-50 p-5 rounded-2xl border-2 border-blue-200">
                                <InputField
                                    label="Link YouTube Embed"
                                    v-model="materialForm.youtube_link"
                                    placeholder="Contoh: https://www.youtube.com/embed/..."
                                    type="url"
                                />
                                <div class="mt-3 text-sm text-blue-800 bg-blue-100 p-3 rounded-lg border border-blue-300">
                                    <p class="font-bold mb-1 flex items-center gap-2"><AlertTriangle class="w-4 h-4" /> Cara Mendapatkan Link Embed YouTube:</p>
                                    <ol class="list-decimal pl-5 space-y-1">
                                        <li>Buka video YouTube yang diinginkan.</li>
                                        <li>Klik tombol <b>Bagikan (Share)</b> di bawah video.</li>
                                        <li>Pilih opsi <b>Sematkan (Embed)</b> (ikon <code>&lt;&gt;</code>).</li>
                                        <li>Salin (copy) <b>hanya link</b> yang ada di bagian <code>src="..."</code>. <br><span class="text-xs opacity-80">(Pastikan link berawalan <code>https://www.youtube.com/embed/...</code>)</span></li>
                                        <li>Tempelkan (paste) link tersebut ke kolom input di atas.</li>
                                    </ol>
                                </div>
                            </div>
                        </template>

                        <!-- ===== MEDIA UPLOAD ===== -->
                        <div v-if="materialForm.layout_type !== 'video_only'">
                            <label
                                class="block text-sm font-bold text-gray-700 mb-3"
                                >Media Pembelajaran</label
                            >
                            <!-- Tab Toggle Gambar / Video -->
                            <div v-if="materialForm.layout_type === 'default'" class="flex gap-2 mb-4">
                                <button
                                    type="button"
                                    @click="switchMediaType('image')"
                                    :class="[
                                        'flex items-center gap-2 px-4 py-2 rounded-xl border-4 font-bold text-sm transition-all',
                                        mediaType === 'image'
                                            ? 'bg-blue-500 text-white border-blue-600'
                                            : 'bg-white text-gray-600 border-gray-200 hover:border-blue-200 hover:bg-blue-50',
                                    ]"
                                >
                                    <ImageIcon class="w-4 h-4" />Gambar
                                </button>
                                <button
                                    type="button"
                                    @click="switchMediaType('video')"
                                    :class="[
                                        'flex items-center gap-2 px-4 py-2 rounded-xl border-4 font-bold text-sm transition-all',
                                        mediaType === 'video'
                                            ? 'bg-blue-500 text-white border-blue-600'
                                            : 'bg-white text-gray-600 border-gray-200 hover:border-blue-200 hover:bg-blue-50',
                                    ]"
                                >
                                    <VideoIcon class="w-4 h-4" />Video
                                </button>
                            </div>
                            <div v-else class="mb-4 text-sm text-gray-600">
                                Unggah gambar utama yang akan diletakkan di tengah diagram. (Video tidak didukung untuk tipe ini).
                            </div>

                            <!-- Upload Gambar -->
                            <div v-if="mediaType === 'image'" class="space-y-3">
                                <div
                                    v-if="mediaPreview"
                                    class="relative inline-block"
                                >
                                    <img
                                        :src="mediaPreview"
                                        alt="Preview"
                                        class="h-40 w-auto object-cover rounded-xl border-4 border-blue-200"
                                    />
                                    <button
                                        type="button"
                                        @click="removeMedia"
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                    >
                                        <X class="w-4 h-4" />
                                    </button>
                                </div>
                                <label
                                    class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-100 border-4 border-blue-200 font-bold text-sm"
                                >
                                    <ImageIcon class="w-5 h-5" />{{
                                        mediaPreview
                                            ? "Ganti Gambar"
                                            : "Pilih Gambar"
                                    }}
                                    <input
                                        type="file"
                                        @change="handleMediaChange"
                                        accept="image/*"
                                        class="hidden"
                                    />
                                </label>
                                <p class="text-xs text-gray-400">
                                    Format: JPG, PNG, GIF. Maks 2MB.
                                </p>
                            </div>

                            <!-- Upload Video -->
                            <div v-if="mediaType === 'video' && materialForm.layout_type === 'default'" class="space-y-3">
                                <div v-if="mediaPreview" class="relative">
                                    <video
                                        :src="mediaPreview"
                                        controls
                                        class="w-full max-h-52 rounded-xl border-4 border-blue-200 bg-black"
                                    />
                                    <button
                                        type="button"
                                        @click="removeMedia"
                                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                    >
                                        <X class="w-4 h-4" />
                                    </button>
                                </div>
                                <label
                                    class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-100 border-4 border-blue-200 font-bold text-sm"
                                >
                                    <VideoIcon class="w-5 h-5" />{{
                                        mediaPreview
                                            ? "Ganti Video"
                                            : "Pilih Video"
                                    }}
                                    <input
                                        type="file"
                                        @change="handleMediaChange"
                                        accept="video/*"
                                        class="hidden"
                                    />
                                </label>
                                <p class="text-xs text-gray-400">
                                    Format: MP4, MOV, AVI, WebM. Maks 50MB.
                                </p>
                            </div>
                        </div>

                        <!-- Mascot Selection -->
                        <div v-if="mascotOptions.length > 0">
                            <SelectField
                                label="Pilih Maskot"
                                v-model="materialForm.mascot_id"
                                :options="mascotOptions"
                                placeholder="Pilih maskot untuk materi ini"
                            />
                            <div
                                v-if="materialForm.mascot_id"
                                class="mt-3 bg-blue-50 p-4 rounded-xl border-2 border-blue-200"
                            >
                                <div class="flex items-center gap-3">
                                    <img
                                        :src="`/storage/${getSelectedMascot(materialForm.mascot_id)?.image}`"
                                        :alt="
                                            getSelectedMascot(
                                                materialForm.mascot_id,
                                            )?.name
                                        "
                                        class="w-16 h-16 object-contain rounded-lg"
                                    />
                                    <div class="flex-1">
                                        <h4 class="font-bold text-blue-800">
                                            {{
                                                getSelectedMascot(
                                                    materialForm.mascot_id,
                                                )?.name
                                            }}
                                        </h4>
                                        <p class="text-sm text-blue-600">
                                            Maskot ini akan muncul di Materi
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        @click="materialForm.mascot_id = null"
                                        class="ml-auto p-1.5 rounded-full text-blue-600 hover:bg-blue-200 hover:text-blue-800 transition"
                                        title="Batalkan pilihan maskot"
                                    >
                                        <X class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="bg-red-50 p-4 rounded-xl border-2 border-red-200"
                        >
                            <p
                                class="text-sm text-red-700 flex items-center gap-2"
                            >
                                <AlertTriangle class="w-4 h-4" /> Template modul
                                ini belum memiliki maskot.
                            </p>
                        </div>
                    </div>

                    <template #footer>
                        <div class="flex justify-between mt-6">
                            <Button
                                variant="light"
                                size="md"
                                @click="
                                    router.visit(
                                        route('admin.modules.missions.show', [
                                            module.id,
                                            mission.id,
                                        ]),
                                    )
                                "
                                >Batal</Button
                            >
                            <div class="flex gap-3">
                                <Button
                                    variant="primary"
                                    size="md"
                                    :icon="Plus"
                                    @click="addMaterial"
                                    >Tambah ke List</Button
                                >
                                <Button
                                    v-if="materials.length > 0"
                                    variant="secondary"
                                    size="md"
                                    :icon="List"
                                    @click="wizardStep = 2"
                                    >Review ({{ materials.length }})</Button
                                >
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- ===== STEP 2: Review Materials ===== -->
            <div v-if="wizardStep === 2">
                <div class="max-w-5xl mx-auto space-y-6">
                    <Card
                        :variant="cardVariant"
                        title="Review Material"
                        subtitle="Periksa kembali material yang akan disimpan"
                        :icon="List"
                        icon-color="blue"
                        border-color="blue"
                        :hoverable="false"
                    />

                    <TransitionGroup
                        name="card"
                        tag="div"
                        class="grid grid-cols-1 md:grid-cols-2 gap-6"
                    >
                        <Card
                            v-for="material in materials"
                            :key="material.id"
                            :variant="cardVariant"
                            :title="material.title"
                            :subtitle="material.description"
                            :icon="FileText"
                            icon-color="blue"
                            border-color="blue"
                        >
                            <div class="mb-3">
                                <div
                                    class="flex items-center gap-2 bg-blue-50 p-2 rounded-lg border border-blue-200"
                                >
                                    <img
                                        v-if="
                                            getSelectedMascot(
                                                material.mascot_id,
                                            )
                                        "
                                        :src="`/storage/${getSelectedMascot(material.mascot_id)?.image}`"
                                        class="w-8 h-8 object-contain rounded"
                                    />
                                    <span
                                        class="text-sm font-medium text-blue-700"
                                        >{{
                                            getSelectedMascot(
                                                material.mascot_id,
                                            )?.name_pose || "No Mascot"
                                        }}</span
                                    >
                                </div>
                            </div>

                            <!-- Media Preview -->
                            <div
                                v-if="
                                    material.mediaPreview &&
                                    material.mediaType === 'image'
                                "
                                class="mb-3"
                            >
                                <img
                                    :src="material.mediaPreview"
                                    alt="Material image"
                                    class="w-full h-32 object-cover rounded-lg"
                                />
                            </div>
                            <div
                                v-else-if="
                                    material.mediaPreview &&
                                    material.mediaType === 'video'
                                "
                                class="mb-3"
                            >
                                <div
                                    class="flex items-center gap-2 bg-blue-50 p-3 rounded-lg border border-blue-200"
                                >
                                    <VideoIcon class="w-5 h-5 text-blue-500" />
                                    <span
                                        class="text-sm font-medium text-blue-700"
                                        >Video terlampir</span
                                    >
                                </div>
                            </div>

                            <div class="text-sm text-gray-600 mb-3">
                                <div v-if="material.layout_type === 'default'" class="line-clamp-2 quill-content" v-html="material.content"></div>
                                <div v-else-if="material.layout_type === 'conceptual_systematic'" class="flex items-center gap-2 p-2 bg-gray-50 border rounded text-xs font-medium text-gray-600">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    Konten Konseptual Sistematis
                                </div>
                                <div v-else-if="material.layout_type === 'video_only'" class="flex items-center gap-2 p-2 bg-gray-50 border rounded text-xs font-medium text-gray-600">
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                    Konten Video YouTube
                                </div>
                            </div>
                            <div v-if="material.youtube_link" class="text-sm text-blue-600 mb-3 truncate flex items-center gap-1">
                                <VideoIcon class="w-4 h-4" /> {{ material.youtube_link }}
                            </div>

                            <template #footer>
                                <div class="flex justify-end gap-2 mt-4">
                                    <Button
                                        variant="secondary"
                                        size="sm"
                                        :icon="Pencil"
                                        @click="editMaterial(material)"
                                    />
                                    <Button
                                        variant="danger"
                                        size="sm"
                                        :icon="Trash2"
                                        @click="removeMaterial(material.id)"
                                    />
                                </div>
                            </template>
                        </Card>
                    </TransitionGroup>

                    <div
                        v-if="materials.length === 0"
                        class="text-center py-12"
                    >
                        <Inbox class="text-gray-300 w-16 h-16 mb-4 mx-auto" />
                        <p class="text-gray-500">
                            Belum ada material yang ditambahkan
                        </p>
                        <Button
                            variant="primary"
                            size="md"
                            :icon="ArrowLeft"
                            @click="prevStep"
                            class="mt-4"
                            >Kembali & Tambah Material</Button
                        >
                    </div>

                    <div
                        v-if="materials.length > 0"
                        class="flex justify-between"
                    >
                        <Button
                            variant="light"
                            size="lg"
                            :icon="ArrowLeft"
                            @click="prevStep"
                            >Kembali</Button
                        >
                        <Button
                            variant="primary"
                            size="lg"
                            :icon="Check"
                            @click="finalSave"
                        >
                            <span class="flex items-center gap-2"
                                >Simpan Semua Material ({{
                                    materials.length
                                }})</span
                            >
                        </Button>
                    </div>
                </div>
            </div>
        </div>
        <Toast
            :show="showSuccess"
            :message="successMessage"
            :type="toastType"
        />
    </AppLayout>
</template>
