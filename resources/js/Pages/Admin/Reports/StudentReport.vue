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
} from "lucide-vue-next";
import { onMounted, ref } from "vue";
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
});

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
                            Laporan Siswa
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
                </DataTable>
            </div>
        </div>
    </AppLayout>
</template>
