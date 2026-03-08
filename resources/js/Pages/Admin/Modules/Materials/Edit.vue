<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import InputField from "@/Components/UI/Forms/InputField.vue";
import TextareaField from "@/Components/UI/Forms/TextAreaField.vue";
import SelectField from "@/Components/UI/Forms/SelectField.vue";
import Button from "@/Components/UI/Button.vue";
import Toast from "@/Components/UI/Toast.vue";
import Card from "@/Components/UI/Card.vue";
import {
    FileEdit,
    Star,
    Check,
    AlertTriangle,
    Image as ImageIcon,
    Video as VideoIcon,
    Pencil,
    X,
    Loader2,
} from "lucide-vue-next";

const props = defineProps({
    module: { type: Object, required: true },
    mission: { type: Object, required: true },
    material: { type: Object, required: true },
    mascots: { type: Array, default: () => [] },
});

const successMessage = ref("");
const showSuccess = ref(false);
const toastType = ref("success");
const cardVariant = ref("playful");
const isSubmitting = ref(false);

// Detect existing media type by file extension
const detectMediaType = (path) => {
    if (!path) return "image";
    const ext = path.split(".").pop().toLowerCase();
    return ["mp4", "mov", "avi", "wmv", "webm"].includes(ext)
        ? "video"
        : "image";
};

const mediaType = ref(detectMediaType(props.material.image));

const materialForm = ref({
    title: props.material.title || "",
    description: props.material.description || "",
    content: props.material.content || "",
    mascot_id: props.material.mascot_id || null,
    image: null, // File baru (gambar atau video)
    remove_image: false,
});

// Preview
const mediaPreview = ref(
    props.material.image ? `/storage/${props.material.image}` : null,
);
const hasExistingMedia = ref(!!props.material.image);

const mascotOptions = computed(() => {
    if (!props.mascots || props.mascots.length === 0) return [];
    return props.mascots.map((m) => ({ value: m.id, label: m.name_pose }));
});
const getSelectedMascot = (mascotId) =>
    props.mascots.find((m) => m.id == mascotId) || null;

const showToast = (message, type = "success") => {
    successMessage.value = message;
    toastType.value = type;
    showSuccess.value = true;
    setTimeout(() => (showSuccess.value = false), 2500);
};

// Switch media type — clear data tipe sebelumnya
const switchMediaType = (type) => {
    mediaType.value = type;
    materialForm.value.image = null;
    materialForm.value.remove_image = hasExistingMedia.value;
    mediaPreview.value = null;
    hasExistingMedia.value = false;
};

// Handle file upload (gambar atau video) — keduanya ke field `image`
const handleMediaChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        materialForm.value.image = file;
        materialForm.value.remove_image = false;
        mediaPreview.value = URL.createObjectURL(file);
        hasExistingMedia.value = false;
    }
};
const removeMedia = () => {
    if (hasExistingMedia.value) {
        materialForm.value.remove_image = true;
    }
    materialForm.value.image = null;
    mediaPreview.value = null;
    hasExistingMedia.value = false;
};

const handleSubmit = () => {
    if (!materialForm.value.title.trim()) {
        showToast("Judul material harus diisi!", "warning");
        return;
    }
    if (!materialForm.value.content.trim()) {
        showToast("Konten material harus diisi!", "warning");
        return;
    }

    isSubmitting.value = true;

    const formData = new FormData();
    formData.append("_method", "PUT");
    formData.append("title", materialForm.value.title);
    formData.append("description", materialForm.value.description || "");
    formData.append("content", materialForm.value.content);
    formData.append("mascot_id", materialForm.value.mascot_id || "");
    formData.append(
        "remove_image",
        materialForm.value.remove_image ? "1" : "0",
    );
    if (materialForm.value.image) {
        formData.append("image", materialForm.value.image);
    }

    router.post(
        route("admin.modules.missions.materials.update", [
            props.module.id,
            props.mission.id,
            props.material.id,
        ]),
        formData,
        {
            onSuccess: () => {
                showToast("Material berhasil diperbarui.", "success");
                setTimeout(() => {
                    router.visit(
                        route("admin.modules.missions.show", [
                            props.module.id,
                            props.mission.id,
                        ]),
                    );
                }, 1500);
            },
            onError: (errors) => {
                showToast(
                    "Gagal menyimpan: " + Object.values(errors).join(", "),
                    "error",
                );
                isSubmitting.value = false;
            },
        },
    );
};

const toggleCardVariant = () => {
    cardVariant.value = cardVariant.value === "playful" ? "normal" : "playful";
};
</script>

<template>
    <AppLayout>
        <div class="p-5">
            <!-- ===== HEADER ===== -->
            <div
                :class="[
                    'rounded-3xl p-5 mb-8',
                    cardVariant === 'playful'
                        ? 'bg-white border-4 border-blue-200 shadow-playful'
                        : 'bg-white border border-gray-200 shadow-md',
                ]"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div
                            :class="[
                                cardVariant === 'playful'
                                    ? 'bg-blue-100 p-3 rounded-2xl border-2 border-blue-300'
                                    : 'bg-blue-50 p-2 rounded-lg',
                            ]"
                        >
                            <Pencil class="text-blue-600 w-6 h-6" />
                        </div>
                        <div>
                            <h1
                                class="text-2xl md:text-3xl font-heading font-bold text-gray-800"
                            >
                                Edit Material: {{ material.title }}
                            </h1>
                            <p class="text-sm text-gray-500">
                                Modul: {{ module.name }} | Misi:
                                {{ mission.name }}
                            </p>
                        </div>
                    </div>
                    <Button
                        :variant="
                            cardVariant === 'playful' ? 'secondary' : 'light'
                        "
                        size="md"
                        :icon="Star"
                        @click="toggleCardVariant"
                    >
                        {{ cardVariant === "playful" ? "Playful" : "Normal" }}
                    </Button>
                </div>
            </div>

            <!-- ===== FORM ===== -->
            <Card
                :variant="cardVariant"
                title="Edit Material"
                subtitle="Perbarui informasi material pembelajaran"
                :icon="FileEdit"
                icon-color="blue"
                border-color="blue"
                :hoverable="false"
            >
                <div class="space-y-5">
                    <InputField
                        label="Judul Material"
                        v-model="materialForm.title"
                        placeholder="Contoh: Pengenalan Fotosintesis"
                        required
                    />
                    <TextareaField
                        label="Deskripsi Singkat"
                        v-model="materialForm.description"
                        placeholder="Deskripsi singkat tentang material ini..."
                        :rows="3"
                    />
                    <TextareaField
                        label="Konten Material"
                        v-model="materialForm.content"
                        placeholder="Tulis konten pembelajaran di sini..."
                        :rows="10"
                        required
                    />

                    <!-- ===== MEDIA UPLOAD ===== -->
                    <div>
                        <label
                            class="block text-sm font-bold text-gray-700 mb-3"
                            >Media Pembelajaran</label
                        >
                        <!-- Tab Toggle Gambar / Video -->
                        <div class="flex gap-2 mb-4">
                            <button
                                type="button"
                                @click="switchMediaType('image')"
                                :class="[
                                    'flex items-center gap-2 px-4 py-2 rounded-xl border-4 font-bold text-sm transition-all',
                                    mediaType === 'image'
                                        ? 'bg-blue-500 text-white border-blue-600'
                                        : 'bg-white text-gray-600 border-gray-200 hover:border-blue-200 hover:bg-blue-50',
                                ]"
                            >
                                <ImageIcon class="w-4 h-4" />Gambar
                            </button>
                            <button
                                type="button"
                                @click="switchMediaType('video')"
                                :class="[
                                    'flex items-center gap-2 px-4 py-2 rounded-xl border-4 font-bold text-sm transition-all',
                                    mediaType === 'video'
                                        ? 'bg-blue-500 text-white border-blue-600'
                                        : 'bg-white text-gray-600 border-gray-200 hover:border-blue-200 hover:bg-blue-50',
                                ]"
                            >
                                <VideoIcon class="w-4 h-4" />Video
                            </button>
                        </div>

                        <!-- Upload Gambar -->
                        <div v-if="mediaType === 'image'" class="space-y-3">
                            <div
                                v-if="mediaPreview"
                                class="relative inline-block"
                            >
                                <img
                                    :src="mediaPreview"
                                    alt="Preview"
                                    class="h-40 w-auto object-cover rounded-xl border-4 border-blue-200"
                                />
                                <button
                                    type="button"
                                    @click="removeMedia"
                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                >
                                    <X class="w-4 h-4" />
                                </button>
                                <span
                                    v-if="hasExistingMedia"
                                    class="absolute bottom-1 left-1 text-xs bg-black/50 text-white px-2 py-0.5 rounded"
                                    >Gambar saat ini</span
                                >
                            </div>
                            <label
                                class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-100 border-4 border-blue-200 font-bold text-sm"
                            >
                                <ImageIcon class="w-5 h-5" />{{
                                    mediaPreview
                                        ? "Ganti Gambar"
                                        : "Pilih Gambar"
                                }}
                                <input
                                    type="file"
                                    @change="handleMediaChange"
                                    accept="image/*"
                                    class="hidden"
                                />
                            </label>
                            <p class="text-xs text-gray-400">
                                Format: JPG, PNG, GIF. Maks 2MB.
                            </p>
                        </div>

                        <!-- Upload Video -->
                        <div v-if="mediaType === 'video'" class="space-y-3">
                            <div v-if="mediaPreview" class="relative">
                                <video
                                    :src="mediaPreview"
                                    controls
                                    class="w-full max-h-52 rounded-xl border-4 border-blue-200 bg-black"
                                />
                                <button
                                    type="button"
                                    @click="removeMedia"
                                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                >
                                    <X class="w-4 h-4" />
                                </button>
                                <span
                                    v-if="hasExistingMedia"
                                    class="absolute bottom-2 left-2 text-xs bg-black/50 text-white px-2 py-0.5 rounded"
                                    >Video saat ini</span
                                >
                            </div>
                            <label
                                class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-100 border-4 border-blue-200 font-bold text-sm"
                            >
                                <VideoIcon class="w-5 h-5" />{{
                                    mediaPreview ? "Ganti Video" : "Pilih Video"
                                }}
                                <input
                                    type="file"
                                    @change="handleMediaChange"
                                    accept="video/*"
                                    class="hidden"
                                />
                            </label>
                            <p class="text-xs text-gray-400">
                                Format: MP4, MOV, AVI, WebM. Maks 50MB.
                            </p>
                        </div>
                    </div>

                    <!-- Mascot Selection -->
                    <div v-if="mascotOptions.length > 0">
                        <SelectField
                            label="Pilih Maskot"
                            v-model="materialForm.mascot_id"
                            :options="mascotOptions"
                            placeholder="Pilih maskot untuk material ini"
                        />
                        <div
                            v-if="materialForm.mascot_id"
                            class="mt-3 bg-blue-50 p-4 rounded-xl border-2 border-blue-200"
                        >
                            <div class="flex items-center gap-3">
                                <img
                                    :src="`/storage/${getSelectedMascot(materialForm.mascot_id)?.image}`"
                                    :alt="
                                        getSelectedMascot(
                                            materialForm.mascot_id,
                                        )?.name
                                    "
                                    class="w-16 h-16 object-contain rounded-lg"
                                />
                                <div class="flex-1">
                                    <h4 class="font-bold text-blue-800">
                                        {{
                                            getSelectedMascot(
                                                materialForm.mascot_id,
                                            )?.name
                                        }}
                                    </h4>
                                    <p class="text-sm text-blue-600">
                                        Maskot ini akan muncul di material
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    @click="materialForm.mascot_id = null"
                                    class="ml-auto p-1.5 rounded-full text-blue-600 hover:bg-blue-200 hover:text-blue-800 transition"
                                    title="Batalkan pilihan maskot"
                                >
                                    <X class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="bg-red-50 p-4 rounded-xl border-2 border-red-200"
                    >
                        <p class="text-sm text-red-700 flex items-center gap-2">
                            <AlertTriangle class="w-4 h-4" /> Template modul ini
                            belum memiliki maskot.
                        </p>
                    </div>
                </div>

                <template #footer>
                    <div class="flex justify-between mt-6">
                        <Button
                            variant="light"
                            size="md"
                            @click="
                                router.visit(
                                    route('admin.modules.missions.show', [
                                        module.id,
                                        mission.id,
                                    ]),
                                )
                            "
                            >Batal</Button
                        >
                        <Button
                            variant="primary"
                            size="md"
                            :icon="isSubmitting ? Loader2 : Check"
                            @click="handleSubmit"
                            :disabled="isSubmitting"
                        >
                            {{
                                isSubmitting
                                    ? "Menyimpan..."
                                    : "Simpan Perubahan"
                            }}
                        </Button>
                    </div>
                </template>
            </Card>
        </div>
        <Toast
            :show="showSuccess"
            :message="successMessage"
            :type="toastType"
        />
    </AppLayout>
</template>
