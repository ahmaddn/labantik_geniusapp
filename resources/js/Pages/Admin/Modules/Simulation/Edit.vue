<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref } from "vue";
import { router, usePage, useForm } from "@inertiajs/vue3";
import InputField from "@/Components/UI/Forms/InputField.vue";
import TextareaField from "@/Components/UI/Forms/TextAreaField.vue";
import SelectField from "@/Components/UI/Forms/SelectField.vue";
import FileDropzone from "@/Components/UI/Forms/FileDropzone.vue";
import Button from "@/Components/UI/Button.vue";
import Toast from "@/Components/UI/Toast.vue";
import { ArrowLeft, Check, Plus, Trash2, FileText } from "lucide-vue-next";

const props = defineProps({
    module: { type: Object, required: true },
    configs: { type: Object, required: true },
});

const page = usePage();

const successMessage = ref("");
const showSuccess = ref(false);
const toastType = ref("success");

const showToast = (message, type = "success") => {
    successMessage.value = message;
    toastType.value = type;
    showSuccess.value = true;
    setTimeout(() => (showSuccess.value = false), 2500);
};

// -------------------------------------------------------------
// SCENARIO CONFIG
// -------------------------------------------------------------
const scenarioForm = useForm({
    _method: 'put',
    config_type: 'scenario',
    scenarios: props.configs.scenarios || []
});

const addScenario = () => {
    scenarioForm.scenarios.push({
        id: null,
        context: '',
        correct_option: '',
        image: null,
        _preview: null,
        options: []
    });
};

const removeScenario = (index) => {
    scenarioForm.scenarios.splice(index, 1);
};

const addScenarioOption = (sIndex) => {
    scenarioForm.scenarios[sIndex].options.push({
        id: null,
        label: '',
        text: '',
        feedback: ''
    });
};
const removeScenarioOption = (sIndex, oIndex) => {
    scenarioForm.scenarios[sIndex].options.splice(oIndex, 1);
};

const saveScenario = () => {
    if (scenarioForm.scenarios.length === 0) {
        showToast("Tambahkan minimal 1 skenario terlebih dahulu!", "error");
        return;
    }

    for (const scn of scenarioForm.scenarios) {
        if (!scn.context) {
            showToast("Konteks / Cerita Kasus tidak boleh kosong!", "error");
            return;
        }
        if (scn.options.length === 0) {
            showToast("Setiap kasus harus memiliki minimal 1 opsi pilihan!", "error");
            return;
        }
    }

    scenarioForm.post(route('admin.modules.simulation.update', [props.module.id]), {
        preserveScroll: true,
        onSuccess: () => showToast("Konfigurasi Studi Kasus disimpan!"),
        onError: (errs) => {
            console.log(errs);
            showToast("Gagal menyimpan Studi Kasus.", "error")
        }
    });
};
</script>

<template>
    <AppLayout>
        <div class="p-5 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-3xl border-4 border-gray-200 shadow-playful p-6 mb-8 flex items-center gap-4">
                <button
                    @click="router.visit(route('admin.modules.show', [module.id]))"
                    class="bg-blue-100 p-3 rounded-2xl border-2 border-blue-300 hover:bg-blue-200 transition-all"
                >
                    <ArrowLeft class="text-blue-500 w-5 h-5" />
                </button>
                <div>
                    <h1 class="text-2xl md:text-3xl font-heading font-bold text-gray-800">
                        Konfigurasi Studi Kasus (Modul)
                    </h1>
                    <p class="text-sm text-gray-500">Modul: {{ module.name }}</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl border-4 border-gray-200 shadow-playful p-6">
                <!-- SCENARIO TAB -->
                <div>
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h2 class="text-xl font-bold text-gray-800">Studi Kasus</h2>
                        <Button variant="primary" size="sm" :icon="Plus" @click="addScenario">Tambah Skenario</Button>
                    </div>

                    <div class="space-y-6">
                        <div v-for="(scn, idx) in scenarioForm.scenarios" :key="idx" class="p-5 border-2 border-purple-100 bg-purple-50/50 rounded-2xl relative">
                            <button @click="removeScenario(idx)" class="absolute top-4 right-4 text-red-500 hover:text-red-700"><Trash2 class="w-5 h-5"/></button>
                            <h4 class="font-bold text-purple-800 mb-4">Kasus {{ idx + 1 }}</h4>
                            
                            <TextareaField label="Konteks / Cerita Kasus" v-model="scn.context" required placeholder="Misal: Warga membuang limbah ke sungai. Apa yang harus dilakukan?" />
                            
                            <div class="mt-3 mb-4">
                                <FileDropzone 
                                    label="Gambar Skenario Kasus" 
                                    accept="image/*" 
                                    v-model="scn.image"
                                    @update:modelValue="(file) => { if(file) scn._preview = window.URL.createObjectURL(file); else scn._preview = null; }" 
                                />
                                <div v-if="scn._preview || (scn.image && typeof scn.image === 'string')" class="mt-3">
                                    <p class="text-xs text-gray-500 mb-1 font-bold">Preview:</p>
                                    <img :src="scn._preview || `/storage/${scn.image}`" class="w-32 h-32 object-cover rounded-xl border-2 border-gray-200 shadow-sm" />
                                </div>
                            </div>

                            <div class="mt-6 border-t-2 border-purple-200 pt-4">
                                <div class="flex justify-between items-center mb-4">
                                    <h5 class="font-bold text-gray-700">Opsi Pilihan</h5>
                                    <Button variant="secondary" size="sm" @click="addScenarioOption(idx)">Tambah Opsi</Button>
                                </div>
                                <div class="space-y-3">
                                    <div v-for="(opt, oIdx) in scn.options" :key="oIdx" class="flex gap-3 items-start bg-white p-3 rounded-xl border border-gray-200">
                                        <div class="w-16">
                                            <InputField label="Label" v-model="opt.label" placeholder="A, B.." />
                                        </div>
                                        <div class="flex-1">
                                            <InputField label="Teks Pilihan" v-model="opt.text" placeholder="Misal: Melapor ke aparat" />
                                        </div>
                                        <div class="flex-1">
                                            <InputField label="Feedback (Dampak)" v-model="opt.feedback" placeholder="Misal: Langkah tepat! Aparat akan..." />
                                        </div>
                                        <button @click="removeScenarioOption(idx, oIdx)" class="mt-8 text-red-400 hover:text-red-600"><Trash2 class="w-4 h-4"/></button>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <SelectField 
                                        label="Label Opsi yang Benar" 
                                        v-model="scn.correct_option" 
                                        :options="scn.options.filter(o => o.label).map(o => ({ value: o.label, label: `Opsi ${o.label}` }))" 
                                        placeholder="Pilih Opsi yang Benar" 
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <Button variant="primary" :icon="Check" :disabled="scenarioForm.processing" @click="saveScenario">Simpan Skenario</Button>
                    </div>
                </div>

            </div>
        </div>
        <Toast :show="showSuccess" :message="successMessage" :type="toastType" />
    </AppLayout>
</template>
