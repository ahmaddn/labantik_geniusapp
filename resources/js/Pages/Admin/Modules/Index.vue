<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch, computed, onMounted, onUnmounted } from "vue";
import { router, usePage, useForm } from "@inertiajs/vue3";
import Modal from "@/Components/UI/Modal.vue";
import ConfirmDialog from "@/Components/UI/ConfirmDialog.vue";
import Toast from "@/Components/UI/Toast.vue";
import InputField from "@/Components/UI/Forms/InputField.vue";
import TextAreaField from "@/Components/UI/Forms/TextAreaField.vue";
import SelectField from "@/Components/UI/Forms/SelectField.vue";
import Button from "@/Components/UI/Button.vue";
import Card from "@/Components/UI/Card.vue";
import {
    Plus,
    Image,
    X,
    Pencil,
    Trash2,
    GraduationCap,
    Quote,
    LayoutTemplate,
    User,
    Search,
    PackageOpen,
    AlertCircle,
    Loader2,
    Power,
} from "lucide-vue-next";

// ── Props ──
const props = defineProps({
    modules: { type: Array, default: () => [] },
    templates: { type: Array, default: () => [] },
});

const page = usePage();

// ── UI State ──
const showDialog = ref(false);
const showDeleteDialog = ref(false);
const isEdit = ref(false);
const selectedId = ref(null);
const searchQuery = ref("");
const thumbnailFile = ref(null);
const thumbnailPreview = ref(null);
const isDeleting = ref(false);

// ── Toast ──
const toastMessage = ref("");
const toastType = ref("success");
const toastVisible = ref(false);

const triggerToast = (message, type = "success") => {
    toastMessage.value = message;
    toastType.value = type;
    toastVisible.value = true;
    setTimeout(() => (toastVisible.value = false), 2800);
};

// Flash messages
watch(
    () => page.props.flash?.success,
    (val) => {
        if (val) triggerToast(val, "success");
    },
);
watch(
    () => page.props.flash?.error,
    (val) => {
        if (val) triggerToast(val, "error");
    },
);

// ── useForm ──
const form = useForm({
    name: "",
    description: "",
    content: "",
    quotes: "",
    closing_text: "",
    template_id: "",
    thumbnail: null,
    thumbnail_remove: false,
    is_active: false, // ✅ Tambahkan field is_activ
});

// ── Scroll lock ──
const lockScroll = (val) =>
    (document.body.style.overflow = val ? "hidden" : "");
watch(showDialog, lockScroll);
watch(showDeleteDialog, lockScroll);

// ── ESC handler ──
const handleEsc = (e) => {
    if (e.key !== "Escape") return;
    if (showDialog.value) closeDialog();
    if (showDeleteDialog.value) showDeleteDialog.value = false;
};
onMounted(() => window.addEventListener("keydown", handleEsc));
onUnmounted(() => window.removeEventListener("keydown", handleEsc));

// ── Computed: filter modules ──
const filteredModules = computed(() => {
    const q = searchQuery.value.toLowerCase().trim();
    if (!q) return props.modules;
    return props.modules.filter(
        (m) =>
            m.name?.toLowerCase().includes(q) ||
            m.description?.toLowerCase().includes(q),
    );
});

// ── Dialog functions ──
const closeDialog = () => {
    showDialog.value = false;
    form.reset();
    form.clearErrors();
    thumbnailPreview.value = null;
    if (thumbnailFile.value) thumbnailFile.value.value = "";
};

const openCreate = () => {
    isEdit.value = false;
    selectedId.value = null;
    form.reset();
    form.clearErrors();
    thumbnailPreview.value = null;
    showDialog.value = true;
};

const openEdit = (module) => {
    isEdit.value = true;
    selectedId.value = module.id;
    form.reset();
    form.clearErrors();

    form.name = module.name ?? "";
    form.description = module.description ?? "";
    form.content = module.content ?? "";
    form.quotes = module.quotes ?? "";
    form.closing_text = module.closing_text ?? "";
    form.template_id = module.template_id ?? "";
    form.thumbnail = null;
    form.thumbnail_remove = false;
    form.is_active = module.is_active ?? false; // ✅ Tambahkan ini

    thumbnailPreview.value = module.thumbnail ?? null;
    showDialog.value = true;
};

// ── Thumbnail handlers ──
const handleThumbnailUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    form.thumbnail = file;
    form.thumbnail_remove = false;
    thumbnailPreview.value = URL.createObjectURL(file);
};

const removeThumbnail = () => {
    form.thumbnail = null;
    form.thumbnail_remove = true;
    thumbnailPreview.value = null;
    if (thumbnailFile.value) thumbnailFile.value.value = "";
};

// ── Save Module ──
const saveModule = () => {
    const options = {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => closeDialog(),
    };

    if (isEdit.value) {
        form.transform((data) => ({ ...data, _method: "PUT" })).post(
            route("admin.modules.update", selectedId.value),
            options,
        );
    } else {
        form.post(route("admin.modules.store"), options);
    }
};

// ── Delete handlers ──
const confirmDelete = (id) => {
    selectedId.value = id;
    showDeleteDialog.value = true;
};

const deleteModule = () => {
    isDeleting.value = true;
    router.delete(route("admin.modules.destroy", selectedId.value), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteDialog.value = false;
            isDeleting.value = false;
        },
        onError: () => {
            isDeleting.value = false;
            triggerToast("Gagal menghapus modul.", "error");
        },
    });
};

// ── Navigation ──
const goToShowModule = (id) => router.visit(route("admin.modules.show", id));

// ── Helpers ──
const truncate = (str, n = 80) =>
    str && str.length > n ? str.slice(0, n) + "…" : str;
const templateName = (id) =>
    props.templates.find((t) => t.id === id)?.name ?? null;

const toggleActive = (module) => {
    router.patch(
        route("admin.modules.toggle-active", module.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                triggerToast(
                    `Modul ${module.is_active ? "dinonaktifkan" : "diaktifkan"}`,
                    "success",
                );
            },
        },
    );
};
</script>

<template>
    <AppLayout>
        <div class="p-5">
            <!-- ░░ TOOLBAR HEADER ░░ -->
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
                            <GraduationCap class="text-blue-600 w-6 h-6" />
                        </div>
                        <div>
                            <h1
                                class="text-2xl md:text-3xl font-heading font-bold text-gray-800"
                            >
                                Learning Modules
                            </h1>
                            <p class="text-sm text-gray-500">
                                {{ modules.length }} modul tersedia
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 flex-wrap">
                        <div class="relative">
                            <Search
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                            />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari modul..."
                                class="pl-9 pr-4 py-2.5 rounded-xl border-2 border-gray-200 focus:border-blue-400 focus:outline-none text-sm font-medium text-gray-700 w-48 transition-all"
                            />
                        </div>
                        <Button
                            variant="primary"
                            size="lg"
                            :icon="Plus"
                            @click="openCreate"
                        >
                            Tambah Modul
                        </Button>
                    </div>
                </div>
            </div>

            <!-- ░░ GRID MODULES ░░ -->
            <TransitionGroup
                v-if="filteredModules.length > 0"
                name="card"
                tag="div"
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"
            >
                <Card
                    v-for="module in filteredModules"
                    :key="module.id"
                    border-color="blue"
                    class="hover:scale-[1.02] transition-all duration-200 flex flex-col"
                >
                    <!-- Thumbnail - Clickable -->
                    <div
                        @click="goToShowModule(module.id)"
                        class="w-full aspect-video rounded-2xl mb-4 overflow-hidden flex-shrink-0 bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center relative cursor-pointer group"
                    >
                        <img
                            v-if="module.thumbnail"
                            :src="module.thumbnail"
                            :alt="module.name"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                        <GraduationCap
                            v-else
                            class="w-12 h-12 text-blue-300 group-hover:scale-110 transition-transform duration-300"
                        />

                        <!-- Badge Status Active -->
                        <div
                            class="absolute top-2 right-2 px-2.5 py-1 rounded-lg text-xs font-bold shadow-md backdrop-blur-sm"
                            :class="
                                module.is_active
                                    ? 'bg-green-500/90 text-white'
                                    : 'bg-gray-500/90 text-white'
                            "
                        >
                            {{ module.is_active ? "Aktif" : "Nonaktif" }}
                        </div>
                    </div>

                    <!-- Content Area - Clickable -->
                    <div
                        @click="goToShowModule(module.id)"
                        class="flex-1 cursor-pointer"
                    >
                        <!-- Nama -->
                        <h3
                            class="font-heading font-bold text-gray-800 text-base leading-snug mb-2 hover:text-blue-600 transition-colors line-clamp-2"
                        >
                            {{ module.name }}
                        </h3>

                        <!-- Deskripsi -->
                        <p
                            class="text-xs text-gray-500 leading-relaxed mb-4 line-clamp-2 min-h-[2.5rem]"
                        >
                            {{ module.description || "Tidak ada deskripsi." }}
                        </p>

                        <!-- Meta Info -->
                        <div class="space-y-2 mb-4">
                            <div
                                v-if="module.template_id"
                                class="flex items-center gap-2 text-xs text-gray-500"
                            >
                                <LayoutTemplate
                                    class="w-3.5 h-3.5 text-indigo-400 flex-shrink-0"
                                />
                                <span class="font-medium truncate">
                                    {{
                                        templateName(module.template_id) ??
                                        module.template_id
                                    }}
                                </span>
                            </div>
                            <div
                                v-if="module.quotes"
                                class="flex items-start gap-2 text-xs text-gray-500"
                            >
                                <Quote
                                    class="w-3.5 h-3.5 text-pink-400 flex-shrink-0 mt-0.5"
                                />
                                <span class="italic line-clamp-1">{{
                                    module.quotes
                                }}</span>
                            </div>
                            <div
                                v-if="module.createdBy"
                                class="flex items-center gap-2 text-xs text-gray-400"
                            >
                                <User class="w-3.5 h-3.5 flex-shrink-0" />
                                <span class="truncate">{{
                                    module.createdBy.name
                                }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <template #footer>
                        <div
                            class="flex justify-between items-center gap-2 pt-4 border-t-2 border-gray-100"
                            @click.stop
                        >
                            <!-- Toggle Button -->
                            <button
                                @click="toggleActive(module)"
                                class="flex items-center gap-1.5 px-3 py-2 rounded-xl transition-all text-xs font-bold shadow-sm hover:shadow-md"
                                :class="
                                    module.is_active
                                        ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                "
                            >
                                <Power class="w-3.5 h-3.5" />
                                <span class="hidden sm:inline">
                                    {{
                                        module.is_active ? "Aktif" : "Nonaktif"
                                    }}
                                </span>
                            </button>

                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <Button
                                    variant="warning"
                                    size="md"
                                    @click="openEdit(module)"
                                    class="!w-10 !h-10 !p-0 flex items-center justify-center"
                                >
                                    <Pencil class="w-4 h-4" />
                                </Button>
                                <Button
                                    variant="danger"
                                    size="md"
                                    @click="confirmDelete(module.id)"
                                    class="!w-10 !h-10 !p-0 flex items-center justify-center"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                            </div>
                        </div>
                    </template>
                </Card>
            </TransitionGroup>

            <!-- ░░ EMPTY STATE ░░ -->
            <div
                v-else
                class="flex flex-col items-center justify-center py-20 text-center"
            >
                <div
                    class="bg-blue-50 p-6 rounded-3xl border-4 border-blue-100 mb-5"
                >
                    <PackageOpen class="w-14 h-14 text-blue-300 mx-auto" />
                </div>
                <h3 class="font-heading font-bold text-gray-600 text-lg mb-1">
                    {{
                        searchQuery
                            ? "Modul tidak ditemukan"
                            : "Belum ada modul"
                    }}
                </h3>
                <p class="text-sm text-gray-400 mb-5">
                    {{
                        searchQuery
                            ? "Coba kata kunci yang berbeda."
                            : "Mulai dengan menambahkan modul pembelajaran pertama."
                    }}
                </p>
                <Button
                    v-if="!searchQuery"
                    variant="primary"
                    size="md"
                    :icon="Plus"
                    @click="openCreate"
                >
                    Tambah Modul Pertama
                </Button>
            </div>
        </div>

        <!-- ░░ MODAL CREATE / EDIT ░░ -->
        <Modal
            :show="showDialog"
            :title="isEdit ? 'Ubah Modul' : 'Tambah Modul Baru'"
            @close="closeDialog"
            max-width="2xl"
        >
            <div class="space-y-5 max-h-[65vh] overflow-y-auto pr-1">
                <!-- Nama Modul -->
                <InputField
                    v-model="form.name"
                    label="Nama Modul"
                    placeholder="Contoh: Pengenalan Matematika Dasar"
                    required
                    border-color="blue"
                    :error="form.errors.name"
                />

                <!-- Template -->
                <SelectField
                    v-model="form.template_id"
                    label="Template"
                    placeholder="— Tanpa Template —"
                    :icon="LayoutTemplate"
                    :options="templates"
                    option-value="id"
                    option-label="name"
                    border-color="blue"
                    :error="form.errors.template_id"
                    required=""
                />

                <!-- Deskripsi -->
                <TextAreaField
                    v-model="form.description"
                    label="Deskripsi"
                    placeholder="Deskripsi singkat tentang modul ini..."
                    :rows="3"
                    border-color="blue"
                    :error="form.errors.description"
                    required=""
                />

                <!-- Konten -->
                <TextAreaField
                    v-model="form.content"
                    label="Konten"
                    placeholder="Isi materi / konten lengkap modul..."
                    :rows="5"
                    border-color="blue"
                    :error="form.errors.content"
                    required
                />

                <!-- Quotes -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <TextAreaField
                        v-model="form.quotes"
                        label="Kutipan / Quotes"
                        placeholder='"Ilmu adalah cahaya yang menerangi jiwa..." — Penulis'
                        :rows="2"
                        border-color="red"
                        :error="form.errors.quotes"
                    />

                    <TextAreaField
                        v-model="form.closing_text"
                        label="Kata Penutup"
                        placeholder="Isi Kata Penutup"
                        :rows="2"
                        border-color="red"
                        :error="form.errors.closing_text"
                    />
                </div>

                <!-- ✅ Status Aktif -->
                <div
                    class="flex items-center justify-between p-4 bg-blue-50 rounded-2xl border-2 border-blue-200"
                >
                    <div class="flex items-center gap-3">
                        <Power class="w-5 h-5 text-blue-600" />
                        <div>
                            <label
                                class="block text-sm font-bold text-gray-700"
                            >
                                Status Modul
                            </label>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Aktifkan modul agar dapat diakses oleh user
                            </p>
                        </div>
                    </div>

                    <!-- Toggle Switch -->
                    <button
                        type="button"
                        @click="form.is_active = !form.is_active"
                        class="relative inline-flex h-7 w-12 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2"
                        :class="form.is_active ? 'bg-green-500' : 'bg-gray-300'"
                    >
                        <span
                            class="inline-block h-5 w-5 transform rounded-full bg-white transition-transform"
                            :class="
                                form.is_active
                                    ? 'translate-x-6'
                                    : 'translate-x-1'
                            "
                        />
                    </button>
                </div>

                <!-- Thumbnail -->
                <div>
                    <label
                        class="block text-sm font-bold text-gray-700 mb-2 flex items-center gap-2"
                    >
                        <Image class="w-4 h-4 text-blue-400" />
                        Thumbnail (Cover Modul)
                        <span class="text-gray-400 font-normal ml-1"></span>
                    </label>

                    <!-- Preview -->
                    <div
                        v-if="thumbnailPreview"
                        class="relative w-full aspect-video rounded-2xl overflow-hidden border-4 border-blue-200 bg-gray-100 mb-3"
                    >
                        <img
                            :src="thumbnailPreview"
                            alt="Preview"
                            class="w-full h-full object-contain"
                        />

                        <!-- Badge jika file baru -->
                        <span
                            v-if="
                                form.thumbnail &&
                                form.thumbnail.constructor?.name === 'File'
                            "
                            class="absolute bottom-2 left-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-lg shadow"
                        >
                            Gambar Baru
                        </span>

                        <!-- Tombol hapus -->
                        <button
                            type="button"
                            @click="removeThumbnail"
                            class="absolute top-2 right-2 w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-md"
                        >
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Drop zone tanpa FileUpload component -->
                    <div v-if="!thumbnailPreview">
                        <input
                            ref="thumbnailFileInput"
                            type="file"
                            accept="image/*"
                            @change="handleThumbnailUpload"
                            class="hidden"
                        />

                        <label
                            @click="$refs.thumbnailFileInput.click()"
                            class="flex flex-col items-center justify-center w-full h-32 rounded-2xl border-2 border-dashed cursor-pointer transition-colors"
                            :class="
                                form.errors.thumbnail
                                    ? 'border-red-400 bg-red-50 hover:bg-red-100'
                                    : 'border-blue-300 bg-blue-50 hover:bg-blue-100'
                            "
                        >
                            <Image class="w-8 h-8 text-blue-300 mb-2" />
                            <span class="text-sm font-medium text-blue-400"
                                >Klik untuk upload gambar</span
                            >
                            <span class="text-xs text-gray-400 mt-1"
                                >PNG, JPG, WEBP (maks. 2MB)</span
                            >
                        </label>
                    </div>

                    <p
                        v-if="form.errors.thumbnail"
                        class="mt-1.5 text-xs text-red-500 flex items-center gap-1"
                    >
                        <AlertCircle class="w-3.5 h-3.5 flex-shrink-0" />
                        {{ form.errors.thumbnail }}
                    </p>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-3">
                    <Button
                        variant="light"
                        size="md"
                        :disabled="form.processing"
                        @click="closeDialog"
                    >
                        Batal
                    </Button>

                    <Button
                        variant="primary"
                        size="md"
                        :disabled="form.processing"
                        @click="saveModule"
                    >
                        <span
                            v-if="form.processing"
                            class="flex items-center gap-2"
                        >
                            <Loader2 class="w-4 h-4 animate-spin" />
                            Menyimpan...
                        </span>
                        <span v-else>
                            {{ isEdit ? "Simpan Perubahan" : "Tambah Modul" }}
                        </span>
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- ░░ DELETE CONFIRM ░░ -->
        <ConfirmDialog
            :show="showDeleteDialog"
            title="Hapus modul ini?"
            message="Semua konten yang terkait dalam modul ini juga akan ikut terhapus secara permanen."
            @confirm="deleteModule"
            @cancel="showDeleteDialog = false"
        />

        <!-- ░░ TOAST ░░ -->
        <Toast
            :show="toastVisible"
            :message="toastMessage"
            :type="toastType"
            @close="toastVisible = false"
        />
    </AppLayout>
</template>
