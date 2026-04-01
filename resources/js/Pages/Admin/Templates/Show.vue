<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch } from "vue";
import { usePage, useForm, router } from "@inertiajs/vue3";
import ConfirmDialog from "@/Components/UI/ConfirmDialog.vue";
import Toast from "@/Components/UI/Toast.vue";
import Button from "@/Components/UI/Button.vue";
import FileUpload from "@/Components/UI/Forms/FileUpload.vue";
import {
    Palette,
    Volume2,
    Images,
    Star,
    Trash2,
    ArrowLeft,
    Plus,
    Music,
} from "lucide-vue-next";

const props = defineProps({
    template: Object,
});

const page = usePage();
const successMessage = ref("");
const showSuccess = ref(false);
const toastType = ref("success");
const showDeleteBgDialog = ref(false);
const showDeleteMascotDialog = ref(false);
const selectedBgId = ref(null);
const selectedMascotId = ref(null);

// Form untuk tambah background/mascot baru
const bgForm = useForm({ backgrounds: [] });
const mascotForm = useForm({ mascots: [] });
const previewBgs = ref([]);
const previewMascots = ref([]);

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

const goBack = () => router.visit(route("admin.templates.index"));

// Hapus background
const confirmDeleteBg = (id) => {
    selectedBgId.value = id;
    showDeleteBgDialog.value = true;
};

const deleteBackground = () => {
    useForm({}).delete(
        route("admin.templates.backgrounds.destroy", selectedBgId.value),
        {
            onSuccess: () => {
                showDeleteBgDialog.value = false;
            },
            onError: () => showToast("Gagal menghapus background.", "error"),
        },
    );
};

// Hapus mascot
const confirmDeleteMascot = (id) => {
    selectedMascotId.value = id;
    showDeleteMascotDialog.value = true;
};

const deleteMascot = () => {
    useForm({}).delete(
        route("admin.templates.mascots.destroy", selectedMascotId.value),
        {
            onSuccess: () => {
                showDeleteMascotDialog.value = false;
            },
            onError: () => showToast("Gagal menghapus maskot.", "error"),
        },
    );
};

// Upload background baru
const handleBgUpload = (event) => {
    const files = Array.from(event.target.files);
    files.forEach((file) => {
        previewBgs.value.push({
            id: Date.now() + Math.random(),
            name: file.name,
        });
        bgForm.backgrounds.push(file);
    });
};

const uploadBackgrounds = () => {
    bgForm
        .transform((data) => ({ ...data, _method: "PUT" }))
        .post(route("admin.templates.update", props.template.id), {
            forceFormData: true,
            onSuccess: () => {
                previewBgs.value = [];
                bgForm.backgrounds = [];
            },
            onError: (e) => showToast(Object.values(e)[0], "error"),
        });
};

// Upload mascot baru
const handleMascotUpload = (event) => {
    const files = Array.from(event.target.files);
    files.forEach((file) => {
        previewMascots.value.push({
            id: Date.now() + Math.random(),
            name: file.name,
        });
        mascotForm.mascots.push(file);
    });
};

const uploadMascots = () => {
    mascotForm
        .transform((data) => ({ ...data, _method: "PUT" }))
        .post(route("admin.templates.update", props.template.id), {
            forceFormData: true,
            onSuccess: () => {
                previewMascots.value = [];
                mascotForm.mascots = [];
            },
            onError: (e) => showToast(Object.values(e)[0], "error"),
        });
};

// Helper ambil nama file dari path
const filename = (path) => path?.split("/").pop() ?? "-";

// Helper storage URL
const storageUrl = (path) => `/storage/${path}`;
</script>

<template>
    <AppLayout>
        <div class="p-5">
            <!-- Header -->
            <div
                class="bg-white rounded-3xl border-4 border-blue-200 shadow-playful p-5 mb-8"
            >
                <div
                    class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
                >
                    <div class="flex items-center gap-4">
                        <button
                            @click="goBack"
                            class="bg-blue-50 hover:bg-blue-100 p-3 rounded-2xl border-2 border-blue-200 transition-colors"
                        >
                            <ArrowLeft class="text-blue-600 w-5 h-5" />
                        </button>
                        <div
                            class="bg-blue-100 p-3 rounded-2xl border-2 border-blue-300"
                        >
                            <Palette class="text-blue-600 w-6 h-6" />
                        </div>
                        <div>
                            <h1
                                class="text-2xl md:text-3xl font-heading font-bold text-gray-800"
                            >
                                {{ template.name }}
                            </h1>
                            <p class="text-sm text-gray-500">
                                Detail konten template
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Backsound -->
                <div
                    class="bg-white rounded-3xl border-4 border-blue-200 shadow-playful p-6"
                >
                    <div class="flex items-center gap-3 mb-5">
                        <div
                            class="bg-blue-100 p-2 rounded-xl border-2 border-blue-200"
                        >
                            <Volume2 class="text-blue-600 w-5 h-5" />
                        </div>
                        <h2 class="text-lg font-bold text-gray-800">
                            Backsound
                        </h2>
                    </div>

                    <div
                        v-if="template.backsound"
                        class="bg-blue-50 p-4 rounded-2xl border-2 border-blue-200"
                    >
                        <div class="flex items-center gap-2 mb-3">
                            <Music class="text-blue-500 w-4 h-4 shrink-0" />
                            <span
                                class="text-sm font-medium text-gray-700 truncate"
                            >
                                {{ filename(template.backsound) }}
                            </span>
                        </div>
                        <audio controls class="w-full h-8">
                            <source :src="storageUrl(template.backsound)" />
                        </audio>
                    </div>

                    <div v-else class="text-center py-8 text-gray-400">
                        <Volume2 class="w-10 h-10 mx-auto mb-2 opacity-30" />
                        <p class="text-sm font-medium">Belum ada backsound</p>
                    </div>
                </div>

                <!-- Backgrounds -->
                <div
                    class="bg-white rounded-3xl border-4 border-green-200 shadow-playful p-6"
                >
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div
                                class="bg-green-100 p-2 rounded-xl border-2 border-green-200"
                            >
                                <Images class="text-green-600 w-5 h-5" />
                            </div>
                            <h2 class="text-lg font-bold text-gray-800">
                                Background
                                <span class="text-sm font-normal text-gray-400"
                                    >({{ template.backgrounds.length }})</span
                                >
                            </h2>
                        </div>
                    </div>

                    <!-- Grid gambar -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div
                            v-for="bg in template.backgrounds"
                            :key="bg.id"
                            class="relative group rounded-2xl overflow-hidden border-2 border-green-200 aspect-video"
                        >
                            <img
                                :src="storageUrl(bg.image)"
                                :alt="bg.name"
                                class="w-full h-full object-cover"
                            />
                            <!-- Overlay hapus -->
                            <div
                                class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all flex items-center justify-center"
                            >
                                <button
                                    @click="confirmDeleteBg(bg.id)"
                                    class="opacity-0 group-hover:opacity-100 transition-opacity bg-red-500 text-white p-2 rounded-xl border-2 border-red-600"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                            <div
                                class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2"
                            >
                                <p
                                    class="text-white text-xs truncate font-medium"
                                >
                                    {{ bg.name }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Upload background baru -->
                    <div v-if="previewBgs.length > 0" class="mb-3 space-y-1">
                        <p class="text-xs font-bold text-gray-500 mb-2">
                            Akan diupload:
                        </p>
                        <div
                            v-for="bg in previewBgs"
                            :key="bg.id"
                            class="text-xs text-gray-600 bg-green-50 px-3 py-1.5 rounded-lg"
                        >
                            {{ bg.name }}
                        </div>
                        <Button
                            variant="primary"
                            size="sm"
                            class="mt-2 w-full"
                            @click="uploadBackgrounds"
                        >
                            Simpan Background
                        </Button>
                    </div>

                    <FileUpload
                        label="Tambah Background"
                        accept="image/*"
                        :multiple="true"
                        button-color="green"
                        @change="handleBgUpload"
                    />
                </div>

                <!-- Mascots -->
                <div
                    class="bg-white rounded-3xl border-4 border-yellow-200 shadow-playful p-6"
                >
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div
                                class="bg-yellow-100 p-2 rounded-xl border-2 border-yellow-200"
                            >
                                <Star class="text-yellow-600 w-5 h-5" />
                            </div>
                            <h2 class="text-lg font-bold text-gray-800">
                                Maskot
                                <span class="text-sm font-normal text-gray-400"
                                    >({{ template.mascots.length }})</span
                                >
                            </h2>
                        </div>
                    </div>

                    <!-- Grid maskot -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div
                            v-for="mascot in template.mascots"
                            :key="mascot.id"
                            class="relative group rounded-2xl overflow-hidden border-2 border-yellow-200 aspect-square bg-yellow-50"
                        >
                            <img
                                :src="storageUrl(mascot.image)"
                                :alt="mascot.name_pose"
                                class="w-full h-full object-contain p-2"
                            />
                            <!-- Overlay hapus -->
                            <div
                                class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all flex items-center justify-center"
                            >
                                <button
                                    @click="confirmDeleteMascot(mascot.id)"
                                    class="opacity-0 group-hover:opacity-100 transition-opacity bg-red-500 text-white p-2 rounded-xl border-2 border-red-600"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                            <div
                                class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2"
                            >
                                <p
                                    class="text-white text-xs truncate font-medium"
                                >
                                    {{ mascot.name_pose }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Upload maskot baru -->
                    <div
                        v-if="previewMascots.length > 0"
                        class="mb-3 space-y-1"
                    >
                        <p class="text-xs font-bold text-gray-500 mb-2">
                            Akan diupload:
                        </p>
                        <div
                            v-for="m in previewMascots"
                            :key="m.id"
                            class="text-xs text-gray-600 bg-yellow-50 px-3 py-1.5 rounded-lg"
                        >
                            {{ m.name }}
                        </div>
                        <Button
                            variant="warning"
                            size="sm"
                            class="mt-2 w-full"
                            @click="uploadMascots"
                        >
                            Simpan Maskot
                        </Button>
                    </div>

                    <FileUpload
                        label="Tambah Maskot"
                        accept="image/*"
                        :multiple="true"
                        button-color="yellow"
                        @change="handleMascotUpload"
                    />
                </div>
            </div>
        </div>

        <!-- Confirm Delete Background -->
        <ConfirmDialog
            :show="showDeleteBgDialog"
            title="Hapus background ini?"
            message="File gambar akan ikut terhapus dari server."
            @confirm="deleteBackground"
            @cancel="showDeleteBgDialog = false"
        />

        <!-- Confirm Delete Mascot -->
        <ConfirmDialog
            :show="showDeleteMascotDialog"
            title="Hapus maskot ini?"
            message="File gambar akan ikut terhapus dari server."
            @confirm="deleteMascot"
            @cancel="showDeleteMascotDialog = false"
        />

        <!-- Toast -->
        <Toast
            :show="showSuccess"
            :message="successMessage"
            :type="toastType || 'success'"
            @close="showSuccess = false"
        />
    </AppLayout>
</template>
