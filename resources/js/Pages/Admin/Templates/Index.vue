<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch, onMounted, onUnmounted } from "vue";
import { usePage, useForm, router } from "@inertiajs/vue3";
import Modal from "@/Components/UI/Modal.vue";
import ConfirmDialog from "@/Components/UI/ConfirmDialog.vue";
import Toast from "@/Components/UI/Toast.vue";
import InputField from "@/Components/UI/Forms/InputField.vue";
import FileUpload from "@/Components/UI/Forms/FileUpload.vue";
import Button from "@/Components/UI/Button.vue";
import Pagination from "@/Components/UI/Pagination.vue";
import {
    Palette,
    Plus,
    Volume2,
    Images,
    Star,
    Image,
    X,
    Pencil,
    Trash2,
    ChevronRight,
    Loader2,
    Save,
} from "lucide-vue-next";

const props = defineProps({
    templates: Object,
});

const page = usePage();
const showDialog = ref(false);
const showDeleteDialog = ref(false);
const isEdit = ref(false);
const selectedId = ref(null);
const successMessage = ref("");
const showSuccess = ref(false);
const toastType = ref("success");
const editId = ref(null);

const previewBacksound = ref(null);
const previewBackground = ref(null); // single: { name, file, preview }
const previewMascots = ref([]);

const form = useForm({ name: "", backsound: null });

const showToast = (message, type = "success") => {
    successMessage.value = message;
    toastType.value = type;
    showSuccess.value = true;
    setTimeout(() => (showSuccess.value = false), 2500);
};

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) showToast(flash.success, "success");
        if (flash?.error) showToast(flash.error, "error");
    },
);

const lockScroll = (state) => {
    document.body.style.overflow = state ? "hidden" : "";
};
watch(showDialog, lockScroll);
watch(showDeleteDialog, lockScroll);

const handleEsc = (e) => {
    if (e.key === "Escape") {
        showDialog.value = false;
        showDeleteDialog.value = false;
    }
};
onMounted(() => window.addEventListener("keydown", handleEsc));
onUnmounted(() => window.removeEventListener("keydown", handleEsc));

const resetForm = () => {
    form.reset();
    previewBacksound.value = null;
    previewBackground.value = null;
    previewMascots.value = [];
};

const openCreate = () => {
    isEdit.value = false;
    editId.value = null;
    resetForm();
    showDialog.value = true;
};

const openEdit = (template) => {
    isEdit.value = true;
    editId.value = template.id;
    resetForm();
    form.name = template.name;
    showDialog.value = true;
};

const confirmDelete = (id) => {
    selectedId.value = id;
    showDeleteDialog.value = true;
};

const goToDetail = (id) => router.visit(route("admin.templates.show", id));

const handlePageChange = (pageNumber) => {
    router.get(
        route("admin.templates.index"),
        { page: pageNumber },
        { preserveScroll: true, preserveState: true },
    );
};

// ── Backsound ─────────────────────────────────────────────────────────────────
const handleBacksoundUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    previewBacksound.value = { name: file.name };
    form.backsound = file;
};
const removeBacksound = () => {
    previewBacksound.value = null;
    form.backsound = null;
};

// ── Background — single ───────────────────────────────────────────────────────
const handleBackgroundUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    previewBackground.value = {
        name: file.name.replace(/\.[^.]+$/, ""),
        file,
        preview: URL.createObjectURL(file),
    };
    event.target.value = "";
};
const removeBackground = () => {
    previewBackground.value = null;
};

// ── Mascots — multiple satu per satu ─────────────────────────────────────────
const handleMascotUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    previewMascots.value.push({
        id: Date.now() + Math.random(),
        name_pose: file.name.replace(/\.[^.]+$/, ""),
        file,
    });
    event.target.value = "";
};
const removeMascot = (id) => {
    previewMascots.value = previewMascots.value.filter((m) => m.id !== id);
};

// ── Save ──────────────────────────────────────────────────────────────────────
const saveTemplate = () => {
    if (
        !form.name.trim() ||
        !form.backsound ||
        !form.background ||
        form.mascot
    ) {
        showToast("Semua kolom wajib diisi.", "error");
        return;
    }
    const fd = new FormData();
    fd.append("name", form.name);
    if (form.backsound) fd.append("backsound", form.backsound);

    // Background single
    if (previewBackground.value) {
        fd.append("background", previewBackground.value.file);
        fd.append("background_name", previewBackground.value.name);
    }

    previewMascots.value.forEach((m, i) => {
        fd.append(`mascots[${i}]`, m.file);
        fd.append(`mascot_pose[${i}]`, m.name_pose);
    });

    if (isEdit.value) fd.append("_method", "PUT");

    router.post(
        isEdit.value
            ? route("admin.templates.update", editId.value)
            : route("admin.templates.store"),
        fd,
        {
            forceFormData: true,
            onSuccess: () => {
                showDialog.value = false;
                resetForm();
            },
            onError: (errors) => showToast(Object.values(errors)[0], "error"),
        },
    );
};

const deleteTemplate = () => {
    useForm({}).delete(route("admin.templates.destroy", selectedId.value), {
        onSuccess: () => {
            showDeleteDialog.value = false;
        },
        onError: () => showToast("Gagal menghapus template.", "error"),
    });
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
                    <div class="flex items-center gap-4">
                        <div
                            class="bg-blue-100 p-3 rounded-2xl border-2 border-blue-300"
                        >
                            <Palette class="text-blue-600 w-6 h-6" />
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
                    <Button
                        variant="primary"
                        size="lg"
                        :icon="Plus"
                        @click="openCreate"
                    >
                        Tambah Template
                    </Button>
                </div>
            </div>

            <!-- Grid -->
            <TransitionGroup
                v-if="props.templates?.data?.length > 0"
                name="card"
                tag="div"
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"
            >
                <div
                    v-for="template in props.templates.data"
                    :key="template.id"
                    class="bg-white rounded-3xl shadow-playful border-4 border-blue-200 p-6 hover:scale-105 transition-all cursor-pointer group"
                    @click="goToDetail(template.id)"
                >
                    <div class="flex items-start justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-800">
                            {{ template.name }}
                        </h2>
                        <ChevronRight
                            class="w-5 h-5 text-gray-300 group-hover:text-blue-400 transition-colors shrink-0 mt-1"
                        />
                    </div>
                    <div
                        class="mb-3 flex items-center gap-2 text-sm text-gray-600"
                    >
                        <Volume2 class="text-blue-500 w-4 h-4 shrink-0" />
                        <span class="font-medium truncate">
                            {{
                                template.backsound
                                    ? template.backsound.split("/").pop()
                                    : "Tidak ada backsound"
                            }}
                        </span>
                    </div>
                    <div
                        class="mb-3 flex items-center gap-2 text-sm text-gray-600"
                    >
                        <Images class="text-green-500 w-4 h-4 shrink-0" />
                        <span class="font-medium"
                            >{{ template.backgrounds_count }} Background</span
                        >
                    </div>
                    <div
                        class="mb-6 flex items-center gap-2 text-sm text-gray-600"
                    >
                        <Star class="text-yellow-500 w-4 h-4 shrink-0" />
                        <span class="font-medium"
                            >{{ template.mascots_count }} Maskot</span
                        >
                    </div>
                    <div class="flex justify-end gap-2" @click.stop>
                        <Button
                            variant="warning"
                            size="md"
                            :icon="Pencil"
                            @click="openEdit(template)"
                        />
                        <Button
                            variant="danger"
                            size="md"
                            :icon="Trash2"
                            @click="confirmDelete(template.id)"
                        />
                    </div>
                </div>
            </TransitionGroup>

            <!-- Empty State -->
            <div
                v-else
                class="flex flex-col items-center justify-center py-20 text-center"
            >
                <div
                    class="bg-blue-50 p-6 rounded-3xl border-4 border-blue-100 mb-5"
                >
                    <Palette class="w-14 h-14 text-blue-300 mx-auto" />
                </div>
                <h3 class="font-heading font-bold text-gray-600 text-lg mb-1">
                    Belum ada template
                </h3>
                <p class="text-sm text-gray-400 mb-5">
                    Mulai dengan menambahkan template pertama untuk
                    pembelajaran.
                </p>
                <Button
                    variant="primary"
                    size="md"
                    :icon="Plus"
                    @click="openCreate"
                >
                    Tambah Template Pertama
                </Button>
            </div>

            <!-- Pagination -->
            <Pagination
                :meta="{
                    current_page: props.templates.current_page,
                    last_page: props.templates.last_page,
                    per_page: props.templates.per_page,
                    total: props.templates.total,
                    from: props.templates.from,
                    to: props.templates.to,
                }"
                color="blue"
                @change="handlePageChange"
            />
        </div>

        <!-- Modal -->
        <Modal
            :show="showDialog"
            :title="isEdit ? 'Ubah Template' : 'Tambah Template Baru'"
            @close="showDialog = false"
            max-width="3xl"
        >
            <div class="space-y-6">
                <!-- Nama -->
                <InputField
                    v-model="form.name"
                    label="Nama Template"
                    placeholder="Contoh: Template Siang Cerah"
                    required
                    border-color="blue"
                />

                <!-- Backsound -->
                <div>
                    <p class="text-sm font-bold text-gray-700 mb-2">
                        Backsound (Audio)
                    </p>
                    <div
                        v-if="previewBacksound"
                        class="flex items-center justify-between bg-blue-50 p-3 rounded-xl border-2 border-blue-200 mb-2"
                    >
                        <div class="flex items-center gap-2">
                            <Volume2 class="text-blue-500 w-4 h-4" />
                            <span class="text-sm font-medium">{{
                                previewBacksound.name
                            }}</span>
                        </div>
                        <Button
                            variant="danger"
                            size="sm"
                            :icon="X"
                            @click="removeBacksound"
                        />
                    </div>
                    <FileUpload
                        v-else
                        accept="audio/*"
                        button-color="blue"
                        @change="handleBacksoundUpload"
                    />
                </div>

                <!-- Background — single -->
                <div>
                    <p class="text-sm font-bold text-gray-700 mb-2">
                        Background
                        <span class="font-normal text-gray-400"
                            >(1 gambar)</span
                        >
                    </p>

                    <!-- Preview -->
                    <div
                        v-if="previewBackground"
                        class="rounded-2xl overflow-hidden border-2 border-green-200 bg-green-50 mb-2"
                    >
                        <img
                            :src="previewBackground.preview"
                            :alt="previewBackground.name"
                            class="w-full h-40 object-cover"
                        />
                        <div
                            class="flex items-center gap-3 px-3 py-2 border-t-2 border-green-200"
                        >
                            <Image class="text-green-500 w-4 h-4 shrink-0" />
                            <input
                                v-model="previewBackground.name"
                                type="text"
                                placeholder="Nama background..."
                                class="flex-1 text-sm font-medium bg-transparent outline-none border-b-2 border-green-300 focus:border-green-500 pb-0.5 transition-colors"
                            />
                            <Button
                                variant="danger"
                                size="sm"
                                :icon="X"
                                @click="removeBackground"
                            />
                        </div>
                    </div>

                    <FileUpload
                        v-else
                        label="Pilih Background"
                        accept="image/*"
                        :multiple="false"
                        button-color="green"
                        @change="handleBackgroundUpload"
                    />
                </div>

                <!-- Mascots -->
                <div>
                    <p class="text-sm font-bold text-gray-700 mb-2">
                        Maskot
                        <span class="font-normal text-gray-400"
                            >(isi nama pose untuk setiap maskot)</span
                        >
                    </p>
                    <div
                        v-if="previewMascots.length > 0"
                        class="space-y-2 mb-3"
                    >
                        <div
                            v-for="mascot in previewMascots"
                            :key="mascot.id"
                            class="flex items-center gap-3 bg-yellow-50 p-3 rounded-xl border-2 border-yellow-200"
                        >
                            <Star class="text-yellow-500 w-4 h-4 shrink-0" />
                            <input
                                v-model="mascot.name_pose"
                                type="text"
                                placeholder="Nama pose maskot..."
                                class="flex-1 text-sm font-medium bg-transparent outline-none border-b-2 border-yellow-300 focus:border-yellow-500 pb-0.5 transition-colors"
                            />
                            <Button
                                variant="danger"
                                size="sm"
                                :icon="X"
                                @click="removeMascot(mascot.id)"
                            />
                        </div>
                    </div>
                    <FileUpload
                        label="Tambah Maskot"
                        accept="image/*"
                        :multiple="false"
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
                        >Batal</Button
                    >
                    <Button
                        variant="primary"
                        size="md"
                        @click="saveTemplate"
                        :disabled="form.processing"
                    >
                        <span
                            v-if="form.processing"
                            class="flex items-center gap-2"
                        >
                            <Loader2 class="w-4 h-4 animate-spin" />
                            Menyimpan...
                        </span>
                        <span v-else class="flex items-center gap-2">
                            <Save class="w-4 h-4" /> Simpan
                        </span>
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- Delete Dialog -->
        <ConfirmDialog
            :show="showDeleteDialog"
            title="Hapus template ini?"
            message="Semua background, maskot, dan backsound akan ikut terhapus."
            @confirm="deleteTemplate"
            @cancel="showDeleteDialog = false"
        />

        <Toast
            :show="showSuccess"
            :message="successMessage"
            :type="toastType || 'success'"
            @close="showSuccess = false"
        />
    </AppLayout>
</template>
