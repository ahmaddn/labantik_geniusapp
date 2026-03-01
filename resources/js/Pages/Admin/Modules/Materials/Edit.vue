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
    Pencil,
} from "lucide-vue-next";

// Props — sesuai controller edit()
const props = defineProps({
    module: { type: Object, required: true }, // { id, name }
    mission: { type: Object, required: true }, // { id, name }
    material: { type: Object, required: true }, // material existing dengan relasi mascot
    mascots: { type: Array, default: () => [] },
});

const successMessage = ref("");
const showSuccess = ref(false);
const cardVariant = ref("playful");

// Form — pre-populate dari material existing
// CATATAN MIGRATION: TIDAK ada kolom thumbnail, hanya image
const materialForm = ref({
    title: props.material.title || "",
    description: props.material.description || "",
    content: props.material.content || "",
    mascot_id: props.material.mascot_id || null,
    image: null, // File baru (null = tidak ganti)
    remove_image: false, // Flag untuk hapus gambar existing
});

// Preview gambar existing atau baru
const imagePreview = ref(
    props.material.image ? `/storage/${props.material.image}` : null,
);
const hasExistingImage = ref(!!props.material.image);

// Mascot options
const mascotOptions = computed(() => {
    if (!props.mascots || props.mascots.length === 0) return [];
    return props.mascots.map((m) => ({ value: m.id, label: m.name }));
});
const getSelectedMascot = (mascotId) =>
    props.mascots.find((m) => m.id == mascotId) || null;

const showToast = (message) => {
    successMessage.value = message;
    showSuccess.value = true;
    setTimeout(() => {
        showSuccess.value = false;
    }, 2500);
};

// Handle image upload baru
const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        materialForm.value.image = file;
        materialForm.value.remove_image = false;
        imagePreview.value = URL.createObjectURL(file);
        hasExistingImage.value = false; // sekarang pakai file baru
    }
};

// Hapus gambar (existing atau preview baru)
const removeImage = () => {
    if (hasExistingImage.value) {
        // Gambar existing → tandai remove_image = true untuk dikirim ke controller
        materialForm.value.remove_image = true;
    }
    materialForm.value.image = null;
    imagePreview.value = null;
    hasExistingImage.value = false;
};

// Submit update — sesuai controller update() validation
const handleSubmit = () => {
    if (!materialForm.value.title.trim()) {
        showToast("Judul material harus diisi!");
        return;
    }
    if (!materialForm.value.content.trim()) {
        showToast("Konten material harus diisi!");
        return;
    }

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
                showToast("Material berhasil diperbarui.");
                setTimeout(() => {
                    router.visit(
                        route("admin.modules.missions.show", [
                            props.module.id,
                            props.mission.id,
                        ]),
                    );
                }, 1500);
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
                        ? 'bg-white border-4 border-green-200 shadow-playful'
                        : 'bg-white border border-gray-200 shadow-md',
                ]"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div
                            :class="[
                                cardVariant === 'playful'
                                    ? 'bg-green-100 p-3 rounded-2xl border-2 border-green-300'
                                    : 'bg-green-50 p-2 rounded-lg',
                            ]"
                        >
                            <Pencil class="text-green-600 w-6 h-6" />
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
                            cardVariant === 'playful' ? 'warning' : 'light'
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
            <!-- Edit material tidak butuh wizard karena hanya 1 item yang diedit -->
            <Card
                :variant="cardVariant"
                title="Edit Material"
                subtitle="Perbarui informasi material pembelajaran"
                :icon="FileEdit"
                icon-color="green"
                border-color="green"
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
                        rows="3"
                    />
                    <TextareaField
                        label="Konten Material"
                        v-model="materialForm.content"
                        placeholder="Tulis konten pembelajaran di sini..."
                        rows="10"
                        required
                    />

                    <!-- Image Upload — sesuai migration hanya kolom 'image' (tidak ada thumbnail) -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Gambar (Opsional)</label
                        >
                        <div class="space-y-3">
                            <!-- Preview gambar existing atau baru -->
                            <div
                                v-if="imagePreview"
                                class="relative inline-block"
                            >
                                <img
                                    :src="imagePreview"
                                    alt="Preview"
                                    class="h-32 w-auto object-cover rounded-lg border-2 border-gray-300"
                                />
                                <button
                                    type="button"
                                    @click="removeImage"
                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                >
                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                                <span
                                    v-if="hasExistingImage"
                                    class="absolute bottom-1 left-1 text-xs bg-black/50 text-white px-1 rounded"
                                    >Gambar saat ini</span
                                >
                            </div>
                            <label
                                class="cursor-pointer inline-flex items-center px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 border-2 border-blue-200"
                            >
                                <ImageIcon class="w-5 h-5 mr-2" />
                                {{
                                    imagePreview
                                        ? "Ganti Gambar"
                                        : "Pilih Gambar"
                                }}
                                <input
                                    type="file"
                                    @change="handleImageChange"
                                    accept="image/*"
                                    class="hidden"
                                />
                            </label>
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
                            class="mt-3 bg-yellow-50 p-4 rounded-xl border-2 border-yellow-200"
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
                                <div>
                                    <h4 class="font-bold text-yellow-800">
                                        {{
                                            getSelectedMascot(
                                                materialForm.mascot_id,
                                            )?.name
                                        }}
                                    </h4>
                                    <p class="text-sm text-yellow-600">
                                        Maskot ini akan muncul di material
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="bg-red-50 p-4 rounded-xl border-2 border-red-200"
                    >
                        <p class="text-sm text-red-700 flex items-center gap-2">
                            <AlertTriangle class="w-4 h-4" />
                            Template modul ini belum memiliki maskot.
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
                        >
                            Batal
                        </Button>
                        <Button
                            variant="success"
                            size="md"
                            :icon="Check"
                            @click="handleSubmit"
                        >
                            Simpan Perubahan
                        </Button>
                    </div>
                </template>
            </Card>
        </div>
        <Toast :show="showSuccess" :message="successMessage" type="success" />
    </AppLayout>
</template>
