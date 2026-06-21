<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch } from "vue";
import { router, usePage, useForm } from "@inertiajs/vue3";
import Button from "@/Components/UI/Button.vue";
import Card from "@/Components/UI/Card.vue";
import Modal from "@/Components/UI/Modal.vue";
import ConfirmDialog from "@/Components/UI/ConfirmDialog.vue";
import Toast from "@/Components/UI/Toast.vue";
import InputField from "@/Components/UI/Forms/InputField.vue";
import FileDropzone from "@/Components/UI/Forms/FileDropzone.vue";
import {
    ArrowLeft,
    Flag,
    Plus,
    FileText,
    Inbox,
    Trash2,
    Pencil,
    Loader2,
    Hash,
    Shuffle,
    ListOrdered,
} from "lucide-vue-next";

const page = usePage();

// Props dari backend
const props = defineProps({
    module: {
        type: Object,
        required: true,
    },
    missions: {
        type: Array,
        default: () => [],
    },
    pretest: {
        type: Array,
        default: () => [],
    },
    posttest: {
        type: Array,
        default: () => [],
    },
});

// State management
const showAddMissionModal = ref(false);
const showEditMissionModal = ref(false);
const showDeleteDialog = ref(false);
const showDeleteQuizDialog = ref(false);
const selectedMission = ref(null);
const selectedQuiz = ref(null);

// Form untuk tambah mission
const form = useForm({
    mission_count: 1,
});

const editForm = useForm({
    name: "",
    order_number: null,
    conclusion_speech: "",
    conclusion_body: "",
});

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

// Import module-level quizzes (pretest) - CSV/XLSX
const moduleImport = useForm({ file: null, category: "pretest" });
const showModuleImportModal = ref(false);
const moduleImportPreview = ref(null);

const openImportModal = (category) => {
    moduleImport.category = category;
    moduleImport.file = null;
    moduleImportPreview.value = null;
    moduleImport.clearErrors();
    showModuleImportModal.value = true;
};

const setModuleFile = (file) => {
    moduleImport.file = file;
    moduleImportPreview.value = file ? file.name : null;
};

const submitModuleImport = () => {
    if (!moduleImport.file) {
        triggerToast("Pilih file CSV/XLSX terlebih dahulu.", "error");
        return;
    }
    const url = route("admin.modules.quizzes.import", props.module.id);
    moduleImport.post(url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: (page) => {
            const flashError = page.props?.flash?.error;
            const flashSuccess = page.props?.flash?.success;
            if (flashError) {
                triggerToast(flashError, "error");
            } else {
                triggerToast(
                    flashSuccess || "Import quiz module berhasil.",
                    "success",
                );
                moduleImport.reset();
                moduleImportPreview.value = null;
                showModuleImportModal.value = false;
            }
        },
        onError: (errs) => {
            const msg =
                errs?.error ||
                errs?.file ||
                Object.values(errs)?.[0] ||
                "Gagal import quiz.";
            triggerToast(msg, "error");
        },
    });
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

// Navigate functions
const goBack = () => {
    router.visit(route("admin.modules.index"));
};

// Open module-level quiz create (pretest/posttest) with preset category
const openCreateQuizPretest = (category) => {
    // The route requires both module id and category as path params
    router.visit(
        route("admin.modules.quizzes.create", [props.module.id, category]),
    );
};

const goToCaseStudy = () => {
    router.visit(route("admin.modules.quizzes.create", [props.module.id, 'case_study']) + '?type=case_study');
};

const openAddMissionModal = () => {
    form.reset();
    form.clearErrors();
    showAddMissionModal.value = true;
};

const closeAddMissionModal = () => {
    showAddMissionModal.value = false;
    form.reset();
    form.clearErrors();
};

const saveMissions = () => {
    form.post(route("admin.modules.missions.store", props.module.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeAddMissionModal();
        },
    });
};

const goToShowMission = (missionId) => {
    router.visit(
        route("admin.modules.missions.show", [props.module.id, missionId]),
    );
};
const goToShowQuiz = (quiz) => {
    // accepts either quiz object or id
    const quizObj = typeof quiz === "object" ? quiz : { id: quiz };
    // If quiz belongs to a mission, navigate to mission-scoped route
    if (quizObj.mission_id) {
        router.visit(
            route("admin.modules.missions.quizzes.show", [
                props.module.id,
                quizObj.mission_id,
                quizObj.id,
            ]),
        );
        return;
    }
    // Module-level quiz (pretest/posttest)
    router.visit(
        route("admin.modules.quizzes.show", [props.module.id, quizObj.id]),
    );
};

const openEditMissionModal = (mission) => {
    selectedMission.value = mission;
    editForm.reset();
    editForm.clearErrors();
    editForm.name = mission.name;
    editForm.order_number = mission.order_number;
    editForm.conclusion_speech = mission.conclusion_speech || "";
    editForm.conclusion_body = mission.conclusion_body || "";
    showEditMissionModal.value = true;
};

const closeEditMissionModal = () => {
    showEditMissionModal.value = false;
    editForm.reset();
    editForm.clearErrors();
    selectedMission.value = null;
};

const updateMission = () => {
    if (!selectedMission.value) return;

    editForm.put(
        route("admin.modules.missions.update", [
            props.module.id,
            selectedMission.value.id,
        ]),
        {
            preserveScroll: true,
            onSuccess: () => {
                closeEditMissionModal();
            },
        },
    );
};

const confirmDeleteMission = (mission) => {
    selectedMission.value = mission;
    showDeleteDialog.value = true;
};

const deleteMission = () => {
    if (!selectedMission.value) return;

    router.delete(
        route("admin.modules.missions.destroy", [
            props.module.id,
            selectedMission.value.id,
        ]),
        {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteDialog.value = false;
                selectedMission.value = null;
            },
            onError: () => {
                triggerToast("Gagal menghapus misi.", "error");
            },
        },
    );
};

const goToEditQuiz = (quiz) => {
    router.visit(
        route("admin.modules.quizzes.show", [props.module.id, quiz.id]),
    ); // Currently it points to show. Edit could be handled in show or we need a module quiz edit route, but let's just go to show. Or actually there is no edit route for module quizzes, so we just use goToShowQuiz. But user asked for edit button, we can just use goToShowQuiz for now since editing is done in the show page for quizzes.
};

const toggleQuizRandomized = (quiz) => {
    router.patch(
        route("admin.modules.quizzes.toggle_randomized", [props.module.id, quiz.id]),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                // Flash message will trigger toast
            },
            onError: () => {
                triggerToast("Gagal memperbarui pengaturan acak soal.", "error");
            }
        }
    );
};

const confirmDeleteQuiz = (quiz) => {
    selectedQuiz.value = quiz;
    showDeleteQuizDialog.value = true;
};

const deleteQuiz = () => {
    if (!selectedQuiz.value) return;

    router.delete(
        route("admin.modules.quizzes.destroy", [
            props.module.id,
            selectedQuiz.value.id,
        ]),
        {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteQuizDialog.value = false;
                selectedQuiz.value = null;
            },
            onError: () => {
                triggerToast("Gagal menghapus tes.", "error");
            },
        },
    );
};
</script>

<template>
    <AppLayout>
        <div class="p-5 max-w-7xl mx-auto">
            <!-- Header -->
            <div
                class="bg-white rounded-3xl border-4 border-blue-200 shadow-playful p-6 mb-8"
            >
                <div
                    class="flex flex-col sm:flex-row items-start sm:items-center gap-4"
                >
                    <button
                        @click="goBack"
                        class="bg-blue-100 p-3 rounded-2xl border-2 border-blue-300 hover:bg-blue-200 transition-all"
                    >
                        <ArrowLeft class="text-blue-600 w-5 h-5" />
                    </button>

                    <div class="flex-1">
                        <h1
                            class="text-2xl md:text-3xl font-heading font-bold text-gray-800 mb-2"
                        >
                            {{ module.name }}
                        </h1>
                        <p
                            v-if="module.description"
                            class="text-sm text-gray-600 mb-4"
                        >
                            {{ module.description }}
                        </p>

                        <!-- Module Stats -->
                        <div class="flex flex-wrap gap-4">
                            <div
                                class="flex items-center gap-2 bg-blue-100 text-blue-700 px-3 py-1.5 rounded-full text-sm font-medium"
                            >
                                <Flag class="text-blue-500 w-4 h-4" />
                                <span class="text-sm font-medium text-gray-700">
                                    {{ missions.length }} Misi
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions: primary buttons with import button in a second row -->
                    <div class="w-full sm:w-auto mt-4 sm:mt-0 sm:self-end">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <Button
                                v-if="!pretest.length"
                                class="w-full"
                                variant="warning"
                                size="md"
                                :icon="Plus"
                                @click="openCreateQuizPretest('pretest')"
                            >
                                Tambah Tes Awal
                            </Button>
                            <Button
                                class="w-full"
                                variant="primary"
                                size="md"
                                :icon="Plus"
                                @click="openAddMissionModal"
                            >
                                Tambah Misi
                            </Button>
                            <Button
                                v-if="!posttest.length"
                                class="w-full"
                                variant="light"
                                size="md"
                                :icon="Plus"
                                @click="openCreateQuizPretest('posttest')"
                            >
                                Tambah Tes Akhir
                            </Button>
                            <Button
                                v-if="!pretest.length"
                                class="w-full"
                                variant="ghost"
                                size="md"
                                :icon="FileText"
                                @click="openImportModal('pretest')"
                            >
                                Import Tes Awal (Pretest)
                            </Button>
                            <Button
                                v-if="!posttest.length"
                                class="w-full"
                                variant="ghost"
                                size="md"
                                :icon="FileText"
                                @click="openImportModal('posttest')"
                            >
                                Import Tes Akhir (Posttest)
                            </Button>
                            <Button
                                class="w-full"
                                variant="secondary"
                                size="md"
                                :icon="FileText"
                                @click="goToCaseStudy"
                            >
                                Studi Kasus
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PRETEST SECTION -->
            <div v-if="pretest.length" class="mb-6">
                <div
                    class="bg-blue-100 rounded-2xl p-4 flex items-center justify-between mb-4"
                >
                    <h2 class="text-xl font-bold text-gray-800">Tes Awal</h2>
                </div>

                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    <Card
                        v-for="quiz in pretest"
                        :key="quiz.id"
                        border-color="blue"
                        @click="goToShowQuiz(quiz.id)"
                        class="cursor-pointer hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between h-full"
                    >
                        <div
                            class="w-full h-32 rounded-2xl mb-4 bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center"
                        >
                            <Flag class="w-12 h-12 text-blue-400" />
                        </div>

                        <h3
                            class="font-heading font-bold text-gray-800 text-lg"
                        >
                            {{ quiz.title }}
                        </h3>

                        <p class="text-sm text-gray-500 mt-2 mb-4">
                            {{ quiz.questions_count }} Soal •
                            {{ quiz.time_limit }} menit
                        </p>

                        <template #footer>
                            <div
                                class="flex justify-end gap-2 pt-4 border-t-2 border-gray-100"
                                @click.stop
                            >
                                <!-- Toggle Randomized Button -->
                                <button
                                    @click="toggleQuizRandomized(quiz)"
                                    :title="quiz.is_randomized ? 'Ubah ke Urutkan Soal' : 'Ubah ke Acak Soal'"
                                    :class="[
                                        'h-10 px-3 flex items-center justify-center gap-1.5 rounded-xl transition-all shadow-sm hover:shadow-md border-2 active:scale-95 font-medium text-xs',
                                        quiz.is_randomized 
                                            ? 'bg-purple-100 text-purple-700 hover:bg-purple-200 border-purple-200' 
                                            : 'bg-gray-100 text-gray-500 hover:bg-gray-200 border-gray-200'
                                    ]"
                                >
                                    <ListOrdered v-if="quiz.is_randomized" class="w-4 h-4" />
                                    <Shuffle v-else class="w-4 h-4" />
                                    <span>{{ quiz.is_randomized ? 'Urutkan Soal' : 'Acak Soal' }}</span>
                                </button>

                                <!-- Edit Button -->
                                <button
                                    @click="goToShowQuiz(quiz.id)"
                                    title="Edit Tes Awal"
                                    class="w-10 h-10 flex items-center justify-center rounded-xl bg-yellow-100 text-yellow-700 hover:bg-yellow-200 active:scale-95 transition-all shadow-sm hover:shadow-md border-2 border-yellow-200"
                                >
                                    <Pencil class="w-4 h-4" />
                                </button>

                                <!-- Delete Button -->
                                <button
                                    @click="confirmDeleteQuiz(quiz)"
                                    title="Hapus Tes Awal"
                                    class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-100 text-red-700 hover:bg-red-200 active:scale-95 transition-all shadow-sm hover:shadow-md border-2 border-red-200"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- Missions List -->
            <div class="space-y-4">
                <div
                    class="bg-blue-100 rounded-2xl p-4 flex items-center justify-between"
                >
                    <h2 class="text-xl font-bold text-gray-800">Daftar Misi</h2>
                    <span
                        class="bg-blue-500 text-white px-4 py-2 rounded-full text-sm font-bold"
                    >
                        {{ missions.length }}
                    </span>
                </div>

                <!-- Empty State -->
                <div
                    v-if="missions.length === 0"
                    class="bg-white rounded-3xl border-4 border-blue-200 shadow-playful p-12 text-center"
                >
                    <Inbox class="text-gray-300 w-16 h-16 mb-4 mx-auto" />
                    <h3 class="text-xl font-bold text-gray-700 mb-2">
                        Belum ada misi
                    </h3>
                    <p class="text-gray-500 mb-6">
                        Mulai dengan menambahkan misi pertama untuk modul ini
                    </p>
                    <Button
                        variant="primary"
                        size="lg"
                        :icon="Plus"
                        @click="openAddMissionModal"
                    >
                        Tambah Misi Pertama
                    </Button>
                </div>

                <!-- Missions Grid -->
                <TransitionGroup
                    v-else
                    name="card"
                    tag="div"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    <Card
                        v-for="mission in missions"
                        :key="mission.id"
                        border-color="blue"
                        class="cursor-pointer hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between h-full"
                        @click="goToShowMission(mission.id)"
                    >
                        <!-- Badge Order -->
                        <div
                            class="absolute top-4 right-4 bg-blue-500 text-white px-3 py-1.5 rounded-full text-xs font-bold shadow-md flex items-center gap-1.5"
                        >
                            <Hash class="w-3.5 h-3.5" />
                            {{ mission.order_number }}
                        </div>

                        <!-- Icon -->
                        <div
                            class="w-full h-32 rounded-2xl mb-4 bg-gradient-to-br from-blue-100 to-gray-100 flex items-center justify-center"
                        >
                            <Flag class="w-12 h-12 text-blue-400" />
                        </div>

                        <!-- Nama Mission -->
                        <h3
                            class="font-heading font-bold text-gray-800 text-lg leading-snug pr-12 truncate"
                        >
                            {{ mission.name }}
                        </h3>

                        <!-- Footer Actions -->
                        <template #footer>
                            <div
                                class="flex justify-end gap-2 pt-4 border-t-2 border-gray-100"
                                @click.stop
                            >
                                <!-- Edit Button -->
                                <button
                                    @click="openEditMissionModal(mission)"
                                    title="Ubah Misi"
                                    class="w-10 h-10 flex items-center justify-center rounded-xl bg-yellow-100 text-yellow-700 hover:bg-yellow-200 active:scale-95 transition-all shadow-sm hover:shadow-md border-2 border-yellow-200"
                                >
                                    <Pencil class="w-4 h-4" />
                                </button>

                                <!-- Delete Button -->
                                <button
                                    @click="confirmDeleteMission(mission)"
                                    title="Hapus Misi"
                                    class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-100 text-red-700 hover:bg-red-200 active:scale-95 transition-all shadow-sm hover:shadow-md border-2 border-red-200"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </template>
                    </Card>
                </TransitionGroup>

                <!-- POSTTEST SECTION -->
                <div v-if="posttest.length" class="mt-10">
                    <div
                        class="bg-green-100 rounded-2xl p-4 flex items-center justify-between mb-4"
                    >
                        <h2 class="text-xl font-bold text-gray-800">
                            Tes Akhir
                        </h2>
                    </div>

                    <div
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                    >
                        <Card
                            v-for="quiz in posttest"
                            :key="quiz.id"
                            border-color="green"
                            @click="goToShowQuiz(quiz.id)"
                            class="cursor-pointer hover:scale-[1.02] transition-all duration-200 flex flex-col justify-between h-full"
                        >
                            <div
                                class="w-full h-32 rounded-2xl mb-4 bg-gradient-to-br from-green-100 to-emerald-100 flex items-center justify-center"
                            >
                                <Flag class="w-12 h-12 text-green-400" />
                            </div>

                            <h3
                                class="font-heading font-bold text-gray-800 text-lg"
                            >
                                {{ quiz.title }}
                            </h3>

                            <p class="text-sm text-gray-500 mt-2 mb-4">
                                {{ quiz.questions_count }} Soal •
                                {{ quiz.time_limit }} menit
                            </p>

                            <template #footer>
                                <div
                                    class="flex justify-end gap-2 pt-4 border-t-2 border-gray-100"
                                    @click.stop
                                >
                                    <!-- Toggle Randomized Button -->
                                    <button
                                        @click="toggleQuizRandomized(quiz)"
                                        :title="quiz.is_randomized ? 'Ubah ke Urutkan Soal' : 'Ubah ke Acak Soal'"
                                        :class="[
                                            'h-10 px-3 flex items-center justify-center gap-1.5 rounded-xl transition-all shadow-sm hover:shadow-md border-2 active:scale-95 font-medium text-xs',
                                            quiz.is_randomized 
                                                ? 'bg-purple-100 text-purple-700 hover:bg-purple-200 border-purple-200' 
                                                : 'bg-gray-100 text-gray-500 hover:bg-gray-200 border-gray-200'
                                        ]"
                                    >
                                        <ListOrdered v-if="quiz.is_randomized" class="w-4 h-4" />
                                        <Shuffle v-else class="w-4 h-4" />
                                        <span>{{ quiz.is_randomized ? 'Urutkan Soal' : 'Acak Soal' }}</span>
                                    </button>

                                    <!-- Edit Button -->
                                    <button
                                        @click="goToShowQuiz(quiz.id)"
                                        title="Edit Tes Akhir"
                                        class="w-10 h-10 flex items-center justify-center rounded-xl bg-yellow-100 text-yellow-700 hover:bg-yellow-200 active:scale-95 transition-all shadow-sm hover:shadow-md border-2 border-yellow-200"
                                    >
                                        <Pencil class="w-4 h-4" />
                                    </button>

                                    <!-- Delete Button -->
                                    <button
                                        @click="confirmDeleteQuiz(quiz)"
                                        title="Hapus Tes Akhir"
                                        class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-100 text-red-700 hover:bg-red-200 active:scale-95 transition-all shadow-sm hover:shadow-md border-2 border-red-200"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </template>
                        </Card>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Mission -->
        <Modal
            :show="showAddMissionModal"
            title="Tambah Misi Baru"
            @close="closeAddMissionModal"
            max-width="md"
        >
            <div class="space-y-5">
                <div
                    class="bg-blue-50 border-2 border-blue-200 rounded-2xl p-4"
                >
                    <p class="text-sm text-gray-700">
                        Misi yang ditambahkan akan otomatis diberi nama
                        <span class="font-bold text-blue-600"
                            >"Misi 1", "Misi 2"</span
                        >, dll. Anda dapat mengubah namanya nanti.
                    </p>
                </div>

                <InputField
                    v-model.number="form.mission_count"
                    label="Jumlah Misi"
                    type="number"
                    placeholder="Contoh: 5"
                    :icon="Hash"
                    min="1"
                    max="20"
                    required
                    border-color="blue"
                    :error="form.errors.mission_count"
                >
                    <template #help>
                        Masukkan jumlah misi yang ingin ditambahkan (1-20)
                    </template>
                </InputField>
            </div>

            <template #footer>
                <div class="flex justify-end gap-3">
                    <Button
                        variant="light"
                        size="md"
                        :disabled="form.processing"
                        @click="closeAddMissionModal"
                    >
                        Batal
                    </Button>

                    <Button
                        variant="primary"
                        size="md"
                        :disabled="form.processing"
                        @click="saveMissions"
                    >
                        <span
                            v-if="form.processing"
                            class="flex items-center gap-2"
                        >
                            <Loader2 class="w-4 h-4 animate-spin" />
                            Menyimpan...
                        </span>
                        <span v-else>
                            <span class="flex items-center gap-2">
                                <Plus class="w-4 h-4" />
                                Tambah {{ form.mission_count }} Misi
                            </span>
                        </span>
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- Modal Edit Mission -->
        <Modal
            :show="showEditMissionModal"
            title="Ubah Misi"
            @close="closeEditMissionModal"
            max-width="md"
        >
            <div class="space-y-5">
                <InputField
                    v-model="editForm.name"
                    label="Nama Misi"
                    type="text"
                    placeholder="Contoh: Misi Pengenalan"
                    :icon="Flag"
                    required
                    border-color="blue"
                    :error="editForm.errors.name"
                >
                    <template #help>
                        Ubah nama mission sesuai kebutuhan
                    </template>
                </InputField>

                <InputField
                    v-model.number="editForm.order_number"
                    label="Nomor Urut"
                    type="number"
                    placeholder="Contoh: 1"
                    :icon="Hash"
                    min="1"
                    required
                    border-color="blue"
                    :error="editForm.errors.order_number"
                >
                    <template #help> Atur urutan tampilan mission </template>
                </InputField>

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700">Teks Kesimpulan Singkat (Gelembung Maskot)</label>
                    <textarea
                        v-model="editForm.conclusion_speech"
                        class="w-full border-2 border-blue-200 rounded-xl p-3 text-sm focus:ring-0 focus:border-blue-400 transition-colors"
                        rows="2"
                        placeholder="Contoh: Kerja bagus! Kamu telah menyelesaikan misi ini."
                    ></textarea>
                    <p class="text-xs text-gray-500 mt-1">Muncul di atas maskot pada halaman akhir.</p>
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700">Penjelasan Kesimpulan</label>
                    <textarea
                        v-model="editForm.conclusion_body"
                        class="w-full border-2 border-blue-200 rounded-xl p-3 text-sm focus:ring-0 focus:border-blue-400 transition-colors"
                        rows="4"
                        placeholder="Contoh: Pada misi ini, kita telah mempelajari bahwa air mengalir dari tempat tinggi ke tempat rendah..."
                    ></textarea>
                    <p class="text-xs text-gray-500 mt-1">Muncul di kotak putih pada halaman akhir.</p>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-3">
                    <Button
                        variant="light"
                        size="md"
                        :disabled="editForm.processing"
                        @click="closeEditMissionModal"
                    >
                        Batal
                    </Button>

                    <Button
                        variant="primary"
                        size="md"
                        :disabled="editForm.processing"
                        @click="updateMission"
                    >
                        <span
                            v-if="editForm.processing"
                            class="flex items-center gap-2"
                        >
                            <Loader2 class="w-4 h-4 animate-spin" />
                            Menyimpan...
                        </span>
                        <span v-else>
                            <span class="flex items-center gap-2">
                                <Pencil class="w-4 h-4" />
                                Simpan Perubahan
                            </span>
                        </span>
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- Delete Confirmation Dialog -->
        <ConfirmDialog
            :show="showDeleteDialog"
            title="Hapus misi ini?"
            :message="`Misi '${selectedMission?.name}' akan dihapus selamanya.`"
            @confirm="deleteMission"
            @cancel="showDeleteDialog = false"
        />

        <!-- Delete Quiz Confirmation Dialog -->
        <ConfirmDialog
            :show="showDeleteQuizDialog"
            title="Hapus tes ini?"
            :message="`Tes '${selectedQuiz?.title}' beserta soal-soalnya akan dihapus selamanya.`"
            @confirm="deleteQuiz"
            @cancel="showDeleteQuizDialog = false"
        />

        <!-- Import Module-level Pretest/Posttest Modal -->
        <Modal
            :show="showModuleImportModal"
            :title="`Import ${moduleImport.category === 'pretest' ? 'Tes Awal' : 'Tes Akhir'} (CSV / XLSX)`"
            @close="showModuleImportModal = false"
            max-width="lg"
        >
            <div class="py-4 space-y-4">
                <p class="text-sm text-gray-600">
                    Unggah file CSV atau XLSX yang berisi quiz untuk kategori
                    <strong>{{ moduleImport.category }}</strong>. Kolom minimal pada setiap baris:
                    <strong
                    >quiz_title, quiz_description, time_limit, question_text, option_1,
                    option_1_is_correct, option_2, option_2_is_correct,
                    ...</strong>. File maksimal 10 MB. Tipe file yang diterima:
                    <strong>.csv, .xlsx, .xls</strong>.
                </p>
                
                <a :href="route('admin.modules.quizzes.template', module.id)" class="text-blue-600 hover:underline text-sm font-semibold flex items-center gap-1 mb-2">
                    Unduh Template Excel
                </a>

                <FileDropzone
                    v-model:modelValue="moduleImport.file"
                    accept=".csv,.xlsx,.xls"
                    label="Pilih atau seret file CSV/XLSX"
                    borderColor="gray"
                    :allowClear="false"
                    @update:modelValue="setModuleFile"
                />
                <p v-if="moduleImportPreview" class="text-sm text-gray-500">
                    File terpilih: {{ moduleImportPreview }}
                </p>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <Button
                        variant="ghost"
                        size="md"
                        @click="showModuleImportModal = false"
                        >Batal</Button
                    >
                    <Button
                        variant="primary"
                        size="md"
                        @click="submitModuleImport"
                        >Import</Button
                    >
                </div>
            </template>
        </Modal>

        <!-- Toast Notification -->
        <Toast :show="toastVisible" :message="toastMessage" :type="toastType" />
    </AppLayout>
</template>
