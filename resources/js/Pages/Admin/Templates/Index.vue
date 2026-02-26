<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch, onMounted, onUnmounted } from "vue";
import Modal from "@/Components/UI/Modal.vue";
import ConfirmDialog from "@/Components/UI/ConfirmDialog.vue";
import Toast from "@/Components/UI/Toast.vue";
import InputField from "@/Components/UI/Forms/InputField.vue";
import FileUpload from "@/Components/UI/Forms/FileUpload.vue";
import Button from "@/Components/UI/Button.vue";

const templates = ref([
    {
        id: 1,
        name: "Template Siang Cerah",
        backsound: { name: "happy-music.mp3", url: "/sounds/happy.mp3" },
        backgroundImages: [
            { id: 1, name: "bg-sky.jpg", url: "/images/bg1.jpg" },
            { id: 2, name: "bg-grass.jpg", url: "/images/bg2.jpg" },
        ],
        mascots: [
            { id: 1, name: "Maskot Kucing", url: "/images/cat.png" },
            { id: 2, name: "Maskot Anjing", url: "/images/dog.png" },
        ],
    },
]);

const showDialog = ref(false);
const showDeleteDialog = ref(false);
const isEdit = ref(false);
const selectedId = ref(null);
const successMessage = ref("");
const showSuccess = ref(false);

const showToast = (message) => {
    successMessage.value = message;
    showSuccess.value = true;

    setTimeout(() => {
        showSuccess.value = false;
    }, 2500);
};

const lockScroll = (state) => {
    document.body.style.overflow = state ? "hidden" : "";
};

watch(showDialog, (val) => lockScroll(val));
watch(showDeleteDialog, (val) => lockScroll(val));

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

const form = ref({
    id: null,
    name: "",
    backsound: null,
    backgroundImages: [],
    mascots: [],
});

const backsoundFile = ref(null);
const backgroundFiles = ref(null);
const mascotFiles = ref(null);

const openCreate = () => {
    isEdit.value = false;
    form.value = {
        id: null,
        name: "",
        backsound: null,
        backgroundImages: [],
        mascots: [],
    };
    backsoundFile.value = null;
    backgroundFiles.value = null;
    mascotFiles.value = null;
    showDialog.value = true;
};

const openEdit = (template) => {
    isEdit.value = true;
    form.value = JSON.parse(JSON.stringify(template));
    backsoundFile.value = null;
    backgroundFiles.value = null;
    mascotFiles.value = null;
    showDialog.value = true;
};

const confirmDelete = (id) => {
    selectedId.value = id;
    showDeleteDialog.value = true;
};

const deleteTemplate = () => {
    templates.value = templates.value.filter((t) => t.id !== selectedId.value);
    showDeleteDialog.value = false;
    showToast("Template berhasil dihapus.");
};

const handleBacksoundUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        const url = URL.createObjectURL(file);
        form.value.backsound = {
            name: file.name,
            url: url,
            file: file,
        };
    }
};

const handleBackgroundUpload = (event) => {
    const files = Array.from(event.target.files);
    files.forEach((file) => {
        const url = URL.createObjectURL(file);
        form.value.backgroundImages.push({
            id: Date.now() + Math.random(),
            name: file.name,
            url: url,
            file: file,
        });
    });
};

const handleMascotUpload = (event) => {
    const files = Array.from(event.target.files);
    files.forEach((file) => {
        const url = URL.createObjectURL(file);
        form.value.mascots.push({
            id: Date.now() + Math.random(),
            name: file.name,
            url: url,
            file: file,
        });
    });
};

const removeBacksound = () => {
    form.value.backsound = null;
    if (backsoundFile.value) {
        backsoundFile.value.value = "";
    }
};

const removeBackground = (id) => {
    form.value.backgroundImages = form.value.backgroundImages.filter(
        (bg) => bg.id !== id,
    );
};

const removeMascot = (id) => {
    form.value.mascots = form.value.mascots.filter((m) => m.id !== id);
};

const saveTemplate = () => {
    if (!form.value.name.trim()) {
        showToast("Nama template harus diisi!");
        return;
    }

    if (isEdit.value) {
        const index = templates.value.findIndex((t) => t.id === form.value.id);
        templates.value[index] = { ...form.value };
        showToast("Template berhasil diperbarui.");
    } else {
        templates.value.push({
            ...form.value,
            id: Date.now(),
        });
        showToast("Template baru berhasil ditambahkan.");
    }

    showDialog.value = false;
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
                            <i class="pi pi-palette text-blue-600 text-2xl"></i>
                        </div>

                        <div>
                            <h1
                                class="text-2xl md:text-3xl font-heading font-bold text-gray-800"
                            >
                                Daftar Template
                            </h1>
                            <p class="text-sm text-gray-500">
                                Kelola template dengan backsound, background,
                                dan maskot
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
                            Tambah Template
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Grid -->
            <TransitionGroup
                name="card"
                tag="div"
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"
            >
                <div
                    v-for="template in templates"
                    :key="template.id"
                    class="bg-white rounded-3xl shadow-playful border-4 border-blue-200 p-6 hover:scale-105 transition-all"
                >
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        {{ template.name }}
                    </h2>

                    <!-- Backsound Info -->
                    <div class="mb-3">
                        <div
                            class="flex items-center gap-2 text-sm text-gray-600"
                        >
                            <i class="pi pi-volume-up text-blue-500"></i>
                            <span class="font-medium">
                                {{
                                    template.backsound
                                        ? template.backsound.name
                                        : "Tidak ada backsound"
                                }}
                            </span>
                        </div>
                    </div>

                    <!-- Background Images Count -->
                    <div class="mb-3">
                        <div
                            class="flex items-center gap-2 text-sm text-gray-600"
                        >
                            <i class="pi pi-images text-green-500"></i>
                            <span class="font-medium">
                                {{ template.backgroundImages.length }}
                                Background
                            </span>
                        </div>
                    </div>

                    <!-- Mascots Count -->
                    <div class="mb-6">
                        <div
                            class="flex items-center gap-2 text-sm text-gray-600"
                        >
                            <i class="pi pi-star text-yellow-500"></i>
                            <span class="font-medium">
                                {{ template.mascots.length }} Maskot
                            </span>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <Button
                            variant="warning"
                            size="md"
                            icon="pi-pencil"
                            @click="openEdit(template)"
                        />

                        <Button
                            variant="danger"
                            size="md"
                            icon="pi-trash"
                            @click="confirmDelete(template.id)"
                        />
                    </div>
                </div>
            </TransitionGroup>
        </div>

        <!-- Create / Edit Modal -->
        <Modal
            :show="showDialog"
            :title="isEdit ? 'Ubah Template' : 'Tambah Template Baru'"
            @close="showDialog = false"
            max-width="3xl"
        >
            <div class="space-y-5 max-h-[60vh] overflow-y-auto pr-2">
                <!-- Nama Template -->
                <InputField
                    v-model="form.name"
                    label="Nama Template"
                    placeholder="Contoh: Template Siang Cerah"
                    required
                    border-color="blue"
                />

                <!-- Backsound Upload -->
                <div>
                    <div v-if="form.backsound" class="mb-3">
                        <div
                            class="flex items-center justify-between bg-blue-50 p-3 rounded-xl border-2 border-blue-200"
                        >
                            <div class="flex items-center gap-2">
                                <i class="pi pi-volume-up text-blue-500"></i>
                                <span class="text-sm font-medium">{{
                                    form.backsound.name
                                }}</span>
                            </div>
                            <Button
                                variant="danger"
                                size="sm"
                                icon="pi-times"
                                @click="removeBacksound"
                            />
                        </div>
                    </div>

                    <FileUpload
                        ref="backsoundFile"
                        label="Backsound (Audio)"
                        accept="audio/*"
                        button-color="blue"
                        @change="handleBacksoundUpload"
                    />
                </div>

                <!-- Background Images Upload -->
                <div>
                    <div
                        v-if="form.backgroundImages.length > 0"
                        class="mb-3 space-y-2"
                    >
                        <div
                            v-for="bg in form.backgroundImages"
                            :key="bg.id"
                            class="flex items-center justify-between bg-green-50 p-3 rounded-xl border-2 border-green-200"
                        >
                            <div class="flex items-center gap-2">
                                <i class="pi pi-image text-green-500"></i>
                                <span class="text-sm font-medium">{{
                                    bg.name
                                }}</span>
                            </div>
                            <Button
                                variant="danger"
                                size="sm"
                                icon="pi-times"
                                @click="removeBackground(bg.id)"
                            />
                        </div>
                    </div>

                    <FileUpload
                        ref="backgroundFiles"
                        label="Background Images (Multiple)"
                        accept="image/*"
                        :multiple="true"
                        button-color="green"
                        @change="handleBackgroundUpload"
                    />
                </div>

                <!-- Mascots Upload -->
                <div>
                    <div v-if="form.mascots.length > 0" class="mb-3 space-y-2">
                        <div
                            v-for="mascot in form.mascots"
                            :key="mascot.id"
                            class="flex items-center justify-between bg-yellow-50 p-3 rounded-xl border-2 border-yellow-200"
                        >
                            <div class="flex items-center gap-2">
                                <i class="pi pi-star text-yellow-500"></i>
                                <span class="text-sm font-medium">{{
                                    mascot.name
                                }}</span>
                            </div>
                            <Button
                                variant="danger"
                                size="sm"
                                icon="pi-times"
                                @click="removeMascot(mascot.id)"
                            />
                        </div>
                    </div>

                    <FileUpload
                        ref="mascotFiles"
                        label="Maskot (Multiple)"
                        accept="image/*"
                        :multiple="true"
                        button-color="yellow"
                        @change="handleMascotUpload"
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

                    <Button variant="primary" size="md" @click="saveTemplate">
                        Simpan
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- Delete Confirmation Dialog -->
        <ConfirmDialog
            :show="showDeleteDialog"
            title="Apakah kamu yakin ingin menghapus template ini?"
            message="Data yang sudah dihapus tidak dapat dikembalikan lagi."
            @confirm="deleteTemplate"
            @cancel="showDeleteDialog = false"
        />

        <!-- Toast Notification -->
        <Toast :show="showSuccess" :message="successMessage" type="success" />
    </AppLayout>
</template>
