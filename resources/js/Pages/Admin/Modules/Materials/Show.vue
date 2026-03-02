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
</script>

<template>
    <AppLayout>
        <div class="p-5 max-w-4xl mx-auto">
            <!-- Header -->
            <div
                class="bg-white rounded-3xl border-4 border-green-200 shadow-playful p-6 mb-8"
            >
                <div class="flex items-start gap-4">
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
                <div
                    class="prose max-w-none text-gray-700 leading-relaxed whitespace-pre-wrap"
                >
                    {{ material.content }}
                </div>
            </Card>
        </div>
    </AppLayout>
</template>
