<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed, watch } from "vue";
import { router, usePage, useForm } from "@inertiajs/vue3";
import Button from "@/Components/UI/Button.vue";
import ConfirmDialog from "@/Components/UI/ConfirmDialog.vue";
import Toast from "@/Components/UI/Toast.vue";
import Modal from "@/Components/UI/Modal.vue";
import FileDropzone from "@/Components/UI/Forms/FileDropzone.vue";
import {
    ArrowLeft,
    ChevronRight,
    Plus,
    FileText,
    HelpCircle,
    Clock,
    User,
    Calendar,
    Tag,
    List,
    Inbox,
    Trash2,
    Pencil,
    Eye,
} from "lucide-vue-next";

const page = usePage();

// Props dari backend
const props = defineProps({
    module: { type: Object, required: true },
    mission: { type: Object, required: true },
    materials: { type: Array, default: () => [] },
    quizzes: { type: Array, default: () => [] },
});

// State management
const showDeleteDialog = ref(false);
const deleteType = ref("");
const selectedItemId = ref(null);

// Toast
const toastMessage = ref("");
const toastType = ref("success");
const toastVisible = ref(false);

const triggerToast = (message, type = "success") => {
    toastMessage.value = message;
    toastType.value = type;
    toastVisible.value = true;
    setTimeout(() => (toastVisible.value = false), 2800);
};

// Flash messages
watch(
    () => page.props.flash?.success,
    (val) => {
        if (val) triggerToast(val, "success");
    },
);
watch(
    () => page.props.flash?.error,
    (val) => {
        if (val) triggerToast(val, "error");
    },
);

// Combine materials and quizzes, sorted by creation date
const sortedItems = computed(() => {
    const materials = props.materials.map((item) => ({
        ...item,
        itemType: "material",
    }));
    const quizzes = props.quizzes.map((item) => ({
        ...item,
        itemType: "quiz",
    }));

    const combined = [...materials, ...quizzes];

    // Sort by created_at in descending order (newest first)
    return combined.sort(
        (a, b) => new Date(b.created_at) - new Date(a.created_at),
    );
});

const totalItems = computed(
    () => props.materials.length + props.quizzes.length,
);

const goBack = () => {
    router.visit(route("admin.modules.show", props.module.id));
};

const goToAddMaterial = () => {
    router.visit(
        route("admin.modules.missions.materials.create", [
            props.module.id,
            props.mission.id,
        ]),
    );
};

const goToAddQuiz = () => {
    router.visit(
        route("admin.modules.missions.quizzes.create", [
            props.module.id,
            props.mission.id,
        ]),
    );
};

// Import forms (CSV/XLSX)
const materialImport = useForm({ file: null });
const quizImport = useForm({ file: null });

const showMaterialImportModal = ref(false);
const showQuizImportModal = ref(false);
const materialPreview = ref(null);
const quizPreview = ref(null);

const setMaterialFile = (file) => {
    materialImport.file = file;
    materialPreview.value = file ? file.name : null;
};

const setQuizFile = (file) => {
    quizImport.file = file;
    quizPreview.value = file ? file.name : null;
};

// FIX: onSuccess juga perlu cek flash.error karena Inertia menganggap
// semua redirect (termasuk back()->with('error',...)) sebagai "success".
const submitMaterialImport = () => {
    if (!materialImport.file) {
        triggerToast("Pilih file CSV/XLSX terlebih dahulu.", "error");
        return;
    }
    const url = route("admin.modules.missions.materials.import", [
        props.module.id,
        props.mission.id,
    ]);
    materialImport.post(url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: (page) => {
            const flashError = page.props?.flash?.error;
            const flashSuccess = page.props?.flash?.success;
            if (flashError) {
                triggerToast(flashError, "error");
            } else {
                triggerToast(
                    flashSuccess || "Import materi berhasil.",
                    "success",
                );
                materialImport.reset();
                materialPreview.value = null;
                showMaterialImportModal.value = false;
            }
        },
        onError: (errs) => {
            const msg =
                errs?.error ||
                errs?.file ||
                Object.values(errs)?.[0] ||
                "Gagal import materi.";
            triggerToast(msg, "error");
        },
    });
};

const submitQuizImport = () => {
    if (!quizImport.file) {
        triggerToast("Pilih file CSV/XLSX terlebih dahulu.", "error");
        return;
    }
    const url = route("admin.modules.missions.quizzes.import", [
        props.module.id,
        props.mission.id,
    ]);
    quizImport.post(url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: (page) => {
            const flashError = page.props?.flash?.error;
            const flashSuccess = page.props?.flash?.success;
            if (flashError) {
                // Import gagal — tampilkan error, jangan tutup modal
                triggerToast(flashError, "error");
            } else {
                triggerToast(
                    flashSuccess || "Import quiz berhasil.",
                    "success",
                );
                quizImport.reset();
                quizPreview.value = null;
                showQuizImportModal.value = false;
            }
        },
        onError: (errs) => {
            // Laravel validation errors (422) masuk sini
            const msg =
                errs?.error ||
                errs?.file ||
                Object.values(errs)?.[0] ||
                "Gagal import quiz.";
            triggerToast(msg, "error");
        },
    });
};

const goToEditMaterial = (materialId) => {
    router.visit(
        route("admin.modules.missions.materials.edit", [
            props.module.id,
            props.mission.id,
            materialId,
        ]),
    );
};

const goToEditQuiz = (quizId) => {
    router.visit(
        route("admin.modules.missions.quizzes.edit", [
            props.module.id,
            props.mission.id,
            quizId,
        ]),
    );
};

const goToShowMaterial = (materialId) => {
    router.visit(
        route("admin.modules.missions.materials.show", [
            props.module.id,
            props.mission.id,
            materialId,
        ]),
    );
};

const goToShowQuiz = (quizId) => {
    router.visit(
        route("admin.modules.missions.quizzes.show", [
            props.module.id,
            props.mission.id,
            quizId,
        ]),
    );
};

const confirmDeleteMaterial = (materialId) => {
    deleteType.value = "material";
    selectedItemId.value = materialId;
    showDeleteDialog.value = true;
};

const confirmDeleteQuiz = (quizId) => {
    deleteType.value = "quiz";
    selectedItemId.value = quizId;
    showDeleteDialog.value = true;
};

const deleteItem = () => {
    if (deleteType.value === "material") {
        router.delete(
            route("admin.modules.missions.materials.destroy", [
                props.module.id,
                props.mission.id,
                selectedItemId.value,
            ]),
            {
                preserveScroll: true,
                onSuccess: () => {
                    showDeleteDialog.value = false;
                },
                onError: () => {
                    triggerToast("Gagal menghapus material.", "error");
                },
            },
        );
    } else {
        router.delete(
            route("admin.modules.missions.quizzes.destroy", [
                props.module.id,
                props.mission.id,
                selectedItemId.value,
            ]),
            {
                preserveScroll: true,
                onSuccess: () => {
                    showDeleteDialog.value = false;
                },
                onError: () => {
                    triggerToast("Gagal menghapus quiz.", "error");
                },
            },
        );
    }
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>

<template>
    <AppLayout>
        <div class="p-5 max-w-7xl mx-auto">
            <!-- Header -->
            <div
                class="bg-white rounded-3xl border-4 border-gray-200 shadow-playful p-6 mb-8"
            >
                <div
                    class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mb-4"
                >
                    <button
                        @click="goBack"
                        class="bg-blue-100 p-3 rounded-2xl border-2 border-blue-300 hover:bg-blue-200 transition-all"
                    >
                        <ArrowLeft class="text-blue-500 w-5 h-5" />
                    </button>

                    <div class="flex-1">
                        <!-- Breadcrumb -->
                        <div
                            class="flex items-center gap-2 text-sm text-gray-500 mb-2"
                        >
                            <span>{{ module.title || module.name }}</span>
                            <ChevronRight class="w-3 h-3" />
                            <span class="text-blue-600 font-medium">
                                Misi {{ mission.order_number }}
                            </span>
                        </div>

                        <h1
                            class="text-2xl md:text-3xl font-heading font-bold text-gray-800 mb-2"
                        >
                            {{ mission.name }}
                        </h1>
                        <p
                            v-if="mission.description"
                            class="text-sm text-gray-600"
                        >
                            {{ mission.description }}
                        </p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-3">
                    <Button
                        class="w-full sm:w-auto"
                        variant="blueLight"
                        size="md"
                        :icon="Plus"
                        @click="goToAddMaterial"
                    >
                        Tambah Materi
                    </Button>
                    <Button
                        class="w-full sm:w-auto"
                        variant="warning"
                        size="md"
                        :icon="Plus"
                        @click="goToAddQuiz"
                    >
                        Tambah Kuis
                    </Button>
                    <!-- Import: Materi (modal) -->
                    <div class="flex items-center gap-2">
                        <Button
                            variant="primary"
                            size="sm"
                            @click="showMaterialImportModal = true"
                        >
                            Import Materi
                        </Button>
                    </div>
                    <!-- Import: Quiz (modal) -->
                    <div class="flex items-center gap-2">
                        <Button
                            variant="warning"
                            size="sm"
                            @click="showQuizImportModal = true"
                        >
                            Import Kuis
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Combined Materials & Quizzes Section -->
            <div>
                <div
                    class="bg-blue-50 border-4 border-blue-100 rounded-2xl p-4 flex items-center justify-between mb-6"
                >
                    <h2 class="text-xl font-bold text-gray-800">
                        Materi & Kuis
                    </h2>
                    <span
                        class="bg-white text-blue-600 px-4 py-2 rounded-full text-sm font-bold shadow-sm border-2 border-blue-100"
                    >
                        {{ totalItems }} Konten
                    </span>
                </div>

                <!-- Empty State -->
                <div
                    v-if="sortedItems.length === 0"
                    class="bg-white rounded-3xl border-4 border-gray-200 shadow-playful p-12 text-center"
                >
                    <Inbox class="text-gray-300 w-16 h-16 mb-4 mx-auto" />
                    <h3 class="text-xl font-bold text-gray-700 mb-2">
                        Belum ada materi atau kuis
                    </h3>
                    <p class="text-gray-500 mb-6">
                        Tambahkan materi atau kuis untuk misi ini
                    </p>
                    <div class="flex justify-center gap-3">
                        <Button
                            variant="blueLight"
                            size="md"
                            :icon="Plus"
                            @click="goToAddMaterial"
                        >
                            Tambah Materi
                        </Button>
                        <Button
                            variant="warning"
                            size="md"
                            :icon="Plus"
                            @click="goToAddQuiz"
                        >
                            Tambah Kuis
                        </Button>
                    </div>
                </div>

                <!-- Items List -->
                <TransitionGroup v-else name="card" tag="div" class="space-y-4">
                    <div
                        v-for="item in sortedItems"
                        :key="`${item.itemType}-${item.id}`"
                        class="bg-white rounded-3xl border-4 shadow-playful p-6 hover:shadow-lg transition-all"
                        :class="{
                            'border-blue-200': item.itemType === 'material',
                            'border-orange-200': item.itemType === 'quiz',
                        }"
                    >
                        <!-- Material Item -->
                        <div
                            v-if="item.itemType === 'material'"
                            class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
                        >
                            <div
                                class="flex flex-col sm:flex-row items-start sm:items-center gap-4 flex-1"
                            >
                                <div
                                    class="bg-blue-100 p-3 rounded-2xl border-2 border-blue-300"
                                >
                                    <FileText class="text-blue-600 w-8 h-8" />
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span
                                            class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700 border border-blue-300 font-medium"
                                        >
                                            MATERI
                                        </span>
                                        <h3
                                            class="text-xl font-bold text-gray-800"
                                        >
                                            {{ item.title }}
                                        </h3>
                                    </div>

                                    <div class="flex items-center gap-3 mb-3">
                                        <span
                                            class="text-xs text-gray-500 flex items-center gap-1"
                                        >
                                            <Clock class="w-3 h-3" />
                                            {{ formatDate(item.created_at) }}
                                        </span>
                                        <span
                                            v-if="item.created_by"
                                            class="text-xs text-gray-500 flex items-center gap-1"
                                        >
                                            <User class="w-3 h-3" />
                                            {{ item.created_by }}
                                        </span>
                                        <span
                                            v-if="item.mascot"
                                            class="text-xs text-gray-500 flex items-center gap-1"
                                        >
                                            <Tag class="w-3 h-3" />
                                            {{ item.mascot.name }}
                                        </span>
                                    </div>

                                    <p
                                        v-if="item.description"
                                        class="text-sm text-gray-600 line-clamp-2"
                                    >
                                        {{ item.description }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-2">
                                <Button
                                    class="w-full sm:w-auto"
                                    variant="info"
                                    size="md"
                                    :icon="Eye"
                                    @click="goToShowMaterial(item.id)"
                                />
                                <Button
                                    class="w-full sm:w-auto"
                                    variant="warning"
                                    size="md"
                                    :icon="Pencil"
                                    @click="goToEditMaterial(item.id)"
                                />
                                <Button
                                    class="w-full sm:w-auto"
                                    variant="danger"
                                    size="md"
                                    :icon="Trash2"
                                    @click="confirmDeleteMaterial(item.id)"
                                />
                            </div>
                        </div>

                        <!-- Quiz Item -->
                        <div
                            v-else
                            class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
                        >
                            <div
                                class="flex flex-col sm:flex-row items-start sm:items-center gap-4 flex-1"
                            >
                                <div
                                    class="bg-orange-100 p-3 rounded-2xl border-2 border-orange-300"
                                >
                                    <HelpCircle
                                        class="text-orange-600 w-8 h-8"
                                    />
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span
                                            class="text-xs px-2 py-1 rounded-full bg-orange-100 text-orange-700 border border-orange-300 font-medium"
                                        >
                                            QUIZ
                                        </span>
                                        <h3
                                            class="text-xl font-bold text-gray-800"
                                        >
                                            {{ item.title }}
                                        </h3>
                                    </div>

                                    <div class="flex items-center gap-3 mb-3">
                                        <span
                                            :class="[
                                                'text-xs px-3 py-1 rounded-full border font-medium',
                                                item.type === 'multiple_choices'
                                                    ? 'bg-blue-100 text-blue-700 border-blue-300'
                                                    : item.type === 'drag_drop'
                                                      ? 'bg-purple-100 text-purple-700 border-purple-300'
                                                      : item.type ===
                                                          'true_false'
                                                        ? 'bg-blue-100 text-blue-700 border-blue-300'
                                                        : item.type ===
                                                            'case_study'
                                                          ? 'bg-pink-100 text-pink-700 border-pink-300'
                                                          : 'bg-gray-100 text-gray-700 border-gray-300',
                                            ]"
                                        >
                                            {{
                                                item.type === "multiple_choices"
                                                    ? "PILIHAN GANDA"
                                                    : item.type === "drag_drop"
                                                      ? "DRAG & DROP"
                                                      : item.type ===
                                                          "true_false"
                                                        ? "TRUE / FALSE"
                                                        : item.type ===
                                                            "case_study"
                                                          ? "CASE STUDY"
                                                          : item.type?.toUpperCase()
                                            }}
                                        </span>
                                        <span
                                            class="text-xs text-gray-500 flex items-center gap-1"
                                        >
                                            <Clock class="w-3 h-3" />
                                            {{ item.time_limit }} menit
                                        </span>
                                        <span
                                            class="text-xs text-gray-500 flex items-center gap-1"
                                        >
                                            <Calendar class="w-3 h-3" />
                                            {{ formatDate(item.created_at) }}
                                        </span>
                                    </div>

                                    <p
                                        v-if="item.description"
                                        class="text-sm text-gray-600 line-clamp-2"
                                    >
                                        {{ item.description }}
                                    </p>

                                    <!-- Quiz Stats -->
                                    <div class="flex gap-4 mt-3">
                                        <span
                                            class="text-xs text-gray-500 flex items-center gap-1"
                                        >
                                            <List class="w-3 h-3" />
                                            {{ item.questions_count || 0 }}
                                            Pertanyaan
                                        </span>
                                        <span
                                            v-if="item.category"
                                            class="text-xs text-gray-500 flex items-center gap-1"
                                        >
                                            <Tag class="w-3 h-3" />
                                            {{ item.category }}
                                        </span>
                                        <span
                                            v-if="item.created_by"
                                            class="text-xs text-gray-500 flex items-center gap-1"
                                        >
                                            <User class="w-3 h-3" />
                                            {{ item.created_by }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-2">
                                <Button
                                    class="w-full sm:w-auto"
                                    variant="info"
                                    size="md"
                                    :icon="Eye"
                                    @click="goToShowQuiz(item.id)"
                                />
                                <Button
                                    class="w-full sm:w-auto"
                                    variant="warning"
                                    size="md"
                                    :icon="Pencil"
                                    @click="goToEditQuiz(item.id)"
                                />
                                <Button
                                    class="w-full sm:w-auto"
                                    variant="danger"
                                    size="md"
                                    :icon="Trash2"
                                    @click="confirmDeleteQuiz(item.id)"
                                />
                            </div>
                        </div>
                    </div>
                </TransitionGroup>
            </div>
        </div>

        <!-- Import Materi Modal -->
        <Modal
            :show="showMaterialImportModal"
            title="Import Materi (CSV / XLSX)"
            @close="showMaterialImportModal = false"
            maxWidth="md"
        >
            <div class="py-4 space-y-4">
                <p class="text-sm text-gray-600">
                    Unggah file CSV atau XLSX dengan header:
                    <strong>title,description,content</strong>. File maksimal 10
                    MB. Tipe file yang diterima:
                    <strong>.csv, .xlsx, .xls</strong>.
                </p>
                <FileDropzone
                    v-model:modelValue="materialImport.file"
                    accept=".csv,.xlsx,.xls"
                    label="Pilih atau seret file CSV/XLSX"
                    borderColor="gray"
                    :allowClear="false"
                    @update:modelValue="setMaterialFile"
                />
                <p v-if="materialPreview" class="text-sm text-gray-500">
                    File terpilih: {{ materialPreview }}
                </p>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <Button
                        variant="ghost"
                        size="md"
                        @click="showMaterialImportModal = false"
                        >Batal</Button
                    >
                    <Button
                        variant="primary"
                        size="md"
                        @click="submitMaterialImport"
                        >Import</Button
                    >
                </div>
            </template>
        </Modal>

        <!-- Import Quiz Modal -->
        <Modal
            :show="showQuizImportModal"
            title="Import Kuis (CSV / XLSX)"
            @close="showQuizImportModal = false"
            maxWidth="lg"
        >
            <div class="py-4 space-y-4">
                <p class="text-sm text-gray-600">
                    Unggah file CSV atau XLSX yang berisi data multiple-choice.
                    Kolom minimal pada setiap baris:
                    <strong
                        >quiz_title, question_text, option_1,
                        option_1_is_correct, option_2, option_2_is_correct,
                        option_3, option_3_is_correct, option_4,
                        option_4_is_correct, dan seterusnya</strong
                    >. Untuk menandai jawaban benar, gunakan nilai
                    <strong>1</strong>, <strong>true</strong>, atau
                    <strong>yes</strong> pada kolom *_is_correct. File maksimal
                    10 MB. Tipe file yang diterima:
                    <strong>.csv, .xlsx, .xls</strong>.
                </p>
                <FileDropzone
                    v-model:modelValue="quizImport.file"
                    accept=".csv,.xlsx,.xls"
                    label="Pilih atau seret file CSV/XLSX"
                    borderColor="gray"
                    :allowClear="false"
                    @update:modelValue="setQuizFile"
                />
                <p v-if="quizPreview" class="text-sm text-gray-500">
                    File terpilih: {{ quizPreview }}
                </p>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <Button
                        variant="ghost"
                        size="md"
                        @click="showQuizImportModal = false"
                        >Batal</Button
                    >
                    <Button
                        variant="primary"
                        size="md"
                        @click="submitQuizImport"
                        >Import</Button
                    >
                </div>
            </template>
        </Modal>

        <ConfirmDialog
            :show="showDeleteDialog"
            :title="
                deleteType === 'material'
                    ? 'Hapus materi ini?'
                    : 'Hapus quiz ini?'
            "
            :message="
                deleteType === 'material'
                    ? 'Materi akan dihapus selamanya.'
                    : 'Semua pertanyaan dan jawaban di dalam quiz ini akan terhapus.'
            "
            @confirm="deleteItem"
            @cancel="showDeleteDialog = false"
        />

        <!-- Toast Notification -->
        <Toast :show="toastVisible" :message="toastMessage" :type="toastType" />
    </AppLayout>
</template>
