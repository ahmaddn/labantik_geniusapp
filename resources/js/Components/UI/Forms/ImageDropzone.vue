<script setup>
import { ref, watch, onUnmounted } from "vue";

const props = defineProps({
    modelValue: {
        type: [File, String, Object, null],
        default: null,
    },
    accept: {
        type: String,
        default: "image/*",
    },
    label: {
        type: String,
        default: "Upload Gambar",
    },
    error: {
        type: String,
        default: "",
    },
    borderColor: {
        type: String,
        default: "blue",
    },
    preview: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(["update:modelValue"]);

const dragActive = ref(false);
const fileInput = ref(null);
const previewUrl = ref(null);
let currentObjectUrl = null;

const colorClasses = {
    blue: "border-blue-200 focus-within:ring-blue-300",
    green: "border-blue-100 focus-within:ring-blue-200",
    yellow: "border-amber-200 focus-within:ring-amber-300",
    red: "border-red-200 focus-within:ring-red-300",
    purple: "border-indigo-200 focus-within:ring-indigo-300",
    gray: "border-gray-200 focus-within:ring-gray-300",
    orange: "border-amber-300 focus-within:ring-amber-400",
};

const revokeCurrentUrl = () => {
    if (currentObjectUrl) {
        URL.revokeObjectURL(currentObjectUrl);
        currentObjectUrl = null;
    }
};

watch(
    () => props.modelValue,
    (val) => {
        revokeCurrentUrl();
        if (typeof val === "string" && val) {
            previewUrl.value = val;
            return;
        }
        if (val instanceof File) {
            currentObjectUrl = URL.createObjectURL(val);
            previewUrl.value = currentObjectUrl;
            return;
        }
        previewUrl.value = null;
    },
    { immediate: true },
);

onUnmounted(() => {
    revokeCurrentUrl();
});

const handleFiles = (files) => {
    if (!files || !files.length) return;
    const file = files[0];
    emit("update:modelValue", file);
};

const onDrop = (e) => {
    e.preventDefault();
    dragActive.value = false;
    handleFiles(e.dataTransfer.files);
};

const onDragOver = (e) => {
    e.preventDefault();
    dragActive.value = true;
};

const onDragLeave = () => {
    dragActive.value = false;
};

const onFileChange = (e) => {
    handleFiles(e.target.files);
};

const triggerChoose = () => {
    fileInput.value?.click();
};

const clear = (e) => {
    if (e) e.stopPropagation();
    emit("update:modelValue", null);
};
</script>

<template>
    <div class="w-full">
        <label v-if="label" class="block text-sm font-bold text-gray-700 mb-2">
            {{ label }}
        </label>

        <div
            @click="triggerChoose"
            @drop="onDrop"
            @dragover="onDragOver"
            @dragleave="onDragLeave"
            class="relative cursor-pointer w-full rounded-2xl border-4 bg-white p-4 flex items-center justify-center transition-all"
            :class="[
                dragActive ? 'ring-4 ring-offset-2 ring-blue-200' : '',
                colorClasses[borderColor] || colorClasses.blue,
            ]"
        >
            <input
                ref="fileInput"
                type="file"
                class="hidden"
                :accept="accept"
                @change="onFileChange"
            />

            <div
                v-if="preview && previewUrl"
                class="flex items-center gap-4 w-full"
            >
                <img
                    :src="previewUrl"
                    class="w-28 h-20 object-cover rounded-lg border"
                />
                <div class="flex-1 text-left">
                    <p class="font-medium text-gray-800 truncate">
                        {{
                            (modelValue && modelValue.name) || "Gambar terpilih"
                        }}
                    </p>
                    <p class="text-sm text-gray-500">
                        Klik untuk mengganti atau seret file ke sini
                    </p>
                </div>
                <button
                    @click.stop="clear"
                    type="button"
                    class="ml-2 text-sm text-red-600 bg-red-50 px-3 py-1 rounded-xl border border-red-100"
                >
                    Hapus
                </button>
            </div>

            <div
                v-else
                class="flex flex-col items-center text-center text-gray-500"
            >
                <svg
                    class="w-12 h-12 mb-2 text-gray-300"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M7 16V4a1 1 0 011-1h8a1 1 0 011 1v12m-5 4l-3-3m0 0l3-3m-3 3h8"
                    ></path>
                </svg>
                <p class="font-medium text-gray-700">
                    Tarik dan lepas gambar di sini
                </p>
                <p class="text-sm text-gray-500">
                    atau klik untuk memilih file
                </p>
            </div>
        </div>

        <p v-if="error" class="mt-2 text-sm text-red-500 font-medium">
            {{ error }}
        </p>
    </div>
</template>
