<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed, watch } from "vue";
import { router } from "@inertiajs/vue3";
import InputField from "@/Components/UI/Forms/InputField.vue";
import TextareaField from "@/Components/UI/Forms/TextAreaField.vue";
import SelectField from "@/Components/UI/Forms/SelectField.vue";
import Button from "@/Components/UI/Button.vue";
import Toast from "@/Components/UI/Toast.vue";
import ReusableCard from "@/Components/UI/ReusableCard.vue";

// Props
const props = defineProps({
    moduleId: {
        type: Number,
        required: true,
    },
    missionId: {
        type: Number,
        required: true,
    },
    moduleName: {
        type: String,
        default: "Module",
    },
    missionName: {
        type: String,
        default: "Mission",
    },
    moduleTemplate: {
        type: Object,
        default: null,
        // Berisi: { id, name, backsound, backgroundImages, mascots }
    },
});

// Wizard state
const wizardStep = ref(1);
const successMessage = ref("");
const showSuccess = ref(false);
const cardVariant = ref("playful");

// Form untuk Material
const materialForm = ref({
    title: "",
    description: "",
    content: "",
    type: "text",
    mascot_id: null, // ID maskot yang dipilih dari template
});

// List materials yang akan disimpan
const materials = ref([]);

// Computed untuk options maskot dari template
const mascotOptions = computed(() => {
    if (!props.moduleTemplate?.mascots) return [];
    return props.moduleTemplate.mascots.map((m) => ({
        value: m.id,
        label: m.name,
    }));
});

// Get selected mascot info
const getSelectedMascot = (mascotId) => {
    if (!props.moduleTemplate?.mascots) return null;
    return props.moduleTemplate.mascots.find((m) => m.id === mascotId);
};

// Toast notification
const showToast = (message) => {
    successMessage.value = message;
    showSuccess.value = true;
    setTimeout(() => {
        showSuccess.value = false;
    }, 2500);
};

// Wizard navigation
const nextStep = () => {
    if (!materialForm.value.title.trim()) {
        showToast("Judul material harus diisi!");
        return;
    }
    wizardStep.value = 2;
};

const prevStep = () => {
    wizardStep.value = 1;
};

// Material operations
const addMaterial = () => {
    if (!materialForm.value.title.trim()) {
        showToast("Judul material harus diisi!");
        return;
    }

    if (!materialForm.value.mascot_id && mascotOptions.value.length > 0) {
        showToast("Pilih maskot untuk material ini!");
        return;
    }

    materials.value.push({
        ...materialForm.value,
        id: Date.now(),
        mission_id: props.missionId,
    });

    materialForm.value = {
        title: "",
        description: "",
        content: "",
        type: "text",
        mascot_id: null,
    };

    showToast("Material ditambahkan ke list.");
};

const removeMaterial = (id) => {
    materials.value = materials.value.filter((m) => m.id !== id);
    showToast("Material dihapus dari list.");
};

const editMaterial = (material) => {
    materialForm.value = { ...material };
    materials.value = materials.value.filter((m) => m.id !== material.id);
};

const finalSave = () => {
    if (materials.value.length === 0) {
        showToast("Tambahkan minimal 1 material!");
        return;
    }

    router.post(
        `/modules/${props.moduleId}/missions/${props.missionId}/materials`,
        {
            materials: materials.value,
        },
        {
            onSuccess: () => {
                showToast("Semua material berhasil disimpan.");
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
</script>

<template>
    <AppLayout>
        <div class="p-5">
            <!-- Header -->
            <div
                :class="[
                    'rounded-3xl p-5 mb-8',
                    cardVariant === 'playful'
                        ? 'bg-white border-4 border-green-200 shadow-playful'
                        : 'bg-white border border-gray-200 shadow-md',
                ]"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div
                            :class="[
                                cardVariant === 'playful'
                                    ? 'bg-green-100 p-3 rounded-2xl border-2 border-green-300'
                                    : 'bg-green-50 p-2 rounded-lg',
                            ]"
                        >
                            <i
                                class="pi pi-file-edit text-green-600 text-2xl"
                            ></i>
                        </div>
                        <div>
                            <h1
                                class="text-2xl md:text-3xl font-heading font-bold text-gray-800"
                            >
                                Buat Material untuk {{ missionName }}
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
                        :icon="
                            cardVariant === 'playful'
                                ? 'pi-star-fill'
                                : 'pi-star'
                        "
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
                                    ? 'bg-green-500 text-white'
                                    : 'bg-gray-300 text-gray-600',
                            ]"
                        >
                            1
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700"
                            >Form Material</span
                        >
                    </div>
                    <div class="h-1 w-20 bg-gray-300"></div>
                    <div class="flex items-center">
                        <div
                            :class="[
                                'w-10 h-10 rounded-full flex items-center justify-center font-bold',
                                wizardStep === 2
                                    ? 'bg-green-500 text-white'
                                    : 'bg-gray-300 text-gray-600',
                            ]"
                        >
                            2
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700"
                            >Review & Simpan</span
                        >
                    </div>
                </div>
            </div>

            <!-- Step 1: Form Material -->
            <div v-if="wizardStep === 1" class="max-w-3xl mx-auto">
                <ReusableCard
                    :variant="cardVariant"
                    title="Form Material Baru"
                    subtitle="Isi informasi material"
                    icon="pi-file-edit"
                    icon-color="green"
                    border-color="green"
                    :hoverable="false"
                >
                    <div class="space-y-5">
                        <!-- Judul -->
                        <InputField
                            v-model="materialForm.title"
                            label="Judul Material"
                            placeholder="Contoh: Pengenalan Angka 1-10"
                            required
                            border-color="green"
                        />

                        <!-- Deskripsi -->
                        <TextareaField
                            v-model="materialForm.description"
                            label="Deskripsi Singkat"
                            placeholder="Jelaskan materi ini..."
                            rows="3"
                            border-color="green"
                        />

                        <!-- Konten Material -->
                        <TextareaField
                            v-model="materialForm.content"
                            label="Konten Material"
                            placeholder="Tulis konten pembelajaran di sini..."
                            rows="6"
                            border-color="green"
                        />

                        <!-- Pilih Maskot (dari template modul) -->
                        <div v-if="mascotOptions.length > 0">
                            <SelectField
                                v-model="materialForm.mascot_id"
                                label="Pilih Maskot Pendamping"
                                :options="mascotOptions"
                                placeholder="-- Pilih Maskot --"
                                required
                                border-color="yellow"
                            />

                            <!-- Preview Maskot -->
                            <div v-if="materialForm.mascot_id" class="mt-3">
                                <div
                                    class="bg-yellow-50 p-4 rounded-xl border-2 border-yellow-200 flex items-center gap-4"
                                >
                                    <img
                                        :src="
                                            getSelectedMascot(
                                                materialForm.mascot_id,
                                            )?.url
                                        "
                                        :alt="
                                            getSelectedMascot(
                                                materialForm.mascot_id,
                                            )?.name
                                        "
                                        class="w-16 h-16 object-contain rounded-lg"
                                    />
                                    <div>
                                        <h4 class="font-bold text-yellow-800">
                                            {{
                                                getSelectedMascot(
                                                    materialForm.mascot_id,
                                                )?.name
                                            }}
                                        </h4>
                                        <p class="text-sm text-yellow-600">
                                            Maskot ini akan muncul di material
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="bg-red-50 p-4 rounded-xl border-2 border-red-200"
                        >
                            <p class="text-sm text-red-700">
                                <i class="pi pi-exclamation-triangle mr-2"></i>
                                Template modul ini belum memiliki maskot.
                                Silakan tambahkan maskot di template terlebih
                                dahulu.
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

                            <div class="flex gap-3">
                                <Button
                                    variant="success"
                                    size="md"
                                    icon="pi-plus"
                                    @click="addMaterial"
                                >
                                    Tambah ke List
                                </Button>

                                <Button
                                    variant="primary"
                                    size="md"
                                    @click="nextStep"
                                >
                                    Lanjut Review
                                </Button>
                            </div>
                        </div>
                    </template>
                </ReusableCard>
            </div>

            <!-- Step 2: Review Materials -->
            <div v-if="wizardStep === 2">
                <div class="max-w-5xl mx-auto space-y-6">
                    <!-- Info -->
                    <ReusableCard
                        :variant="cardVariant"
                        title="Review Material"
                        subtitle="Periksa kembali material yang akan disimpan"
                        icon="pi-list"
                        icon-color="blue"
                        border-color="blue"
                        :hoverable="false"
                    />

                    <!-- List Materials -->
                    <TransitionGroup
                        name="card"
                        tag="div"
                        class="grid grid-cols-1 md:grid-cols-2 gap-6"
                    >
                        <ReusableCard
                            v-for="material in materials"
                            :key="material.id"
                            :variant="cardVariant"
                            :title="material.title"
                            :subtitle="material.description"
                            icon="pi-file"
                            icon-color="green"
                            border-color="green"
                        >
                            <!-- Maskot Info -->
                            <div class="mb-3">
                                <div
                                    class="flex items-center gap-2 bg-yellow-50 p-2 rounded-lg border border-yellow-200"
                                >
                                    <img
                                        v-if="
                                            getSelectedMascot(
                                                material.mascot_id,
                                            )
                                        "
                                        :src="
                                            getSelectedMascot(
                                                material.mascot_id,
                                            ).url
                                        "
                                        class="w-8 h-8 object-contain rounded"
                                    />
                                    <span
                                        class="text-sm font-medium text-yellow-700"
                                    >
                                        {{
                                            getSelectedMascot(
                                                material.mascot_id,
                                            )?.name || "No Mascot"
                                        }}
                                    </span>
                                </div>
                            </div>

                            <!-- Content Preview -->
                            <div class="text-sm text-gray-600 mb-3">
                                <p class="line-clamp-2">
                                    {{ material.content }}
                                </p>
                            </div>

                            <template #footer>
                                <div class="flex justify-end gap-2 mt-4">
                                    <Button
                                        variant="warning"
                                        size="sm"
                                        icon="pi-pencil"
                                        @click="editMaterial(material)"
                                    />
                                    <Button
                                        variant="danger"
                                        size="sm"
                                        icon="pi-trash"
                                        @click="removeMaterial(material.id)"
                                    />
                                </div>
                            </template>
                        </ReusableCard>
                    </TransitionGroup>

                    <!-- Empty State -->
                    <div
                        v-if="materials.length === 0"
                        class="text-center py-12"
                    >
                        <i class="pi pi-inbox text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">
                            Belum ada material yang ditambahkan
                        </p>
                        <Button
                            variant="primary"
                            size="md"
                            icon="pi-arrow-left"
                            @click="prevStep"
                            class="mt-4"
                        >
                            Kembali & Tambah Material
                        </Button>
                    </div>

                    <!-- Actions -->
                    <div
                        v-if="materials.length > 0"
                        class="flex justify-between"
                    >
                        <Button
                            variant="light"
                            size="lg"
                            icon="pi-arrow-left"
                            @click="prevStep"
                        >
                            Kembali
                        </Button>

                        <Button
                            variant="success"
                            size="lg"
                            icon="pi-check"
                            @click="finalSave"
                        >
                            Simpan Semua Material ({{ materials.length }})
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <Toast :show="showSuccess" :message="successMessage" type="success" />
    </AppLayout>
</template>

<style scoped>
.shadow-playful {
    box-shadow:
        0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06),
        0 8px 16px -4px rgba(34, 197, 94, 0.1);
}

.card-enter-active,
.card-leave-active {
    transition: all 0.3s ease;
}

.card-enter-from {
    opacity: 0;
    transform: translateY(20px);
}

.card-leave-to {
    opacity: 0;
    transform: scale(0.9);
}
</style>
