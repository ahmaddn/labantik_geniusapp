<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import InputField from "@/Components/UI/Forms/InputField.vue";
import TextareaField from "@/Components/UI/Forms/TextAreaField.vue";
import SelectField from "@/Components/UI/Forms/SelectField.vue";
import FileUpload from "@/Components/UI/Forms/FileUpload.vue";
import Button from "@/Components/UI/Button.vue";
import Toast from "@/Components/UI/Toast.vue";
import Card from "@/Components/UI/Card.vue";
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import {
    FileEdit,
    Star,
    Check,
    AlertTriangle,
    Image as ImageIcon,
    Video as VideoIcon,
    Pencil,
    X,
    Loader2,
    Plus,
    Trash2
} from "lucide-vue-next";

const props = defineProps({
    module: { type: Object, required: true },
    mission: { type: Object, required: true },
    material: { type: Object, required: true },
    mascots: { type: Array, default: () => [] },
});

const successMessage = ref("");
const showSuccess = ref(false);
const toastType = ref("success");
const cardVariant = ref("playful");
const isSubmitting = ref(false);

// Detect existing media type by file extension
const detectMediaType = (path) => {
    if (!path) return "image";
    const ext = path.split(".").pop().toLowerCase();
    return ["mp4", "mov", "avi", "wmv", "webm"].includes(ext)
        ? "video"
        : "image";
};

const mediaType = ref(detectMediaType(props.material.image));

const initialLayoutType = props.material.layout_type || "default";
let initialConceptualData = {
    topLeft: "",
    topRight: "",
    bottomLeft: "",
    bottomRight: "",
    variables: [],
    levels: []
};
let initialLearningObjectives = [''];
let initialInitialQuestions = [''];
let initialProcessList = [''];
let initialCoverData = { subtitle: '' };
let initialImageComparison = {
    left_label: 'MUSIM KEMARAU',
    right_label: 'MUSIM HUJAN',
    image_left: null,
    image_right: null,
    image_left_preview: null,
    image_right_preview: null,
    existing_image_left: null,
    existing_image_right: null
};

if (props.material.content) {
    try {
        const parsed = JSON.parse(props.material.content);
        if (initialLayoutType === 'conceptual_systematic') {
            initialConceptualData = parsed;
            if (initialConceptualData.levels) {
                initialConceptualData.levels = initialConceptualData.levels.map(lvl => ({
                    ...lvl,
                    existing_image: lvl.image || null,
                    image: null,
                    _preview: lvl.image ? `/storage/${lvl.image}` : null
                }));
            }
        }
        else if (initialLayoutType === 'learning_objectives') initialLearningObjectives = parsed;
        else if (initialLayoutType === 'initial_questions') initialInitialQuestions = parsed;
        else if (initialLayoutType === 'process_list') initialProcessList = parsed;
        else if (initialLayoutType === 'cover_page') initialCoverData = parsed;
        else if (initialLayoutType === 'image_comparison') {
            initialImageComparison.left_label = parsed.left_label || 'KIRI';
            initialImageComparison.right_label = parsed.right_label || 'KANAN';
            initialImageComparison.existing_image_left = parsed.image_left || null;
            initialImageComparison.existing_image_right = parsed.image_right || null;
            if (parsed.image_left) initialImageComparison.image_left_preview = `/storage/${parsed.image_left}`;
            if (parsed.image_right) initialImageComparison.image_right_preview = `/storage/${parsed.image_right}`;
        }
    } catch(e) {}
}

const materialForm = ref({
    title: props.material.title || "",
    description: props.material.description || "",
    content: initialLayoutType === 'default' ? (props.material.content || "") : "",
    youtube_link: props.material.youtube_link || "",
    mascot_id: props.material.mascot_id || null,
    image: null,
    remove_image: false,
    layout_type: initialLayoutType,
    conceptual_data: initialConceptualData,
    learning_objectives: initialLearningObjectives,
    initial_questions: initialInitialQuestions,
    process_list: initialProcessList,
    cover_data: initialCoverData,
    image_comparison: initialImageComparison
});

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
        id: null,
        level_name: '',
        status: 'aman',
        animation_effect: 'none',
        narration: '',
        metric_value: '',
        image: null
    });
};

const removeSliderLevel = (index) => {
    materialForm.value.conceptual_data.levels.splice(index, 1);
};

// Preview
const mediaPreview = ref(
    props.material.image ? `/storage/${props.material.image}` : null,
);
const hasExistingMedia = ref(!!props.material.image);

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

// Switch media type — clear data tipe sebelumnya
const switchMediaType = (type) => {
    mediaType.value = type;
    materialForm.value.image = null;
    materialForm.value.remove_image = hasExistingMedia.value;
    mediaPreview.value = null;
    hasExistingMedia.value = false;
};

// Handle file upload (gambar atau video) — keduanya ke field `image`
const handleMediaChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        materialForm.value.image = file;
        materialForm.value.remove_image = false;
        mediaPreview.value = URL.createObjectURL(file);
        hasExistingMedia.value = false;
    }
};
const removeMedia = () => {
    if (hasExistingMedia.value) {
        materialForm.value.remove_image = true;
    }
    materialForm.value.image = null;
    mediaPreview.value = null;
    hasExistingMedia.value = false;
};

const handleSubmit = () => {
    if (!materialForm.value.title.trim()) {
        showToast("Judul material harus diisi!", "warning");
        return;
    }
    if (materialForm.value.layout_type === 'default' && !materialForm.value.content.trim()) {
        showToast("Konten material harus diisi!", "warning");
        return;
    }

    isSubmitting.value = true;

    let finalContent = materialForm.value.content;
    let conceptualLevelsToSave = null;
    if (materialForm.value.layout_type === 'conceptual_systematic') {
        const payload = JSON.parse(JSON.stringify(materialForm.value.conceptual_data));
        payload.levels.forEach(l => { delete l.image; delete l._preview; delete l.existing_image; });
        finalContent = JSON.stringify(payload);
        conceptualLevelsToSave = materialForm.value.conceptual_data.levels;
    } else if (materialForm.value.layout_type === 'learning_objectives') {
        finalContent = JSON.stringify(materialForm.value.learning_objectives);
    } else if (materialForm.value.layout_type === 'initial_questions') {
        finalContent = JSON.stringify(materialForm.value.initial_questions);
    } else if (materialForm.value.layout_type === 'process_list') {
        finalContent = JSON.stringify(materialForm.value.process_list);
    } else if (materialForm.value.layout_type === 'cover_page') {
        finalContent = JSON.stringify(materialForm.value.cover_data);
    } else if (materialForm.value.layout_type === 'image_comparison') {
        finalContent = JSON.stringify({
            left_label: materialForm.value.image_comparison.left_label,
            right_label: materialForm.value.image_comparison.right_label,
            image_left: materialForm.value.image_comparison.existing_image_left,
            image_right: materialForm.value.image_comparison.existing_image_right,
        });
    }

    const formData = new FormData();
    formData.append("_method", "PUT");
    formData.append("title", materialForm.value.title);
    formData.append("description", materialForm.value.description || "");
    formData.append("content", finalContent);
    formData.append("layout_type", materialForm.value.layout_type || "default");
    formData.append("youtube_link", materialForm.value.youtube_link || "");
    formData.append("mascot_id", materialForm.value.mascot_id || "");
    formData.append(
        "remove_image",
        materialForm.value.remove_image ? "1" : "0",
    );
    if (materialForm.value.image) {
        formData.append("image", materialForm.value.image);
    }
    if (materialForm.value.layout_type === 'image_comparison') {
        if (materialForm.value.image_comparison.image_left) {
            formData.append("image_left", materialForm.value.image_comparison.image_left);
        }
        if (materialForm.value.image_comparison.image_right) {
            formData.append("image_right", materialForm.value.image_comparison.image_right);
        }
    }
    if (materialForm.value.layout_type === 'conceptual_systematic' && conceptualLevelsToSave) {
        conceptualLevelsToSave.forEach((lvl, lvlIdx) => {
            if (lvl.image) {
                formData.append(`conceptual_level_${lvlIdx}_image`, lvl.image);
            }
        });
    }

    router.post(
        route("admin.modules.missions.materials.update", [
            props.module.id,
            props.mission.id,
            props.material.id,
        ]),
        formData,
        {
            onSuccess: () => {
                showToast("Material berhasil diperbarui.", "success");
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
                isSubmitting.value = false;
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
                            <Pencil class="text-blue-600 w-6 h-6" />
                        </div>
                        <div>
                            <h1
                                class="text-2xl md:text-3xl font-heading font-bold text-gray-800"
                            >
                                Edit Material: {{ material.title }}
                            </h1>
                            <p class="text-sm text-gray-500">
                                Modul: {{ module.name }} | Misi:
                                {{ mission.name }}
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

            <!-- ===== FORM ===== -->
            <Card
                :variant="cardVariant"
                title="Edit Material"
                subtitle="Perbarui informasi material pembelajaran"
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
                                   :class="materialForm.layout_type === 'cover_page' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50'">
                                <input type="radio" v-model="materialForm.layout_type" value="cover_page" class="hidden" />
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                                     :class="materialForm.layout_type === 'cover_page' ? 'border-blue-500' : 'border-gray-300'">
                                    <div v-if="materialForm.layout_type === 'cover_page'" class="w-2.5 h-2.5 bg-blue-500 rounded-full"></div>
                                </div>
                                <span class="font-bold text-gray-700">Halaman Cover</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer p-3 border-2 rounded-xl transition-all"
                                   :class="materialForm.layout_type === 'learning_objectives' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50'">
                                <input type="radio" v-model="materialForm.layout_type" value="learning_objectives" class="hidden" />
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                                     :class="materialForm.layout_type === 'learning_objectives' ? 'border-blue-500' : 'border-gray-300'">
                                    <div v-if="materialForm.layout_type === 'learning_objectives'" class="w-2.5 h-2.5 bg-blue-500 rounded-full"></div>
                                </div>
                                <span class="font-bold text-gray-700">Tujuan Pembelajaran</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer p-3 border-2 rounded-xl transition-all"
                                   :class="materialForm.layout_type === 'initial_questions' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50'">
                                <input type="radio" v-model="materialForm.layout_type" value="initial_questions" class="hidden" />
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                                     :class="materialForm.layout_type === 'initial_questions' ? 'border-blue-500' : 'border-gray-300'">
                                    <div v-if="materialForm.layout_type === 'initial_questions'" class="w-2.5 h-2.5 bg-blue-500 rounded-full"></div>
                                </div>
                                <span class="font-bold text-gray-700">Pertanyaan Awal</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer p-3 border-2 rounded-xl transition-all"
                                   :class="materialForm.layout_type === 'image_comparison' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50'">
                                <input type="radio" v-model="materialForm.layout_type" value="image_comparison" class="hidden" />
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                                     :class="materialForm.layout_type === 'image_comparison' ? 'border-blue-500' : 'border-gray-300'">
                                    <div v-if="materialForm.layout_type === 'image_comparison'" class="w-2.5 h-2.5 bg-blue-500 rounded-full"></div>
                                </div>
                                <span class="font-bold text-gray-700">Mengamati Gambar</span>
                            </label>
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
                                   :class="materialForm.layout_type === 'process_list' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50'">
                                <input type="radio" v-model="materialForm.layout_type" value="process_list" class="hidden" />
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                                     :class="materialForm.layout_type === 'process_list' ? 'border-blue-500' : 'border-gray-300'">
                                    <div v-if="materialForm.layout_type === 'process_list'" class="w-2.5 h-2.5 bg-blue-500 rounded-full"></div>
                                </div>
                                <span class="font-bold text-gray-700">List Proses & Gambar</span>
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
                    <template v-else-if="materialForm.layout_type === 'conceptual_systematic'">
                        <div class="bg-gray-50 p-5 rounded-2xl border-2 border-gray-200 space-y-6">
                            <h3 class="font-bold text-gray-800 text-lg border-b pb-2">Konfigurasi Konseptual Sistematis</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <TextareaField label="Teks Kiri Atas" v-model="materialForm.conceptual_data.topLeft" :rows="2" placeholder="Contoh: Curah hujan adalah..." />
                                <TextareaField label="Teks Kanan Atas" v-model="materialForm.conceptual_data.topRight" :rows="2" placeholder="Contoh: Semakin tinggi curah hujan..." />
                                <TextareaField label="Teks Kiri Bawah" v-model="materialForm.conceptual_data.bottomLeft" :rows="2" placeholder="Contoh: Semakin banyak air..." />
                                <TextareaField label="Teks Kanan Bawah" v-model="materialForm.conceptual_data.bottomRight" :rows="2" placeholder="Contoh: Banyaknya air mengalir..." />
                            </div>
                            
                            <div class="border-t pt-4">
                                <div class="flex justify-between items-center mt-6 mb-2">
                                    <h4 class="font-bold text-gray-700">Variabel Penggeser (Slider)</h4>
                                    <Button v-if="materialForm.conceptual_data.variables.length < 10" variant="outline" size="sm" :icon="Plus" @click="addVariable">Tambah Variabel</Button>
                                </div>
                                
                                <div v-if="materialForm.conceptual_data.variables.length === 0" class="text-center py-4 bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl text-gray-500">
                                    Belum ada variabel penggeser. Klik "Tambah Variabel" untuk menambahkan.
                                </div>

                                <div v-for="(variable, vIdx) in materialForm.conceptual_data.variables" :key="'var-'+vIdx" class="p-4 border-2 border-indigo-100 bg-indigo-50/50 rounded-xl relative mb-4">
                                    <button @click="removeVariable(vIdx)" class="absolute top-4 right-4 text-red-500 hover:text-red-700" title="Hapus Variabel"><Trash2 class="w-5 h-5"/></button>
                                    <h5 class="font-bold text-indigo-800 mb-3">Variabel {{ vIdx + 1 }}</h5>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <InputField label="Nama Variabel" v-model="variable.name" placeholder="Misal: Intensitas Suhu" />
                                        <InputField label="Label Kiri (Minimal)" v-model="variable.min_label" placeholder="Misal: Dingin" />
                                        <InputField label="Label Kanan (Maksimal)" v-model="variable.max_label" placeholder="Misal: Panas" />
                                    </div>
                                </div>
                            </div>

                            <div class="border-t pt-4">
                                <div class="flex justify-between items-center mb-4 mt-6">
                                    <h4 class="font-bold text-gray-700">Level / Tahapan</h4>
                                    <Button variant="primary" size="sm" :icon="Plus" @click="addSliderLevel">Tambah Level</Button>
                                </div>

                                <div class="space-y-6">
                                    <div v-for="(level, idx) in materialForm.conceptual_data.levels" :key="'lvl-'+idx" class="p-4 border-2 border-blue-100 bg-blue-50/50 rounded-2xl relative">
                                        <button @click="removeSliderLevel(idx)" class="absolute top-4 right-4 text-red-500 hover:text-red-700"><Trash2 class="w-5 h-5"/></button>
                                        <h5 class="font-bold text-blue-800 mb-4">Level {{ idx + 1 }}</h5>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <InputField label="Nama Level (contoh: Tahap Awal, Level 1)" v-model="level.level_name" required placeholder="Misal: Level 1" />
                                            <InputField label="Keterangan Tambahan Level (opsional)" v-model="level.metric_value" placeholder="Misal: Status Bahaya / Suhu 30C" />
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
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
                                            <div>
                                                <label class="block text-sm font-bold text-gray-700 mb-2">Efek Transisi Gambar</label>
                                                <select v-model="level.image_transition" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                                    <option value="none">Normal (Tanpa Transisi)</option>
                                                    <option value="fade">Magic Crossfade (Halus)</option>
                                                    <option value="zoom-fade">Magic Zoom & Fade</option>
                                                    <option value="slide-left">Geser Kiri Halus</option>
                                                    <option value="slide-right">Geser Kanan Halus</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <TextareaField label="Narasi Penjelasan Level" v-model="level.narration" :rows="2" placeholder="Misal: Pada level ini, debit air masih stabil..." />
                                        </div>
                                        <div class="mt-4">
                                            <label class="block text-sm font-bold text-gray-700 mb-2">Gambar / Ilustrasi Level</label>
                                            <FileUpload 
                                                @change="e => { level.image = e.target.files[0]; level._preview = URL.createObjectURL(e.target.files[0]) }" 
                                                accept="image/*" 
                                                buttonText="Pilih Gambar" 
                                                buttonColor="blue" 
                                            />
                                            <img v-if="level._preview || level.existing_image" :src="level._preview || `/storage/${level.existing_image}`" class="mt-4 h-48 w-full object-cover rounded-xl border-4 border-blue-200" />
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

                    <!-- Learning Objectives Content -->
                    <template v-else-if="materialForm.layout_type === 'learning_objectives'">
                        <div class="bg-blue-50 p-5 rounded-2xl border-2 border-blue-200 space-y-4">
                            <h3 class="font-bold text-gray-800 border-b pb-2">Poin Tujuan Pembelajaran</h3>
                            <div v-for="(obj, idx) in materialForm.learning_objectives" :key="idx" class="flex gap-2 mb-2">
                                <InputField :label="`Poin ${idx+1}`" v-model="materialForm.learning_objectives[idx]" class="flex-1" placeholder="Masukkan tujuan..." />
                                <Button variant="danger" class="mt-7" @click="materialForm.learning_objectives.splice(idx, 1)" :icon="Trash2" />
                            </div>
                            <Button variant="outline" size="sm" :icon="Plus" @click="materialForm.learning_objectives.push('')">Tambah Poin</Button>
                        </div>
                    </template>

                    <!-- Cover Page Content -->
                    <template v-else-if="materialForm.layout_type === 'cover_page'">
                        <div class="bg-indigo-50 p-5 rounded-2xl border-2 border-indigo-200 space-y-4">
                            <h3 class="font-bold text-gray-800 border-b pb-2">Konfigurasi Cover Misi</h3>
                            <InputField label="Teks Subjudul (Misi X: ...)" v-model="materialForm.cover_data.subtitle" placeholder="Contoh: DARI MANA AIR DATANG?" />
                            <SelectField label="Pilih Maskot Cover" v-model="materialForm.mascot_id" :options="mascotOptions" />
                        </div>
                    </template>

                    <!-- Initial Questions Content -->
                    <template v-else-if="materialForm.layout_type === 'initial_questions'">
                        <div class="bg-purple-50 p-5 rounded-2xl border-2 border-purple-200 space-y-4">
                            <h3 class="font-bold text-gray-800 border-b pb-2">Pertanyaan Awal Pembuka</h3>
                            <SelectField label="Pilih Maskot (Penanya)" v-model="materialForm.mascot_id" :options="mascotOptions" />
                            <div v-for="(q, idx) in materialForm.initial_questions" :key="idx" class="flex gap-2 mb-2">
                                <InputField :label="`Pertanyaan ${idx+1}`" v-model="materialForm.initial_questions[idx]" class="flex-1" placeholder="Pernahkah kamu berpikir..." />
                                <Button variant="danger" class="mt-7" @click="materialForm.initial_questions.splice(idx, 1)" :icon="Trash2" />
                            </div>
                            <Button variant="outline" size="sm" :icon="Plus" @click="materialForm.initial_questions.push('')">Tambah Pertanyaan</Button>
                        </div>
                    </template>

                    <!-- Image Comparison Content -->
                    <template v-else-if="materialForm.layout_type === 'image_comparison'">
                        <div class="bg-green-50 p-5 rounded-2xl border-2 border-green-200 space-y-4">
                            <h3 class="font-bold text-gray-800 border-b pb-2">Konfigurasi Mengamati Gambar</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                <!-- Kiri -->
                                <div class="border p-4 rounded bg-white">
                                    <h4 class="font-bold mb-2">Gambar Kiri</h4>
                                    <InputField label="Label Gambar Kiri" v-model="materialForm.image_comparison.left_label" />
                                    <div class="mt-2">
                                        <FileUpload @change="e => { materialForm.image_comparison.image_left = e.target.files[0]; materialForm.image_comparison.image_left_preview = URL.createObjectURL(e.target.files[0]) }" accept="image/*" buttonText="Pilih Gambar Kiri" buttonColor="green" />
                                        <img v-if="materialForm.image_comparison.image_left_preview || materialForm.image_comparison.existing_image_left" :src="materialForm.image_comparison.image_left_preview || `/storage/${materialForm.image_comparison.existing_image_left}`" class="mt-4 h-32 w-full object-cover rounded-xl border" />
                                    </div>
                                </div>
                                <!-- Kanan -->
                                <div class="border p-4 rounded bg-white">
                                    <h4 class="font-bold mb-2">Gambar Kanan</h4>
                                    <InputField label="Label Gambar Kanan" v-model="materialForm.image_comparison.right_label" />
                                    <div class="mt-2">
                                        <FileUpload @change="e => { materialForm.image_comparison.image_right = e.target.files[0]; materialForm.image_comparison.image_right_preview = URL.createObjectURL(e.target.files[0]) }" accept="image/*" buttonText="Pilih Gambar Kanan" buttonColor="blue" />
                                        <img v-if="materialForm.image_comparison.image_right_preview || materialForm.image_comparison.existing_image_right" :src="materialForm.image_comparison.image_right_preview || `/storage/${materialForm.image_comparison.existing_image_right}`" class="mt-4 h-32 w-full object-cover rounded-xl border" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Process List Content -->
                    <template v-else-if="materialForm.layout_type === 'process_list'">
                        <div class="bg-orange-50 p-5 rounded-2xl border-2 border-orange-200 space-y-4">
                            <h3 class="font-bold text-gray-800 border-b pb-2">Konfigurasi List Proses</h3>
                            <div v-for="(obj, idx) in materialForm.process_list" :key="idx" class="flex gap-2 mb-2">
                                <InputField :label="`Langkah ${idx+1}`" v-model="materialForm.process_list[idx]" class="flex-1" placeholder="Masukkan langkah proses..." />
                                <Button variant="danger" class="mt-7" @click="materialForm.process_list.splice(idx, 1)" :icon="Trash2" />
                            </div>
                            <Button variant="outline" size="sm" :icon="Plus" @click="materialForm.process_list.push('')">Tambah Langkah</Button>
                        </div>
                    </template>

                    <!-- ===== MEDIA UPLOAD ===== -->
                    <div v-if="['default', 'learning_objectives', 'process_list'].includes(materialForm.layout_type)">
                        <label
                            class="block text-sm font-bold text-gray-700 mb-3"
                            >Media Pembelajaran / Background</label
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
                                <span
                                    v-if="hasExistingMedia"
                                    class="absolute bottom-1 left-1 text-xs bg-black/50 text-white px-2 py-0.5 rounded"
                                    >Gambar saat ini</span
                                >
                            </div>
                            <FileUpload 
                                @change="handleMediaChange" 
                                accept="image/*" 
                                :buttonText="mediaPreview ? 'Ganti Gambar' : 'Pilih Gambar'" 
                                buttonColor="blue" 
                            />
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
                                <span
                                    v-if="hasExistingMedia"
                                    class="absolute bottom-2 left-2 text-xs bg-black/50 text-white px-2 py-0.5 rounded"
                                    >Video saat ini</span
                                >
                            </div>
                            <FileUpload 
                                @change="handleMediaChange" 
                                accept="video/*" 
                                :buttonText="mediaPreview ? 'Ganti Video' : 'Pilih Video'" 
                                buttonColor="blue" 
                            />
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
                            placeholder="Pilih maskot untuk material ini"
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
                                        Maskot ini akan muncul di material
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
                        <p class="text-sm text-red-700 flex items-center gap-2">
                            <AlertTriangle class="w-4 h-4" /> Template modul ini
                            belum memiliki maskot.
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
                        <Button
                            variant="primary"
                            size="md"
                            :icon="isSubmitting ? Loader2 : Check"
                            @click="handleSubmit"
                            :disabled="isSubmitting"
                        >
                            {{
                                isSubmitting
                                    ? "Menyimpan..."
                                    : "Simpan Perubahan"
                            }}
                        </Button>
                    </div>
                </template>
            </Card>
        </div>
        <Toast
            :show="showSuccess"
            :message="successMessage"
            :type="toastType"
        />
    </AppLayout>
</template>
