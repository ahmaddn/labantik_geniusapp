<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import DataTable from "@/Components/UI/DataTable.vue";
import { router } from "@inertiajs/vue3";
import {
    ArrowLeft,
    ClipboardList,
    BookOpen,
    Star,
    CheckCircle,
    AlertTriangle,
    Eye,
    Edit3
} from "lucide-vue-next";
import { onMounted, ref } from "vue";
import Modal from "@/Components/UI/Modal.vue";
import { useForm } from "@inertiajs/vue3";
import {
    Chart,
    BarController,
    BarElement,
    LineController,
    LineElement,
    PointElement,
    DoughnutController,
    ArcElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
    Filler,
} from "chart.js";

Chart.register(
    BarController,
    BarElement,
    LineController,
    LineElement,
    PointElement,
    DoughnutController,
    ArcElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
    Filler,
);

const props = defineProps({
    module: Object,
    student: Object,
    quizzes: Array,
    overall: Number,
    chartLabels: Array,
    chartScores: Array,
    scoreDistribution: Object,
    reflections: Array,
});

const showAnswersModal = ref(false);
const showScoreModal = ref(false);
const selectedItemForAnswers = ref(null);
const scoreForm = useForm({
    type: "",
    id: "",
    score: 0,
});

const openAnswers = (item) => {
    selectedItemForAnswers.value = item;
    showAnswersModal.value = true;
};

const openScore = (item, type) => {
    scoreForm.clearErrors();
    scoreForm.type = type;
    scoreForm.id = type === 'quiz' ? item.attempt_id : item.answer_id;
    scoreForm.score = item.score !== undefined ? item.score : (item.overall_score || 0);
    showScoreModal.value = true;
};

const submitScore = () => {
    scoreForm.post(route('admin.reports.update_score', [props.module.id, props.student.id]), {
        onSuccess: () => {
            showScoreModal.value = false;
        }
    });
};

const goBack = () => {
    router.visit(route("admin.reports.history", props.module.id));
};

const formatDate = (val) => {
    if (!val) return "-";
    return new Date(val).toLocaleString("id-ID", {
        dateStyle: "medium",
        timeStyle: "short",
    });
};

const scoreColor = (score) => {
    if (score >= 80) return "text-blue-600";
    if (score >= 60) return "text-yellow-600";
    return "text-red-500";
};

// ── DataTable ──────────────────────────────────────────────
const columns = [
    { key: "quiz_title", label: "Nama Quiz", sortable: true },
    { key: "score", label: "Skor", sortable: true, align: "center" },
    {
        key: "started_at",
        label: "Mulai",
        sortable: true,
        formatter: (val) => formatDate(val),
    },
    {
        key: "finished_at",
        label: "Selesai",
        sortable: true,
        formatter: (val) => formatDate(val),
    },
    { key: "actions", label: "Aksi", align: "right" },
];

// ── Chart refs ─────────────────────────────────────────────
const barChartRef = ref(null);
const lineChartRef = ref(null);
const donutChartRef = ref(null);

onMounted(() => {
    if (!props.quizzes.length) return;

    const labels = props.chartLabels;
    const scores = props.chartScores;

    const barColors = scores.map((s) =>
        s >= 80
            ? "rgba(59,130,246,0.7)"
            : s >= 60
              ? "rgba(234,179,8,0.7)"
              : "rgba(239,68,68,0.7)",
    );
    const borderColors = scores.map((s) =>
        s >= 80 ? "#3b82f6" : s >= 60 ? "#eab308" : "#ef4444",
    );

    const scaleDefaults = {
        y: { beginAtZero: true, max: 100, grid: { color: "#e0e7ff" } },
        x: { grid: { display: false } },
    };

    // 1. Bar chart
    if (barChartRef.value) {
        new Chart(barChartRef.value, {
            type: "bar",
            data: {
                labels,
                datasets: [
                    {
                        label: "Skor",
                        data: scores,
                        backgroundColor: barColors,
                        borderColor: borderColors,
                        borderWidth: 2,
                        borderRadius: 8,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: { label: (ctx) => ` Skor: ${ctx.raw}` },
                    },
                },
                scales: scaleDefaults,
            },
        });
    }

    // 2. Line chart
    if (lineChartRef.value) {
        new Chart(lineChartRef.value, {
            type: "line",
            data: {
                labels,
                datasets: [
                    {
                        label: "Tren Skor",
                        data: scores,
                        borderColor: "#6366f1",
                        backgroundColor: "rgba(99,102,241,0.1)",
                        pointBackgroundColor: "#6366f1",
                        pointRadius: 5,
                        tension: 0.4,
                        fill: true,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: { label: (ctx) => ` Skor: ${ctx.raw}` },
                    },
                },
                scales: scaleDefaults,
            },
        });
    }

    // 3. Donut chart
    if (donutChartRef.value) {
        const { low, medium, high } = props.scoreDistribution;
        new Chart(donutChartRef.value, {
            type: "doughnut",
            data: {
                labels: ["Rendah (<60)", "Cukup (60–79)", "Baik (≥80)"],
                datasets: [
                    {
                        data: [low, medium, high],
                        backgroundColor: [
                            "rgba(239,68,68,0.8)",
                            "rgba(234,179,8,0.8)",
                            "rgba(59,130,246,0.8)",
                        ],
                        borderColor: ["#ef4444", "#eab308", "#3b82f6"],
                        borderWidth: 2,
                    },
                ],
            },
            options: {
                responsive: true,
                cutout: "65%",
                plugins: {
                    legend: { position: "bottom" },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.label}: ${ctx.raw} quiz`,
                        },
                    },
                },
            },
        });
    }
});
</script>

<template>
    <AppLayout>
        <div class="p-5 max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div
                class="bg-white rounded-3xl border-4 border-blue-200 shadow-playful p-6"
            >
                <div
                    class="flex flex-col sm:flex-row items-start sm:items-center gap-4"
                >
                    <button
                        @click="goBack"
                        class="bg-blue-100 p-3 rounded-2xl border-2 border-blue-300 hover:bg-blue-200 transition-all"
                    >
                        <ArrowLeft class="text-blue-600 w-5 h-5" />
                    </button>
                    <div class="flex-1">
                        <p
                            class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-0.5"
                        >
                            Laporan & Penilaian
                        </p>
                        <h1
                            class="text-2xl md:text-3xl font-heading font-bold text-gray-800 mb-1"
                        >
                            {{ student.name }}
                        </h1>
                        <p class="text-sm text-gray-500">
                            {{ module.name }} • Kelas {{ student.class }}
                        </p>
                    </div>

                    <!-- Overall score badge -->
                    <div
                        class="shrink-0 flex flex-col items-center px-6 py-3 rounded-3xl border-4"
                        :class="
                            overall >= 80
                                ? 'bg-blue-50 border-blue-200'
                                : overall >= 60
                                  ? 'bg-yellow-50 border-yellow-200'
                                  : 'bg-red-50 border-red-200'
                        "
                    >
                        <p class="text-xs text-gray-500 font-medium">
                            Nilai Akhir
                        </p>
                        <p
                            class="text-3xl font-bold"
                            :class="scoreColor(overall)"
                        >
                            {{ overall }}
                        </p>
                        <div class="flex items-center gap-1 mt-0.5">
                            <Star
                                v-if="overall >= 80"
                                class="w-3.5 h-3.5 text-blue-500"
                            />
                            <CheckCircle
                                v-else-if="overall >= 60"
                                class="w-3.5 h-3.5 text-yellow-500"
                            />
                            <AlertTriangle
                                v-else
                                class="w-3.5 h-3.5 text-red-500"
                            />
                            <span
                                class="text-xs font-medium"
                                :class="scoreColor(overall)"
                            >
                                {{
                                    overall >= 80
                                        ? "Baik"
                                        : overall >= 60
                                          ? "Cukup"
                                          : "Perlu Perhatian"
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stat pills -->
            <div class="flex flex-wrap gap-3">
                <div
                    class="flex items-center gap-2 bg-white border-4 border-blue-100 px-4 py-2 rounded-2xl shadow-playful"
                >
                    <ClipboardList class="w-4 h-4 text-blue-500" />
                    <span class="text-sm font-medium text-gray-700"
                        >{{ quizzes.length }} Quiz Dikerjakan</span
                    >
                </div>
                <div
                    class="flex items-center gap-2 bg-white border-4 border-blue-100 px-4 py-2 rounded-2xl shadow-playful"
                >
                    <Star class="w-4 h-4 text-blue-500" />
                    <span class="text-sm font-medium text-gray-700"
                        >{{ scoreDistribution.high }} Nilai Baik</span
                    >
                </div>
                <div
                    class="flex items-center gap-2 bg-white border-4 border-yellow-100 px-4 py-2 rounded-2xl shadow-playful"
                >
                    <CheckCircle class="w-4 h-4 text-yellow-500" />
                    <span class="text-sm font-medium text-gray-700"
                        >{{ scoreDistribution.medium }} Nilai Cukup</span
                    >
                </div>
                <div
                    class="flex items-center gap-2 bg-white border-4 border-red-100 px-4 py-2 rounded-2xl shadow-playful"
                >
                    <AlertTriangle class="w-4 h-4 text-red-500" />
                    <span class="text-sm font-medium text-gray-700"
                        >{{ scoreDistribution.low }} Perlu Perhatian</span
                    >
                </div>
            </div>

            <!-- Charts -->
            <div
                v-if="quizzes.length > 0"
                class="grid grid-cols-1 lg:grid-cols-3 gap-6"
            >
                <!-- Bar chart -->
                <div
                    class="lg:col-span-2 bg-white rounded-3xl border-4 border-blue-200 shadow-playful p-6"
                >
                    <h2 class="text-lg font-bold text-gray-800 mb-4">
                        Skor per Quiz
                    </h2>
                    <canvas ref="barChartRef" />
                </div>

                <!-- Donut chart -->
                <div
                    class="bg-white rounded-3xl border-4 border-blue-200 shadow-playful p-6 flex flex-col"
                >
                    <h2 class="text-lg font-bold text-gray-800 mb-4">
                        Distribusi Nilai
                    </h2>
                    <div class="flex-1 flex items-center justify-center">
                        <canvas ref="donutChartRef" />
                    </div>
                </div>

                <!-- Line chart -->
                <div
                    class="lg:col-span-3 bg-white rounded-3xl border-4 border-indigo-200 shadow-playful p-6"
                >
                    <h2 class="text-lg font-bold text-gray-800 mb-4">
                        Tren Nilai
                    </h2>
                    <canvas ref="lineChartRef" />
                </div>
            </div>

            <!-- DataTable -->
            <div>
                <div
                    class="bg-blue-100 rounded-2xl p-4 flex items-center justify-between mb-4"
                >
                    <h2 class="text-xl font-bold text-gray-800">
                        Detail Hasil Quiz
                    </h2>
                    <span
                        class="bg-blue-500 text-white px-4 py-1.5 rounded-full text-sm font-bold"
                    >
                        {{ quizzes.length }} Quiz
                    </span>
                </div>

                <DataTable
                    :columns="columns"
                    :data="quizzes"
                    :actions="[]"
                    :initial-per-page="10"
                    empty-message="Siswa ini belum mengerjakan quiz apapun."
                    search-placeholder="Cari nama quiz..."
                >
                    <!-- Icon + angka untuk kolom Skor -->
                    <template #cell-score="{ row }">
                        <div class="flex items-center justify-center gap-1.5">
                            <Star
                                v-if="row.score >= 80"
                                class="w-4 h-4 text-blue-500 shrink-0"
                            />
                            <CheckCircle
                                v-else-if="row.score >= 60"
                                class="w-4 h-4 text-yellow-500 shrink-0"
                            />
                            <AlertTriangle
                                v-else
                                class="w-4 h-4 text-red-500 shrink-0"
                            />
                            <span
                                class="font-bold"
                                :class="scoreColor(row.score)"
                            >
                                {{ row.score }}
                            </span>
                        </div>
                    </template>
                    <template #cell-actions="{ row }">
                        <div class="flex items-center justify-end gap-2">
                            <button
                                @click="openAnswers(row)"
                                class="bg-blue-50 text-blue-600 hover:bg-blue-100 px-3 py-1.5 rounded-xl text-sm font-bold flex items-center gap-1.5 transition-colors border border-blue-200"
                            >
                                <Eye class="w-4 h-4" /> Detail
                            </button>
                            <button
                                @click="openScore(row, 'quiz')"
                                class="bg-yellow-50 text-yellow-600 hover:bg-yellow-100 px-3 py-1.5 rounded-xl text-sm font-bold flex items-center gap-1.5 transition-colors border border-yellow-200"
                            >
                                <Edit3 class="w-4 h-4" /> Nilai
                            </button>
                        </div>
                    </template>
                </DataTable>
            </div>

            <!-- Bagian Jawaban Refleksi -->
            <div v-if="reflections && reflections.length > 0" class="mt-8">
                <div class="bg-purple-100 rounded-2xl p-4 flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800">Jawaban Refleksi</h2>
                    <span class="bg-purple-500 text-white px-4 py-1.5 rounded-full text-sm font-bold">
                        {{ reflections.length }} Refleksi
                    </span>
                </div>

                <div v-for="refData in reflections" :key="refData.reflection_id" class="bg-white rounded-3xl border-4 border-purple-200 shadow-playful p-6 mb-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 border-b-2 border-gray-100 pb-4">
                        <h3 class="text-xl font-bold text-gray-800">{{ refData.title }}</h3>
                        <div class="flex items-center gap-2">
                            <span class="bg-purple-50 text-purple-700 px-3 py-1 rounded-xl text-sm font-bold border border-purple-200">
                                Rata-rata: {{ refData.overall_score }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div v-for="ans in refData.answers" :key="ans.answer_id" class="bg-gray-50 rounded-2xl p-4 border-2 border-gray-100">
                            <p class="font-bold text-gray-800 mb-2">{{ ans.question_text }}</p>
                            <div class="bg-white p-3 rounded-xl border border-gray-200 mb-3 whitespace-pre-wrap text-gray-700 text-sm">
                                {{ ans.answer_text }}
                            </div>
                            <div class="flex items-center justify-between mt-2 pt-3 border-t border-gray-200">
                                <span class="text-sm font-bold text-gray-500">
                                    Nilai saat ini: <span :class="scoreColor(ans.score)">{{ ans.score }}</span>
                                </span>
                                <button
                                    @click="openScore(ans, 'reflection_answer')"
                                    class="bg-yellow-50 text-yellow-600 hover:bg-yellow-100 px-3 py-1.5 rounded-xl text-sm font-bold flex items-center gap-1.5 transition-colors border border-yellow-200"
                                >
                                    <Edit3 class="w-4 h-4" /> Beri Nilai
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Detail Jawaban Kuis -->
        <Modal :show="showAnswersModal" :title="`Detail: ${selectedItemForAnswers?.quiz_title}`" @close="showAnswersModal = false" maxWidth="3xl">
            <div v-if="selectedItemForAnswers && selectedItemForAnswers.answers && selectedItemForAnswers.answers.length > 0" class="space-y-4 pb-4">
                <div v-for="(ans, idx) in selectedItemForAnswers.answers" :key="idx" class="bg-gray-50 p-4 rounded-2xl border-2 border-gray-100">
                    <p class="font-bold text-gray-800 mb-2">{{ idx + 1 }}. {{ ans.question_text || 'Pertanyaan' }}</p>
                    
                    <div v-if="selectedItemForAnswers.quiz_type === 'drag_drop' || (ans.user_answer_map && Object.keys(ans.user_answer_map).length > 0)" class="text-sm text-gray-700 bg-white p-3 rounded-xl border border-gray-200 space-y-2">
                        <p class="font-semibold mb-1 text-gray-500 text-xs">PENCELUPAN / PENEMPATAN GRUP:</p>
                        <div v-for="(groupName, itemText) in ans.user_answer_map" :key="itemText" class="flex justify-between items-center py-1.5 border-b last:border-b-0 border-gray-100">
                            <span class="font-medium text-gray-800">"{{ itemText }}" dipasangkan ke "{{ groupName }}"</span>
                            <span v-if="ans.correct_answer_map && ans.correct_answer_map[itemText] === groupName" class="inline-flex items-center gap-0.5 bg-green-50 text-green-700 px-2 py-0.5 rounded text-xs font-bold border border-green-200">
                                ✓ Benar
                            </span>
                            <span v-else class="inline-flex flex-col items-end gap-0.5">
                                <span class="inline-flex items-center gap-0.5 bg-red-50 text-red-700 px-2 py-0.5 rounded text-xs font-bold border border-red-200">
                                    ✗ Salah
                                </span>
                                <span class="text-[10px] text-gray-500 mt-0.5">Harusnya: {{ ans.correct_answer_map ? ans.correct_answer_map[itemText] : '' }}</span>
                            </span>
                        </div>
                    </div>
                    
                    <div v-else class="flex flex-col sm:flex-row sm:items-center gap-2">
                        <div class="flex-1 bg-white p-3 rounded-xl border border-gray-200 text-sm text-gray-700">
                            <span class="font-semibold text-gray-500 block text-xs mb-1">JAWABAN SISWA:</span>
                            <span class="font-medium">{{ ans.selected_option || ans.user_answer_text || ans.response || '(Tidak dijawab)' }}</span>
                            
                            <div v-if="!ans.is_correct && ans.correct_answer_text" class="mt-2 text-xs text-red-600 bg-red-50 border border-red-100 p-2 rounded-lg font-medium">
                                <strong>Jawaban Benar:</strong> {{ ans.correct_answer_text }}
                            </div>
                        </div>
                        <div v-if="ans.is_correct !== null" class="shrink-0">
                            <span v-if="ans.is_correct" class="inline-flex items-center gap-1 bg-green-50 text-green-700 px-3 py-1.5 rounded-lg text-sm font-bold border border-green-200">
                                <CheckCircle class="w-4 h-4" /> Benar
                            </span>
                            <span v-else class="inline-flex items-center gap-1 bg-red-50 text-red-700 px-3 py-1.5 rounded-lg text-sm font-bold border border-red-200">
                                <AlertTriangle class="w-4 h-4" /> Salah
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="py-8 text-center bg-gray-50 rounded-2xl border-2 border-gray-100 mt-2">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-red-100 rounded-full mb-3">
                    <AlertTriangle class="w-6 h-6 text-red-500" />
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-1">Tidak Ada Jawaban</h3>
                <p class="text-gray-500 text-sm max-w-md mx-auto">
                    Siswa ini tidak menjawab pertanyaan apa pun pada kuis ini (sehingga mendapat nilai 0 secara otomatis).
                </p>
            </div>
        </Modal>

        <!-- Modal Beri Nilai -->
        <Modal :show="showScoreModal" title="Penilaian Manual" @close="showScoreModal = false" maxWidth="md" borderColor="yellow">
            <div class="pb-6">
                <p class="text-gray-600 text-sm mb-4">
                    Anda dapat menyesuaikan nilai yang diberikan secara manual. Nilai berkisar antara 0 - 100.
                </p>
                
                <form @submit.prevent="submitScore" class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nilai (0 - 100)</label>
                        <input
                            type="number"
                            v-model="scoreForm.score"
                            min="0"
                            max="100"
                            required
                            class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all font-bold text-gray-800 text-lg"
                        />
                        <p v-if="scoreForm.errors.score" class="text-red-500 text-xs mt-1">{{ scoreForm.errors.score }}</p>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showScoreModal = false" class="px-4 py-2 text-gray-500 hover:bg-gray-100 rounded-xl font-medium transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="scoreForm.processing" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-xl font-bold shadow-md hover:shadow-lg transition-all disabled:opacity-50">
                            Simpan Nilai
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>
