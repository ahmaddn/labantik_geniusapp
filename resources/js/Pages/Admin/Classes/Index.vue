<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch, onMounted, onUnmounted } from "vue";
import Modal from "@/Components/UI/Modal.vue";
import ConfirmDialog from "@/Components/UI/ConfirmDialog.vue";
import Toast from "@/Components/UI/Toast.vue";

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
                        <button
                            @click="openCreate"
                            class="bg-blue-500 text-white px-6 py-3 rounded-2xl font-bold border-4 border-blue-600 hover:scale-105 transition-transform flex items-center gap-2"
                        >
                            <i class="pi pi-plus"></i>
                            Tambah Kelas
                        </button>
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
                    v-for="classItem in classes"
                    :key="classItem.id"
                    class="bg-white rounded-3xl shadow-playful border-4 border-blue-200 p-6 hover:scale-105 transition-all"
                >
                    <h2 class="text-xl font-bold text-gray-800 mb-2">
                        {{ classItem.name }}
                    </h2>

                    <p class="text-gray-600 mb-6">
                        {{ classItem.description }}
                    </p>

                    <div class="flex justify-end gap-2">
                        <button
                            @click="openEdit(classItem)"
                            class="bg-yellow-400 text-white px-4 py-2 rounded-xl border-4 border-yellow-500 font-bold hover:scale-105 transition-transform"
                        >
                            <i class="pi pi-pencil"></i>
                        </button>

                        <button
                            @click="confirmDelete(classItem.id)"
                            class="bg-red-400 text-white px-4 py-2 rounded-xl border-4 border-red-500 font-bold hover:scale-105 transition-transform"
                        >
                            <i class="pi pi-trash"></i>
                        </button>
                    </div>
                </div>
            </TransitionGroup>
        </div>

        <!-- Create / Edit Modal -->
        <Modal
            :show="showDialog"
            :title="isEdit ? 'Ubah Data Kelas' : 'Tambah Kelas Baru'"
            @close="showDialog = false"
        >
            <div class="space-y-4">
                <input
                    v-model="form.name"
                    type="text"
                    placeholder="Nama Kelas (Contoh: 2A)"
                    class="w-full px-4 py-3 rounded-2xl border-4 border-blue-200 focus:border-blue-400 outline-none font-medium"
                />

                <textarea
                    v-model="form.description"
                    rows="3"
                    placeholder="Penjelasan singkat tentang kelas ini"
                    class="w-full px-4 py-3 rounded-2xl border-4 border-blue-200 focus:border-blue-400 outline-none font-medium"
                />
            </div>

            <template #footer>
                <div class="flex justify-end gap-3">
                    <button
                        @click="showDialog = false"
                        class="px-5 py-2 rounded-xl font-bold border-4 border-gray-300 hover:scale-105 transition-transform"
                    >
                        Batal
                    </button>

                    <button
                        @click="saveClass"
                        class="bg-blue-500 text-white px-5 py-2 rounded-xl font-bold border-4 border-blue-600 hover:scale-105 transition-transform"
                    >
                        Simpan
                    </button>
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
<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.modal-enter-active {
    transition: all 0.3s ease;
}
.modal-enter-from {
    opacity: 0;
    transform: scale(0.9) translateY(20px);
}
.modal-leave-active {
    transition: all 0.2s ease;
}
.modal-leave-to {
    opacity: 0;
    transform: scale(0.9) translateY(20px);
}

.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateY(-20px);
}

.toast-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}

.card-enter-active,
.card-leave-active {
    transition: all 0.4s ease;
}

.card-enter-from {
    opacity: 0;
    transform: scale(0.8) translateY(20px);
}

.card-leave-to {
    opacity: 0;
    transform: scale(0.8) translateY(-20px);
}

/* animasi geser posisi */
.card-move {
    transition: transform 0.4s ease;
}
</style>
