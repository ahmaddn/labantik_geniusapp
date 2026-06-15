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
    if (['conceptual_systematic', 'learning_objectives', 'initial_questions', 'cover_page', 'image_comparison', 'process_list'].includes(props.material.layout_type) && props.material.content) {
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
                            <div class="md:col-span-2 grid grid-cols-1 gap-4 mt-2 border-t pt-4">
                                <div v-if="conceptualData.variables" class="w-full">
                                    <h4 class="font-bold text-sm text-gray-700 mb-2">Variabel Penggeser:</h4>
                                    <div v-for="(v, idx) in conceptualData.variables" :key="idx" class="text-sm bg-indigo-50 p-2 mb-2 rounded border border-indigo-100">
                                        <strong>{{ v.name || 'Variabel ' + (idx+1) }}</strong>: {{ v.min_label }} - {{ v.max_label }}
                                    </div>
                                </div>
                                <div v-if="conceptualData.levels" class="w-full mt-4">
                                    <h4 class="font-bold text-sm text-gray-700 mb-2">Level/Tahapan:</h4>
                                    <div v-for="(lvl, idx) in conceptualData.levels" :key="'lvl-'+idx" class="text-sm bg-blue-50 p-2 mb-2 rounded border border-blue-100">
                                        <strong>{{ lvl.level_name || 'Level ' + (idx+1) }}</strong> (Status: {{ lvl.status }}, Efek: {{ lvl.animation_effect }})<br/>
                                        <span class="text-xs text-gray-500">{{ lvl.narration }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <pre class="whitespace-pre-wrap text-sm text-red-500">Gagal memuat data (Invalid Format)</pre>
                        </div>
                    </div>
                </div>
                <!-- Learning Objectives Preview -->
                <div v-else-if="material.layout_type === 'learning_objectives'" class="space-y-4">
                    <div class="bg-blue-50 p-5 rounded-2xl border-2 border-blue-200">
                        <h3 class="font-bold text-gray-800 text-lg border-b pb-2 mb-4">Tujuan Pembelajaran</h3>
                        <ul class="list-decimal pl-5 space-y-2">
                            <li v-for="(item, idx) in (conceptualData || [])" :key="idx" class="text-gray-700">{{ item }}</li>
                        </ul>
                    </div>
                </div>
                <!-- Cover Page Preview -->
                <div v-else-if="material.layout_type === 'cover_page'" class="space-y-4">
                    <div class="bg-indigo-50 p-5 rounded-2xl border-2 border-indigo-200">
                        <h3 class="font-bold text-gray-800 text-lg border-b pb-2 mb-4">Halaman Cover</h3>
                        <div class="text-center">
                            <h2 class="text-2xl font-black text-gray-800 uppercase">{{ material.title }}</h2>
                            <p class="text-lg text-gray-600 font-bold mt-2">{{ conceptualData?.subtitle || '-' }}</p>
                        </div>
                    </div>
                </div>
                <!-- Initial Questions Preview -->
                <div v-else-if="material.layout_type === 'initial_questions'" class="space-y-4">
                    <div class="bg-purple-50 p-5 rounded-2xl border-2 border-purple-200">
                        <h3 class="font-bold text-gray-800 text-lg border-b pb-2 mb-4">Pertanyaan Awal</h3>
                        <ul class="list-disc pl-5 space-y-2">
                            <li v-for="(item, idx) in (conceptualData || [])" :key="idx" class="text-gray-700">{{ item }}</li>
                        </ul>
                    </div>
                </div>
                <!-- Image Comparison Preview -->
                <div v-else-if="material.layout_type === 'image_comparison'" class="space-y-4">
                    <div class="bg-green-50 p-5 rounded-2xl border-2 border-green-200">
                        <h3 class="font-bold text-gray-800 text-lg border-b pb-2 mb-4">Mengamati Perbedaan Gambar</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center">
                                <img v-if="conceptualData?.image_left" :src="`/storage/${conceptualData.image_left}`" class="w-full max-h-48 object-cover rounded border" />
                                <div v-else class="h-32 bg-gray-200 flex items-center justify-center rounded border text-gray-500">Kiri</div>
                                <p class="font-bold mt-2 text-gray-700">{{ conceptualData?.left_label || '-' }}</p>
                            </div>
                            <div class="text-center">
                                <img v-if="conceptualData?.image_right" :src="`/storage/${conceptualData.image_right}`" class="w-full max-h-48 object-cover rounded border" />
                                <div v-else class="h-32 bg-gray-200 flex items-center justify-center rounded border text-gray-500">Kanan</div>
                                <p class="font-bold mt-2 text-gray-700">{{ conceptualData?.right_label || '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Process List Preview -->
                <div v-else-if="material.layout_type === 'process_list'" class="space-y-4">
                    <div class="bg-orange-50 p-5 rounded-2xl border-2 border-orange-200 flex gap-6">
                        <div class="w-1/3">
                            <img v-if="material.image" :src="`/storage/${material.image}`" class="w-full h-auto object-cover rounded-xl border-4 border-white shadow-md" />
                            <div v-else class="w-full h-32 bg-gray-200 flex items-center justify-center rounded-xl border-4 border-white shadow-md text-gray-500">Gambar Proses</div>
                        </div>
                        <div class="w-2/3">
                            <h3 class="font-bold text-gray-800 text-lg border-b pb-2 mb-4">List Proses</h3>
                            <div class="space-y-2">
                                <div v-for="(item, idx) in (conceptualData || [])" :key="idx" class="flex items-center gap-3 bg-white p-2 rounded-lg border border-orange-100 shadow-sm">
                                    <div class="w-8 h-8 rounded-full bg-orange-100 text-orange-600 font-bold flex items-center justify-center">{{ idx + 1 }}</div>
                                    <span class="font-bold text-gray-700">{{ item }}</span>
                                </div>
                            </div>
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
