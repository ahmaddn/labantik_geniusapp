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

            <!-- Combined Materials & Quizzes Section -->
            <div>
                <div
                    class="bg-gradient-to-r from-green-100 to-orange-100 rounded-2xl p-4 flex items-center justify-between mb-4"
                >
                    <h2
                        class="text-xl font-bold text-gray-800 flex items-center gap-2"
                    >
                        <FileText class="text-green-600 w-5 h-5" />
                        Materials & Quizzes
                    </h2>
                    <span
                        class="bg-purple-500 text-white px-4 py-2 rounded-full text-sm font-bold"
                    >
                        {{ totalItems }}
                    </span>
                </div>

                <!-- Empty State -->
                <div
                    v-if="sortedItems.length === 0"
                    class="bg-white rounded-3xl border-4 border-purple-200 shadow-playful p-12 text-center"
                >
                    <Inbox class="text-gray-300 w-16 h-16 mb-4 mx-auto" />
                    <h3 class="text-xl font-bold text-gray-700 mb-2">
                        Belum ada Material atau Quiz
                    </h3>
                    <p class="text-gray-500 mb-6">
                        Tambahkan material pembelajaran atau quiz untuk mission
                        ini
                    </p>
                    <div class="flex flex-wrap gap-3 justify-center">
                        <Button
                            variant="success"
                            size="lg"
                            :icon="Plus"
                            @click="goToAddMaterial"
                        >
                            Tambah Material
                        </Button>
                        <Button
                            variant="warning"
                            size="lg"
                            :icon="Plus"
                            @click="goToAddQuiz"
                        >
                            Tambah Quiz
                        </Button>
                    </div>
                </div>

                <!-- Items List -->
                <TransitionGroup v-else name="card" tag="div" class="space-y-4">
                    <!-- Material Card -->
                    <div
                        v-for="item in sortedItems"
                        :key="`${item.itemType}-${item.id}`"
                        :class="[
                            'bg-white rounded-3xl border-4 shadow-playful p-6 transition-all',
                            item.itemType === 'material'
                                ? 'border-green-200 hover:border-green-400'
                                : 'border-orange-200 hover:border-orange-400',
                        ]"
                    >
                        <!-- Material Item -->
                        <div
                            v-if="item.itemType === 'material'"
                            class="flex items-start justify-between gap-4"
                        >
                            <div class="flex items-start gap-4 flex-1">
                                <component
                                    :is="getMaterialIcon(item.type)"
                                    :class="[
                                        'w-8 h-8 mt-1',
                                        getMaterialColor(item.type),
                                    ]"
                                />
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span
                                            class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700 border border-green-300 font-medium"
                                        >
                                            MATERIAL
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
                                                getMaterialBadge(item.type),
                                            ]"
                                        >
                                            {{ item.type.toUpperCase() }}
                                        </span>
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
                                    </div>

                                    <p
                                        v-if="item.description"
                                        class="text-sm text-gray-600 line-clamp-2"
                                    >
                                        {{ item.description }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <Button
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
                            class="flex items-start justify-between gap-4"
                        >
                            <div class="flex items-start gap-4 flex-1">
                                <HelpCircle
                                    class="text-orange-500 w-8 h-8 mt-1"
                                />
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
                                                item.type === 'multiple_choice'
                                                    ? 'bg-blue-100 text-blue-700 border-blue-300'
                                                    : 'bg-purple-100 text-purple-700 border-purple-300',
                                            ]"
                                        >
                                            {{
                                                item.type === "multiple_choice"
                                                    ? "MULTIPLE CHOICE"
                                                    : "DRAG & DROP"
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
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <Button
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
