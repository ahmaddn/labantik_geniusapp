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
    GripVertical,
    SlidersHorizontal,
    GitCompare,
    MousePointerClick,
    AlignLeft,
    FileArchive,
    Settings,
    Settings2,
    Workflow,
    Volume2,
    VolumeX,
    Mic,
    Upload,
    Play,
    MessageSquare,
} from "lucide-vue-next";
import draggable from "vuedraggable";

const page = usePage();

// Props dari backend
const props = defineProps({
    module: { type: Object, required: true },
    mission: { type: Object, required: true },
    materials: { type: Array, default: () => [] },
    quizzes: { type: Array, default: () => [] },
    simulations: { type: Array, default: () => [] },
    reflections: { type: Array, default: () => [] },
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
    { immediate: true }
);
watch(
    () => page.props.flash?.error,
    (val) => {
        if (val) triggerToast(val, "error");
    },
    { immediate: true }
);

// Combine materials, quizzes, simulations, sorted by order_number or created date
const sortedItems = ref([]);

watch(
    () => [
        props.materials,
        props.quizzes,
        props.simulations,
        props.reflections,
    ],
    () => {
        const materials = props.materials.map((item) => ({
            ...item,
            itemType: "material",
        }));
        const quizzes = props.quizzes.map((item) => ({
            ...item,
            itemType: "quiz",
        }));
        const simulations = props.simulations.map((item) => ({
            ...item,
            itemType: item.type,
        }));
        const reflections = props.reflections.map((item) => ({
            ...item,
            itemType: "reflection",
        }));

        const combined = [
            ...materials,
            ...quizzes,
            ...simulations,
            ...reflections,
        ];
        sortedItems.value = combined.sort((a, b) => {
            if (
                a.order_number !== undefined &&
                b.order_number !== undefined &&
                (a.order_number !== 0 || b.order_number !== 0)
            ) {
                return a.order_number - b.order_number;
            }
            return new Date(b.created_at) - new Date(a.created_at);
        });
    },
    { immediate: true, deep: true },
);

const totalItems = computed(
    () =>
        props.materials.length +
        props.quizzes.length +
        props.simulations.length +
        props.reflections.length,
);

const handleDragEnd = () => {
    // Update order numbers sequentially
    const updatedSteps = sortedItems.value.map((item, index) => ({
        id: item.id,
        itemType: item.itemType,
        order_number: index + 1,
    }));

    router.post(
        route("admin.modules.missions.reorder", [
            props.module.id,
            props.mission.id,
        ]),
        {
            steps: updatedSteps,
        },
        {
            preserveScroll: true,
            onSuccess: () =>
                triggerToast("Urutan berhasil disimpan!", "success"),
            onError: () => triggerToast("Gagal menyimpan urutan.", "error"),
        },
    );
};

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

const goToAddCaseStudy = () => {
    router.visit(
        route("admin.modules.missions.quizzes.create", [
            props.module.id,
            props.mission.id,
        ]) + "?type=case_study",
    );
};

const goToAddReflection = () => {
    router.visit(
        route("admin.modules.missions.reflections.create", [
            props.module.id,
            props.mission.id,
        ]),
    );
};

const goToSimulationConfig = () => {
    router.visit(
        route("admin.modules.missions.simulation.edit", [
            props.module.id,
            props.mission.id,
        ]),
    );
};

// Edit Mission & Voice Over Form
const showEditMissionModal = ref(false);
const voiceoverFileInput = ref(null);
const editMissionForm = useForm({
    _method: 'PUT',
    name: props.mission.name,
    order_number: props.mission.order_number,
    voiceover_file: null,
    remove_voiceover: false,
});

const openEditMissionModal = () => {
    editMissionForm.name = props.mission.name;
    editMissionForm.order_number = props.mission.order_number;
    editMissionForm.voiceover_file = null;
    editMissionForm.remove_voiceover = false;
    showEditMissionModal.value = true;
};

const handleVoiceoverChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        editMissionForm.voiceover_file = file;
        editMissionForm.remove_voiceover = false;
    }
};

const submitEditMission = () => {
    const url = route("admin.modules.missions.update", [
        props.module.id,
        props.mission.id,
    ]);
    editMissionForm.post(url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showEditMissionModal.value = false;
            triggerToast("Pengaturan misi dan Voice Over berhasil disimpan!", "success");
        },
        onError: () => triggerToast("Gagal menyimpan pengaturan misi.", "error"),
    });
};

// Mascot Dialogues Form (Random Sentences)
const showMascotDialogModal = ref(false);
const mascotDialogTargetTitle = ref('');
const mascotDialogForm = useForm({
    item_id: '',
    item_type: '', // 'material' or 'quiz'
    custom_dialogues: '',
});

const openMascotDialogModal = (item) => {
    mascotDialogForm.item_id = item.id;
    mascotDialogForm.item_type = item.itemType;
    mascotDialogForm.custom_dialogues = item.custom_dialogues || '';
    mascotDialogTargetTitle.value = item.title;
    showMascotDialogModal.value = true;
};

const submitMascotDialog = () => {
    const url = route("admin.modules.missions.custom-dialogues.update", [
        props.module.id,
        props.mission.id,
    ]);
    mascotDialogForm.post(url, {
        preserveScroll: true,
        onSuccess: () => {
            showMascotDialogModal.value = false;
            triggerToast("Dialog acak maskot berhasil diperbarui!", "success");
        },
        onError: () => triggerToast("Gagal memperbarui dialog maskot.", "error"),
    });
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

const confirmDeleteReflection = (reflectionId) => {
    deleteType.value = "reflection";
    selectedItemId.value = reflectionId;
    showDeleteDialog.value = true;
};

const simulationTypeToDelete = ref("");
const confirmDeleteSimulation = (simulationId, simulationType) => {
    deleteType.value = "simulation";
    selectedItemId.value = simulationId;
    simulationTypeToDelete.value = simulationType;
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
    } else if (deleteType.value === "quiz") {
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
    } else if (deleteType.value === "reflection") {
        router.delete(
            route("admin.modules.missions.reflections.destroy", [
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
                    triggerToast("Gagal menghapus refleksi.", "error");
                },
            },
        );
    } else if (deleteType.value === "simulation") {
        router.delete(
            route("admin.modules.missions.simulation.destroy", [
                props.module.id,
                props.mission.id,
                selectedItemId.value,
            ]) +
                "?type=" +
                simulationTypeToDelete.value,
            {
                preserveScroll: true,
                onSuccess: () => {
                    showDeleteDialog.value = false;
                },
                onError: () => {
                    triggerToast("Gagal menghapus simulasi.", "error");
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

const getLayoutTypeLabel = (type) => {
    const labels = {
        cover_page: "Halaman Cover",
        learning_objectives: "Tujuan Pembelajaran",
        initial_questions: "Pertanyaan Awal",
        image_comparison: "Mengamati Gambar",
        default: "Reguler (Teks/Video)",
        process_list: "List Proses & Gambar",
        conceptual_systematic: "Konseptual Sistematis",
        interactive_examples: "Contoh Materi Dinamis",
        summary_list: "Ringkasan Materi",
        video_only: "Hanya Video YouTube",
    };
    return labels[type] || type;
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

                        <div class="flex items-center gap-3 flex-wrap mb-2">
                            <h1 class="text-2xl md:text-3xl font-heading font-bold text-gray-800">
                                {{ mission.name }}
                            </h1>
                            <button
                                @click="openEditMissionModal"
                                class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-medium rounded-xl border-2 border-indigo-200 transition-colors text-sm"
                            >
                                <Volume2 class="w-4 h-4 text-indigo-600" />
                                <span>Voice Over & Pengaturan</span>
                            </button>
                        </div>
                        <div v-if="mission.voiceover_url" class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs border border-emerald-200 mb-2">
                            <Volume2 class="w-3.5 h-3.5" />
                            <span>Voice Over Terpasang</span>
                        </div>
                        <p
                            v-if="mission.description"
                            class="text-sm text-gray-600"
                        >
                            {{ mission.description }}
                        </p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div
                    class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"
                >
                    <Button
                        class="w-full"
                        variant="blueLight"
                        size="md"
                        :icon="Plus"
                        @click="goToAddMaterial"
                    >
                        Tambah Materi
                    </Button>
                    <Button
                        class="w-full"
                        variant="warning"
                        size="md"
                        :icon="Plus"
                        @click="goToAddQuiz"
                    >
                        Tambah Kuis
                    </Button>
                    <Button
                        class="w-full"
                        variant="danger"
                        size="md"
                        :icon="Plus"
                        @click="goToAddCaseStudy"
                    >
                        Tambah Studi Kasus
                    </Button>
                    <Button
                        class="w-full"
                        variant="success"
                        size="md"
                        :icon="Plus"
                        @click="goToAddReflection"
                    >
                        Tambah Refleksi
                    </Button>
                    <!-- Import: Materi (modal) -->
                    <Button
                        class="w-full"
                        :icon="FileArchive"
                        variant="primary"
                        size="md"
                        @click="showMaterialImportModal = true"
                    >
                        Import Materi
                    </Button>
                    <!-- Import: Quiz (modal) -->
                    <Button
                        class="w-full"
                        :icon="FileArchive"
                        variant="warning"
                        size="md"
                        @click="showQuizImportModal = true"
                    >
                        Import Kuis
                    </Button>
                    <!-- Konfigurasi Simulasi -->
                    <Button
                        class="w-full"
                        variant="info"
                        :icon="Settings2"
                        size="md"
                        @click="goToSimulationConfig"
                    >
                        Konfig Simulasi
                    </Button>
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
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4"
                    >
                        <Button
                            class="w-full"
                            variant="blueLight"
                            size="md"
                            :icon="Plus"
                            @click="goToAddMaterial"
                        >
                            Tambah Materi
                        </Button>
                        <Button
                            class="w-full"
                            variant="warning"
                            size="md"
                            :icon="Plus"
                            @click="goToAddQuiz"
                        >
                            Tambah Kuis
                        </Button>
                        <Button
                            class="w-full"
                            variant="danger"
                            size="md"
                            :icon="Plus"
                            @click="goToAddCaseStudy"
                        >
                            Tambah Studi Kasus
                        </Button>
                        <Button
                            class="w-full"
                            variant="success"
                            size="md"
                            :icon="Plus"
                            @click="goToAddReflection"
                        >
                            Tambah Refleksi
                        </Button>
                    </div>
                </div>

                <!-- Items List -->
                <draggable
                    v-else
                    v-model="sortedItems"
                    item-key="id"
                    handle=".drag-handle"
                    @end="handleDragEnd"
                    class="space-y-4"
                >
                    <template #item="{ element: item }">
                        <div
                            class="bg-white rounded-3xl border-4 shadow-playful p-6 hover:shadow-lg transition-all"
                            :class="{
                                'border-blue-200': item.itemType === 'material',
                                'border-orange-200':
                                    item.itemType === 'quiz' &&
                                    item.type !== 'case_study',
                                'border-teal-200':
                                    item.itemType === 'quiz' &&
                                    item.type === 'case_study',
                                'border-purple-200':
                                    item.itemType.startsWith('simulation_'),
                                'border-green-200':
                                    item.itemType === 'reflection',
                            }"
                        >
                            <!-- Flex Container with Drag Handle -->
                            <div class="flex items-center gap-4">
                                <!-- Drag Handle -->
                                <button
                                    class="drag-handle cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600 p-2 shrink-0"
                                >
                                    <GripVertical class="w-6 h-6" />
                                </button>

                                <div class="flex-1 min-w-0">
                                    <!-- Material Item -->
                                    <div
                                        v-if="item.itemType === 'material'"
                                        class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
                                    >
                                        <div
                                            class="flex flex-col sm:flex-row items-start sm:items-center gap-4 flex-1 min-w-0"
                                        >
                                            <div
                                                class="bg-blue-100 p-3 rounded-2xl border-2 border-blue-300 shrink-0"
                                            >
                                                <FileText
                                                    class="text-blue-600 w-8 h-8"
                                                />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div
                                                    class="flex items-center gap-2 mb-2"
                                                >
                                                    <div class="flex items-center gap-2">
                                                        <span
                                                            class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700 border border-blue-300 font-medium"
                                                        >
                                                            MATERI
                                                        </span>
                                                        <span
                                                            v-if="item.layout_type"
                                                            class="text-xs px-2 py-1 rounded-full bg-indigo-100 text-indigo-700 border border-indigo-300 font-medium"
                                                        >
                                                            {{ getLayoutTypeLabel(item.layout_type) }}
                                                        </span>
                                                    </div>
                                                    <h3
                                                        class="text-xl font-bold text-gray-800 truncate"
                                                    >
                                                        {{ item.title }}
                                                    </h3>
                                                </div>
                                                <div
                                                    class="flex flex-wrap items-center gap-3 mb-3"
                                                >
                                                    <span
                                                        class="text-xs text-gray-500 flex items-center gap-1"
                                                        ><Clock
                                                            class="w-3 h-3"
                                                        />
                                                        {{
                                                            formatDate(
                                                                item.created_at,
                                                            )
                                                        }}</span
                                                    >
                                                    <span
                                                        v-if="item.created_by"
                                                        class="text-xs text-gray-500 flex items-center gap-1"
                                                        ><User
                                                            class="w-3 h-3"
                                                        />
                                                        {{
                                                            item.created_by
                                                        }}</span
                                                    >
                                                    <span
                                                        v-if="item.mascot"
                                                        class="text-xs text-gray-500 flex items-center gap-1"
                                                        ><Tag class="w-3 h-3" />
                                                        {{
                                                            item.mascot.name
                                                        }}</span
                                                    >
                                                </div>
                                                <p
                                                    v-if="item.description"
                                                    class="text-sm text-gray-600 line-clamp-2"
                                                >
                                                    {{ item.description }}
                                                </p>
                                            </div>
                                        </div>
                                        <div
                                            class="flex flex-col sm:flex-row gap-2 shrink-0 items-center"
                                        >
                                            <button
                                                type="button"
                                                class="w-full sm:w-auto px-3.5 py-2.5 bg-indigo-50 hover:bg-indigo-100 border-2 border-indigo-200 text-indigo-700 font-bold rounded-2xl text-xs transition-all flex items-center justify-center gap-1.5"
                                                @click="openMascotDialogModal(item)"
                                                title="Dialog Maskot"
                                            >
                                                <MessageSquare class="w-4 h-4 text-indigo-600" />
                                                <span>Dialog Maskot</span>
                                            </button>
                                            <Button
                                                class="w-full sm:w-auto"
                                                variant="info"
                                                size="md"
                                                :icon="Eye"
                                                @click="
                                                    goToShowMaterial(item.id)
                                                "
                                            />
                                            <Button
                                                class="w-full sm:w-auto"
                                                variant="warning"
                                                size="md"
                                                :icon="Pencil"
                                                @click="
                                                    goToEditMaterial(item.id)
                                                "
                                            />
                                            <Button
                                                class="w-full sm:w-auto"
                                                variant="danger"
                                                size="md"
                                                :icon="Trash2"
                                                @click="
                                                    confirmDeleteMaterial(
                                                        item.id,
                                                    )
                                                "
                                            />
                                        </div>
                                    </div>

                                    <!-- Quiz Item -->
                                    <div
                                        v-else-if="
                                            item.itemType === 'quiz' &&
                                            item.type !== 'case_study'
                                        "
                                        class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
                                    >
                                        <div
                                            class="flex flex-col sm:flex-row items-start sm:items-center gap-4 flex-1 min-w-0"
                                        >
                                            <div
                                                class="bg-orange-100 p-3 rounded-2xl border-2 border-orange-300 shrink-0"
                                            >
                                                <HelpCircle
                                                    class="text-orange-600 w-8 h-8"
                                                />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div
                                                    class="flex items-center gap-2 mb-2"
                                                >
                                                    <span
                                                        class="text-xs px-2 py-1 rounded-full bg-orange-100 text-orange-700 border border-orange-300 font-medium"
                                                        >QUIZ</span
                                                    >
                                                    <h3
                                                        class="text-xl font-bold text-gray-800 truncate"
                                                    >
                                                        {{ item.title }}
                                                    </h3>
                                                </div>
                                                <div
                                                    class="flex flex-wrap items-center gap-3 mb-3"
                                                >
                                                    <span
                                                        :class="[
                                                            'text-xs px-3 py-1 rounded-full border font-medium',
                                                            item.type ===
                                                            'multiple_choices'
                                                                ? 'bg-blue-100 text-blue-700 border-blue-300'
                                                                : item.type ===
                                                                    'drag_drop'
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
                                                            item.type ===
                                                            "multiple_choices"
                                                                ? "PILIHAN GANDA"
                                                                : item.type ===
                                                                    "drag_drop"
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
                                                        ><Clock
                                                            class="w-3 h-3"
                                                        />
                                                        {{ item.time_limit }}
                                                        menit</span
                                                    >
                                                    <span
                                                        class="text-xs text-gray-500 flex items-center gap-1"
                                                        ><Calendar
                                                            class="w-3 h-3"
                                                        />
                                                        {{
                                                            formatDate(
                                                                item.created_at,
                                                            )
                                                        }}</span
                                                    >
                                                </div>
                                                <p
                                                    v-if="item.description"
                                                    class="text-sm text-gray-600 line-clamp-2"
                                                >
                                                    {{ item.description }}
                                                </p>
                                                <div
                                                    class="flex flex-wrap gap-4 mt-3"
                                                >
                                                    <span
                                                        class="text-xs text-gray-500 flex items-center gap-1"
                                                        ><List
                                                            class="w-3 h-3"
                                                        />
                                                        {{
                                                            item.questions_count ||
                                                            0
                                                        }}
                                                        Pertanyaan</span
                                                    >
                                                    <span
                                                        v-if="item.category"
                                                        class="text-xs text-gray-500 flex items-center gap-1"
                                                        ><Tag class="w-3 h-3" />
                                                        {{
                                                            item.category
                                                        }}</span
                                                    >
                                                    <span
                                                        v-if="item.created_by"
                                                        class="text-xs text-gray-500 flex items-center gap-1"
                                                        ><User
                                                            class="w-3 h-3"
                                                        />
                                                        {{
                                                            item.created_by
                                                        }}</span
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            class="flex flex-col sm:flex-row gap-2 shrink-0 items-center"
                                        >
                                            <button
                                                type="button"
                                                class="w-full sm:w-auto px-3.5 py-2.5 bg-indigo-50 hover:bg-indigo-100 border-2 border-indigo-200 text-indigo-700 font-bold rounded-2xl text-xs transition-all flex items-center justify-center gap-1.5"
                                                @click="openMascotDialogModal(item)"
                                                title="Dialog Maskot"
                                            >
                                                <MessageSquare class="w-4 h-4 text-indigo-600" />
                                                <span>Dialog Maskot</span>
                                            </button>
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
                                                @click="
                                                    confirmDeleteQuiz(item.id)
                                                "
                                            />
                                        </div>
                                    </div>

                                    <!-- Case Study Item -->
                                    <div
                                        v-else-if="
                                            item.itemType === 'quiz' &&
                                            item.type === 'case_study'
                                        "
                                        class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
                                    >
                                        <div
                                            class="flex flex-col sm:flex-row items-start sm:items-center gap-4 flex-1 min-w-0"
                                        >
                                            <div
                                                class="bg-teal-100 p-3 rounded-2xl border-2 border-teal-300 shrink-0"
                                            >
                                                <Workflow
                                                    class="text-teal-600 w-8 h-8"
                                                />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div
                                                    class="flex items-center gap-2 mb-2"
                                                >
                                                    <span
                                                        class="text-xs px-2 py-1 rounded-full bg-teal-100 text-teal-700 border border-teal-300 font-medium"
                                                        >STUDI KASUS</span
                                                    >
                                                    <h3
                                                        class="text-xl font-bold text-gray-800 truncate"
                                                    >
                                                        {{ item.title }}
                                                    </h3>
                                                </div>
                                                <div
                                                    class="flex flex-wrap items-center gap-3 mb-3"
                                                >
                                                    <span
                                                        class="text-xs text-gray-500 flex items-center gap-1"
                                                        ><Clock
                                                            class="w-3 h-3"
                                                        />
                                                        {{ item.time_limit }}
                                                        menit</span
                                                    >
                                                    <span
                                                        class="text-xs text-gray-500 flex items-center gap-1"
                                                        ><Calendar
                                                            class="w-3 h-3"
                                                        />
                                                        {{
                                                            formatDate(
                                                                item.created_at,
                                                            )
                                                        }}</span
                                                    >
                                                </div>
                                                <p
                                                    v-if="item.description"
                                                    class="text-sm text-gray-600 line-clamp-2"
                                                >
                                                    {{ item.description }}
                                                </p>
                                                <div
                                                    class="flex flex-wrap gap-4 mt-3"
                                                >
                                                    <span
                                                        class="text-xs text-gray-500 flex items-center gap-1"
                                                        ><List
                                                            class="w-3 h-3"
                                                        />
                                                        {{
                                                            item.questions_count ||
                                                            0
                                                        }}
                                                        Pertanyaan</span
                                                    >
                                                    <span
                                                        v-if="item.category"
                                                        class="text-xs text-gray-500 flex items-center gap-1"
                                                        ><Tag class="w-3 h-3" />
                                                        {{
                                                            item.category
                                                        }}</span
                                                    >
                                                    <span
                                                        v-if="item.created_by"
                                                        class="text-xs text-gray-500 flex items-center gap-1"
                                                        ><User
                                                            class="w-3 h-3"
                                                        />
                                                        {{
                                                            item.created_by
                                                        }}</span
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            class="flex flex-col sm:flex-row gap-2 shrink-0"
                                        >
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
                                                @click="
                                                    confirmDeleteQuiz(item.id)
                                                "
                                            />
                                        </div>
                                    </div>

                                    <!-- Reflection Item -->
                                    <div
                                        v-else-if="
                                            item.itemType === 'reflection'
                                        "
                                        class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
                                    >
                                        <div
                                            class="flex flex-col sm:flex-row items-start sm:items-center gap-4 flex-1 min-w-0"
                                        >
                                            <div
                                                class="bg-green-100 p-3 rounded-2xl border-2 border-green-300 shrink-0"
                                            >
                                                <AlignLeft
                                                    class="text-green-600 w-8 h-8"
                                                />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div
                                                    class="flex items-center gap-2 mb-2"
                                                >
                                                    <span
                                                        class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700 border border-green-300 font-medium"
                                                        >REFLEKSI ILMIAH</span
                                                    >
                                                    <h3
                                                        class="text-xl font-bold text-gray-800 truncate"
                                                    >
                                                        {{ item.title }}
                                                    </h3>
                                                </div>
                                                <div
                                                    class="flex items-center gap-3 mb-3"
                                                >
                                                    <span
                                                        class="text-xs text-gray-500 flex items-center gap-1"
                                                        ><Clock
                                                            class="w-3 h-3"
                                                        />
                                                        {{
                                                            formatDate(
                                                                item.created_at,
                                                            )
                                                        }}</span
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            class="flex flex-col sm:flex-row gap-2 shrink-0"
                                        >
                                            <Button
                                                class="w-full sm:w-auto"
                                                variant="info"
                                                size="md"
                                                :icon="Eye"
                                                @click="
                                                    router.visit(
                                                        route(
                                                            'admin.modules.missions.reflections.show',
                                                            [
                                                                module.id,
                                                                mission.id,
                                                                item.id,
                                                            ],
                                                        ),
                                                    )
                                                "
                                            />
                                            <Button
                                                class="w-full sm:w-auto"
                                                variant="warning"
                                                size="md"
                                                :icon="Pencil"
                                                @click="
                                                    router.visit(
                                                        route(
                                                            'admin.modules.missions.reflections.edit',
                                                            [
                                                                module.id,
                                                                mission.id,
                                                                item.id,
                                                            ],
                                                        ),
                                                    )
                                                "
                                            />
                                            <Button
                                                class="w-full sm:w-auto"
                                                variant="danger"
                                                size="md"
                                                :icon="Trash2"
                                                @click="
                                                    confirmDeleteReflection(
                                                        item.id,
                                                    )
                                                "
                                            />
                                        </div>
                                    </div>

                                    <!-- Simulation Item -->
                                    <div
                                        v-else
                                        class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
                                    >
                                        <div
                                            class="flex flex-col sm:flex-row items-start sm:items-center gap-4 flex-1 min-w-0"
                                        >
                                            <div
                                                class="bg-purple-100 p-3 rounded-2xl border-2 border-purple-300 shrink-0"
                                            >
                                                <SlidersHorizontal
                                                    v-if="
                                                        item.itemType ===
                                                        'simulation_slider'
                                                    "
                                                    class="text-purple-600 w-8 h-8"
                                                />
                                                <GitCompare
                                                    v-else-if="
                                                        item.itemType ===
                                                        'simulation_comparison'
                                                    "
                                                    class="text-purple-600 w-8 h-8"
                                                />
                                                <MousePointerClick
                                                    v-else-if="
                                                        item.itemType ===
                                                        'simulation_clickable_object'
                                                    "
                                                    class="text-purple-600 w-8 h-8"
                                                />
                                                <HelpCircle
                                                    v-else
                                                    class="text-purple-600 w-8 h-8"
                                                />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div
                                                    class="flex items-center gap-2 mb-2"
                                                >
                                                    <span
                                                        class="text-xs px-2 py-1 rounded-full bg-purple-100 text-purple-700 border border-purple-300 font-medium"
                                                        >SIMULASI</span
                                                    >
                                                    <h3
                                                        class="text-xl font-bold text-gray-800 truncate"
                                                    >
                                                        {{ item.title }}
                                                    </h3>
                                                </div>
                                                <div
                                                    class="flex items-center gap-3 mb-3"
                                                >
                                                    <span
                                                        class="text-xs text-gray-500 flex items-center gap-1"
                                                        ><Clock
                                                            class="w-3 h-3"
                                                        />
                                                        {{
                                                            formatDate(
                                                                item.created_at,
                                                            )
                                                        }}</span
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            class="flex flex-col sm:flex-row gap-2 shrink-0"
                                        >
                                            <Button
                                                class="w-full sm:w-auto"
                                                variant="warning"
                                                size="md"
                                                :icon="Pencil"
                                                @click="
                                                    router.visit(
                                                        route(
                                                            'admin.modules.missions.simulation.edit',
                                                            [
                                                                module.id,
                                                                mission.id,
                                                            ],
                                                        ),
                                                    )
                                                "
                                            />
                                            <Button
                                                class="w-full sm:w-auto"
                                                variant="danger"
                                                size="md"
                                                :icon="Trash2"
                                                @click="
                                                    confirmDeleteSimulation(
                                                        item.id,
                                                        item.itemType,
                                                    )
                                                "
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </draggable>
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

                <a :href="route('admin.modules.missions.materials.template', [module.id, mission.id])" class="text-blue-600 hover:underline text-sm font-semibold flex items-center gap-1 mb-2">
                    Unduh Template Excel
                </a>

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

                <a :href="route('admin.modules.missions.quizzes.template', [module.id, mission.id])" class="text-blue-600 hover:underline text-sm font-semibold flex items-center gap-1 mb-2">
                    Unduh Template Excel
                </a>

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

        <!-- Modal Edit Mission & Voice Over -->
        <Modal :show="showEditMissionModal" @close="showEditMissionModal = false">
            <template #title>
                <div class="flex items-center gap-2 text-indigo-600">
                    <Volume2 class="w-5 h-5" />
                    <span class="font-bold">Pengaturan Misi & Voice Over</span>
                </div>
            </template>
            <div class="space-y-4 py-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Misi</label>
                    <input
                        v-model="editMissionForm.name"
                        type="text"
                        class="w-full px-4 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Misal: Misi 1"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Urut</label>
                    <input
                        v-model="editMissionForm.order_number"
                        type="number"
                        min="1"
                        class="w-full px-4 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    />
                </div>

                <div class="pt-2 border-t border-gray-100">
                    <label class="block text-sm font-medium text-gray-800 mb-1 flex items-center gap-2">
                        <Mic class="w-4 h-4 text-indigo-500" />
                        <span>File Voice Over Narasi (MP3 / WAV / OGG)</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-2">Voice Over ini akan diputar otomatis saat siswa memasuki Misi ini.</p>

                    <div v-if="mission.voiceover_url && !editMissionForm.remove_voiceover" class="mb-3 p-3 bg-indigo-50 rounded-xl border border-indigo-100 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 overflow-hidden">
                            <Volume2 class="w-4 h-4 text-indigo-600 shrink-0" />
                            <audio :src="mission.voiceover_url" controls class="h-8 max-w-xs"></audio>
                        </div>
                        <button
                            type="button"
                            @click="editMissionForm.remove_voiceover = true"
                            class="text-xs text-red-600 hover:text-red-700 font-medium shrink-0 flex items-center gap-1 bg-white px-2.5 py-1 rounded-lg border border-red-200"
                        >
                            <Trash2 class="w-3.5 h-3.5" />
                            <span>Hapus</span>
                        </button>
                    </div>

                    <input
                        ref="voiceoverFileInput"
                        type="file"
                        accept="audio/mp3,audio/wav,audio/ogg,audio/mpeg"
                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer"
                        @change="handleVoiceoverChange"
                    />
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <Button variant="ghost" size="md" @click="showEditMissionModal = false">Batal</Button>
                    <Button variant="primary" size="md" :disabled="editMissionForm.processing" @click="submitEditMission">Simpan Pengaturan</Button>
                </div>
            </template>
        </Modal>

        <!-- Modal Kalimat Dialog Maskot (Kalimat Random) -->
        <Modal :show="showMascotDialogModal" @close="showMascotDialogModal = false">
            <template #title>
                <div class="flex items-center gap-2 text-indigo-600">
                    <MessageSquare class="w-5 h-5" />
                    <span class="font-bold">Dialog Acak Maskot: {{ mascotDialogTargetTitle }}</span>
                </div>
            </template>
            <div class="space-y-4 py-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Kalimat-Kalimat Acak Maskot
                    </label>
                    <p class="text-xs text-gray-500 mb-2">
                        Tulis kalimat dialog yang akan diucapkan maskot secara acak/bergantian saat siswa membuka kuis/materi ini.
                        <strong>Pisahkan setiap kalimat dengan baris baru (Enter).</strong>
                    </p>
                    <textarea
                        v-model="mascotDialogForm.custom_dialogues"
                        rows="6"
                        class="w-full px-4 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                        placeholder="Misal:&#10;Ayo pahami materi ini pelan-pelan ya!&#10;Fokus bacanya ya, kamu pasti bisa!&#10;Jangan terburu-buru menjawabnya!"
                    ></textarea>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <Button variant="ghost" size="md" @click="showMascotDialogModal = false">Batal</Button>
                    <Button variant="primary" size="md" :disabled="mascotDialogForm.processing" @click="submitMascotDialog">Simpan Dialog</Button>
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
