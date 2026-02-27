<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import Button from "@/Components/UI/Button.vue";
import ConfirmDialog from "@/Components/UI/ConfirmDialog.vue";
import Toast from "@/Components/UI/Toast.vue";
import {
    ArrowLeft,
    ChevronRight,
    Plus,
    FileText,
    FileEdit,
    Video,
    FileType,
    HelpCircle,
    Clock,
    User,
    Calendar,
    Tag,
    List,
    Inbox,
    Trash2,
} from "lucide-vue-next";

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
const successMessage = ref("");
const showSuccess = ref(false);

const sortedMaterials = computed(() => props.materials);
const sortedQuizzes = computed(() => props.quizzes);

const showToast = (message) => {
    successMessage.value = message;
    showSuccess.value = true;
    setTimeout(() => {
        showSuccess.value = false;
    }, 2500);
};

const goBack = () => {
    router.visit(route("admin.modules.show", props.module.id));
};
const goToAddMaterial = () => {
    router.visit(
        route("admin.modules.material", [props.module.id, props.mission.id]),
    );
};
const goToAddQuiz = () => {
    router.visit(
        route("admin.modules.quiz", [props.module.id, props.mission.id]),
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
    console.log(`Delete ${deleteType.value}:`, selectedItemId.value);
    showDeleteDialog.value = false;
    showToast(
        deleteType.value === "material"
            ? "Material berhasil dihapus."
            : "Quiz berhasil dihapus.",
    );
};

const getMaterialIcon = (type) => {
    const icons = { text: FileEdit, video: Video, pdf: FileType };
    return icons[type] || FileText;
};

const getMaterialColor = (type) => {
    const colors = {
        text: "text-blue-500",
        video: "text-red-500",
        pdf: "text-orange-500",
    };
    return colors[type] || "text-gray-500";
};

const getMaterialBadge = (type) => {
    const badges = {
        text: "bg-blue-100 text-blue-700 border-blue-300",
        video: "bg-red-100 text-red-700 border-red-300",
        pdf: "bg-orange-100 text-orange-700 border-orange-300",
    };
    return badges[type] || "bg-gray-100 text-gray-700 border-gray-300";
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
                class="bg-white rounded-3xl border-4 border-purple-200 shadow-playful p-6 mb-8"
            >
                <div class="flex items-start gap-4 mb-4">
                    <button
                        @click="goBack"
                        class="bg-purple-100 p-3 rounded-2xl border-2 border-purple-300 hover:bg-purple-200 transition-all"
                    >
                        <ArrowLeft class="text-purple-600 w-5 h-5" />
                    </button>

                    <div class="flex-1">
                        <!-- Breadcrumb -->
                        <div
                            class="flex items-center gap-2 text-sm text-gray-500 mb-2"
                        >
                            <span>{{ module.title }}</span>
                            <ChevronRight class="w-3 h-3" />
                            <span class="text-purple-600 font-medium">
                                Mission {{ mission.order_number }}
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
                        variant="success"
                        size="md"
                        :icon="Plus"
                        @click="goToAddMaterial"
                    >
                        Tambah Material
                    </Button>
                    <Button
                        variant="warning"
                        size="md"
                        :icon="Plus"
                        @click="goToAddQuiz"
                    >
                        Tambah Quiz
                    </Button>
                </div>
            </div>

            <!-- Materials Section -->
            <div class="mb-8">
                <div
                    class="bg-green-100 rounded-2xl p-4 flex items-center justify-between mb-4"
                >
                    <h2
                        class="text-xl font-bold text-gray-800 flex items-center gap-2"
                    >
                        <FileText class="text-green-600 w-5 h-5" />
                        Materials
                    </h2>
                    <span
                        class="bg-green-500 text-white px-4 py-2 rounded-full text-sm font-bold"
                    >
                        {{ sortedMaterials.length }}
                    </span>
                </div>

                <!-- Empty State Materials -->
                <div
                    v-if="sortedMaterials.length === 0"
                    class="bg-white rounded-3xl border-4 border-green-200 shadow-playful p-12 text-center"
                >
                    <Inbox class="text-gray-300 w-16 h-16 mb-4 mx-auto" />
                    <h3 class="text-xl font-bold text-gray-700 mb-2">
                        Belum ada Material
                    </h3>
                    <p class="text-gray-500 mb-6">
                        Tambahkan material pembelajaran untuk mission ini
                    </p>
                    <Button
                        variant="success"
                        size="lg"
                        :icon="Plus"
                        @click="goToAddMaterial"
                    >
                        Tambah Material
                    </Button>
                </div>

                <!-- Materials List -->
                <TransitionGroup v-else name="card" tag="div" class="space-y-4">
                    <div
                        v-for="material in sortedMaterials"
                        :key="material.id"
                        class="bg-white rounded-3xl border-4 border-green-200 shadow-playful p-6 hover:border-green-400 transition-all"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-4 flex-1">
                                <component
                                    :is="getMaterialIcon(material.type)"
                                    :class="[
                                        'w-8 h-8 mt-1',
                                        getMaterialColor(material.type),
                                    ]"
                                />
                                <div class="flex-1">
                                    <h3
                                        class="text-xl font-bold text-gray-800 mb-2"
                                    >
                                        {{ material.title }}
                                    </h3>

                                    <div class="flex items-center gap-3 mb-3">
                                        <span
                                            :class="[
                                                'text-xs px-3 py-1 rounded-full border font-medium',
                                                getMaterialBadge(material.type),
                                            ]"
                                        >
                                            {{ material.type.toUpperCase() }}
                                        </span>
                                        <span
                                            class="text-xs text-gray-500 flex items-center gap-1"
                                        >
                                            <Clock class="w-3 h-3" />
                                            {{
                                                formatDate(material.created_at)
                                            }}
                                        </span>
                                        <span
                                            v-if="material.created_by"
                                            class="text-xs text-gray-500 flex items-center gap-1"
                                        >
                                            <User class="w-3 h-3" />
                                            {{ material.created_by }}
                                        </span>
                                    </div>

                                    <p
                                        v-if="material.description"
                                        class="text-sm text-gray-600 line-clamp-2"
                                    >
                                        {{ material.description }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <Button
                                    variant="danger"
                                    size="md"
                                    :icon="Trash2"
                                    @click="confirmDeleteMaterial(material.id)"
                                />
                            </div>
                        </div>
                    </div>
                </TransitionGroup>
            </div>

            <!-- Quizzes Section -->
            <div>
                <div
                    class="bg-orange-100 rounded-2xl p-4 flex items-center justify-between mb-4"
                >
                    <h2
                        class="text-xl font-bold text-gray-800 flex items-center gap-2"
                    >
                        <HelpCircle class="text-orange-600 w-5 h-5" />
                        Quizzes
                    </h2>
                    <span
                        class="bg-orange-500 text-white px-4 py-2 rounded-full text-sm font-bold"
                    >
                        {{ sortedQuizzes.length }}
                    </span>
                </div>

                <!-- Empty State Quizzes -->
                <div
                    v-if="sortedQuizzes.length === 0"
                    class="bg-white rounded-3xl border-4 border-orange-200 shadow-playful p-12 text-center"
                >
                    <Inbox class="text-gray-300 w-16 h-16 mb-4 mx-auto" />
                    <h3 class="text-xl font-bold text-gray-700 mb-2">
                        Belum ada Quiz
                    </h3>
                    <p class="text-gray-500 mb-6">
                        Tambahkan quiz untuk menguji pemahaman siswa
                    </p>
                    <Button
                        variant="warning"
                        size="lg"
                        :icon="Plus"
                        @click="goToAddQuiz"
                    >
                        Tambah Quiz
                    </Button>
                </div>

                <!-- Quizzes List -->
                <TransitionGroup v-else name="card" tag="div" class="space-y-4">
                    <div
                        v-for="quiz in sortedQuizzes"
                        :key="quiz.id"
                        class="bg-white rounded-3xl border-4 border-orange-200 shadow-playful p-6 hover:border-orange-400 transition-all"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-4 flex-1">
                                <HelpCircle
                                    class="text-orange-500 w-8 h-8 mt-1"
                                />
                                <div class="flex-1">
                                    <h3
                                        class="text-xl font-bold text-gray-800 mb-2"
                                    >
                                        {{ quiz.title }}
                                    </h3>

                                    <div class="flex items-center gap-3 mb-3">
                                        <span
                                            :class="[
                                                'text-xs px-3 py-1 rounded-full border font-medium',
                                                quiz.type === 'multiple_choice'
                                                    ? 'bg-blue-100 text-blue-700 border-blue-300'
                                                    : 'bg-purple-100 text-purple-700 border-purple-300',
                                            ]"
                                        >
                                            {{
                                                quiz.type === "multiple_choice"
                                                    ? "MULTIPLE CHOICE"
                                                    : "DRAG & DROP"
                                            }}
                                        </span>
                                        <span
                                            class="text-xs text-gray-500 flex items-center gap-1"
                                        >
                                            <Clock class="w-3 h-3" />
                                            {{ quiz.time_limit }} menit
                                        </span>
                                        <span
                                            class="text-xs text-gray-500 flex items-center gap-1"
                                        >
                                            <Calendar class="w-3 h-3" />
                                            {{ formatDate(quiz.created_at) }}
                                        </span>
                                    </div>

                                    <p
                                        v-if="quiz.description"
                                        class="text-sm text-gray-600 line-clamp-2"
                                    >
                                        {{ quiz.description }}
                                    </p>

                                    <!-- Quiz Stats -->
                                    <div class="flex gap-4 mt-3">
                                        <span
                                            class="text-xs text-gray-500 flex items-center gap-1"
                                        >
                                            <List class="w-3 h-3" />
                                            {{ quiz.questions_count || 0 }}
                                            Pertanyaan
                                        </span>
                                        <span
                                            v-if="quiz.category"
                                            class="text-xs text-gray-500 flex items-center gap-1"
                                        >
                                            <Tag class="w-3 h-3" />
                                            {{ quiz.category }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <Button
                                    variant="danger"
                                    size="md"
                                    :icon="Trash2"
                                    @click="confirmDeleteQuiz(quiz.id)"
                                />
                            </div>
                        </div>
                    </div>
                </TransitionGroup>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <ConfirmDialog
            :show="showDeleteDialog"
            :title="
                deleteType === 'material'
                    ? 'Apakah kamu yakin ingin menghapus material ini?'
                    : 'Apakah kamu yakin ingin menghapus quiz ini?'
            "
            :message="
                deleteType === 'material'
                    ? 'Data material akan terhapus permanen.'
                    : 'Semua pertanyaan dan jawaban di dalam quiz ini akan terhapus.'
            "
            @confirm="deleteItem"
            @cancel="showDeleteDialog = false"
        />

        <!-- Toast Notification -->
        <Toast :show="showSuccess" :message="successMessage" type="success" />
    </AppLayout>
</template>

<style scoped>
.card-enter-active,
.card-leave-active {
    transition: all 0.3s ease;
}

.card-enter-from {
    opacity: 0;
    transform: scale(0.9) translateY(20px);
}

.card-leave-to {
    opacity: 0;
    transform: scale(0.9) translateY(-20px);
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
