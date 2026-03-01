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
} from "lucide-vue-next";

// Props
const props = defineProps({
    moduleId: { type: Number, required: true },
    missionId: { type: Number, required: true },
    moduleName: { type: String, default: "Module" },
    missionName: { type: String, default: "Mission" },
    moduleTemplate: { type: Object, default: null },
});

// Wizard state
const wizardStep = ref(1);
const successMessage = ref("");
const showSuccess = ref(false);
const cardVariant = ref("playful");

const quizForm = ref({
    title: "",
    description: "",
    time_limit: 30,
    type: "multiple_choice",
    mascot_id: null,
});

const quizQuestions = ref([]);
const currentQuestion = ref({
    question_text: "",
    type: "multiple_choice",
    order_number: 1,
});
const questionOptions = ref([]);
const currentOption = ref({ option_text: "", is_correct: false });

const dragDropGroups = ref([]);
const currentDragDropGroup = ref({ group_name: "" });
const dragDropItems = ref([]);
const currentDragDropItem = ref({ item_text: "", correct_group_id: null });

const mascotOptions = computed(() => {
    if (!props.moduleTemplate?.mascots) return [];
    return props.moduleTemplate.mascots.map((m) => ({
        value: m.id,
        label: m.name,
    }));
});

const getSelectedMascot = (mascotId) => {
    if (!props.moduleTemplate?.mascots) return null;
    return props.moduleTemplate.mascots.find((m) => m.id === mascotId);
};

const groupOptions = computed(() => {
    return dragDropGroups.value.map((g) => ({
        value: g.id,
        label: g.group_name,
    }));
});

const showToast = (message) => {
    successMessage.value = message;
    showSuccess.value = true;
    setTimeout(() => {
        showSuccess.value = false;
    }, 2500);
};

const nextStep = () => {
    if (wizardStep.value === 1) {
        if (!quizForm.value.title.trim()) {
            showToast("Judul quiz harus diisi!");
            return;
        }
        if (!quizForm.value.mascot_id && mascotOptions.value.length > 0) {
            showToast("Pilih maskot untuk quiz!");
            return;
        }
    }
    if (wizardStep.value === 1 && quizForm.value.type === "drag_drop") {
        wizardStep.value = 3;
    } else {
        wizardStep.value++;
    }
};

const prevStep = () => {
    if (wizardStep.value === 3 && quizForm.value.type === "drag_drop") {
        wizardStep.value = 1;
    } else {
        wizardStep.value--;
    }
};

const addOption = () => {
    if (!currentOption.value.option_text.trim()) {
        showToast("Teks opsi harus diisi!");
        return;
    }
    questionOptions.value.push({
        ...currentOption.value,
        id: Date.now() + Math.random(),
    });
    currentOption.value = { option_text: "", is_correct: false };
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
    currentQuestion.value = {
        question_text: "",
        type: "multiple_choice",
        order_number: quizQuestions.value.length + 1,
    };
    questionOptions.value = [];
    showToast("Pertanyaan ditambahkan!");
};

const removeQuestion = (id) => {
    quizQuestions.value = quizQuestions.value.filter((q) => q.id !== id);
};

const addDragDropGroup = () => {
    if (!currentDragDropGroup.value.group_name.trim()) {
        showToast("Nama grup harus diisi!");
        return;
    }
    dragDropGroups.value.push({
        ...currentDragDropGroup.value,
        id: Date.now() + Math.random(),
    });
    currentDragDropGroup.value = { group_name: "" };
    showToast("Grup ditambahkan!");
};

const removeDragDropGroup = (id) => {
    dragDropGroups.value = dragDropGroups.value.filter((g) => g.id !== id);
    dragDropItems.value = dragDropItems.value.filter(
        (i) => i.correct_group_id !== id,
    );
};

const addDragDropItem = () => {
    if (!currentDragDropItem.value.item_text.trim()) {
        showToast("Teks item harus diisi!");
        return;
    }
    if (!currentDragDropItem.value.correct_group_id) {
        showToast("Pilih grup yang benar untuk item ini!");
        return;
    }
    dragDropItems.value.push({
        ...currentDragDropItem.value,
        id: Date.now() + Math.random(),
    });
    currentDragDropItem.value = { item_text: "", correct_group_id: null };
    showToast("Item ditambahkan!");
};

const removeDragDropItem = (id) => {
    dragDropItems.value = dragDropItems.value.filter((i) => i.id !== id);
};

const finalSave = () => {
    if (
        quizForm.value.type === "multiple_choice" &&
        quizQuestions.value.length === 0
    ) {
        showToast("Tambahkan minimal 1 pertanyaan!");
        return;
    }
    if (quizForm.value.type === "drag_drop") {
        if (dragDropGroups.value.length < 2) {
            showToast("Tambahkan minimal 2 grup!");
            return;
        }
        if (dragDropItems.value.length < 2) {
            showToast("Tambahkan minimal 2 item!");
            return;
        }
    }

    const payload = {
        quiz: quizForm.value,
        questions:
            quizForm.value.type === "multiple_choice"
                ? quizQuestions.value
                : [],
        drag_drop_groups:
            quizForm.value.type === "drag_drop" ? dragDropGroups.value : [],
        drag_drop_items:
            quizForm.value.type === "drag_drop" ? dragDropItems.value : [],
    };

    router.post(
        `/modules/${props.moduleId}/missions/${props.missionId}/quiz`,
        payload,
        {
            onSuccess: () => {
                showToast("Quiz berhasil disimpan.");
                setTimeout(() => {
                    router.visit(`/modules/${props.moduleId}/missions`);
                }, 1500);
            },
        },
    );
};

const toggleCardVariant = () => {
    cardVariant.value = cardVariant.value === "playful" ? "normal" : "playful";
};

const getGroupName = (groupId) => {
    const group = dragDropGroups.value.find((g) => g.id === groupId);
    return group?.group_name || "";
};
</script>

<template>
    <AppLayout>
        <div class="p-5">
            <!-- Header -->
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
                                Buat Quiz untuk {{ missionName }}
                            </h1>
                            <p class="text-sm text-gray-500">
                                Modul: {{ moduleName }} | Template:
                                {{ moduleTemplate?.name || "No Template" }}
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

            <!-- Wizard Progress -->
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
                            quizForm.type === "multiple_choice"
                                ? "Pertanyaan"
                                : "Grup"
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
                            quizForm.type === "drag_drop" ? "Items" : "Review"
                        }}</span>
                    </div>
                    <div
                        v-if="quizForm.type === 'drag_drop'"
                        class="h-1 w-20 bg-gray-300"
                    ></div>
                    <div
                        v-if="quizForm.type === 'drag_drop'"
                        class="flex items-center"
                    >
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
                </div>
            </div>

            <!-- Step 1: Info Quiz -->
            <div v-if="wizardStep === 1" class="max-w-3xl mx-auto">
                <Card
                    :variant="cardVariant"
                    title="Informasi Quiz"
                    subtitle="Isi detail quiz"
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

                        <SelectField
                            v-model="quizForm.type"
                            label="Tipe Quiz"
                            :options="[
                                {
                                    value: 'multiple_choice',
                                    label: 'Multiple Choice',
                                },
                                { value: 'drag_drop', label: 'Drag & Drop' },
                            ]"
                            border-color="orange"
                        />

                        <div v-if="mascotOptions.length > 0">
                            <SelectField
                                v-model="quizForm.mascot_id"
                                label="Pilih Maskot Quiz"
                                :options="mascotOptions"
                                placeholder="-- Pilih Maskot --"
                                required
                                border-color="yellow"
                            />

                            <div v-if="quizForm.mascot_id" class="mt-3">
                                <div
                                    class="bg-yellow-50 p-4 rounded-xl border-2 border-yellow-200 flex items-center gap-4"
                                >
                                    <img
                                        :src="
                                            getSelectedMascot(
                                                quizForm.mascot_id,
                                            )?.url
                                        "
                                        :alt="
                                            getSelectedMascot(
                                                quizForm.mascot_id,
                                            )?.name
                                        "
                                        class="w-16 h-16 object-contain rounded-lg"
                                    />
                                    <div>
                                        <h4 class="font-bold text-yellow-800">
                                            {{
                                                getSelectedMascot(
                                                    quizForm.mascot_id,
                                                )?.name
                                            }}
                                        </h4>
                                        <p class="text-sm text-yellow-600">
                                            Maskot akan muncul di quiz
                                        </p>
                                    </div>
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
                                <AlertTriangle class="w-4 h-4" />
                                Template modul ini belum memiliki maskot.
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
                                        `/modules/${moduleId}/missions`,
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

            <!-- Step 2: Multiple Choice Questions -->
            <div
                v-if="wizardStep === 2 && quizForm.type === 'multiple_choice'"
                class="max-w-5xl mx-auto"
            >
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Form Pertanyaan -->
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
                                rows="3"
                                border-color="blue"
                            />

                            <!-- Form Opsi -->
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
                                    >
                                        Tambah Opsi
                                    </Button>
                                </div>
                            </div>

                            <!-- List Opsi -->
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
                                            class="text-green-600 w-4 h-4"
                                        />
                                        <span class="text-sm">{{
                                            option.option_text
                                        }}</span>
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
                            >
                                Tambah Pertanyaan
                            </Button>
                        </div>
                    </Card>

                    <!-- List Pertanyaan -->
                    <div>
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
                                    <p class="text-sm mb-2">
                                        {{ question.question_text }}
                                    </p>
                                    <div class="text-xs text-blue-600">
                                        {{ question.options.length }} opsi
                                    </div>
                                </div>
                            </div>
                        </Card>
                    </div>
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

            <!-- Step 2 for Drag & Drop: Groups -->
            <div
                v-if="wizardStep === 2 && quizForm.type === 'drag_drop'"
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
                        >
                            Tambah Grup
                        </Button>

                        <div v-if="dragDropGroups.length > 0" class="space-y-2">
                            <h4 class="font-bold text-sm">
                                Grup yang sudah dibuat:
                            </h4>
                            <div
                                v-for="group in dragDropGroups"
                                :key="group.id"
                                class="p-3 bg-purple-50 rounded-lg border-2 border-purple-200 flex items-center justify-between"
                            >
                                <span class="font-medium">{{
                                    group.group_name
                                }}</span>
                                <Button
                                    variant="danger"
                                    size="xs"
                                    :icon="Trash2"
                                    @click="removeDragDropGroup(group.id)"
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

            <!-- Step 3 for Drag & Drop: Items -->
            <div
                v-if="wizardStep === 3 && quizForm.type === 'drag_drop'"
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

                        <SelectField
                            v-model="currentDragDropItem.correct_group_id"
                            label="Grup yang Benar"
                            :options="groupOptions"
                            placeholder="-- Pilih Grup --"
                            border-color="purple"
                        />

                        <Button
                            variant="success"
                            size="md"
                            :icon="Plus"
                            @click="addDragDropItem"
                        >
                            Tambah Item
                        </Button>

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
                                        {{
                                            getGroupName(item.correct_group_id)
                                        }}
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

            <!-- Step 3/4: Review & Save -->
            <div
                v-if="
                    (wizardStep === 3 && quizForm.type === 'multiple_choice') ||
                    (wizardStep === 4 && quizForm.type === 'drag_drop')
                "
                class="max-w-4xl mx-auto"
            >
                <Card
                    :variant="cardVariant"
                    title="Review Quiz"
                    subtitle="Periksa kembali quiz yang akan disimpan"
                    :icon="CheckCircle"
                    icon-color="blue"
                    border-color="blue"
                    :hoverable="false"
                >
                    <div class="space-y-4">
                        <!-- Quiz Info -->
                        <div
                            class="bg-orange-50 p-4 rounded-xl border-2 border-orange-200"
                        >
                            <h3 class="font-bold text-lg mb-2">
                                {{ quizForm.title }}
                            </h3>
                            <p class="text-sm text-gray-600 mb-2">
                                {{ quizForm.description }}
                            </p>
                            <div class="flex items-center gap-4 text-sm">
                                <span class="flex items-center gap-1"
                                    ><Clock class="w-4 h-4" />{{
                                        quizForm.time_limit
                                    }}s</span
                                >
                                <span class="flex items-center gap-1"
                                    ><Tag class="w-4 h-4" />{{
                                        quizForm.type === "multiple_choice"
                                            ? "Multiple Choice"
                                            : "Drag & Drop"
                                    }}</span
                                >
                            </div>

                            <div
                                v-if="quizForm.mascot_id"
                                class="mt-3 flex items-center gap-3 bg-yellow-50 p-2 rounded-lg border border-yellow-200"
                            >
                                <img
                                    :src="
                                        getSelectedMascot(quizForm.mascot_id)
                                            ?.url
                                    "
                                    class="w-10 h-10 object-contain rounded"
                                />
                                <span
                                    class="text-sm font-medium text-yellow-700"
                                >
                                    {{
                                        getSelectedMascot(quizForm.mascot_id)
                                            ?.name
                                    }}
                                </span>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div
                            class="bg-blue-50 p-4 rounded-xl border-2 border-blue-200"
                        >
                            <div v-if="quizForm.type === 'multiple_choice'">
                                <strong>{{ quizQuestions.length }}</strong>
                                pertanyaan multiple choice
                            </div>
                            <div v-else>
                                <strong>{{ dragDropGroups.length }}</strong>
                                grup dan
                                <strong>{{ dragDropItems.length }}</strong> item
                                drag & drop
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
                            >
                                Simpan Quiz
                            </Button>
                        </div>
                    </template>
                </Card>
            </div>
        </div>

        <!-- Toast Notification -->
        <Toast :show="showSuccess" :message="successMessage" type="success" />
    </AppLayout>
</template>
