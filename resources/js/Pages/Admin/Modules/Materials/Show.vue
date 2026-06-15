<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { router } from "@inertiajs/vue3";
import Button from "@/Components/UI/Button.vue";
import Card from "@/Components/UI/Card.vue";
import {
    FileText,
    ArrowLeft,
    Pencil,
    Tag,
    User,
    Calendar,
    Image as ImageIcon,
} from "lucide-vue-next";

const props = defineProps({
    module: { type: Object, required: true },
    mission: { type: Object, required: true },
    material: { type: Object, required: true },
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "long",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

import { computed } from "vue";
const conceptualData = computed(() => {
    if (props.material.layout_type === 'conceptual_systematic' && props.material.content) {
        try {
            return JSON.parse(props.material.content);
        } catch(e) {
            return null;
        }
    }
    return null;
});
</script>

<template>
    <AppLayout>
        <div class="p-5 max-w-4xl mx-auto">
            <!-- Header -->
            <div
                class="bg-white rounded-3xl border-4 border-green-200 shadow-playful p-6 mb-8"
            >
                <div
                    class="flex flex-col sm:flex-row items-start sm:items-center gap-4"
                >
                    <button
                        @click="
                            router.visit(
                                route('admin.modules.missions.show', [
                                    module.id,
                                    mission.id,
                                ]),
                            )
                        "
                        class="bg-green-100 p-3 rounded-2xl border-2 border-green-300 hover:bg-green-200 transition-all"
                    >
                        <ArrowLeft class="text-green-600 w-5 h-5" />
                    </button>
                    <div class="flex-1">
                        <p class="text-sm text-gray-500 mb-1">
                            {{ module.name }} / {{ mission.name }}
                        </p>
                        <h1
                            class="text-2xl md:text-3xl font-heading font-bold text-gray-800"
                        >
                            {{ material.title }}
                        </h1>
                    </div>
                    <Button
                        class="w-full sm:w-auto"
                        variant="warning"
                        size="md"
                        :icon="Pencil"
                        @click="
                            router.visit(
                                route('admin.modules.missions.materials.edit', [
                                    module.id,
                                    mission.id,
                                    material.id,
                                ]),
                            )
                        "
                    >
                        Edit
                    </Button>
                </div>
            </div>

            <!-- Meta Info -->
            <Card
                variant="playful"
                title="Informasi Material"
                :icon="FileText"
                icon-color="green"
                border-color="green"
                :hoverable="false"
                class="mb-6"
            >
                <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                    <span
                        v-if="material.created_by"
                        class="flex items-center gap-1"
                    >
                        <User class="w-4 h-4" />
                        {{ material.created_by?.name || material.created_by }}
                    </span>
                    <span class="flex items-center gap-1">
                        <Calendar class="w-4 h-4" />
                        {{ formatDate(material.created_at) }}
                    </span>
                    <span
                        v-if="material.mascot"
                        class="flex items-center gap-1"
                    >
                        <Tag class="w-4 h-4" />
                        {{ material.mascot.name_pose }}
                    </span>
                </div>

                <!-- Deskripsi -->
                <p
                    v-if="material.description"
                    class="mt-4 text-gray-600 text-sm bg-gray-50 p-3 rounded-xl border border-gray-200"
                >
                    {{ material.description }}
                </p>
            </Card>

            <!-- Gambar -->
            <Card
                v-if="material.image"
                variant="playful"
                title="Gambar"
                :icon="ImageIcon"
                icon-color="blue"
                border-color="blue"
                :hoverable="false"
                class="mb-6"
            >
                <img
                    :src="`/storage/${material.image}`"
                    :alt="material.title"
                    class="w-full max-h-96 object-contain rounded-xl border-2 border-gray-200"
                />
            </Card>

            <!-- Maskot -->
            <Card
                v-if="material.mascot"
                variant="playful"
                title="Maskot"
                :icon="Tag"
                icon-color="yellow"
                border-color="yellow"
                :hoverable="false"
                class="mb-6"
            >
                <div
                    class="flex items-center gap-4 bg-yellow-50 p-4 rounded-xl border-2 border-yellow-200"
                >
                    <img
                        :src="`/storage/${material.mascot.image}`"
                        :alt="material.mascot.name"
                        class="w-20 h-20 object-contain rounded-xl"
                    />
                    <div>
                        <h3 class="font-bold text-yellow-800 text-lg">
                            {{ material.mascot.name }}
                        </h3>
                        <p class="text-sm text-yellow-600">
                            {{ material.mascot.name_pose }}
                        </p>
                    </div>
                </div>
            </Card>

            <!-- Konten -->
            <Card
                variant="playful"
                title="Konten Material"
                :icon="FileText"
                icon-color="green"
                border-color="green"
                :hoverable="false"
            >
                <div v-if="material.layout_type === 'video_only'" class="mt-4">
                    <iframe 
                        class="w-full h-64 md:h-96 rounded-xl border border-gray-200"
                        :src="material.youtube_link" 
                        title="YouTube video player" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                </div>
                <div v-else-if="material.layout_type === 'conceptual_systematic'" class="space-y-4">
                    <div class="bg-gray-50 p-5 rounded-2xl border-2 border-gray-200">
                        <h3 class="font-bold text-gray-800 text-lg border-b pb-2 mb-4">Data Konseptual Sistematis</h3>
                        <div v-if="conceptualData" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div><strong class="text-xs text-gray-500 uppercase">Teks Kiri Atas</strong><p class="text-sm bg-white p-2 border rounded">{{ conceptualData.topLeft || '-' }}</p></div>
                            <div><strong class="text-xs text-gray-500 uppercase">Teks Kanan Atas</strong><p class="text-sm bg-white p-2 border rounded">{{ conceptualData.topRight || '-' }}</p></div>
                            <div><strong class="text-xs text-gray-500 uppercase">Teks Kiri Bawah</strong><p class="text-sm bg-white p-2 border rounded">{{ conceptualData.bottomLeft || '-' }}</p></div>
                            <div><strong class="text-xs text-gray-500 uppercase">Teks Kanan Bawah</strong><p class="text-sm bg-white p-2 border rounded">{{ conceptualData.bottomRight || '-' }}</p></div>
                            <div v-if="conceptualData.variables && conceptualData.variables.length" class="md:col-span-2 border-t pt-4">
                                <h4 class="font-bold text-xs text-gray-500 uppercase mb-2">Variabel Slider</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div v-for="(v, vIdx) in conceptualData.variables" :key="vIdx" class="bg-white p-3 border rounded-xl">
                                        <p class="font-bold text-sm text-gray-800">{{ v.name || '-' }}</p>
                                        <p class="text-xs text-gray-500">Label: {{ v.min_label || 'Min' }} - {{ v.max_label || 'Max' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-if="conceptualData.levels && conceptualData.levels.length" class="md:col-span-2 border-t pt-4">
                                <h4 class="font-bold text-xs text-gray-500 uppercase mb-2">Level / Tahapan</h4>
                                <div class="space-y-3">
                                    <div v-for="(lvl, lIdx) in conceptualData.levels" :key="lIdx" class="bg-white p-3 border rounded-xl flex gap-3 items-center">
                                        <img v-if="lvl.image" :src="`/storage/${lvl.image}`" class="w-12 h-12 object-cover rounded-lg border" />
                                        <div class="flex-1">
                                            <p class="font-bold text-sm text-blue-800">{{ lvl.level_name || '-' }} <span class="ml-2 text-xs px-2 py-0.5 rounded-full capitalize font-bold" :class="lvl.status === 'bahaya' ? 'bg-red-100 text-red-700' : lvl.status === 'waspada' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700'">{{ lvl.status }}</span></p>
                                            <p class="text-xs text-gray-600 line-clamp-1 italic">"{{ lvl.narration || '-' }}"</p>
                                            <p class="text-[10px] text-gray-400">Efek: {{ lvl.animation_effect || 'none' }} | Metrik: {{ lvl.metric_value || '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <pre class="whitespace-pre-wrap text-sm text-red-500">Gagal memuat data (Invalid Format)</pre>
                        </div>
                    </div>
                </div>
                <div v-else
                    class="prose max-w-none text-gray-700 leading-relaxed quill-content"
                    v-html="material.content"
                >
                </div>
                
                <!-- If default layout has youtube link -->
                <div v-if="material.layout_type === 'default' && material.youtube_link" class="mt-6">
                    <h3 class="font-bold text-gray-700 mb-2">Tautan YouTube:</h3>
                    <a :href="material.youtube_link" target="_blank" class="text-blue-500 hover:underline break-all">{{ material.youtube_link }}</a>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>
