<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import DataTable from "@/Components/UI/DataTable.vue";
import Modal from "@/Components/UI/Modal.vue";
import ConfirmDialog from "@/Components/UI/ConfirmDialog.vue";
import Toast from "@/Components/UI/Toast.vue";
import InputField from "@/Components/UI/Forms/InputField.vue";
import SelectField from "@/Components/UI/Forms/SelectField.vue";
import Button from "@/Components/UI/Button.vue";
import { ref, watch, onMounted, onUnmounted } from "vue";

const users = ref([
    { id: 1, name: "Ahmad", email: "ahmad@email.com", role: "Admin" },
    { id: 2, name: "Siti", email: "siti@email.com", role: "Guru" },
    { id: 3, name: "Budi", email: "budi@email.com", role: "Siswa" },
]);

// Role options untuk select
const roleOptions = [
    { value: "Admin", label: "Admin" },
    { value: "Guru", label: "Guru" },
    { value: "Siswa", label: "Siswa" },
];

// Konfigurasi kolom untuk DataTable
const columns = [
    { key: "name", label: "Nama", sortable: true },
    { key: "email", label: "Email", sortable: true },
    { key: "role", label: "Peran", sortable: true },
];

// Konfigurasi action buttons untuk DataTable
const actions = [
    {
        name: "edit",
        icon: "pi-pencil",
        class: "bg-yellow-400 border-yellow-500",
    },
    {
        name: "delete",
        icon: "pi-trash",
        class: "bg-red-400 border-red-500",
    },
];

const showDialog = ref(false);
const showDeleteDialog = ref(false);
const isEdit = ref(false);
const selectedUser = ref(null);
const successMessage = ref("");
const showSuccess = ref(false);

const form = ref({
    id: null,
    name: "",
    email: "",
    role: "",
});

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

const showToast = (message) => {
    successMessage.value = message;
    showSuccess.value = true;
    setTimeout(() => (showSuccess.value = false), 2500);
};

const openCreate = () => {
    isEdit.value = false;
    form.value = { id: null, name: "", email: "", role: "" };
    showDialog.value = true;
};

const openEdit = (user) => {
    isEdit.value = true;
    form.value = { ...user };
    showDialog.value = true;
};

const confirmDelete = (user) => {
    selectedUser.value = user;
    showDeleteDialog.value = true;
};

const deleteUser = () => {
    users.value = users.value.filter((u) => u.id !== selectedUser.value.id);
    showDeleteDialog.value = false;
    showToast("Data pengguna berhasil dihapus.");
};

const saveUser = () => {
    if (!form.value.name || !form.value.email) return;

    if (isEdit.value) {
        const index = users.value.findIndex((u) => u.id === form.value.id);
        users.value[index] = { ...form.value };
        showToast("Data pengguna diperbarui.");
    } else {
        users.value.push({
            ...form.value,
            id: Date.now(),
        });
        showToast("Pengguna baru ditambahkan.");
    }

    showDialog.value = false;
};

// Handler untuk action dari DataTable
const handleTableAction = ({ action, data }) => {
    if (action === "edit") {
        openEdit(data);
    } else if (action === "delete") {
        confirmDelete(data);
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
                            <i class="pi pi-users text-blue-600 text-2xl"></i>
                        </div>

                        <div>
                            <h1
                                class="text-2xl md:text-3xl font-heading font-bold text-gray-800"
                            >
                                Data Pengguna
                            </h1>
                            <p class="text-sm text-gray-500">
                                Kelola dan atur data pengguna dengan mudah
                            </p>
                        </div>
                    </div>

                    <!-- Kanan -->
                    <Button
                        variant="primary"
                        size="lg"
                        icon="pi-plus"
                        @click="openCreate"
                    >
                        Tambah Pengguna
                    </Button>
                </div>
            </div>

            <!-- DataTable Component -->
            <DataTable
                :columns="columns"
                :data="users"
                :actions="actions"
                :per-page-options="[5, 10, 15, 20]"
                :initial-per-page="5"
                empty-message="Belum ada data pengguna."
                @action="handleTableAction"
            />
        </div>

        <!-- Create / Edit Modal -->
        <Modal
            :show="showDialog"
            :title="isEdit ? 'Ubah Data Pengguna' : 'Tambah Pengguna Baru'"
            @close="showDialog = false"
        >
            <div class="space-y-4">
                <InputField
                    v-model="form.name"
                    type="text"
                    label="Nama Lengkap"
                    placeholder="Nama Lengkap"
                    icon="pi-user"
                    required
                    border-color="blue"
                />

                <InputField
                    v-model="form.email"
                    type="email"
                    label="Email"
                    placeholder="Email"
                    icon="pi-envelope"
                    required
                    border-color="blue"
                />

                <SelectField
                    v-model="form.role"
                    :options="roleOptions"
                    label="Role"
                    placeholder="Pilih Role"
                    icon="pi-shield"
                    required
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
                        @click="saveUser"
                    >
                        Simpan
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- Delete Confirmation Dialog -->
        <ConfirmDialog
            :show="showDeleteDialog"
            title="Apakah kamu yakin ingin menghapus pengguna ini?"
            message="Data yang sudah dihapus tidak dapat dikembalikan lagi."
            @confirm="deleteUser"
            @cancel="showDeleteDialog = false"
        />

        <!-- Toast Notification -->
        <Toast :show="showSuccess" :message="successMessage" type="success" />
    </AppLayout>
</template>
