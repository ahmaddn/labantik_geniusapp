<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import Button from "@/Components/UI/Button.vue";
import ConfirmDialog from "@/Components/UI/ConfirmDialog.vue";
import Toast from "@/Components/UI/Toast.vue";
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
                            <span>{{ module.title || module.name }}</span>
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
                    class="bg-gradient-to-r from-green-100 to-orange-100 rounded-2xl p-4 flex items-center justify-between mb-6"
                >
                    <h2 class="text-xl font-bold text-gray-800">
                        Materials & Quizzes
                    </h2>
                    <span
                        class="bg-white text-gray-700 px-4 py-2 rounded-full text-sm font-bold shadow-sm"
                    >
                        {{ totalItems }} Item{{ totalItems !== 1 ? "s" : "" }}
                    </span>
                </div>

                <!-- Empty State -->
                <div
                    v-if="sortedItems.length === 0"
                    class="bg-white rounded-3xl border-4 border-gray-200 shadow-playful p-12 text-center"
                >
                    <Inbox class="text-gray-300 w-16 h-16 mb-4 mx-auto" />
                    <h3 class="text-xl font-bold text-gray-700 mb-2">
                        Belum ada Material atau Quiz
                    </h3>
                    <p class="text-gray-500 mb-6">
                        Mulai dengan menambahkan material pembelajaran atau quiz
                        untuk mission ini
                    </p>
                    <div class="flex justify-center gap-3">
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

                <!-- Items List -->
                <TransitionGroup v-else name="card" tag="div" class="space-y-4">
                    <div
                        v-for="item in sortedItems"
                        :key="`${item.itemType}-${item.id}`"
                        class="bg-white rounded-3xl border-4 shadow-playful p-6 hover:shadow-lg transition-all"
                        :class="{
                            'border-green-200': item.itemType === 'material',
                            'border-orange-200': item.itemType === 'quiz',
                        }"
                    >
                        <!-- Material Item -->
                        <div
                            v-if="item.itemType === 'material'"
                            class="flex items-start justify-between gap-4"
                        >
                            <div class="flex items-start gap-4 flex-1">
                                <div
                                    class="bg-green-100 p-3 rounded-2xl border-2 border-green-300"
                                >
                                    <FileText class="text-green-600 w-8 h-8" />
                                </div>
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

                            <div class="flex gap-2">
                                <Button
                                    variant="warning"
                                    size="md"
                                    :icon="Pencil"
                                    @click="goToEditMaterial(item.id)"
                                />
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

                            <div class="flex gap-2">
                                <Button
                                    variant="warning"
                                    size="md"
                                    :icon="Pencil"
                                    @click="goToEditQuiz(item.id)"
                                />
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
                    ? 'Hapus material ini?'
                    : 'Hapus quiz ini?'
            "
            :message="
                deleteType === 'material'
                    ? 'Material akan dihapus permanen.'
                    : 'Semua pertanyaan dan jawaban di dalam quiz ini akan terhapus.'
            "
            @confirm="deleteItem"
            @cancel="showDeleteDialog = false"
        />

        <!-- Toast Notification -->
        <Toast :show="toastVisible" :message="toastMessage" :type="toastType" />
    </AppLayout>
</template>
