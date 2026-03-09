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
import {
    ArrowLeft,
    Flag,
    Plus,
    Inbox,
    Trash2,
    Pencil,
    Loader2,
    Hash,
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
const selectedMission = ref(null);

// Form untuk tambah mission
const form = useForm({
    mission_count: 1,
});

// Form untuk edit mission
const editForm = useForm({
    name: "",
    order_number: null,
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

                    <!-- Actions (responsive: wrap on small screens) -->
                    <div class="flex flex-wrap gap-2 mt-4 sm:mt-0">
                        <Button
                            class="w-full sm:w-auto"
                            variant="warning"
                            size="md"
                            :icon="Plus"
                            @click="openCreateQuizPretest('pretest')"
                        >
                            Tambah Tes Awal
                        </Button>
                        <Button
                            class="w-full sm:w-auto"
                            variant="primary"
                            size="lg"
                            :icon="Plus"
                            @click="openAddMissionModal"
                        >
                            Tambah Misi
                        </Button>
                        <Button
                            class="w-full sm:w-auto"
                            variant="light"
                            size="md"
                            :icon="Plus"
                            @click="openCreateQuizPretest('posttest')"
                        >
                            Tambah Tes Akhir
                        </Button>
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

                        <p class="text-sm text-gray-500 mt-2">
                            {{ quiz.questions_count }} Soal •
                            {{ quiz.time_limit }} menit
                        </p>
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

                            <p class="text-sm text-gray-500 mt-2">
                                {{ quiz.questions_count }} Soal •
                                {{ quiz.time_limit }} menit
                            </p>
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

        <!-- Toast Notification -->
        <Toast :show="toastVisible" :message="toastMessage" :type="toastType" />
    </AppLayout>
</template>
