<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import DataTable from "@/Components/UI/DataTable.vue";
import Modal from "@/Components/UI/Modal.vue";
import ConfirmDialog from "@/Components/UI/ConfirmDialog.vue";
import Toast from "@/Components/UI/Toast.vue";
import InputField from "@/Components/UI/Forms/InputField.vue";
import Button from "@/Components/UI/Button.vue";
import { ref, watch, onMounted, onUnmounted } from "vue";
import {
    Smile,
    Image as ImageIcon,
    Plus,
    Pencil,
    Trash2,
    Save,
    Loader2,
} from "lucide-vue-next";
import { useForm, usePage } from "@inertiajs/vue3";

const toastType = ref("success");
const showDialog = ref(false);
const showDeleteDialog = ref(false);
const isEdit = ref(false);
const successMessage = ref("");
const showSuccess = ref(false);
const selectedId = ref(null);

const props = defineProps({
    mascots: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

// Konfigurasi kolom untuk DataTable
const columns = [
    {
        key: "image_url",
        label: "Gambar",
        sortable: false,
    },
    { key: "name_pose", label: "Nama Pose", sortable: true },
    { key: "created_at", label: "Tanggal Dibuat", sortable: true, formatter: (val) => new Date(val).toLocaleDateString("id-ID") },
];

const actions = [
    {
        name: "edit",
        icon: Pencil,
        class: "bg-yellow-400 border-yellow-500",
    },
    {
        name: "delete",
        icon: Trash2,
        class: "bg-red-400 border-red-500",
    },
];

const editId = ref(null);
const form = useForm({
    id: null,
    name_pose: "",
    image: null,
});

const imagePreview = ref(null);

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
    imagePreview.value = null;
    showDialog.value = true;
};

const openEdit = (mascot) => {
    isEdit.value = true;
    editId.value = mascot.id;
    form.name_pose = mascot.name_pose;
    form.image = null; // Don't bind the file object
    imagePreview.value = mascot.image_url;
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

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const saveMascot = () => {
    if (!form.name_pose.trim()) {
        showToast("Nama pose maskot wajib diisi.", "error");
        return;
    }

    if (!isEdit.value && !form.image) {
        showToast("Gambar maskot wajib diunggah.", "error");
        return;
    }

    const options = {
        onError: (errors) => showToast(getFirstError(errors), "error"),
        onSuccess: () => {
            showDialog.value = false;
            form.reset();
            imagePreview.value = null;
        },
        forceFormData: true,
    };

    if (isEdit.value) {
        // Inertia doesn't support PUT with files natively, so we use POST with _method=PUT
        form.transform((data) => ({
            ...data,
            _method: 'put'
        })).post(route("admin.mascots.update", editId.value), options);
    } else {
        form.post(route("admin.mascots.store"), options);
    }
};

const deleteMascot = () => {
    form.delete(route("admin.mascots.destroy", selectedId.value), {
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
            <div class="bg-white rounded-3xl border-4 border-blue-200 shadow-playful p-5 mb-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <!-- Kiri -->
                    <div class="flex items-center gap-4">
                        <div class="bg-blue-100 p-3 rounded-2xl border-2 border-blue-300">
                            <Smile class="text-blue-600 w-6 h-6" />
                        </div>

                        <div>
                            <h1 class="text-2xl md:text-3xl font-heading font-bold text-gray-800">
                                Data Maskot
                            </h1>
                            <p class="text-sm text-gray-500">
                                Kelola karakter maskot untuk ditampilkan di misi dan kuis
                            </p>
                        </div>
                    </div>

                    <!-- Kanan -->
                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <Button
                            variant="primary"
                            size="lg"
                            :icon="Plus"
                            @click="openCreate"
                            class="flex-1 md:flex-none"
                        >
                            Tambah Maskot
                        </Button>
                    </div>
                </div>
            </div>

            <!-- DataTable Component -->
            <DataTable
                :columns="columns"
                :data="props.mascots"
                :actions="actions"
                :per-page-options="[5, 10, 15, 20]"
                :initial-per-page="10"
                empty-message="Belum ada data maskot."
                @action="handleTableAction"
            >
                <!-- Custom render for image column -->
                <template #cell-image_url="{ item }">
                    <div class="w-16 h-16 rounded-xl border-2 border-gray-200 overflow-hidden bg-gray-50 flex items-center justify-center">
                        <img v-if="item.image_url" :src="item.image_url" alt="Mascot" class="w-full h-full object-contain" />
                        <ImageIcon v-else class="w-6 h-6 text-gray-300" />
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Create / Edit Modal -->
        <Modal
            :show="showDialog"
            :title="isEdit ? 'Ubah Maskot' : 'Tambah Maskot Baru'"
            @close="showDialog = false"
        >
            <div class="space-y-4">
                <InputField
                    v-model="form.name_pose"
                    type="text"
                    label="Nama Pose (Misal: Senyum, Sedih, Berpikir)"
                    placeholder="Nama Pose"
                    :icon="Smile"
                    required
                    border-color="blue"
                />

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Gambar Maskot <span v-if="!isEdit" class="text-red-500">*</span></label>
                    
                    <div class="flex items-center gap-4">
                        <div class="w-24 h-24 rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50 flex flex-col items-center justify-center overflow-hidden relative group">
                            <img v-if="imagePreview" :src="imagePreview" class="w-full h-full object-contain p-2" />
                            <div v-else class="flex flex-col items-center">
                                <ImageIcon class="w-6 h-6 text-gray-400 mb-1" />
                                <span class="text-[10px] text-gray-500 font-medium">Kosong</span>
                            </div>
                            
                            <input 
                                type="file" 
                                accept="image/*"
                                @change="handleImageChange"
                                class="absolute inset-0 opacity-0 cursor-pointer"
                            />
                        </div>
                        <div class="text-sm text-gray-500 flex-1">
                            <p>Format yang didukung: JPG, PNG, WEBP, SVG.</p>
                            <p>Ukuran maksimal 5MB.</p>
                            <p v-if="isEdit" class="text-yellow-600 mt-1 italic text-xs">Kosongkan jika tidak ingin mengubah gambar.</p>
                        </div>
                    </div>
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

                    <Button
                        variant="primary"
                        size="md"
                        :icon="Save"
                        @click="saveMascot"
                        :disabled="form.processing"
                    >
                        <span
                            v-if="form.processing"
                            class="flex items-center gap-2"
                        >
                            <Loader2 class="w-4 h-4 animate-spin" />
                            Menyimpan...
                        </span>
                        <span v-else>
                            <span class="flex items-center gap-2">
                                Simpan
                            </span>
                        </span>
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- Delete Confirmation Dialog -->
        <ConfirmDialog
            :show="showDeleteDialog"
            title="Apakah kamu yakin ingin menghapus maskot ini?"
            message="Data yang sudah dihapus tidak dapat dikembalikan lagi."
            @confirm="deleteMascot"
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
