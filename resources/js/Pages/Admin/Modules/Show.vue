<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import Button from "@/Components/UI/Button.vue";
import Card from "@/Components/UI/Card.vue";
import ConfirmDialog from "@/Components/UI/ConfirmDialog.vue";
import Toast from "@/Components/UI/Toast.vue";
import {
    ArrowLeft,
    Flag,
    FileText,
    HelpCircle,
    Plus,
    Inbox,
    Star,
    Trash2,
} from "lucide-vue-next";

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
});

// State management
const showDeleteDialog = ref(false);
const selectedMissionId = ref(null);
const successMessage = ref("");
const showSuccess = ref(false);
const cardVariant = ref("playful");

// Toast notification
const showToast = (message) => {
    successMessage.value = message;
    showSuccess.value = true;
    setTimeout(() => {
        showSuccess.value = false;
    }, 2500);
};

// Navigate functions
const goBack = () => {
    router.visit(route("admin.modules.index"));
};

const goToAddMission = () => {
    router.visit(route("admin.modules.mission", props.module.id));
};

const goToShowMission = (missionId) => {
    router.visit(
        route("admin.modules.mission.show", [props.module.id, missionId]),
    );
};

const confirmDeleteMission = (missionId) => {
    selectedMissionId.value = missionId;
    showDeleteDialog.value = true;
};

const deleteMission = () => {
    console.log("Delete mission:", selectedMissionId.value);
    showDeleteDialog.value = false;
    showToast("Mission berhasil dihapus.");
};

const toggleCardVariant = () => {
    cardVariant.value = cardVariant.value === "playful" ? "normal" : "playful";
};
</script>

<template>
    <AppLayout>
        <div class="p-5 max-w-7xl mx-auto">
            <!-- Header -->
            <div
                class="bg-white rounded-3xl border-4 border-blue-200 shadow-playful p-6 mb-8"
            >
                <div class="flex items-start gap-4">
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
                            {{ module.title }}
                        </h1>
                        <p class="text-sm text-gray-600 mb-4">
                            {{ module.description }}
                        </p>

                        <!-- Module Stats -->
                        <div class="flex flex-wrap gap-4">
                            <div
                                class="flex items-center gap-2 bg-purple-50 px-4 py-2 rounded-xl border-2 border-purple-200"
                            >
                                <Flag class="text-purple-500 w-4 h-4" />
                                <span class="text-sm font-medium text-gray-700">
                                    {{ missions.length }} Missions
                                </span>
                            </div>
                            <div
                                class="flex items-center gap-2 bg-green-50 px-4 py-2 rounded-xl border-2 border-green-200"
                            >
                                <FileText class="text-green-500 w-4 h-4" />
                                <span class="text-sm font-medium text-gray-700">
                                    {{ module.materials_count || 0 }} Materials
                                </span>
                            </div>
                            <div
                                class="flex items-center gap-2 bg-orange-50 px-4 py-2 rounded-xl border-2 border-orange-200"
                            >
                                <HelpCircle class="text-orange-500 w-4 h-4" />
                                <span class="text-sm font-medium text-gray-700">
                                    {{ module.quizzes_count || 0 }} Quizzes
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <Button
                            :variant="
                                cardVariant === 'playful' ? 'warning' : 'light'
                            "
                            size="md"
                            :icon="Star"
                            @click="toggleCardVariant"
                        >
                            {{
                                cardVariant === "playful" ? "Playful" : "Normal"
                            }}
                        </Button>

                        <Button
                            variant="success"
                            size="lg"
                            :icon="Plus"
                            @click="goToAddMission"
                        >
                            Tambah Mission
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Missions List -->
            <div class="space-y-4">
                <div
                    class="bg-purple-100 rounded-2xl p-4 flex items-center justify-between"
                >
                    <h2 class="text-xl font-bold text-gray-800">
                        Daftar Missions
                    </h2>
                    <span
                        class="bg-purple-500 text-white px-4 py-2 rounded-full text-sm font-bold"
                    >
                        {{ missions.length }}
                    </span>
                </div>

                <!-- Empty State -->
                <div
                    v-if="missions.length === 0"
                    class="bg-white rounded-3xl border-4 border-purple-200 shadow-playful p-12 text-center"
                >
                    <Inbox class="text-gray-300 w-16 h-16 mb-4 mx-auto" />
                    <h3 class="text-xl font-bold text-gray-700 mb-2">
                        Belum ada Mission
                    </h3>
                    <p class="text-gray-500 mb-6">
                        Mulai dengan menambahkan mission pertama untuk module
                        ini
                    </p>
                    <Button
                        variant="success"
                        size="lg"
                        :icon="Plus"
                        @click="goToAddMission"
                    >
                        Tambah Mission Pertama
                    </Button>
                </div>

                <!-- Missions Grid menggunakan Card -->
                <TransitionGroup
                    v-else
                    name="card"
                    tag="div"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    <Card
                        v-for="mission in missions"
                        :key="mission.id"
                        :variant="cardVariant"
                        :title="mission.name"
                        :subtitle="mission.description"
                        :icon="Flag"
                        icon-color="purple"
                        border-color="purple"
                        :badge="`#${mission.order_number}`"
                        badge-color="purple"
                        @click="goToShowMission(mission.id)"
                    >
                        <!-- Mission Stats -->
                        <div
                            class="space-y-2 pt-2 border-t-2 border-purple-100"
                        >
                            <div
                                class="flex items-center gap-2 text-sm text-gray-600"
                            >
                                <FileText class="text-green-500 w-4 h-4" />
                                <span class="font-medium">
                                    {{ mission.materials_count || 0 }} Materials
                                </span>
                            </div>
                            <div
                                class="flex items-center gap-2 text-sm text-gray-600"
                            >
                                <HelpCircle class="text-orange-500 w-4 h-4" />
                                <span class="font-medium">
                                    {{ mission.quizzes_count || 0 }} Quizzes
                                </span>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <template #footer>
                            <div class="flex justify-end gap-2 mt-4">
                                <Button
                                    variant="danger"
                                    size="sm"
                                    :icon="Trash2"
                                    @click.stop="
                                        confirmDeleteMission(mission.id)
                                    "
                                />
                            </div>
                        </template>
                    </Card>
                </TransitionGroup>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <ConfirmDialog
            :show="showDeleteDialog"
            title="Apakah kamu yakin ingin menghapus mission ini?"
            message="Semua materials dan quizzes di dalam mission ini juga akan terhapus."
            @confirm="deleteMission"
            @cancel="showDeleteDialog = false"
        />

        <!-- Toast Notification -->
        <Toast :show="showSuccess" :message="successMessage" type="success" />
    </AppLayout>
</template>
