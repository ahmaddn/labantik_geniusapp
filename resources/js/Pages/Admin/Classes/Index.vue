<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch, onMounted, onUnmounted } from "vue";
import Modal from "@/Components/UI/Modal.vue";
import ConfirmDialog from "@/Components/UI/ConfirmDialog.vue";
import Toast from "@/Components/UI/Toast.vue";
import InputField from "@/Components/UI/Forms/InputField.vue";
import TextareaField from "@/Components/UI/Forms/TextAreaField.vue";
import Button from "@/Components/UI/Button.vue";
import Card from "@/Components/UI/Card.vue";

const classes = ref([
    { id: 1, name: "Mathematics", description: "Basic algebra and geometry" },
    { id: 2, name: "Physics", description: "Introduction to mechanics" },
    { id: 3, name: "Chemistry", description: "Organic chemistry fundamentals" },
    { id: 4, name: "Biology", description: "Introduction to living organisms" },
]);

const showDialog = ref(false);
const showDeleteDialog = ref(false);
const isEdit = ref(false);
const selectedId = ref(null);
const successMessage = ref("");
const showSuccess = ref(false);
const cardVariant = ref("playful"); // Toggle between 'playful' and 'normal'

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
    description: "",
});

const openCreate = () => {
    isEdit.value = false;
    form.value = { id: null, name: "", description: "" };
    showDialog.value = true;
};

const openEdit = (classItem) => {
    isEdit.value = true;
    form.value = { ...classItem };
    showDialog.value = true;
};

const confirmDelete = (id) => {
    selectedId.value = id;
    showDeleteDialog.value = true;
};

const deleteClass = () => {
    classes.value = classes.value.filter((c) => c.id !== selectedId.value);
    showDeleteDialog.value = false;
    showToast("Data kelas berhasil dihapus.");
};

const saveClass = () => {
    if (!form.value.name.trim()) return;

    if (isEdit.value) {
        const index = classes.value.findIndex((c) => c.id === form.value.id);
        classes.value[index] = { ...form.value };
        showToast("Data kelas berhasil diperbarui.");
    } else {
        classes.value.push({
            ...form.value,
            id: Date.now(),
        });
        showToast("Kelas baru berhasil ditambahkan.");
    }

    showDialog.value = false;
};

const toggleCardVariant = () => {
    cardVariant.value = cardVariant.value === "playful" ? "normal" : "playful";
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
                                Daftar Kelas
                            </h1>
                            <p class="text-sm text-gray-500">
                                Kelola dan atur data kelas dengan mudah
                            </p>
                        </div>
                    </div>

                    <!-- Kanan -->
                    <div class="flex items-center gap-3">
                        <!-- Toggle Card Style -->
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
                            {{
                                cardVariant === "playful" ? "Playful" : "Normal"
                            }}
                        </Button>

                        <Button
                            variant="primary"
                            size="lg"
                            icon="pi-plus"
                            @click="openCreate"
                        >
                            Tambah Kelas
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Grid menggunakan Card -->
            <TransitionGroup
                name="card"
                tag="div"
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"
            >
                <Card
                    v-for="classItem in classes"
                    :key="classItem.id"
                    :variant="cardVariant"
                    :title="classItem.name"
                    :subtitle="classItem.description"
                    icon="pi-book"
                    icon-color="blue"
                    border-color="blue"
                >
                    <template #footer>
                        <div class="flex justify-end gap-2 mt-4">
                            <Button
                                variant="warning"
                                size="md"
                                icon="pi-pencil"
                                @click="openEdit(classItem)"
                            />

                            <Button
                                variant="danger"
                                size="md"
                                icon="pi-trash"
                                @click="confirmDelete(classItem.id)"
                            />
                        </div>
                    </template>
                </Card>
            </TransitionGroup>
        </div>

        <!-- Create / Edit Modal -->
        <Modal
            :show="showDialog"
            :title="isEdit ? 'Ubah Data Kelas' : 'Tambah Kelas Baru'"
            @close="showDialog = false"
        >
            <div class="space-y-4">
                <InputField
                    v-model="form.name"
                    type="text"
                    label="Nama Kelas"
                    placeholder="Nama Kelas (Contoh: 2A)"
                    icon="pi-book"
                    required
                    border-color="blue"
                />

                <TextareaField
                    v-model="form.description"
                    label="Deskripsi"
                    placeholder="Penjelasan singkat tentang kelas ini"
                    :rows="3"
                    border-color="blue"
                />
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

                    <Button
                        variant="primary"
                        size="md"
                        icon="pi-save"
                        @click="saveClass"
                    >
                        Simpan
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- Delete Confirmation Dialog -->
        <ConfirmDialog
            :show="showDeleteDialog"
            title="Apakah kamu yakin ingin menghapus kelas ini?"
            message="Data yang sudah dihapus tidak dapat dikembalikan lagi."
            @confirm="deleteClass"
            @cancel="showDeleteDialog = false"
        />

        <!-- Toast Notification -->
        <Toast :show="showSuccess" :message="successMessage" type="success" />
    </AppLayout>
</template>
