<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import InputField from "@/Components/UI/Forms/InputField.vue";
import TextareaField from "@/Components/UI/Forms/TextAreaField.vue";
import SelectField from "@/Components/UI/Forms/SelectField.vue";
import ImageDropzone from "@/Components/UI/Forms/ImageDropzone.vue";
import Button from "@/Components/UI/Button.vue";
import Toast from "@/Components/UI/Toast.vue";
import Card from "@/Components/UI/Card.vue";
import {
    HelpCircle,
    Star,
    Info,
    List,
    LayoutGrid,
    Box,
    CheckCircle,
    Clock,
    Tag,
    Plus,
    Check,
    Trash2,
    AlertTriangle,
    X,
    Image as ImageIcon,
    ToggleLeft,
    CheckSquare,
    Loader2,
    Save,
} from "lucide-vue-next";

const props = defineProps({
    module: { type: Object, required: true },
    mission: { type: Object, required: false, default: null },
    mascots: { type: Array, default: () => [] },
    presetCategory: { type: String, required: false, default: null },
});

const wizardStep = ref(1);
const successMessage = ref("");
const showSuccess = ref(false);
const toastType = ref("success");
const cardVariant = ref("playful");

const quizForm = ref({
    title: "",
    description: "",
    time_limit: 30,
    type: "multiple_choices",
    category: props.presetCategory || "mission",
});

// Image quiz
const quizImageFile = ref(null);
const quizImagePreview = ref(null);
const handleQuizImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        quizImageFile.value = file;
        quizImagePreview.value = URL.createObjectURL(file);
    }
};
const removeQuizImage = () => {
    quizImageFile.value = null;
    quizImagePreview.value = null;
};

// --- PILIHAN GANDA / Case Study State ---
const quizQuestions = ref([]);
const currentQuestion = ref({
    question_text: "",
    mascot_id: null,
    image: null,
});
const questionOptions = ref([]);
const currentOption = ref({ option_text: "", is_correct: false, feedback: "" });

// --- Drag & Drop State ---
const dragDropQuestionText = ref("");
const dragDropMascotId = ref(null);
const dragDropGroups = ref([]);
const dragDropItems = ref([]);
const currentDragDropGroup = ref({ group_name: "" });
const currentDragDropItem = ref({
    item_text: "",
    group_local_id: null,
    item_image_file: null,
    item_image_preview: null,
});

// --- True/False (Image Select) State ---
// Satu soal dengan beberapa opsi berupa gambar, user memilih yang benar
const trueFalseQuestionText = ref("");
const trueFalseMascotId = ref(null);
const trueFalseOptions = ref([]); // { id, option_text, option_image_file, option_image_preview, is_correct }
const currentTFOption = ref({ option_text: "", is_correct: false });
const currentTFImageFile = ref(null);
const currentTFImagePreview = ref(null);

const handleTFImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        currentTFImageFile.value = file;
        currentTFImagePreview.value = URL.createObjectURL(file);
    }
};
const removeTFImage = () => {
    currentTFImageFile.value = null;
    currentTFImagePreview.value = null;
};
const addTFOption = () => {
    if (
        !currentTFOption.value.option_text.trim() &&
        !currentTFImageFile.value
    ) {
        showToast("Teks atau gambar opsi harus diisi!", "warning");
        return;
    }
    trueFalseOptions.value.push({
        id: Date.now() + Math.random(),
        option_text: currentTFOption.value.option_text,
        is_correct: currentTFOption.value.is_correct,
        option_image_file: currentTFImageFile.value,
        option_image_preview: currentTFImagePreview.value,
    });
    currentTFOption.value = { option_text: "", is_correct: false };
    currentTFImageFile.value = null;
    currentTFImagePreview.value = null;
    showToast("Opsi gambar ditambahkan!", "success");
};
const removeTFOption = (id) => {
    trueFalseOptions.value = trueFalseOptions.value.filter((o) => o.id !== id);
};

// Mascot options
const mascotOptions = computed(() =>
    props.mascots.map((m) => ({ value: m.id, label: m.name_pose })),
);
const getSelectedMascot = (mascotId) =>
    props.mascots.find((m) => m.id == mascotId) || null;

const groupSelectOptions = computed(() =>
    dragDropGroups.value.map((g) => ({
        value: g.local_id,
        label: g.group_name,
    })),
);

const quizTypeOptions = [
    { value: "multiple_choices", label: "PILIHAN GANDA" },
    { value: "drag_drop", label: "Drag & Drop" },
    { value: "true_false", label: "True / False (Pilih Gambar)" },
    { value: "case_study", label: "Studo Kasus" },
];
const categoryOptions = [
    { value: "pretest", label: "Tes Awal" },
    { value: "mission", label: "Misi" },
    { value: "posttest", label: "Tes Akhir" },
];

const getCategoryLabel = (value) => {
    const map = {
        pretest: "Tes Awal",
        mission: "Misi",
        posttest: "Tes Akhir",
    };
    return map[value] || value;
};

const isDragDrop = computed(() => quizForm.value.type === "drag_drop");
const isTrueFalse = computed(() => quizForm.value.type === "true_false");
// timer unit label depends on where the user opened the create flow
const timeUnitLabel = computed(() =>
    props.presetCategory === "pretest" || props.presetCategory === "posttest"
        ? "menit"
        : "detik",
);

const showToast = (message, type = "success") => {
    successMessage.value = message;
    toastType.value = type;
    showSuccess.value = true;
    setTimeout(() => {
        showSuccess.value = false;
    }, 2500);
};

// Wizard navigation:
// multiple_choices/case_study: 1 → 2(pertanyaan) → 3(review)
// drag_drop:                   1 → 3(grup) → 4(items) → 5(review)
// true_false:                  1 → 3(opsi gambar) → 5(review)  [reuse step numbers]
const nextStep = () => {
    if (wizardStep.value === 1) {
        if (!quizForm.value.title.trim()) {
            showToast("Judul kuis harus diisi!", "warning");
            return;
        }
        if (isDragDrop.value) {
            wizardStep.value = 3;
            return;
        }
        if (isTrueFalse.value) {
            wizardStep.value = 3;
            return;
        }
    }
    // drag_drop: step 4 → 5(review)
    if (wizardStep.value === 4 && isDragDrop.value) {
        wizardStep.value = 5;
        return;
    }
    // true_false: step 3 → 5(review)
    if (wizardStep.value === 3 && isTrueFalse.value) {
        wizardStep.value = 5;
        return;
    }
    wizardStep.value++;
};
const prevStep = () => {
    if (wizardStep.value === 3 && (isDragDrop.value || isTrueFalse.value)) {
        wizardStep.value = 1;
        return;
    }
    if (wizardStep.value === 4 && isDragDrop.value) {
        wizardStep.value = 3;
        return;
    }
    if (wizardStep.value === 5 && isDragDrop.value) {
        wizardStep.value = 4;
        return;
    }
    if (wizardStep.value === 5 && isTrueFalse.value) {
        wizardStep.value = 3;
        return;
    }
    wizardStep.value--;
};

// Review step number
const reviewStep = computed(() => {
    if (isDragDrop.value) return 5;
    if (isTrueFalse.value) return 5;
    return 3;
});

// --- PILIHAN GANDA Methods ---
const addOption = () => {
    if (!currentOption.value.option_text.trim()) {
        showToast("Teks opsi harus diisi!", "warning");
        return;
    }
    questionOptions.value.push({
        ...currentOption.value,
        id: Date.now() + Math.random(),
    });
    currentOption.value = { option_text: "", is_correct: false, feedback: "" };
};
const removeOption = (id) => {
    questionOptions.value = questionOptions.value.filter((o) => o.id !== id);
};
const addQuestion = () => {
    if (!currentQuestion.value.question_text.trim()) {
        showToast("Teks pertanyaan harus diisi!", "warning");
        return;
    }
    if (questionOptions.value.length < 2) {
        showToast("Minimal 2 opsi jawaban diperlukan!", "warning");
        return;
    }
    if (!questionOptions.value.some((o) => o.is_correct)) {
        showToast(
            "Minimal 1 opsi harus ditandai sebagai jawaban benar!",
            "warning",
        );
        return;
    }
    quizQuestions.value.push({
        ...currentQuestion.value,
        id: Date.now(),
        options: [...questionOptions.value],
    });
    currentQuestion.value = { question_text: "", mascot_id: null, image: null };
    questionOptions.value = [];
    showToast("Pertanyaan ditambahkan!", "success");
};
const removeQuestion = (id) => {
    quizQuestions.value = quizQuestions.value.filter((q) => q.id !== id);
};

// --- Drag & Drop Methods ---
const addDragDropGroup = () => {
    if (!currentDragDropGroup.value.group_name.trim()) {
        showToast("Nama grup harus diisi!", "warning");
        return;
    }
    dragDropGroups.value.push({
        group_name: currentDragDropGroup.value.group_name,
        local_id: Date.now() + Math.random(),
    });
    currentDragDropGroup.value = { group_name: "" };
    showToast("Grup ditambahkan!", "success");
};
const removeDragDropGroup = (local_id) => {
    dragDropGroups.value = dragDropGroups.value.filter(
        (g) => g.local_id !== local_id,
    );
    dragDropItems.value = dragDropItems.value.filter(
        (i) => i.group_local_id !== local_id,
    );
};
const addDragDropItem = () => {
    if (!currentDragDropItem.value.item_text.trim()) {
        showToast("Teks item harus diisi!", "warning");
        return;
    }
    if (!currentDragDropItem.value.group_local_id) {
        showToast("Pilih grup yang benar untuk item ini!", "warning");
        return;
    }
    // preserve image file + preview if present
    const item = {
        ...currentDragDropItem.value,
        id: Date.now() + Math.random(),
    };
    // if a File is present, create a preview URL for display
    if (item.item_image_file instanceof File) {
        try {
            item.item_image_preview = URL.createObjectURL(item.item_image_file);
        } catch (e) {
            item.item_image_preview = null;
        }
    }
    dragDropItems.value.push(item);
    currentDragDropItem.value = {
        item_text: "",
        group_local_id: null,
        item_image_file: null,
        item_image_preview: null,
    };
    showToast("Item ditambahkan!", "success");
};
const removeDragDropItem = (id) => {
    dragDropItems.value = dragDropItems.value.filter((i) => i.id !== id);
};
const getGroupName = (local_id) =>
    dragDropGroups.value.find((g) => g.local_id === local_id)?.group_name || "";

// --- Final Save ---
const finalSave = () => {
    // Validation
    if (isTrueFalse.value) {
        if (!trueFalseQuestionText.value.trim()) {
            showToast("Teks pertanyaan harus diisi!", "warning");
            return;
        }
        if (trueFalseOptions.value.length < 2) {
            showToast("Tambahkan minimal 2 opsi gambar!", "warning");
            return;
        }
        if (!trueFalseOptions.value.some((o) => o.is_correct)) {
            showToast(
                "Minimal 1 opsi harus ditandai sebagai jawaban benar!",
                "warning",
            );
            return;
        }
    } else if (!isDragDrop.value && quizQuestions.value.length === 0) {
        showToast("Tambahkan minimal 1 pertanyaan!", "warning");
        return;
    }
    if (isDragDrop.value) {
        if (dragDropGroups.value.length < 2) {
            showToast("Tambahkan minimal 2 grup!", "warning");
            return;
        }
        if (dragDropItems.value.length < 2) {
            showToast("Tambahkan minimal 2 item!", "warning");
            return;
        }
    }

    const formData = new FormData();
    formData.append("title", quizForm.value.title);
    formData.append("description", quizForm.value.description || "");
    formData.append("type", quizForm.value.type);
    formData.append("time_limit", quizForm.value.time_limit);
    formData.append("category", quizForm.value.category);
    if (quizImageFile.value) formData.append("image", quizImageFile.value);

    if (isTrueFalse.value) {
        // Kirim question text & mascot, dan options dengan gambar via FormData
        const tfQuestionMeta = {
            question_text: trueFalseQuestionText.value,
            mascot_id: trueFalseMascotId.value,
            options: trueFalseOptions.value.map((o, idx) => ({
                option_text: o.option_text,
                is_correct: o.is_correct,
                has_image: !!o.option_image_file,
                image_index: idx,
            })),
        };
        formData.append("tf_question", JSON.stringify(tfQuestionMeta));
        // Kirim file gambar per opsi
        trueFalseOptions.value.forEach((o, idx) => {
            if (o.option_image_file) {
                formData.append(
                    `tf_option_images[${idx}]`,
                    o.option_image_file,
                );
            }
        });
    } else {
        let questions = [];
        if (isDragDrop.value) {
            const groupIndexMap = {};
            dragDropGroups.value.forEach((g, idx) => {
                groupIndexMap[g.local_id] = idx;
            });
            questions = [
                {
                    question_text:
                        dragDropQuestionText.value || quizForm.value.title,
                    mascot_id: dragDropMascotId.value,
                    image: null,
                    drag_drop_groups: dragDropGroups.value.map((g) => ({
                        group_name: g.group_name,
                    })),
                    drag_drop_items: dragDropItems.value.map((i) => ({
                        item_text: i.item_text,
                        // indicate whether the item has an image; actual files are appended separately
                        item_image: i.item_image_file ? true : null,
                        group_index: groupIndexMap[i.group_local_id],
                    })),
                },
            ];
        } else {
            questions = quizQuestions.value.map((q, index) => ({
                question_text: q.question_text,
                mascot_id: q.mascot_id,
                image: null,
                order_number: index + 1,
                options: q.options.map((o) => ({
                    option_text: o.option_text,
                    is_correct: o.is_correct,
                    feedback: o.feedback || null,
                })),
            }));
        }
        formData.append("questions", JSON.stringify(questions));
        // attach drag & drop item images if present
        if (isDragDrop.value) {
            dragDropItems.value.forEach((i, idx) => {
                if (i.item_image_file instanceof File) {
                    formData.append(
                        `drag_item_images[${idx}]`,
                        i.item_image_file,
                    );
                }
            });
        }
    }

    // Choose correct route based on whether mission is present
    if (props.mission) {
        router.post(
            route("admin.modules.missions.quizzes.store", [
                props.module.id,
                props.mission.id,
            ]),
            formData,
            {
                onSuccess: (page) => {
                    if (page.props.flash?.error) {
                        showToast("Gagal: " + page.props.flash.error, "error");
                        return;
                    }
                    showToast("Kuis berhasil disimpan.", "success");
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
    } else {
        // module-level (pretest/posttest)
        router.post(
            route("admin.modules.quizzes.store", [props.module.id]),
            formData,
            {
                onSuccess: (page) => {
                    if (page.props.flash?.error) {
                        showToast("Gagal: " + page.props.flash.error, "error");
                        return;
                    }
                    showToast("Kuis berhasil disimpan.", "success");
                    setTimeout(() => {
                        router.visit(
                            route("admin.modules.show", [props.module.id]),
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
    }
};
</script>

<template>
    <AppLayout>
        <div class="p-5">
            <!-- HEADER -->
            <div
                :class="[
                    'rounded-3xl p-5 mb-8',
                    cardVariant === 'playful'
                        ? 'bg-white border-4 border-orange-200 shadow-playful'
                        : 'bg-white border border-gray-200 shadow-md',
                ]"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div
                            :class="[
                                cardVariant === 'playful'
                                    ? 'bg-orange-100 p-3 rounded-2xl border-2 border-orange-300'
                                    : 'bg-orange-50 p-2 rounded-lg',
                            ]"
                        >
                            <HelpCircle class="text-orange-600 w-6 h-6" />
                        </div>
                        <div>
                            <h1
                                class="text-2xl md:text-3xl font-heading font-bold text-gray-800"
                            >
                                {{
                                    mission
                                        ? "Buat Kuis untuk " + mission.name
                                        : props.presetCategory
                                          ? "Buat " +
                                            getCategoryLabel(
                                                props.presetCategory,
                                            ) +
                                            " untuk " +
                                            module.name
                                          : "Buat Kuis untuk " + module.name
                                }}
                            </h1>
                            <p class="text-sm text-gray-500">
                                Modul: {{ module.name }} | Template:
                                {{ module.template?.name || "No Template" }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- WIZARD PROGRESS -->
            <div class="mb-8">
                <div class="flex items-center justify-center gap-4 flex-wrap">
                    <!-- Step 1: Info Quiz -->
                    <div class="flex items-center">
                        <div
                            :class="[
                                'w-10 h-10 rounded-full flex items-center justify-center font-bold',
                                wizardStep === 1
                                    ? 'bg-orange-500 text-white'
                                    : 'bg-gray-300 text-gray-600',
                            ]"
                        >
                            1
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700"
                            >Info Kuis</span
                        >
                    </div>

                    <div class="h-1 w-16 bg-gray-300"></div>

                    <!-- Step 2: Pertanyaan (multiple_choices / case_study only) -->
                    <template v-if="!isDragDrop && !isTrueFalse">
                        <div class="flex items-center">
                            <div
                                :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center font-bold',
                                    wizardStep === 2
                                        ? 'bg-orange-500 text-white'
                                        : 'bg-gray-300 text-gray-600',
                                ]"
                            >
                                2
                            </div>
                            <span class="ml-2 text-sm font-medium text-gray-700"
                                >Pertanyaan</span
                            >
                        </div>
                        <div class="h-1 w-16 bg-gray-300"></div>
                    </template>

                    <!-- Step 2: Grup + Step 3: Items (drag_drop only) -->
                    <template v-if="isDragDrop">
                        <div class="flex items-center">
                            <div
                                :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center font-bold',
                                    wizardStep === 3
                                        ? 'bg-orange-500 text-white'
                                        : 'bg-gray-300 text-gray-600',
                                ]"
                            >
                                2
                            </div>
                            <span class="ml-2 text-sm font-medium text-gray-700"
                                >Grup</span
                            >
                        </div>
                        <div class="h-1 w-16 bg-gray-300"></div>
                        <div class="flex items-center">
                            <div
                                :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center font-bold',
                                    wizardStep === 4
                                        ? 'bg-orange-500 text-white'
                                        : 'bg-gray-300 text-gray-600',
                                ]"
                            >
                                3
                            </div>
                            <span class="ml-2 text-sm font-medium text-gray-700"
                                >Items</span
                            >
                        </div>
                        <div class="h-1 w-16 bg-gray-300"></div>
                    </template>

                    <!-- Step 2: Opsi Gambar (true_false only) -->
                    <template v-if="isTrueFalse">
                        <div class="flex items-center">
                            <div
                                :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center font-bold',
                                    wizardStep === 3
                                        ? 'bg-orange-500 text-white'
                                        : 'bg-gray-300 text-gray-600',
                                ]"
                            >
                                2
                            </div>
                            <span class="ml-2 text-sm font-medium text-gray-700"
                                >Opsi Gambar</span
                            >
                        </div>
                        <div class="h-1 w-16 bg-gray-300"></div>
                    </template>

                    <!-- Step terakhir: Review (semua tipe) -->
                    <div class="flex items-center">
                        <div
                            :class="[
                                'w-10 h-10 rounded-full flex items-center justify-center font-bold',
                                wizardStep === reviewStep
                                    ? 'bg-orange-500 text-white'
                                    : 'bg-gray-300 text-gray-600',
                            ]"
                        >
                            {{ isDragDrop ? 4 : 3 }}
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700"
                            >Review</span
                        >
                    </div>
                </div>
            </div>

            <!-- STEP 1: Info Quiz -->
            <div v-if="wizardStep === 1" class="max-w-3xl mx-auto">
                <Card
                    :variant="cardVariant"
                    title="Informasi Kuis"
                    subtitle="Isi detail kuis"
                    :icon="Info"
                    icon-color="orange"
                    border-color="orange"
                    :hoverable="false"
                >
                    <div class="space-y-5">
                        <InputField
                            v-model="quizForm.title"
                            label="Judul Kuis"
                            placeholder="Contoh: Kuis Angka 1-10"
                            required
                            border-color="orange"
                        />
                        <TextareaField
                            v-model="quizForm.description"
                            label="Deskripsi Kuis"
                            placeholder="Jelaskan kuis ini..."
                            :rows="3"
                            border-color="orange"
                        />
                        <!-- Image Quiz -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                                >Gambar Kuis (Opsional)</label
                            >
                            <div class="space-y-3">
                                <div
                                    v-if="quizImagePreview"
                                    class="relative inline-block"
                                >
                                    <img
                                        :src="quizImagePreview"
                                        alt="Preview"
                                        class="h-32 w-32 object-cover rounded-lg border-2 border-gray-300"
                                    />
                                    <button
                                        type="button"
                                        @click="removeQuizImage"
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                    >
                                        <X class="w-4 h-4" />
                                    </button>
                                </div>
                                <label
                                    class="cursor-pointer inline-flex items-center px-4 py-2 bg-orange-50 text-orange-600 rounded-lg hover:bg-orange-100 border-2 border-orange-200"
                                >
                                    <ImageIcon class="w-5 h-5 mr-2" />Pilih
                                    Gambar
                                    <input
                                        type="file"
                                        @change="handleQuizImageChange"
                                        accept="image/*"
                                        class="hidden"
                                    />
                                </label>
                            </div>
                        </div>
                        <InputField
                            v-model.number="quizForm.time_limit"
                            type="number"
                            :label="`Batas Waktu (${timeUnitLabel})`"
                            placeholder="30"
                            border-color="orange"
                            min="1"
                        />
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <SelectField
                                v-model="quizForm.type"
                                label="Tipe Kuis"
                                :options="quizTypeOptions"
                                border-color="orange"
                            />
                            <SelectField
                                v-model="quizForm.category"
                                label="Kategori"
                                :options="
                                    props.mission
                                        ? [{ value: 'mission', label: 'Misi' }]
                                        : categoryOptions
                                "
                                border-color="orange"
                                :disabled="
                                    !!props.presetCategory || !!props.mission
                                "
                            />
                        </div>
                        <div
                            v-if="isTrueFalse"
                            class="bg-teal-50 p-3 rounded-xl border border-teal-200"
                        >
                            <p
                                class="text-sm text-teal-700 flex items-center gap-2"
                            >
                                <ImageIcon class="w-4 h-4 shrink-0" />
                                Mode <strong>True/False (Pilih Gambar)</strong>:
                                siswa memilih satu atau lebih gambar yang benar
                                sesuai pertanyaan.
                            </p>
                        </div>
                        <div
                            v-if="mascotOptions.length === 0"
                            class="bg-red-50 p-4 rounded-xl border-2 border-red-200"
                        >
                            <p
                                class="text-sm text-red-700 flex items-center gap-2"
                            >
                                <AlertTriangle class="w-4 h-4 shrink-0" />
                                Template modul ini belum memiliki maskot.
                                Tambahkan maskot di template terlebih dahulu.
                            </p>
                        </div>
                        <div
                            v-else
                            class="bg-blue-50 p-3 rounded-xl border border-blue-200"
                        >
                            <p
                                class="text-sm text-blue-700 flex items-center gap-2"
                            >
                                <Info class="w-4 h-4 shrink-0" />
                                Maskot dipilih per pertanyaan pada langkah
                                berikutnya.
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
                                        props.mission
                                            ? route(
                                                  'admin.modules.missions.show',
                                                  [module.id, mission.id],
                                              )
                                            : route(
                                                  'admin.modules.show',
                                                  module.id,
                                              ),
                                    )
                                "
                                >Batal</Button
                            >
                            <Button
                                variant="primary"
                                size="md"
                                @click="nextStep"
                                >Lanjut</Button
                            >
                        </div>
                    </template>
                </Card>
            </div>

            <!-- STEP 2: Pertanyaan (multiple_choices / case_study) -->
            <div
                v-if="wizardStep === 2 && !isDragDrop && !isTrueFalse"
                class="max-w-5xl mx-auto"
            >
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <Card
                        :variant="cardVariant"
                        title="Buat Pertanyaan"
                        :icon="HelpCircle"
                        icon-color="blue"
                        border-color="blue"
                        :hoverable="false"
                    >
                        <div class="space-y-4">
                            <TextareaField
                                v-model="currentQuestion.question_text"
                                label="Teks Pertanyaan"
                                placeholder="Tulis pertanyaan..."
                                :rows="3"
                                border-color="blue"
                            />
                            <div v-if="mascotOptions.length > 0">
                                <SelectField
                                    v-model="currentQuestion.mascot_id"
                                    label="Maskot (Opsional)"
                                    :options="mascotOptions"
                                    placeholder="-- Pilih Maskot --"
                                    border-color="yellow"
                                />
                                <div
                                    v-if="currentQuestion.mascot_id"
                                    class="mt-2 bg-yellow-50 p-3 rounded-xl border-2 border-yellow-200 flex items-center gap-3"
                                >
                                    <img
                                        :src="`/storage/${getSelectedMascot(currentQuestion.mascot_id)?.image}`"
                                        class="w-10 h-10 object-contain rounded-lg"
                                    />
                                    <span
                                        class="font-bold text-sm text-yellow-800 flex-1"
                                        >{{
                                            getSelectedMascot(
                                                currentQuestion.mascot_id,
                                            )?.name
                                        }}</span
                                    >
                                    <button
                                        type="button"
                                        @click="
                                            currentQuestion.mascot_id = null
                                        "
                                        class="p-1.5 rounded-full text-yellow-600 hover:bg-yellow-200 transition"
                                    >
                                        <X class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                            <div class="border-t pt-4">
                                <h4 class="font-bold text-sm mb-3">
                                    Tambah Opsi Jawaban
                                </h4>
                                <div class="space-y-2">
                                    <InputField
                                        v-model="currentOption.option_text"
                                        placeholder="Teks opsi jawaban"
                                        border-color="green"
                                    />
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            v-model="currentOption.is_correct"
                                            class="rounded"
                                        />
                                        <span class="text-sm"
                                            >Tandai sebagai jawaban benar</span
                                        >
                                    </label>
                                    <Button
                                        variant="success"
                                        size="sm"
                                        :icon="Plus"
                                        @click="addOption"
                                        >Tambah Opsi</Button
                                    >
                                </div>
                            </div>
                            <div
                                v-if="questionOptions.length > 0"
                                class="space-y-2"
                            >
                                <h4 class="font-bold text-sm">Opsi Jawaban:</h4>
                                <div
                                    v-for="option in questionOptions"
                                    :key="option.id"
                                    :class="[
                                        'p-3 rounded-lg border-2 flex items-center justify-between',
                                        option.is_correct
                                            ? 'bg-green-50 border-green-300'
                                            : 'bg-gray-50 border-gray-200',
                                    ]"
                                >
                                    <div class="flex items-center gap-2">
                                        <CheckCircle
                                            v-if="option.is_correct"
                                            class="text-green-600 w-4 h-4 shrink-0"
                                        />
                                        <div>
                                            <span class="text-sm">{{
                                                option.option_text
                                            }}</span>
                                            <p
                                                v-if="option.feedback"
                                                class="text-xs text-gray-500"
                                            >
                                                {{ option.feedback }}
                                            </p>
                                        </div>
                                    </div>
                                    <Button
                                        variant="danger"
                                        size="xs"
                                        :icon="Trash2"
                                        @click="removeOption(option.id)"
                                    />
                                </div>
                            </div>
                            <Button
                                variant="primary"
                                size="md"
                                :icon="Plus"
                                @click="addQuestion"
                                class="w-full"
                                >Tambah Pertanyaan</Button
                            >
                        </div>
                    </Card>
                    <Card
                        :variant="cardVariant"
                        title="Daftar Pertanyaan"
                        :badge="`${quizQuestions.length} Pertanyaan`"
                        badge-color="green"
                        :icon="List"
                        icon-color="green"
                        border-color="green"
                        :hoverable="false"
                    >
                        <div
                            v-if="quizQuestions.length === 0"
                            class="text-center py-8 text-gray-500"
                        >
                            Belum ada pertanyaan
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="(question, index) in quizQuestions"
                                :key="question.id"
                                class="p-3 bg-blue-50 rounded-lg border-2 border-blue-200"
                            >
                                <div
                                    class="flex justify-between items-start mb-2"
                                >
                                    <span
                                        class="font-bold text-sm text-blue-800"
                                        >Q{{ index + 1 }}</span
                                    >
                                    <Button
                                        variant="danger"
                                        size="xs"
                                        :icon="Trash2"
                                        @click="removeQuestion(question.id)"
                                    />
                                </div>
                                <p class="text-sm mb-1">
                                    {{ question.question_text }}
                                </p>
                                <div class="text-xs text-blue-600">
                                    {{ question.options.length }} opsi
                                </div>
                            </div>
                        </div>
                    </Card>
                </div>
                <div class="flex justify-between mt-6">
                    <Button variant="light" size="md" @click="prevStep"
                        >Kembali</Button
                    >
                    <Button variant="primary" size="md" @click="nextStep"
                        >Lanjut Review</Button
                    >
                </div>
            </div>

            <!-- STEP 3: True/False — Opsi Gambar -->
            <div
                v-if="wizardStep === 3 && isTrueFalse"
                class="max-w-3xl mx-auto"
            >
                <Card
                    :variant="cardVariant"
                    title="Buat Soal & Opsi Gambar"
                    subtitle="Siswa akan memilih gambar yang benar"
                    :icon="ToggleLeft"
                    icon-color="teal"
                    border-color="teal"
                    :hoverable="false"
                >
                    <div class="space-y-5">
                        <!-- Pertanyaan -->
                        <TextareaField
                            v-model="trueFalseQuestionText"
                            label="Teks Pertanyaan / Instruksi"
                            placeholder="Contoh: Pilih gambar hewan yang berkaki empat!"
                            :rows="3"
                            border-color="teal"
                        />

                        <!-- Mascot -->
                        <div v-if="mascotOptions.length > 0">
                            <SelectField
                                v-model="trueFalseMascotId"
                                label="Maskot (Opsional)"
                                :options="mascotOptions"
                                placeholder="-- Pilih Maskot --"
                                border-color="yellow"
                            />
                            <div
                                v-if="trueFalseMascotId"
                                class="mt-2 bg-yellow-50 p-3 rounded-xl border-2 border-yellow-200 flex items-center gap-3"
                            >
                                <img
                                    :src="`/storage/${getSelectedMascot(trueFalseMascotId)?.image}`"
                                    class="w-10 h-10 object-contain rounded-lg"
                                />
                                <span
                                    class="font-bold text-sm text-yellow-800 flex-1"
                                    >{{
                                        getSelectedMascot(trueFalseMascotId)
                                            ?.name
                                    }}</span
                                >
                                <button
                                    type="button"
                                    @click="trueFalseMascotId = null"
                                    class="p-1.5 rounded-full text-yellow-600 hover:bg-yellow-200 transition"
                                >
                                    <X class="w-5 h-5" />
                                </button>
                            </div>
                        </div>

                        <!-- Form Tambah Opsi Gambar -->
                        <div class="border-t pt-4 space-y-3">
                            <h4 class="font-bold text-sm text-gray-700">
                                Tambah Opsi Gambar
                            </h4>
                            <InputField
                                v-model="currentTFOption.option_text"
                                label="Label Opsi"
                                placeholder="Contoh: Kucing"
                                border-color="teal"
                            />

                            <!-- Upload Gambar -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Gambar Opsi
                                    <span class="text-red-500">*</span></label
                                >
                                <div class="space-y-2">
                                    <div
                                        v-if="currentTFImagePreview"
                                        class="relative inline-block"
                                    >
                                        <img
                                            :src="currentTFImagePreview"
                                            alt="Preview"
                                            class="h-28 w-28 object-cover rounded-xl border-2 border-teal-300"
                                        />
                                        <button
                                            type="button"
                                            @click="removeTFImage"
                                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                        >
                                            <X class="w-4 h-4" />
                                        </button>
                                    </div>
                                    <label
                                        class="cursor-pointer inline-flex items-center px-4 py-2 bg-teal-50 text-teal-600 rounded-lg hover:bg-teal-100 border-2 border-teal-200"
                                    >
                                        <ImageIcon class="w-5 h-5 mr-2" />
                                        {{
                                            currentTFImagePreview
                                                ? "Ganti Gambar"
                                                : "Pilih Gambar"
                                        }}
                                        <input
                                            type="file"
                                            @change="handleTFImageChange"
                                            accept="image/*"
                                            class="hidden"
                                        />
                                    </label>
                                </div>
                            </div>

                            <label
                                class="flex items-center gap-2 cursor-pointer"
                            >
                                <input
                                    type="checkbox"
                                    v-model="currentTFOption.is_correct"
                                    class="rounded w-4 h-4 accent-teal-500"
                                />
                                <CheckSquare
                                    :class="[
                                        'w-4 h-4 shrink-0',
                                        currentTFOption.is_correct
                                            ? 'text-teal-500'
                                            : 'text-gray-400',
                                    ]"
                                />
                                <span class="text-sm font-medium text-gray-700">
                                    Tandai sebagai jawaban
                                    <strong>BENAR</strong>
                                </span>
                            </label>

                            <Button
                                variant="success"
                                size="md"
                                :icon="Plus"
                                @click="addTFOption"
                                >Tambah Opsi</Button
                            >
                        </div>

                        <!-- Daftar Opsi yang Sudah Dibuat -->
                        <div
                            v-if="trueFalseOptions.length > 0"
                            class="space-y-3 border-t pt-4"
                        >
                            <h4 class="font-bold text-sm text-gray-700">
                                Opsi yang sudah dibuat ({{
                                    trueFalseOptions.length
                                }}):
                            </h4>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <div
                                    v-for="opt in trueFalseOptions"
                                    :key="opt.id"
                                    :class="[
                                        'relative rounded-xl border-2 overflow-hidden',
                                        opt.is_correct
                                            ? 'border-green-400 bg-green-50'
                                            : 'border-gray-300 bg-gray-50',
                                    ]"
                                >
                                    <img
                                        v-if="opt.option_image_preview"
                                        :src="opt.option_image_preview"
                                        alt="Opsi"
                                        class="w-full h-24 object-cover"
                                    />
                                    <div
                                        v-else
                                        class="w-full h-24 bg-gray-200 flex items-center justify-center text-gray-400 text-xs"
                                    >
                                        No Image
                                    </div>
                                    <div class="p-2">
                                        <p
                                            class="text-xs font-medium text-gray-700 truncate"
                                        >
                                            {{ opt.option_text || "—" }}
                                        </p>
                                        <div
                                            v-if="opt.is_correct"
                                            class="flex items-center gap-1 mt-1"
                                        >
                                            <CheckCircle
                                                class="w-3 h-3 text-green-600"
                                            />
                                            <span
                                                class="text-xs text-green-700 font-semibold"
                                                >Benar</span
                                            >
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="removeTFOption(opt.id)"
                                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-0.5 hover:bg-red-600"
                                    >
                                        <X class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <template #footer>
                        <div class="flex justify-between mt-6">
                            <Button variant="light" size="md" @click="prevStep"
                                >Kembali</Button
                            >
                            <Button
                                variant="primary"
                                size="md"
                                @click="nextStep"
                                >Lanjut Review</Button
                            >
                        </div>
                    </template>
                </Card>
            </div>

            <!-- STEP 3: Drag & Drop — Grup -->
            <div
                v-if="wizardStep === 3 && isDragDrop"
                class="max-w-3xl mx-auto"
            >
                <Card
                    :variant="cardVariant"
                    title="Buat Grup"
                    subtitle="Buat grup untuk drag & drop"
                    :icon="LayoutGrid"
                    icon-color="purple"
                    border-color="purple"
                    :hoverable="false"
                >
                    <div class="space-y-4">
                        <TextareaField
                            v-model="dragDropQuestionText"
                            label="Instruksi / Teks Pertanyaan"
                            placeholder="Contoh: Kelompokkan item berikut!"
                            :rows="2"
                            border-color="blue"
                        />
                        <div v-if="mascotOptions.length > 0">
                            <SelectField
                                v-model="dragDropMascotId"
                                label="Maskot (Opsional)"
                                :options="mascotOptions"
                                placeholder="-- Pilih Maskot --"
                                border-color="yellow"
                            />
                            <div
                                v-if="dragDropMascotId"
                                class="mt-2 bg-yellow-50 p-3 rounded-xl border-2 border-yellow-200 flex items-center gap-3"
                            >
                                <img
                                    :src="`/storage/${getSelectedMascot(dragDropMascotId)?.image}`"
                                    class="w-10 h-10 object-contain rounded-lg"
                                />
                                <span
                                    class="font-bold text-sm text-yellow-800 flex-1"
                                    >{{
                                        getSelectedMascot(dragDropMascotId)
                                            ?.name
                                    }}</span
                                >
                                <button
                                    type="button"
                                    @click="dragDropMascotId = null"
                                    class="p-1.5 rounded-full text-yellow-600 hover:bg-yellow-200 transition"
                                >
                                    <X class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                        <div class="border-t pt-4">
                            <InputField
                                v-model="currentDragDropGroup.group_name"
                                label="Nama Grup"
                                placeholder="Contoh: Angka Genap"
                                border-color="purple"
                            />
                            <Button
                                variant="primary"
                                size="md"
                                :icon="Plus"
                                @click="addDragDropGroup"
                                class="mt-3"
                                >Tambah Grup</Button
                            >
                        </div>
                        <div v-if="dragDropGroups.length > 0" class="space-y-2">
                            <h4 class="font-bold text-sm">
                                Grup yang sudah dibuat:
                            </h4>
                            <div
                                v-for="group in dragDropGroups"
                                :key="group.local_id"
                                class="p-3 bg-purple-50 rounded-lg border-2 border-purple-200 flex items-center justify-between"
                            >
                                <span class="font-medium">{{
                                    group.group_name
                                }}</span>
                                <Button
                                    variant="danger"
                                    size="xs"
                                    :icon="Trash2"
                                    @click="removeDragDropGroup(group.local_id)"
                                />
                            </div>
                        </div>
                    </div>
                    <template #footer>
                        <div class="flex justify-between mt-6">
                            <Button variant="light" size="md" @click="prevStep"
                                >Kembali</Button
                            >
                            <Button
                                variant="primary"
                                size="md"
                                @click="nextStep"
                                >Lanjut ke Items</Button
                            >
                        </div>
                    </template>
                </Card>
            </div>

            <!-- STEP 4: Drag & Drop — Items -->
            <div
                v-if="wizardStep === 4 && isDragDrop"
                class="max-w-3xl mx-auto"
            >
                <Card
                    :variant="cardVariant"
                    title="Buat Item"
                    subtitle="Buat item yang akan di-drag ke grup"
                    :icon="Box"
                    icon-color="green"
                    border-color="green"
                    :hoverable="false"
                >
                    <div class="space-y-4">
                        <InputField
                            v-model="currentDragDropItem.item_text"
                            label="Teks Item"
                            placeholder="Contoh: 2"
                            border-color="green"
                        />
                        <div>
                            <ImageDropzone
                                v-model="currentDragDropItem.item_image_file"
                                label="Gambar Item"
                                accept="image/*"
                                border-color="green"
                            />
                        </div>
                        <SelectField
                            v-model="currentDragDropItem.group_local_id"
                            label="Grup yang Benar"
                            :options="groupSelectOptions"
                            placeholder="-- Pilih Grup --"
                            border-color="purple"
                        />
                        <Button
                            variant="success"
                            size="md"
                            :icon="Plus"
                            @click="addDragDropItem"
                            >Tambah Item</Button
                        >
                        <div v-if="dragDropItems.length > 0" class="space-y-2">
                            <h4 class="font-bold text-sm">
                                Item yang sudah dibuat:
                            </h4>
                            <div
                                v-for="item in dragDropItems"
                                :key="item.id"
                                class="p-3 bg-green-50 rounded-lg border-2 border-green-200 flex items-center justify-between"
                            >
                                <div>
                                    <div class="font-medium">
                                        {{ item.item_text }}
                                    </div>
                                    <div class="text-xs text-gray-600">
                                        →
                                        {{ getGroupName(item.group_local_id) }}
                                    </div>
                                </div>
                                <Button
                                    variant="danger"
                                    size="xs"
                                    :icon="Trash2"
                                    @click="removeDragDropItem(item.id)"
                                />
                            </div>
                        </div>
                    </div>
                    <template #footer>
                        <div class="flex justify-between mt-6">
                            <Button variant="light" size="md" @click="prevStep"
                                >Kembali</Button
                            >
                            <Button
                                variant="primary"
                                size="md"
                                @click="nextStep"
                                >Lanjut Review</Button
                            >
                        </div>
                    </template>
                </Card>
            </div>

            <!-- REVIEW -->
            <div v-if="wizardStep === reviewStep" class="max-w-4xl mx-auto">
                <Card
                    :variant="cardVariant"
                    title="Review Kuis"
                    subtitle="Periksa kembali kuis yang akan disimpan"
                    :icon="CheckCircle"
                    icon-color="blue"
                    border-color="blue"
                    :hoverable="false"
                >
                    <div class="space-y-4">
                        <div
                            class="bg-orange-50 p-4 rounded-xl border-2 border-orange-200"
                        >
                            <h3 class="font-bold text-lg mb-2">
                                {{ quizForm.title }}
                            </h3>
                            <p class="text-sm text-gray-600 mb-3">
                                {{ quizForm.description }}
                            </p>
                            <div
                                class="flex flex-wrap items-center gap-3 text-sm"
                            >
                                <span class="flex items-center gap-1"
                                    ><Clock class="w-4 h-4" />{{
                                        quizForm.time_limit
                                    }} {{ timeUnitLabel }}</span
                                >
                                <span class="flex items-center gap-1"
                                    ><Tag class="w-4 h-4" />{{
                                        quizTypeOptions.find(
                                            (t) => t.value === quizForm.type,
                                        )?.label
                                    }}</span
                                >
                                <span
                                    class="px-2 py-0.5 bg-orange-200 rounded-full text-xs font-semibold capitalize"
                                    >{{
                                        getCategoryLabel(quizForm.category)
                                    }}</span
                                >
                            </div>
                        </div>
                        <div
                            class="bg-blue-50 p-4 rounded-xl border-2 border-blue-200"
                        >
                            <p v-if="!isDragDrop && !isTrueFalse">
                                <strong>{{ quizQuestions.length }}</strong>
                                pertanyaan
                            </p>
                            <p v-else-if="isDragDrop">
                                <strong>{{ dragDropGroups.length }}</strong>
                                grup dan
                                <strong>{{ dragDropItems.length }}</strong> item
                                drag & drop
                            </p>
                            <div v-else-if="isTrueFalse">
                                <p class="font-medium mb-1">
                                    Pertanyaan:
                                    <em>{{ trueFalseQuestionText }}</em>
                                </p>
                                <p>
                                    <strong>{{
                                        trueFalseOptions.length
                                    }}</strong>
                                    opsi gambar (<strong>{{
                                        trueFalseOptions.filter(
                                            (o) => o.is_correct,
                                        ).length
                                    }}</strong>
                                    jawaban benar)
                                </p>
                                <!-- Preview grid gambar opsi -->
                                <div
                                    class="grid grid-cols-3 sm:grid-cols-4 gap-2 mt-3"
                                >
                                    <div
                                        v-for="opt in trueFalseOptions"
                                        :key="opt.id"
                                        :class="[
                                            'rounded-lg border-2 overflow-hidden',
                                            opt.is_correct
                                                ? 'border-green-400'
                                                : 'border-gray-300',
                                        ]"
                                    >
                                        <img
                                            v-if="opt.option_image_preview"
                                            :src="opt.option_image_preview"
                                            class="w-full h-16 object-cover"
                                        />
                                        <div
                                            class="p-1 text-center text-xs truncate"
                                        >
                                            {{ opt.option_text || "—" }}
                                            <span
                                                v-if="opt.is_correct"
                                                class="text-green-600 font-bold"
                                            >
                                                ✓</span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <template #footer>
                        <div class="flex justify-between mt-6">
                            <Button variant="light" size="md" @click="prevStep"
                                >Kembali</Button
                            >
                            <Button
                                variant="success"
                                size="lg"
                                :icon="Check"
                                @click="finalSave"
                                :disabled="quizForm.processing"
                                ><span
                                    v-if="quizForm.processing"
                                    class="flex items-center gap-2"
                                >
                                    <Loader2 class="w-4 h-4 animate-spin" />
                                    Menyimpan...
                                </span>
                                <span v-else>
                                    <span class="flex items-center gap-2">
                                        Simpan
                                    </span>
                                </span>
                            </Button>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
        <Toast
            :show="showSuccess"
            :message="successMessage"
            :type="toastType"
        />
    </AppLayout>
</template>
