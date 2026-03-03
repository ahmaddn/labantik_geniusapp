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
import {
    Users,
    User,
    Mail,
    Shield,
    Plus,
    Pencil,
    Trash2,
    Save,
    BookOpen,
    LockIcon,
} from "lucide-vue-next";
import { useForm, usePage } from "@inertiajs/vue3";

const toastType = ref("success"); // ← ini sudah benar
const showDialog = ref(false);
const showDeleteDialog = ref(false);
const isEdit = ref(false);
const selectedUser = ref(null);
const successMessage = ref("");
const showSuccess = ref(false);
const selectedId = ref(null);

const props = defineProps({
    users: {
        type: Array,
    },
});

const page = usePage();
const currentRole = page.props.auth?.user?.role || null;
const isGuru = currentRole === "guru";

// Role options untuk select
const allRoleOptions = [
    { value: "admin", label: "Admin" },
    { value: "guru", label: "Guru" },
    { value: "siswa", label: "Siswa" },
];
const roleOptions = isGuru
    ? allRoleOptions.filter((r) => r.value === "siswa")
    : allRoleOptions;

// Konfigurasi kolom untuk DataTable
const columns = [
    { key: "name", label: "Nama", sortable: true },
    { key: "email", label: "Email", sortable: true },
    { key: "role", label: "Peran", sortable: true },
    {
        key: "class",
        label: "Kelas",
        sortable: false,
        formatter: (value) => value?.name || "-",
    },
    {
        key: "created_by",
        label: "Dibuat Oleh",
        sortable: false,
        formatter: (value) => value?.name || "-",
    },
];

// Konfigurasi action buttons untuk DataTable
const actions = [
    {
        name: "edit",
        icon: Pencil,
        class: "bg-yellow-400 border-yellow-500",
        // show only if not guru OR the row is a student
        showIf: (row) => !isGuru || row.role === "student",
    },
    {
        name: "delete",
        icon: Trash2,
        class: "bg-red-400 border-red-500",
        showIf: () => !isGuru,
    },
];

const editId = ref(null);
const form = useForm({
    id: null,
    name: "",
    email: "",
    role: "",
    password: "",
    class_id: "",
});

const lockScroll = (state) => {
    document.body.style.overflow = state ? "hidden" : "";
};

const showToast = (message, type = "success") => {
    successMessage.value = message;
    toastType.value = type;
    showSuccess.value = true;
    setTimeout(() => (showSuccess.value = false), 2500);
};

const openCreate = () => {
    isEdit.value = false;
    form.reset();
    // If current user is guru, force role to siswa
    if (isGuru) form.role = "siswa";
    showDialog.value = true;
};

const openEdit = (userItem) => {
    // Prevent teachers from editing admin/guru accounts
    if (isGuru && userItem.role !== "siswa") {
        showToast("Anda tidak diizinkan mengubah akun non-siswa.", "error");
        return;
    }

    isEdit.value = true;
    editId.value = userItem.id;
    form.name = userItem.name;
    form.email = userItem.email;
    form.role = userItem.role;
    form.class_id = userItem.class_id || "";
    showDialog.value = true;
};

const confirmDelete = (id) => {
    selectedId.value = id;
    showDeleteDialog.value = true;
};

// Helper untuk ambil pesan error pertama dari object errors
const getFirstError = (errors) => {
    return Object.values(errors)[0] ?? "Terjadi kesalahan.";
};

const saveUser = () => {
    if (!form.name.trim() || !form.email.trim() || !form.role) {
        showToast("Semua kolom wajib diisi.", "error");
        return;
    }

    const options = {
        onError: (errors) => showToast(getFirstError(errors), "error"),
        onSuccess: () => {
            showDialog.value = false;
            form.reset();
        },
    };

    if (isEdit.value) {
        form.put(route("admin.users.update", editId.value), options);
    } else {
        form.post(route("admin.users.store"), options);
    }
};

const deleteUser = () => {
    form.delete(route("admin.users.destroy", selectedId.value), {
        onError: (errors) => showToast(getFirstError(errors), "error"),
        onSuccess: () => {
            showDeleteDialog.value = false;
        },
    });
};

// Handler untuk action dari DataTable
const handleTableAction = ({ action, data }) => {
    if (action === "edit") {
        openEdit(data);
    } else if (action === "delete") {
        // Prevent teachers from deleting non-student accounts
        if (isGuru && data.role !== "siswa") {
            showToast(
                "Anda tidak diizinkan menghapus akun non-siswa.",
                "error",
            );
            return;
        }
        confirmDelete(data.id || data);
    }
};

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) showToast(flash.success, "success");
        if (flash?.error) showToast(flash.error, "error");
        if (flash?.message) showToast(flash.message, "info");
    },
);
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
                            <Users class="text-blue-600 w-6 h-6" />
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
                        :icon="Plus"
                        @click="openCreate"
                    >
                        Tambah Pengguna
                    </Button>
                </div>
            </div>

            <!-- DataTable Component -->
            <DataTable
                :columns="columns"
                :data="props.users"
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
                    :icon="User"
                    required
                    border-color="blue"
                />

                <InputField
                    v-model="form.email"
                    type="email"
                    label="Email"
                    placeholder="Email"
                    :icon="Mail"
                    required
                    border-color="blue"
                />

                <InputField
                    v-if="!isEdit"
                    v-model="form.password"
                    type="password"
                    label="Password"
                    placeholder="Password"
                    :icon="LockIcon"
                    required
                    border-color="blue"
                />

                <SelectField
                    v-model="form.role"
                    :options="roleOptions"
                    label="Role"
                    placeholder="Pilih Role"
                    :icon="Shield"
                    required
                    :disabled="isGuru"
                    border-color="blue"
                />

                <SelectField
                    v-if="form.role === 'siswa'"
                    v-model="form.class_id"
                    :options="
                        page.props.classes.map((cls) => ({
                            value: cls.id,
                            label: cls.name,
                        }))
                    "
                    label="Kelas"
                    placeholder="Pilih Kelas"
                    :icon="BookOpen"
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
                        :icon="Save"
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
        <Toast
            :show="showSuccess"
            :message="successMessage"
            :type="toastType"
            @close="showSuccess = false"
        />
    </AppLayout>
</template>
