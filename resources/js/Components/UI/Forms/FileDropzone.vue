<script setup>
import { ref, onUnmounted } from "vue";
import { UploadCloud } from "lucide-vue-next";

const props = defineProps({
    modelValue: { type: [File, String, Object, null], default: null },
    previewUrl: { type: String, default: null },
    accept: { type: String, default: "*/*" },
    label: { type: String, default: "Pilih file" },
    error: { type: String, default: "" },
    borderColor: { type: String, default: "blue" },
    allowClear: { type: Boolean, default: true },
});

const emit = defineEmits(["update:modelValue"]);

const dragActive = ref(false);
const fileInput = ref(null);

const colorClasses = {
    blue: "border-blue-200 focus-within:ring-blue-300",
    green: "border-blue-100 focus-within:ring-blue-200",
    yellow: "border-amber-200 focus-within:ring-amber-300",
    red: "border-red-200 focus-within:ring-red-300",
    purple: "border-indigo-200 focus-within:ring-indigo-300",
    gray: "border-gray-200 focus-within:ring-gray-300",
    orange: "border-amber-300 focus-within:ring-amber-400",
};

const triggerChoose = () => fileInput.value?.click();

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

const onFileChange = (e) => handleFiles(e.target.files);

const clear = (e) => {
    if (e) e.stopPropagation();
    if (!props.allowClear) return;
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
            class="relative cursor-pointer w-full rounded-2xl border-4 bg-white p-6 flex items-center justify-center transition-all"
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

            <div v-if="previewUrl" class="w-full h-40 flex items-center justify-center p-2">
                <img :src="previewUrl" class="max-w-full max-h-full object-contain rounded-xl shadow-sm" />
            </div>
            <div v-else class="flex flex-col items-center text-center text-gray-500">
                <UploadCloud class="w-12 h-12 mb-3 text-blue-500" />
                <p class="font-bold text-gray-800 truncate max-w-xs mb-1">
                    {{
                        (modelValue && modelValue.name) ||
                        "Klik atau seret file ke sini"
                    }}
                </p>
                <p class="text-xs text-gray-400 font-medium">
                    {{ accept === '*/*' ? 'Semua tipe file diterima' : 'Format: ' + accept }}
                </p>
            </div>

            <button
                v-if="props.allowClear && (modelValue || previewUrl)"
                @click.stop="clear"
                type="button"
                class="absolute top-3 right-3 text-sm text-red-600 bg-red-50 px-3 py-1 rounded-xl border border-red-100"
            >
                Hapus
            </button>
        </div>

        <p v-if="error" class="mt-2 text-sm text-red-500 font-medium">
            {{ error }}
        </p>
    </div>
</template>
