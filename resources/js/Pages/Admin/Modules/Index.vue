<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch, onMounted, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";
import Modal from "@/Components/UI/Modal.vue";
import ConfirmDialog from "@/Components/UI/ConfirmDialog.vue";
import Toast from "@/Components/UI/Toast.vue";
import InputField from "@/Components/UI/Forms/InputField.vue";
import FileUpload from "@/Components/UI/Forms/FileUpload.vue";
import Button from "@/Components/UI/Button.vue";

// Props dari backend
const props = defineProps({
    modules: {
        type: Array,
        default: () => [],
    },
});

// State management
const showDialog = ref(false);
const showDeleteDialog = ref(false);
const isEdit = ref(false);
const selectedId = ref(null);
const successMessage = ref("");
const showSuccess = ref(false);

// Form untuk module
const moduleForm = ref({
    id: null,
    title: "",
    description: "",
    content: "",
    thumbnail: null,
    maxval_id: null,
});

// Toast notification
const showToast = (message) => {
    successMessage.value = message;
    showSuccess.value = true;
    setTimeout(() => {
        showSuccess.value = false;
    }, 2500);
};

// Lock scroll when modal open
const lockScroll = (state) => {
    document.body.style.overflow = state ? "hidden" : "";
};

watch(showDialog, (val) => lockScroll(val));
watch(showDeleteDialog, (val) => lockScroll(val));

// Handle ESC key
const handleEsc = (e) => {
    if (e.key === "Escape") {
        showDialog.value = false;
        showDeleteDialog.value = false;
    }
};

onMounted(() => {
    window.addEventListener("keydown", handleEsc);
});

onUnmounted(() => {
    window.removeEventListener("keydown", handleEsc);
});

// Module CRUD operations
const openCreate = () => {
    isEdit.value = false;
    moduleForm.value = {
        id: null,
        title: "",
        description: "",
        content: "",
        thumbnail: null,
        maxval_id: null,
    };
    showDialog.value = true;
};

const openEdit = (module) => {
    isEdit.value = true;
    moduleForm.value = JSON.parse(JSON.stringify(module));
    showDialog.value = true;
};

const confirmDelete = (id) => {
    selectedId.value = id;
    showDeleteDialog.value = true;
};

const deleteModule = () => {
    // Implementasi delete ke backend
    console.log("Delete module:", selectedId.value);
    showDeleteDialog.value = false;
    showToast("Module berhasil dihapus.");
};

const saveModule = () => {
    if (!moduleForm.value.title.trim()) {
        showToast("Judul module harus diisi!");
        return;
    }

    // Implementasi save ke backend
    if (isEdit.value) {
        console.log("Update module:", moduleForm.value);
        showToast("Module berhasil diperbarui.");
    } else {
        console.log("Create module:", moduleForm.value);
        showToast("Module baru berhasil ditambahkan.");
    }

    showDialog.value = false;
};

// Navigate to show module page
const goToShowModule = (moduleId) => {
    router.visit(route("admin.modules.show", moduleId));
};

// Handle thumbnail upload
const thumbnailFile = ref(null);
const handleThumbnailUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        const url = URL.createObjectURL(file);
        moduleForm.value.thumbnail = {
            name: file.name,
            url: url,
            file: file,
        };
    }
};

const removeThumbnail = () => {
    moduleForm.value.thumbnail = null;
    if (thumbnailFile.value) {
        thumbnailFile.value.value = "";
    }
};
</script>

<template>
    <AppLayout>
        <div class="p-5">
            <!-- Toolbar Header -->
            <div
                class="bg-white rounded-3xl border-4 border-blue-200 shadow-playful p-5 mb-8"
            >
                <div
                    class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
                >
                    <!-- Kiri -->
                    <div class="flex items-center gap-4">
                        <div
                            class="bg-blue-100 p-3 rounded-2xl border-2 border-blue-300"
                        >
                            <i class="pi pi-book text-blue-600 text-2xl"></i>
                        </div>

                        <div>
                            <h1
                                class="text-2xl md:text-3xl font-heading font-bold text-gray-800"
                            >
                                Learning Modules
                            </h1>
                            <p class="text-sm text-gray-500">
                                Kelola modul pembelajaran
                            </p>
                        </div>
                    </div>

                    <!-- Kanan -->
                    <div class="flex items-center gap-3">
                        <Button
                            variant="primary"
                            size="lg"
                            icon="pi-plus"
                            @click="openCreate"
                        >
                            Tambah Module
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Grid Modules -->
            <TransitionGroup
                name="card"
                tag="div"
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"
            >
                <div
                    v-for="module in modules"
                    :key="module.id"
                    class="bg-white rounded-3xl shadow-playful border-4 border-blue-200 p-6 hover:scale-105 transition-all cursor-pointer"
                    @click="goToShowModule(module.id)"
                >
                    <!-- Thumbnail -->
                    <div
                        v-if="module.thumbnail"
                        class="w-full h-32 bg-gray-200 rounded-2xl mb-4 overflow-hidden"
                    >
                        <img
                            :src="module.thumbnail"
                            :alt="module.title"
                            class="w-full h-full object-cover"
                        />
                    </div>

                    <h2 class="text-xl font-bold text-gray-800 mb-2">
                        {{ module.title }}
                    </h2>

                    <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                        {{ module.description }}
                    </p>

                    <!-- Stats -->
                    <div class="space-y-2 mb-6">
                        <div
                            class="flex items-center gap-2 text-sm text-gray-600"
                        >
                            <i class="pi pi-flag text-purple-500"></i>
                            <span class="font-medium">
                                {{ module.missionsCount }} Missions
                            </span>
                        </div>
                        <div
                            class="flex items-center gap-2 text-sm text-gray-600"
                        >
                            <i class="pi pi-file text-green-500"></i>
                            <span class="font-medium">
                                {{ module.materialsCount }} Materials
                            </span>
                        </div>
                        <div
                            class="flex items-center gap-2 text-sm text-gray-600"
                        >
                            <i
                                class="pi pi-question-circle text-orange-500"
                            ></i>
                            <span class="font-medium">
                                {{ module.quizzesCount }} Quizzes
                            </span>
                        </div>
                    </div>

                    <!-- Edit/Delete -->
                    <div
                        class="flex justify-end gap-2 pt-4 border-t-2 border-gray-100"
                        @click.stop
                    >
                        <Button
                            variant="warning"
                            size="md"
                            icon="pi-pencil"
                            @click="openEdit(module)"
                        />

                        <Button
                            variant="danger"
                            size="md"
                            icon="pi-trash"
                            @click="confirmDelete(module.id)"
                        />
                    </div>
                </div>
            </TransitionGroup>
        </div>

        <!-- Create / Edit Module Modal -->
        <Modal
            :show="showDialog"
            :title="isEdit ? 'Ubah Module' : 'Tambah Module Baru'"
            @close="showDialog = false"
            max-width="3xl"
        >
            <div class="space-y-5 max-h-[60vh] overflow-y-auto pr-2">
                <!-- Judul -->
                <InputField
                    v-model="moduleForm.title"
                    label="Judul Module"
                    placeholder="Contoh: Pengenalan JavaScript"
                    required
                    border-color="blue"
                />

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Deskripsi
                    </label>
                    <textarea
                        v-model="moduleForm.description"
                        rows="3"
                        class="w-full px-4 py-3 rounded-xl border-2 border-blue-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                        placeholder="Deskripsi singkat module"
                    ></textarea>
                </div>

                <!-- Content -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Konten
                    </label>
                    <textarea
                        v-model="moduleForm.content"
                        rows="5"
                        class="w-full px-4 py-3 rounded-xl border-2 border-blue-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                        placeholder="Konten lengkap module"
                    ></textarea>
                </div>

                <!-- Thumbnail Upload -->
                <div>
                    <div v-if="moduleForm.thumbnail" class="mb-3">
                        <div
                            class="flex items-center justify-between bg-blue-50 p-3 rounded-xl border-2 border-blue-200"
                        >
                            <div class="flex items-center gap-2">
                                <i class="pi pi-image text-blue-500"></i>
                                <span class="text-sm font-medium">
                                    {{
                                        typeof moduleForm.thumbnail === "string"
                                            ? "Thumbnail saat ini"
                                            : moduleForm.thumbnail.name
                                    }}
                                </span>
                            </div>
                            <Button
                                variant="danger"
                                size="sm"
                                icon="pi-times"
                                @click="removeThumbnail"
                            />
                        </div>
                    </div>

                    <FileUpload
                        ref="thumbnailFile"
                        label="Thumbnail"
                        accept="image/*"
                        button-color="blue"
                        @change="handleThumbnailUpload"
                    />
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-3">
                    <Button
                        variant="light"
                        size="md"
                        @click="showDialog = false"
                    >
                        Batal
                    </Button>

                    <Button variant="primary" size="md" @click="saveModule">
                        Simpan
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- Delete Confirmation Dialog -->
        <ConfirmDialog
            :show="showDeleteDialog"
            title="Apakah kamu yakin ingin menghapus module ini?"
            message="Semua missions, materials, dan quizzes di dalam module ini juga akan terhapus."
            @confirm="deleteModule"
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
