<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { router } from "@inertiajs/vue3";
import Button from "@/Components/UI/Button.vue";
import Card from "@/Components/UI/Card.vue";
import {
    HelpCircle,
    ArrowLeft,
    Pencil,
    Clock,
    Tag,
    Calendar,
    CheckCircle,
    XCircle,
    LayoutGrid,
    Box,
    Image as ImageIcon,
} from "lucide-vue-next";

const props = defineProps({
    module: { type: Object, required: true },
    mission: { type: Object, required: false, default: null },
    quiz: { type: Object, required: true },
});

const quizTypeLabel = (type) => {
    const map = {
        multiple_choices: "PILIHAN GANDA",
        drag_drop: "Drag & Drop",
        true_false: "True / False",
        case_study: "Case Study",
    };
    return map[type] || type;
};

const quizTypeBadgeClass = (type) => {
    const map = {
        multiple_choices: "bg-blue-100 text-blue-700 border-blue-300",
        drag_drop: "bg-purple-100 text-purple-700 border-purple-300",
        true_false: "bg-green-100 text-green-700 border-green-300",
        case_study: "bg-pink-100 text-pink-700 border-pink-300",
    };
    return map[type] || "bg-gray-100 text-gray-700 border-gray-300";
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "long",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const getCategoryLabel = (value) => {
    const map = {
        pretest: "Tes Awal",
        mission: "Misi",
        posttest: "Tes Akhir",
    };
    return map[value] || value;
};
</script>

<template>
    <AppLayout>
        <div class="p-5 max-w-4xl mx-auto">
            <!-- Header -->
            <div
                class="bg-white rounded-3xl border-4 border-orange-200 shadow-playful p-6 mb-8"
            >
                <div
                    class="flex flex-col sm:flex-row items-start sm:items-center gap-4"
                >
                    <button
                        @click="
                            router.visit(
                                mission
                                    ? route('admin.modules.missions.show', [
                                          module.id,
                                          mission.id,
                                      ])
                                    : route('admin.modules.show', module.id),
                            )
                        "
                        class="bg-orange-100 p-3 rounded-2xl border-2 border-orange-300 hover:bg-orange-200 transition-all"
                    >
                        <ArrowLeft class="text-orange-600 w-5 h-5" />
                    </button>
                    <div class="flex-1">
                        <p class="text-sm text-gray-500 mb-1">
                            {{ module.name
                            }}<span v-if="mission"> / {{ mission.name }}</span>
                        </p>
                        <h1
                            class="text-2xl md:text-3xl font-heading font-bold text-gray-800 mb-2"
                        >
                            {{ quiz.title }}
                        </h1>
                        <div class="flex flex-wrap gap-2">
                            <span
                                :class="[
                                    'text-xs px-3 py-1 rounded-full border font-medium',
                                    quizTypeBadgeClass(quiz.type),
                                ]"
                            >
                                {{ quizTypeLabel(quiz.type) }}
                            </span>
                            <span
                                v-if="quiz.category"
                                class="text-xs px-3 py-1 rounded-full border font-medium bg-orange-100 text-orange-700 border-orange-300 capitalize"
                            >
                                {{ getCategoryLabel(quiz.category) }}
                            </span>
                        </div>
                    </div>
                    <Button
                        v-if="mission"
                        class="w-full sm:w-auto"
                        variant="warning"
                        size="md"
                        :icon="Pencil"
                        @click="
                            router.visit(
                                route('admin.modules.missions.quizzes.edit', [
                                    module.id,
                                    mission.id,
                                    quiz.id,
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
                title="Informasi Kuis"
                :icon="HelpCircle"
                icon-color="orange"
                border-color="orange"
                :hoverable="false"
                class="mb-6"
            >
                <div class="flex flex-wrap gap-4 text-sm text-gray-600 mb-4">
                    <span class="flex items-center gap-1">
                        <Clock class="w-4 h-4" />
                        {{ quiz.time_limit }} menit
                    </span>
                    <span class="flex items-center gap-1">
                        <Calendar class="w-4 h-4" />
                        {{ formatDate(quiz.created_at) }}
                    </span>
                    <span class="flex items-center gap-1">
                        <HelpCircle class="w-4 h-4" />
                        {{ quiz.questions?.length || 0 }} Pertanyaan
                    </span>
                </div>
                <p
                    v-if="quiz.description"
                    class="text-gray-600 text-sm bg-gray-50 p-3 rounded-xl border border-gray-200"
                >
                    {{ quiz.description }}
                </p>
            </Card>

            <!-- Gambar Kuis -->
            <Card
                v-if="quiz.image"
                variant="playful"
                title="Gambar Kuis"
                :icon="ImageIcon"
                icon-color="blue"
                border-color="blue"
                :hoverable="false"
                class="mb-6"
            >
                <img
                    :src="`/storage/${quiz.image}`"
                    :alt="quiz.title"
                    class="w-full max-h-96 object-contain rounded-xl border-2 border-gray-200"
                />
            </Card>

            <!-- Pertanyaan (non drag_drop) -->
            <div v-if="quiz.type !== 'drag_drop'" class="space-y-4">
                <h2 class="text-lg font-bold text-gray-700 mb-2">
                    Daftar Pertanyaan
                </h2>
                <Card
                    v-for="(question, index) in quiz.questions"
                    :key="question.id"
                    variant="playful"
                    :title="`Q${index + 1}`"
                    :icon="HelpCircle"
                    icon-color="blue"
                    border-color="blue"
                    :hoverable="false"
                >
                    <!-- Maskot pertanyaan -->
                    <div
                        v-if="question.mascot"
                        class="flex items-center gap-2 mb-3 bg-yellow-50 p-2 rounded-lg border border-yellow-200"
                    >
                        <img
                            :src="`/storage/${question.mascot.image}`"
                            class="w-8 h-8 object-contain rounded"
                        />
                        <span class="text-sm font-medium text-yellow-700">{{
                            question.mascot.name_pose
                        }}</span>
                    </div>

                    <!-- Gambar pertanyaan -->
                    <img
                        v-if="question.image"
                        :src="`/storage/${question.image}`"
                        class="w-full max-h-48 object-contain rounded-lg border mb-3"
                    />

                    <p class="text-gray-800 font-medium mb-4">
                        {{ question.question_text }}
                    </p>

                    <!-- Opsi jawaban -->
                    <div class="space-y-2">
                        <div
                            v-for="option in question.options"
                            :key="option.id"
                            :class="[
                                'p-3 rounded-xl border-2 flex items-start gap-3',
                                option.is_correct
                                    ? 'bg-green-50 border-green-300'
                                    : 'bg-gray-50 border-gray-200',
                            ]"
                        >
                            <CheckCircle
                                v-if="option.is_correct"
                                class="text-green-500 w-5 h-5 shrink-0 mt-0.5"
                            />
                            <XCircle
                                v-else
                                class="text-gray-300 w-5 h-5 shrink-0 mt-0.5"
                            />
                            <div>
                                <p class="text-sm font-medium">
                                    {{ option.option_text }}
                                </p>
                                <p
                                    v-if="option.feedback"
                                    class="text-xs text-gray-500 mt-1"
                                >
                                    Feedback: {{ option.feedback }}
                                </p>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Drag & Drop -->
            <div v-else class="space-y-6">
                <div v-for="question in quiz.questions" :key="question.id">
                    <!-- Instruksi -->
                    <Card
                        variant="playful"
                        title="Instruksi"
                        :icon="HelpCircle"
                        icon-color="blue"
                        border-color="blue"
                        :hoverable="false"
                        class="mb-4"
                    >
                        <div
                            v-if="question.mascot"
                            class="flex items-center gap-2 mb-3 bg-yellow-50 p-2 rounded-lg border border-yellow-200"
                        >
                            <img
                                :src="`/storage/${question.mascot.image}`"
                                class="w-8 h-8 object-contain rounded"
                            />
                            <span class="text-sm font-medium text-yellow-700">{{
                                question.mascot.name_pose
                            }}</span>
                        </div>
                        <p class="text-gray-800 font-medium">
                            {{ question.question_text }}
                        </p>
                    </Card>

                    <!-- Grup -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Card
                            v-for="group in question.drag_drop_groups"
                            :key="group.id"
                            variant="playful"
                            :title="group.group_name"
                            :icon="LayoutGrid"
                            icon-color="purple"
                            border-color="purple"
                            :hoverable="false"
                        >
                            <div class="space-y-2">
                                <div
                                    v-for="item in question.drag_drop_items?.filter(
                                        (i) =>
                                            i.drag_drop_group_id === group.id,
                                    )"
                                    :key="item.id"
                                    class="flex items-center gap-2 bg-purple-50 p-2 rounded-lg border border-purple-200"
                                >
                                    <template v-if="item.item_image">
                                        <img
                                            :src="`/storage/${item.item_image}`"
                                            :alt="item.item_text"
                                            class="w-12 h-12 object-cover rounded-md border mr-2"
                                        />
                                        <div>
                                            <div class="text-sm font-medium">
                                                {{ item.item_text }}
                                            </div>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <Box
                                            class="w-4 h-4 text-purple-500 shrink-0"
                                        />
                                        <span class="text-sm font-medium">{{
                                            item.item_text
                                        }}</span>
                                    </template>
                                </div>
                                <p
                                    v-if="
                                        !question.drag_drop_items?.filter(
                                            (i) =>
                                                i.drag_drop_group_id ===
                                                group.id,
                                        ).length
                                    "
                                    class="text-xs text-gray-400 italic"
                                >
                                    Tidak ada item
                                </p>
                            </div>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
