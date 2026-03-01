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
    Pencil,
} from "lucide-vue-next";

// Props — sesuai controller edit()
const props = defineProps({
    module: { type: Object, required: true }, // { id, name, template }
    mission: { type: Object, required: true }, // { id, name }
    quiz: { type: Object, required: true }, // quiz dengan relations (questions, options, drag_drop_groups, drag_drop_items)
    mascots: { type: Array, default: () => [] },
});

// Wizard state
const wizardStep = ref(1);
const successMessage = ref("");
const showSuccess = ref(false);
const cardVariant = ref("playful");

// Form — pre-populate dari quiz existing
const quizForm = ref({
    title: props.quiz.title || "",
    description: props.quiz.description || "",
    time_limit: props.quiz.time_limit || 30,
    type: props.quiz.type || "multiple_choices",
    category: props.quiz.category || "mission",
});

// --- Multiple Choice / True-False / Case Study State ---
// Pre-populate dari existing questions
const quizQuestions = ref(
    (props.quiz.questions || []).map((q) => ({
        id: q.id || Date.now() + Math.random(),
        question_text: q.question_text || "",
        mascot_id: q.mascot_id || null,
        image: q.image || null,
        options: (q.options || []).map((o) => ({
            id: o.id || Date.now() + Math.random(),
            option_text: o.option_text || "",
            is_correct: !!o.is_correct,
            feedback: o.feedback || "",
        })),
    })),
);
const currentQuestion = ref({
    question_text: "",
    mascot_id: null,
    image: null,
});
const questionOptions = ref([]);
const currentOption = ref({ option_text: "", is_correct: false, feedback: "" });

// --- Drag & Drop State ---
// Pre-populate dari question pertama jika tipe drag_drop
const firstDdQ =
    props.quiz.type === "drag_drop" ? props.quiz.questions?.[0] || null : null;

const dragDropQuestionText = ref(firstDdQ?.question_text || "");
const dragDropMascotId = ref(firstDdQ?.mascot_id || null);

// Build groups — simpan original_id agar bisa dipakai sebagai referensi item
const dragDropGroups = ref(
    (firstDdQ?.drag_drop_groups || []).map((g) => ({
        local_id: g.id, // pakai id DB sebagai local_id karena sudah unique
        group_name: g.group_name,
    })),
);

// Build items — cari group_local_id berdasarkan drag_drop_group_id
const dragDropItems = ref(
    (firstDdQ?.drag_drop_items || []).map((item) => ({
        id: item.id || Date.now() + Math.random(),
        item_text: item.item_text || "",
        group_local_id: item.drag_drop_group_id, // cocok dengan local_id group di atas
    })),
);

const currentDragDropGroup = ref({ group_name: "" });
const currentDragDropItem = ref({ item_text: "", group_local_id: null });

// Computed
const mascotOptions = computed(() =>
    props.mascots.map((m) => ({ value: m.id, label: m.name })),
);
const getSelectedMascot = (mascotId) =>
    props.mascots.find((m) => m.id == mascotId) || null;

const groupSelectOptions = computed(() =>
    dragDropGroups.value.map((g) => ({
        value: g.local_id,
        label: g.group_name,
    })),
);

const isDragDrop = computed(() => quizForm.value.type === "drag_drop");

const quizTypeOptions = [
    { value: "multiple_choices", label: "Multiple Choice" },
    { value: "drag_drop", label: "Drag & Drop" },
    { value: "true_false", label: "True / False" },
    { value: "case_study", label: "Case Study" },
];
const categoryOptions = [
    { value: "pretest", label: "Pretest" },
    { value: "mission", label: "Mission" },
    { value: "posttest", label: "Posttest" },
];

const showToast = (message) => {
    successMessage.value = message;
    showSuccess.value = true;
    setTimeout(() => {
        showSuccess.value = false;
    }, 2500);
};

// Navigasi wizard
const nextStep = () => {
    if (wizardStep.value === 1) {
        if (!quizForm.value.title.trim()) {
            showToast("Judul quiz harus diisi!");
            return;
        }
        if (isDragDrop.value) {
            wizardStep.value = 3;
            return;
        }
    }
    wizardStep.value++;
};
const prevStep = () => {
    if (wizardStep.value === 3 && isDragDrop.value) {
        wizardStep.value = 1;
        return;
    }
    wizardStep.value--;
};

// --- Multiple Choice Methods ---
const addOption = () => {
    if (!currentOption.value.option_text.trim()) {
        showToast("Teks opsi harus diisi!");
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
        showToast("Teks pertanyaan harus diisi!");
        return;
    }
    if (questionOptions.value.length < 2) {
        showToast("Minimal 2 opsi jawaban diperlukan!");
        return;
    }
    if (!questionOptions.value.some((o) => o.is_correct)) {
        showToast("Minimal 1 opsi harus ditandai sebagai jawaban benar!");
        return;
    }
    quizQuestions.value.push({
        ...currentQuestion.value,
        id: Date.now(),
        options: [...questionOptions.value],
    });
    currentQuestion.value = { question_text: "", mascot_id: null, image: null };
    questionOptions.value = [];
    showToast("Pertanyaan ditambahkan!");
};
const removeQuestion = (id) => {
    quizQuestions.value = quizQuestions.value.filter((q) => q.id !== id);
};

// --- Drag & Drop Methods ---
const addDragDropGroup = () => {
    if (!currentDragDropGroup.value.group_name.trim()) {
        showToast("Nama grup harus diisi!");
        return;
    }
    dragDropGroups.value.push({
        group_name: currentDragDropGroup.value.group_name,
        local_id: Date.now() + Math.random(),
    });
    currentDragDropGroup.value = { group_name: "" };
    showToast("Grup ditambahkan!");
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
        showToast("Teks item harus diisi!");
        return;
    }
    if (!currentDragDropItem.value.group_local_id) {
        showToast("Pilih grup yang benar untuk item ini!");
        return;
    }
    dragDropItems.value.push({
        ...currentDragDropItem.value,
        id: Date.now() + Math.random(),
    });
    currentDragDropItem.value = { item_text: "", group_local_id: null };
    showToast("Item ditambahkan!");
};
const removeDragDropItem = (id) => {
    dragDropItems.value = dragDropItems.value.filter((i) => i.id !== id);
};
const getGroupName = (local_id) =>
    dragDropGroups.value.find((g) => g.local_id === local_id)?.group_name || "";

// --- Final Save (Update) --- sesuai controller update() validation
const finalSave = () => {
    if (!isDragDrop.value && quizQuestions.value.length === 0) {
        showToast("Tambahkan minimal 1 pertanyaan!");
        return;
    }
    if (isDragDrop.value) {
        if (dragDropGroups.value.length < 2) {
            showToast("Tambahkan minimal 2 grup!");
            return;
        }
        if (dragDropItems.value.length < 2) {
            showToast("Tambahkan minimal 2 item!");
            return;
        }
    }

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
                    item_image: null,
                    group_index: groupIndexMap[i.group_local_id],
                })),
            },
        ];
    } else {
        questions = quizQuestions.value.map((q, index) => ({
            question_text: q.question_text,
            mascot_id: q.mascot_id,
            image: q.image,
            order_number: index + 1,
            options: q.options.map((o) => ({
                option_text: o.option_text,
                is_correct: o.is_correct,
                feedback: o.feedback || null,
            })),
        }));
    }

    // Kirim sebagai PUT via _method spoofing (Inertia)
    router.post(
        route("admin.modules.missions.quiz.update", [
            props.module.id,
            props.mission.id,
            props.quiz.id,
        ]),
        {
            _method: "PUT",
            title: quizForm.value.title,
            description: quizForm.value.description,
            type: quizForm.value.type,
            time_limit: quizForm.value.time_limit,
            category: quizForm.value.category,
            questions,
        },
        { onSuccess: () => showToast("Quiz berhasil diperbarui.") },
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
                            <Pencil class="text-orange-600 w-6 h-6" />
                        </div>
                        <div>
                            <h1
                                class="text-2xl md:text-3xl font-heading font-bold text-gray-800"
                            >
                                Edit Quiz: {{ quiz.title }}
                            </h1>
                            <p class="text-sm text-gray-500">
                                Modul: {{ module.name }} | Misi:
                                {{ mission.name }}
                            </p>
                        </div>
                    </div>
                    <Button
                        :variant="
                            cardVariant === 'playful' ? 'warning' : 'light'
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
                                    ? 'bg-orange-500 text-white'
                                    : 'bg-gray-300 text-gray-600',
                            ]"
                        >
                            1
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700"
                            >Info Quiz</span
                        >
                    </div>
                    <div class="h-1 w-20 bg-gray-300"></div>
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
                        <span class="ml-2 text-sm font-medium text-gray-700">{{
                            isDragDrop ? "Grup" : "Pertanyaan"
                        }}</span>
                    </div>
                    <div class="h-1 w-20 bg-gray-300"></div>
                    <div class="flex items-center">
                        <div
                            :class="[
                                'w-10 h-10 rounded-full flex items-center justify-center font-bold',
                                wizardStep === 3
                                    ? 'bg-orange-500 text-white'
                                    : 'bg-gray-300 text-gray-600',
                            ]"
                        >
                            3
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700">{{
                            isDragDrop ? "Items" : "Review"
                        }}</span>
                    </div>
                    <template v-if="isDragDrop">
                        <div class="h-1 w-20 bg-gray-300"></div>
                        <div class="flex items-center">
                            <div
                                :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center font-bold',
                                    wizardStep === 4
                                        ? 'bg-orange-500 text-white'
                                        : 'bg-gray-300 text-gray-600',
                                ]"
                            >
                                4
                            </div>
                            <span class="ml-2 text-sm font-medium text-gray-700"
                                >Review</span
                            >
                        </div>
                    </template>
                </div>
            </div>

            <!-- ===== STEP 1: Info Quiz ===== -->
            <div v-if="wizardStep === 1" class="max-w-3xl mx-auto">
                <Card
                    :variant="cardVariant"
                    title="Informasi Quiz"
                    subtitle="Edit detail quiz"
                    :icon="Info"
                    icon-color="orange"
                    border-color="orange"
                    :hoverable="false"
                >
                    <div class="space-y-5">
                        <InputField
                            v-model="quizForm.title"
                            label="Judul Quiz"
                            placeholder="Contoh: Quiz Angka 1-10"
                            required
                            border-color="orange"
                        />
                        <TextareaField
                            v-model="quizForm.description"
                            label="Deskripsi Quiz"
                            placeholder="Jelaskan quiz ini..."
                            rows="3"
                            border-color="orange"
                        />
                        <InputField
                            v-model.number="quizForm.time_limit"
                            type="number"
                            label="Batas Waktu (detik)"
                            placeholder="30"
                            border-color="orange"
                        />
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <SelectField
                                v-model="quizForm.type"
                                label="Tipe Quiz"
                                :options="quizTypeOptions"
                                border-color="orange"
                            />
                            <SelectField
                                v-model="quizForm.category"
                                label="Kategori"
                                :options="categoryOptions"
                                border-color="orange"
                            />
                        </div>
                        <div
                            v-if="mascotOptions.length === 0"
                            class="bg-red-50 p-4 rounded-xl border-2 border-red-200"
                        >
                            <p
                                class="text-sm text-red-700 flex items-center gap-2"
                            >
                                <AlertTriangle class="w-4 h-4" />
                                Template modul ini belum memiliki maskot.
                            </p>
                        </div>
                        <div
                            v-else
                            class="bg-blue-50 p-3 rounded-xl border border-blue-200"
                        >
                            <p class="text-sm text-blue-700">
                                ℹ️ Maskot dipilih per pertanyaan pada langkah
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
                                        route('admin.modules.missions.show', [
                                            module.id,
                                            mission.id,
                                        ]),
                                    )
                                "
                            >
                                Batal
                            </Button>
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

            <!-- ===== STEP 2: Pertanyaan (non drag_drop) ===== -->
            <div
                v-if="wizardStep === 2 && !isDragDrop"
                class="max-w-5xl mx-auto"
            >
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Form Pertanyaan Baru -->
                    <Card
                        :variant="cardVariant"
                        title="Tambah Pertanyaan"
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
                                rows="3"
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
                                        :src="
                                            getSelectedMascot(
                                                currentQuestion.mascot_id,
                                            )?.url
                                        "
                                        :alt="
                                            getSelectedMascot(
                                                currentQuestion.mascot_id,
                                            )?.name
                                        "
                                        class="w-10 h-10 object-contain rounded-lg"
                                    />
                                    <span
                                        class="font-bold text-sm text-yellow-800"
                                        >{{
                                            getSelectedMascot(
                                                currentQuestion.mascot_id,
                                            )?.name
                                        }}</span
                                    >
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
                                    <InputField
                                        v-model="currentOption.feedback"
                                        placeholder="Feedback (opsional)"
                                        border-color="gray"
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

                    <!-- Daftar Pertanyaan -->
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

            <!-- ===== STEP 3: Drag & Drop — Grup ===== -->
            <div
                v-if="wizardStep === 3 && isDragDrop"
                class="max-w-3xl mx-auto"
            >
                <Card
                    :variant="cardVariant"
                    title="Edit Grup"
                    subtitle="Kelola grup untuk drag & drop"
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
                            rows="2"
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
                                    :src="
                                        getSelectedMascot(dragDropMascotId)?.url
                                    "
                                    :alt="
                                        getSelectedMascot(dragDropMascotId)
                                            ?.name
                                    "
                                    class="w-10 h-10 object-contain rounded-lg"
                                />
                                <span
                                    class="font-bold text-sm text-yellow-800"
                                    >{{
                                        getSelectedMascot(dragDropMascotId)
                                            ?.name
                                    }}</span
                                >
                            </div>
                        </div>

                        <div class="border-t pt-4">
                            <InputField
                                v-model="currentDragDropGroup.group_name"
                                label="Nama Grup Baru"
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
                            <h4 class="font-bold text-sm">Grup saat ini:</h4>
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

            <!-- ===== STEP 4: Drag & Drop — Items ===== -->
            <div
                v-if="wizardStep === 4 && isDragDrop"
                class="max-w-3xl mx-auto"
            >
                <Card
                    :variant="cardVariant"
                    title="Edit Item"
                    subtitle="Kelola item yang akan di-drag ke grup"
                    :icon="Box"
                    icon-color="green"
                    border-color="green"
                    :hoverable="false"
                >
                    <div class="space-y-4">
                        <InputField
                            v-model="currentDragDropItem.item_text"
                            label="Teks Item Baru"
                            placeholder="Contoh: 2"
                            border-color="green"
                        />
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
                            <h4 class="font-bold text-sm">Item saat ini:</h4>
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

            <!-- ===== REVIEW ===== -->
            <div
                v-if="
                    (wizardStep === 3 && !isDragDrop) ||
                    (wizardStep === 5 && isDragDrop)
                "
                class="max-w-4xl mx-auto"
            >
                <Card
                    :variant="cardVariant"
                    title="Review Quiz"
                    subtitle="Periksa kembali perubahan yang akan disimpan"
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
                                    }}s</span
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
                                    >{{ quizForm.category }}</span
                                >
                            </div>
                        </div>
                        <div
                            class="bg-blue-50 p-4 rounded-xl border-2 border-blue-200"
                        >
                            <p v-if="!isDragDrop">
                                <strong>{{ quizQuestions.length }}</strong>
                                pertanyaan
                            </p>
                            <p v-else>
                                <strong>{{ dragDropGroups.length }}</strong>
                                grup dan
                                <strong>{{ dragDropItems.length }}</strong> item
                                drag & drop
                            </p>
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
                                >Simpan Perubahan</Button
                            >
                        </div>
                    </template>
                </Card>
            </div>
        </div>
        <Toast :show="showSuccess" :message="successMessage" type="success" />
    </AppLayout>
</template>
