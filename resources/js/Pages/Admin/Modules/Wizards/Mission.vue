<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import InputField from "@/Components/UI/Forms/InputField.vue";
import Button from "@/Components/UI/Button.vue";
import Toast from "@/Components/UI/Toast.vue";
import {
    ArrowLeft,
    ArrowRight,
    Check,
    Plus,
    Pencil,
    Trash2,
    Info,
    PlusCircle,
} from "lucide-vue-next";

// Props
const props = defineProps({
    moduleId: { type: Number, required: true },
    moduleName: { type: String, default: "Module" },
});

// Wizard state
const wizardStep = ref(1);
const successMessage = ref("");
const showSuccess = ref(false);

// Form untuk Mission
const missionForm = ref({
    name: "",
    description: "",
    order_number: 1,
    maxval_id: null,
});

// List missions yang akan disimpan
const missions = ref([]);

const showToast = (message) => {
    successMessage.value = message;
    showSuccess.value = true;
    setTimeout(() => {
        showSuccess.value = false;
    }, 2500);
};

const nextStep = () => {
    if (!missionForm.value.name.trim()) {
        showToast("Nama mission harus diisi!");
        return;
    }
    wizardStep.value = 2;
};

const prevStep = () => {
    wizardStep.value = 1;
};

const addMission = () => {
    if (!missionForm.value.name.trim()) {
        showToast("Nama mission harus diisi!");
        return;
    }

    missions.value.push({
        ...missionForm.value,
        id: Date.now(),
        module_id: props.moduleId,
    });
    missionForm.value = {
        name: "",
        description: "",
        order_number: missions.value.length + 1,
        maxval_id: null,
    };
    showToast("Mission ditambahkan ke list.");
};

const removeMission = (id) => {
    missions.value = missions.value.filter((m) => m.id !== id);
    missions.value.forEach((m, index) => {
        m.order_number = index + 1;
    });
};

const editMission = (mission) => {
    missionForm.value = { ...mission };
    missions.value = missions.value.filter((m) => m.id !== mission.id);
};

const saveMissions = () => {
    if (missions.value.length === 0) {
        showToast("Minimal tambahkan 1 mission!");
        return;
    }
    console.log("Saving missions:", missions.value);
    showToast(`${missions.value.length} Mission berhasil ditambahkan.`);
    setTimeout(() => {
        router.visit(route("admin.modules.show", props.moduleId));
    }, 1000);
};

const cancel = () => {
    router.visit(route("admin.modules.show", props.moduleId));
};

const wizardTitle = computed(() => {
    return wizardStep.value === 1
        ? "Step 1: Info Dasar Mission"
        : "Step 2: Daftar Missions";
});
</script>

<template>
    <AppLayout>
        <div class="p-5 max-w-5xl mx-auto">
            <!-- Header -->
            <div
                class="bg-white rounded-3xl border-4 border-purple-200 shadow-playful p-5 mb-8"
            >
                <div class="flex items-center gap-4">
                    <button
                        @click="cancel"
                        class="bg-purple-100 p-3 rounded-2xl border-2 border-purple-300 hover:bg-purple-200 transition-all"
                    >
                        <ArrowLeft class="text-purple-600 w-5 h-5" />
                    </button>

                    <div class="flex-1">
                        <h1
                            class="text-2xl md:text-3xl font-heading font-bold text-gray-800"
                        >
                            Tambah Mission
                        </h1>
                        <p class="text-sm text-gray-500">
                            Module: {{ moduleName }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Wizard Container -->
            <div
                class="bg-white rounded-3xl border-4 border-purple-200 shadow-playful p-8"
            >
                <!-- Wizard Progress -->
                <div class="mb-8">
                    <div class="flex items-center justify-center gap-2">
                        <div class="flex items-center">
                            <div
                                :class="[
                                    'w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg transition-all',
                                    wizardStep >= 1
                                        ? 'bg-purple-500 text-white'
                                        : 'bg-gray-200 text-gray-500',
                                ]"
                            >
                                1
                            </div>
                            <div class="ml-3 text-sm font-medium text-gray-700">
                                Info Dasar
                            </div>
                        </div>

                        <div
                            :class="[
                                'w-24 h-1 transition-all',
                                wizardStep > 1
                                    ? 'bg-purple-500'
                                    : 'bg-gray-200',
                            ]"
                        ></div>

                        <div class="flex items-center">
                            <div
                                :class="[
                                    'w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg transition-all',
                                    wizardStep >= 2
                                        ? 'bg-purple-500 text-white'
                                        : 'bg-gray-200 text-gray-500',
                                ]"
                            >
                                2
                            </div>
                            <div class="ml-3 text-sm font-medium text-gray-700">
                                Daftar Missions
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Title -->
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
                    {{ wizardTitle }}
                </h2>

                <!-- Step 1: Mission Info -->
                <div v-if="wizardStep === 1" class="space-y-5">
                    <InputField
                        v-model="missionForm.name"
                        label="Nama Mission"
                        placeholder="Contoh: Misi Pertama - Pengenalan Variabel"
                        required
                        border-color="purple"
                    />

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Deskripsi
                        </label>
                        <textarea
                            v-model="missionForm.description"
                            rows="4"
                            class="w-full px-4 py-3 rounded-xl border-2 border-purple-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all"
                            placeholder="Deskripsi lengkap mission ini..."
                        ></textarea>
                    </div>

                    <InputField
                        v-model.number="missionForm.order_number"
                        label="Urutan Mission"
                        type="number"
                        placeholder="1"
                        border-color="purple"
                    />

                    <div
                        class="bg-purple-50 p-4 rounded-xl border-2 border-purple-200"
                    >
                        <div class="flex items-start gap-3">
                            <Info class="text-purple-500 w-5 h-5 mt-1" />
                            <div class="text-sm text-gray-700">
                                <p class="font-medium mb-1">Tips:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>
                                        Berikan nama yang jelas dan deskriptif
                                    </li>
                                    <li>
                                        Urutan mission menentukan alur
                                        pembelajaran
                                    </li>
                                    <li>
                                        Anda bisa menambahkan banyak mission di
                                        step berikutnya
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Mission List -->
                <div
                    v-if="wizardStep === 2"
                    class="space-y-5 max-h-[50vh] overflow-y-auto pr-2"
                >
                    <!-- Add Mission Form -->
                    <div
                        class="bg-purple-50 p-5 rounded-2xl border-2 border-purple-200"
                    >
                        <h3
                            class="font-bold text-gray-800 mb-4 flex items-center gap-2"
                        >
                            <PlusCircle class="text-purple-500 w-5 h-5" />
                            Tambah Mission Baru
                        </h3>

                        <div class="space-y-4">
                            <InputField
                                v-model="missionForm.name"
                                label="Nama Mission"
                                placeholder="Nama mission"
                                border-color="purple"
                            />

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Deskripsi (Opsional)
                                </label>
                                <textarea
                                    v-model="missionForm.description"
                                    rows="2"
                                    class="w-full px-4 py-3 rounded-xl border-2 border-purple-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all"
                                    placeholder="Deskripsi singkat..."
                                ></textarea>
                            </div>

                            <div class="flex gap-3">
                                <div class="flex-1">
                                    <InputField
                                        v-model.number="
                                            missionForm.order_number
                                        "
                                        label="Urutan"
                                        type="number"
                                        border-color="purple"
                                    />
                                </div>
                                <div class="flex items-end">
                                    <Button
                                        variant="success"
                                        size="md"
                                        :icon="Plus"
                                        @click="addMission"
                                    >
                                        Tambah ke List
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mission List -->
                    <div v-if="missions.length > 0" class="space-y-3">
                        <div
                            class="flex items-center justify-between bg-purple-100 px-4 py-2 rounded-xl"
                        >
                            <h4 class="font-bold text-gray-800">
                                Daftar Missions
                            </h4>
                            <span
                                class="bg-purple-500 text-white px-3 py-1 rounded-full text-sm font-bold"
                            >
                                {{ missions.length }}
                            </span>
                        </div>

                        <div
                            v-for="(mission, index) in missions"
                            :key="mission.id"
                            class="bg-white p-4 rounded-2xl border-2 border-purple-200 hover:border-purple-400 transition-all"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span
                                            class="bg-purple-500 text-white w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold"
                                        >
                                            {{ mission.order_number }}
                                        </span>
                                        <h3 class="font-bold text-gray-800">
                                            {{ mission.name }}
                                        </h3>
                                    </div>

                                    <p
                                        v-if="mission.description"
                                        class="text-sm text-gray-600 ml-11"
                                    >
                                        {{ mission.description }}
                                    </p>
                                </div>

                                <div class="flex gap-2">
                                    <Button
                                        variant="warning"
                                        size="sm"
                                        :icon="Pencil"
                                        @click="editMission(mission)"
                                    />
                                    <Button
                                        variant="danger"
                                        size="sm"
                                        :icon="Trash2"
                                        @click="removeMission(mission.id)"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-12 text-gray-400">
                        <ArrowLeft class="w-12 h-12 mb-3 mx-auto opacity-30" />
                        <p class="text-lg">Belum ada mission ditambahkan</p>
                        <p class="text-sm">
                            Gunakan form di atas untuk menambahkan mission
                        </p>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div
                    class="flex justify-between mt-8 pt-6 border-t-2 border-purple-100"
                >
                    <div>
                        <Button
                            v-if="wizardStep > 1"
                            variant="light"
                            size="lg"
                            :icon="ArrowLeft"
                            @click="prevStep"
                        >
                            Kembali
                        </Button>
                    </div>

                    <div class="flex gap-3">
                        <Button variant="light" size="lg" @click="cancel">
                            Batal
                        </Button>

                        <Button
                            v-if="wizardStep < 2"
                            variant="primary"
                            size="lg"
                            :icon="ArrowRight"
                            icon-pos="right"
                            @click="nextStep"
                        >
                            Lanjut ke Step 2
                        </Button>

                        <Button
                            v-else
                            variant="success"
                            size="lg"
                            :icon="Check"
                            @click="saveMissions"
                        >
                            Simpan Semua Mission
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <Toast :show="showSuccess" :message="successMessage" type="success" />
    </AppLayout>
</template>
