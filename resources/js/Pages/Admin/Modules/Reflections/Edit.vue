<script setup>
import { ref, onMounted } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Button from "@/Components/UI/Button.vue";
import Card from "@/Components/UI/Card.vue";
import InputField from "@/Components/UI/Forms/InputField.vue";
import TextareaField from "@/Components/UI/Forms/TextAreaField.vue";
import { ArrowLeft, Plus, Trash2, Image as ImageIcon, Info, GitMerge, MessageSquare } from "lucide-vue-next";

const props = defineProps({
    module: { type: Object, required: true },
    mission: { type: Object, required: true },
    reflection: { type: Object, required: true },
});

const form = useForm({
    _method: 'put',
    title: props.reflection.title,
    mascot_left_text: props.reflection.mascot_left_text,
    mascot_right_text: props.reflection.mascot_right_text,
    flowchart_data: props.reflection.flowchart_data ? [...props.reflection.flowchart_data] : [],
    questions: props.reflection.questions ? [...props.reflection.questions] : [],
});

onMounted(() => {
    // Inject existing_image so controller knows
    form.flowchart_data.forEach(step => {
        if (step.image) {
            step.existing_image = step.image;
        }
    });
});

const addFlowchartStep = () => {
    form.flowchart_data.push({ title: "", fallback_icon: "Circle", image: null, existing_image: null });
};

const removeFlowchartStep = (index) => {
    form.flowchart_data.splice(index, 1);
};

const handleImageUpload = (e, index) => {
    const file = e.target.files[0];
    if (file) {
        form.flowchart_data[index].image = file;
    }
};

const addQuestion = () => {
    form.questions.push({ question_text: "", id: null });
};

const removeQuestion = (index) => {
    form.questions.splice(index, 1);
};

const submit = () => {
    form.post(route("admin.modules.missions.reflections.update", [props.module.id, props.mission.id, props.reflection.id]), {
        forceFormData: true
    });
};

const goBack = () => {
    router.visit(route("admin.modules.missions.show", [props.module.id, props.mission.id]));
};
</script>

<template>
    <AppLayout>
        <div class="p-5 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-6">
                <button @click="goBack" class="bg-white p-3 rounded-2xl border-4 border-gray-200 shadow-sm hover:bg-gray-50 transition-all">
                    <ArrowLeft class="text-gray-500 w-5 h-5" />
                </button>
                <div>
                    <h1 class="text-2xl font-heading font-bold text-gray-800">Edit Refleksi Ilmiah</h1>
                    <p class="text-sm text-gray-500">Misi: {{ mission.name }}</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Info Utama -->
                <Card variant="playful" title="Informasi Utama" subtitle="Informasi dasar untuk refleksi ilmiah" :icon="Info" iconColor="blue" borderColor="blue" :hoverable="false">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div class="md:col-span-2">
                            <InputField 
                                label="Judul Refleksi" 
                                v-model="form.title" 
                                placeholder="Contoh: Sungai dan Manusia dalam Satu Sistem" 
                            />
                            <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</div>
                        </div>
                        <div>
                            <TextareaField 
                                label="Pesan Maskot Kiri" 
                                v-model="form.mascot_left_text" 
                                :rows="3" 
                            />
                            <div v-if="form.errors.mascot_left_text" class="text-red-500 text-sm mt-1">{{ form.errors.mascot_left_text }}</div>
                        </div>
                        <div>
                            <TextareaField 
                                label="Pesan Maskot Kanan" 
                                v-model="form.mascot_right_text" 
                                :rows="3" 
                            />
                            <div v-if="form.errors.mascot_right_text" class="text-red-500 text-sm mt-1">{{ form.errors.mascot_right_text }}</div>
                        </div>
                    </div>
                </Card>

                <!-- Flowchart -->
                <Card variant="playful" title="Diagram Alur (Flowchart)" subtitle="Tentukan urutan langkah di diagram" :icon="GitMerge" iconColor="green" borderColor="green" :hoverable="false">
                    <div class="flex justify-end mb-4">
                        <Button type="button" variant="success" size="sm" :icon="Plus" @click="addFlowchartStep">Tambah Langkah</Button>
                    </div>
                    
                    <div v-for="(step, index) in form.flowchart_data" :key="index" class="mb-4 p-4 border-2 border-gray-100 rounded-xl bg-gray-50 flex flex-col md:flex-row gap-4 items-start md:items-center relative">
                        <button type="button" @click="removeFlowchartStep(index)" class="absolute top-2 right-2 text-red-400 hover:text-red-600">
                            <Trash2 class="w-5 h-5" />
                        </button>
                        <div class="w-full md:w-1/3 pt-2">
                            <InputField 
                                label="Judul Langkah" 
                                v-model="step.title" 
                                placeholder="Contoh: Manusia" 
                                required 
                            />
                        </div>
                        <div class="w-full md:w-1/3 pt-2">
                            <InputField 
                                label="Icon Cadangan (Lucide)" 
                                v-model="step.fallback_icon" 
                                placeholder="Contoh: User, Droplet" 
                            />
                        </div>
                        <div class="w-full md:w-1/3 pt-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Gambar (Opsional)</label>
                            <div class="mt-1 flex items-center">
                                <label class="cursor-pointer bg-white px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                    <ImageIcon class="w-4 h-4" />
                                    <span>Pilih File</span>
                                    <input type="file" class="sr-only" accept="image/*" @change="(e) => handleImageUpload(e, index)">
                                </label>
                                <span class="ml-3 text-sm text-gray-500 overflow-hidden text-ellipsis whitespace-nowrap w-24">
                                    <span v-if="step.image && step.image instanceof File">{{ step.image.name }}</span>
                                    <span v-else-if="step.existing_image">Telah diunggah</span>
                                    <span v-else>Tidak ada</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Questions -->
                <Card variant="playful" title="Pertanyaan Refleksi" subtitle="Daftar pertanyaan yang harus dijawab siswa" :icon="MessageSquare" iconColor="purple" borderColor="purple" :hoverable="false">
                    <div class="flex justify-end mb-4">
                        <Button type="button" variant="purple" size="sm" :icon="Plus" @click="addQuestion">Tambah Pertanyaan</Button>
                    </div>
                    
                    <div v-for="(q, index) in form.questions" :key="index" class="mb-4 p-4 border-2 border-gray-100 rounded-xl bg-gray-50 relative flex gap-4">
                        <div class="font-bold text-lg text-purple-500 pt-8">{{ index + 1 }}.</div>
                        <div class="w-full">
                            <TextareaField 
                                label="Teks Pertanyaan" 
                                v-model="q.question_text" 
                                :rows="2" 
                                required 
                                borderColor="purple"
                            />
                        </div>
                        <button type="button" @click="removeQuestion(index)" class="absolute top-2 right-2 text-red-400 hover:text-red-600">
                            <Trash2 class="w-5 h-5" />
                        </button>
                    </div>
                </Card>

                <div class="flex justify-end gap-3">
                    <Button type="button" variant="ghost" size="lg" @click="goBack">Batal</Button>
                    <Button type="submit" variant="primary" size="lg" :disabled="form.processing">Perbarui Refleksi</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
