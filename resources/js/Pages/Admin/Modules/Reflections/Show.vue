<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Button from "@/Components/UI/Button.vue";
import Card from "@/Components/UI/Card.vue";
import { ArrowLeft, Pencil, BookOpen } from "lucide-vue-next";
import * as LucideIcons from "lucide-vue-next";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    module: { type: Object, required: true },
    mission: { type: Object, required: true },
    reflection: { type: Object, required: true },
});

const goBack = () => {
    router.visit(route("admin.modules.missions.show", [props.module.id, props.mission.id]));
};

const goToEdit = () => {
    router.visit(route("admin.modules.missions.reflections.edit", [props.module.id, props.mission.id, props.reflection.id]));
};
</script>

<template>
    <AppLayout>
        <div class="p-5 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <button @click="goBack" class="bg-white p-3 rounded-2xl border-4 border-gray-200 shadow-sm hover:bg-gray-50 transition-all">
                        <ArrowLeft class="text-gray-500 w-5 h-5" />
                    </button>
                    <div>
                        <h1 class="text-2xl font-heading font-bold text-gray-800">Detail Refleksi</h1>
                        <p class="text-sm text-gray-500">Misi: {{ mission.name }}</p>
                    </div>
                </div>
                <Button variant="warning" :icon="Pencil" @click="goToEdit">Edit Refleksi</Button>
            </div>

            <!-- Content -->
            <Card variant="playful" :title="reflection.title" subtitle="Detail Refleksi Ilmiah" :icon="BookOpen" iconColor="blue" borderColor="blue" :hoverable="false" class="space-y-8">
                <!-- Title & Text -->
                <div class="text-center mt-6">
                    <div class="flex flex-col md:flex-row justify-center gap-4 md:gap-8 mb-8">
                        <div class="bg-blue-50 border-2 border-blue-200 p-4 rounded-xl max-w-sm flex-1">
                            <p class="text-sm text-blue-800 italic">"{{ reflection.mascot_left_text }}"</p>
                        </div>
                        <div class="bg-blue-50 border-2 border-blue-200 p-4 rounded-xl max-w-sm flex-1">
                            <p class="text-sm text-blue-800 italic">"{{ reflection.mascot_right_text }}"</p>
                        </div>
                    </div>
                </div>

                <!-- Flowchart -->
                <div v-if="reflection.flowchart_data && reflection.flowchart_data.length > 0" class="flex flex-wrap justify-center items-center gap-4 py-8 bg-gray-50 rounded-2xl border-2 border-gray-100">
                    <div v-for="(step, index) in reflection.flowchart_data" :key="index" class="flex items-center">
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-20 h-20 bg-white rounded-full border-4 border-blue-200 flex items-center justify-center overflow-hidden shadow-sm">
                                <img v-if="step.image" :src="'/storage/' + step.image" alt="Step icon" class="w-full h-full object-cover">
                                <component v-else-if="LucideIcons[step.fallback_icon]" :is="LucideIcons[step.fallback_icon]" class="w-10 h-10 text-blue-400" />
                                <span v-else class="text-gray-400 font-medium text-xs text-center px-1">{{ step.fallback_icon || 'Ikon' }}</span>
                            </div>
                            <span class="font-bold text-gray-700">{{ step.title }}</span>
                        </div>
                        <div v-if="index < reflection.flowchart_data.length - 1" class="w-12 h-1 bg-blue-300 mx-4 relative">
                            <div class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-1/2 w-3 h-3 bg-blue-400 transform rotate-45"></div>
                        </div>
                    </div>
                </div>

                <!-- Questions -->
                <div v-if="reflection.questions && reflection.questions.length > 0">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 text-center">Refleksi Ilmiah</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div v-for="(q, index) in reflection.questions" :key="index" class="bg-white border-2 border-gray-200 p-4 rounded-2xl shadow-sm relative pt-8">
                            <div class="absolute -top-3 left-4 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold border-2 border-white shadow-sm">
                                {{ index + 1 }}
                            </div>
                            <p class="font-medium text-gray-700 text-center">{{ q.question_text }}</p>
                        </div>
                    </div>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>
