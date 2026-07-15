<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import DataTable from "@/Components/UI/DataTable.vue";
import { router } from "@inertiajs/vue3";
import {
    ArrowLeft,
    Users,
    TrendingUp,
    ClipboardList,
    Eye,
    Star,
    CheckCircle,
    Download
} from "lucide-vue-next";
import { ref, computed } from "vue";
import { h } from "vue";
import Pagination from "@/Components/UI/Pagination.vue";

const props = defineProps({
    module: Object,
    students: Array,
    module_summary: Object,
    mission_logs: Array,
});

const activeTab = ref("summary"); // "summary" atau "history"

const currentPage = ref(1);
const perPage = 10;

const paginatedLogs = computed(() => {
    return props.mission_logs.slice(
        (currentPage.value - 1) * perPage,
        currentPage.value * perPage
    );
});

const paginationMeta = computed(() => {
    const total = props.mission_logs.length;
    const lastPage = Math.ceil(total / perPage);
    const from = total > 0 ? (currentPage.value - 1) * perPage + 1 : 0;
    const to = Math.min(currentPage.value * perPage, total);
    return {
        current_page: currentPage.value,
        last_page: lastPage,
        per_page: perPage,
        total: total,
        from: from,
        to: to
    };
});

const handlePageChange = (page) => {
    currentPage.value = page;
};


const goBack = () => {
    router.visit(route("admin.reports.index"));
};

const goToStudentReport = (studentId) => {
    router.visit(
        route("admin.reports.student", {
            modules: props.module.id,
            student: studentId,
        }),
    );
};

const scoreColor = (score) => {
    if (score >= 80) return "text-blue-600";
    if (score >= 60) return "text-yellow-600";
    return "text-red-500";
};

const columns = [
    { key: "name", label: "Nama Siswa", sortable: true },
    { key: "class", label: "Kelas", sortable: true },
    {
        key: "completion",
        label: "Progres Quiz",
        sortable: true,
        align: "center",
    },
    {
        key: "overall_score",
        label: "Nilai Akhir",
        sortable: true,
        align: "center",
    },
];

const actions = [
    {
        name: "detail",
        label: "Lihat Detail",
        icon: Eye,
        class: "bg-blue-500 border-blue-600",
        showIf: () => true,
    },
];

const handleAction = ({ action, data }) => {
    if (action === "detail") goToStudentReport(data.id);
};
</script>

<template>
    <AppLayout>
        <div class="p-5 max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div
                class="bg-white rounded-3xl border-4 border-blue-200 shadow-playful p-6"
            >
                <div
                    class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
                >
                    <div class="flex items-center gap-4">
                        <button
                            @click="goBack"
                            class="bg-blue-100 p-3 rounded-2xl border-2 border-blue-300 hover:bg-blue-200 transition-all"
                        >
                            <ArrowLeft class="text-blue-600 w-5 h-5" />
                        </button>
                        <div>
                            <p
                                class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-0.5"
                            >
                                Laporan Modul
                            </p>
                            <h1
                                class="text-2xl md:text-3xl font-heading font-bold text-gray-800"
                            >
                                {{ module.name }}
                            </h1>
                        </div>
                    </div>
                    <a :href="route('admin.reports.export', { modules: module.id, include_all: 1 })" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-bold rounded-xl transition-all shadow-sm">
                        <Download class="w-5 h-5" /> Export Excel
                    </a>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex gap-4 border-b border-gray-200">
                <button
                    @click="activeTab = 'summary'"
                    class="pb-3 px-2 font-bold text-lg transition-colors border-b-4"
                    :class="activeTab === 'summary' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                >
                    Ringkasan & Siswa
                </button>
                <button
                    @click="activeTab = 'history'"
                    class="pb-3 px-2 font-bold text-lg transition-colors border-b-4"
                    :class="activeTab === 'history' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                >
                    Riwayat Penyelesaian Misi
                </button>
            </div>

            <div v-if="activeTab === 'summary'" class="space-y-6">
                <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div
                    class="bg-white rounded-3xl border-4 border-blue-200 shadow-playful p-5 flex items-center gap-4"
                >
                    <div
                        class="bg-blue-100 w-12 h-12 rounded-2xl flex items-center justify-center shrink-0"
                    >
                        <Users class="w-6 h-6 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Siswa Mengerjakan</p>
                        <p class="text-2xl font-bold text-gray-800">
                            {{ module_summary.total_students }}
                        </p>
                    </div>
                </div>

                <div
                    class="bg-white rounded-3xl border-4 border-indigo-200 shadow-playful p-5 flex items-center gap-4"
                >
                    <div
                        class="bg-indigo-100 w-12 h-12 rounded-2xl flex items-center justify-center shrink-0"
                    >
                        <ClipboardList class="w-6 h-6 text-indigo-600" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Quiz di Modul</p>
                        <p class="text-2xl font-bold text-gray-800">
                            {{ module_summary.total_quizzes }}
                        </p>
                    </div>
                </div>

                <div
                    class="bg-white rounded-3xl border-4 border-green-200 shadow-playful p-5 flex items-center gap-4"
                >
                    <div
                        class="bg-green-100 w-12 h-12 rounded-2xl flex items-center justify-center shrink-0"
                    >
                        <TrendingUp class="w-6 h-6 text-green-600" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Rata-rata Nilai</p>
                        <p
                            class="text-2xl font-bold"
                            :class="
                                module_summary.avg_score !== null
                                    ? scoreColor(module_summary.avg_score)
                                    : 'text-gray-400'
                            "
                        >
                            {{ module_summary.avg_score ?? "—" }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- DataTable -->
            <div>
                <div
                    class="bg-blue-100 rounded-2xl p-4 flex items-center justify-between mb-4"
                >
                    <h2 class="text-xl font-bold text-gray-800">
                        Daftar Siswa
                    </h2>
                    <span
                        class="bg-blue-500 text-white px-4 py-1.5 rounded-full text-sm font-bold"
                    >
                        {{ students.length }} Siswa
                    </span>
                </div>

                <DataTable
                    :columns="columns"
                    :data="students"
                    :actions="actions"
                    :initial-per-page="10"
                    empty-message="Belum ada siswa yang mengerjakan modul ini."
                    search-placeholder="Cari nama atau kelas siswa..."
                    @action="handleAction"
                >
                    <!-- Progress bar untuk kolom Progres Quiz -->
                    <template #cell-completion="{ row }">
                        <div class="flex items-center gap-2 justify-center">
                            <div
                                class="w-24 bg-gray-200 rounded-full h-2 overflow-hidden"
                            >
                                <div
                                    class="h-2 rounded-full transition-all"
                                    :class="
                                        row.completion >= 80
                                            ? 'bg-blue-500'
                                            : row.completion >= 50
                                              ? 'bg-yellow-400'
                                              : 'bg-red-400'
                                    "
                                    :style="{ width: row.completion + '%' }"
                                />
                            </div>
                            <span class="text-xs text-gray-500 shrink-0">
                                {{ row.quizzes_count }}/{{ row.quizzes_total }}
                            </span>
                        </div>
                    </template>

                    <!-- Icon + angka untuk kolom Nilai Akhir -->
                    <template #cell-overall_score="{ row }">
                        <div class="flex items-center justify-center gap-1.5">
                            <Star
                                v-if="row.overall_score >= 80"
                                class="w-4 h-4 text-blue-500 shrink-0"
                            />
                            <CheckCircle
                                v-else-if="row.overall_score >= 60"
                                class="w-4 h-4 text-yellow-500 shrink-0"
                            />
                            <AlertTriangle
                                v-else
                                class="w-4 h-4 text-red-500 shrink-0"
                            />
                            <span
                                class="font-bold"
                                :class="scoreColor(row.overall_score)"
                            >
                                {{ row.overall_score }}
                            </span>
                        </div>
                    </template>
                </DataTable>
            </div>
            </div>

            <!-- History Tab -->
            <div v-if="activeTab === 'history'" class="space-y-6">
                <div class="bg-blue-100 rounded-2xl p-4 flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800">Riwayat Misi Selesai</h2>
                    <span class="bg-blue-500 text-white px-4 py-1.5 rounded-full text-sm font-bold">
                        {{ mission_logs.length }} Catatan
                    </span>
                </div>

                <div v-if="mission_logs.length === 0" class="bg-white p-8 rounded-3xl border-2 border-gray-200 text-center">
                    <p class="text-gray-500 font-medium">Belum ada riwayat penyelesaian misi.</p>
                </div>

                <div v-else class="space-y-4">
                    <div class="overflow-x-auto bg-white rounded-3xl border-4 border-blue-200 shadow-playful">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-blue-500 text-white text-left">
                                    <th class="p-4 font-bold border-b border-blue-200">Nama Siswa</th>
                                    <th class="p-4 font-bold border-b border-blue-200">Misi</th>
                                    <th class="p-4 font-bold border-b border-blue-200 text-center">Percobaan Ke</th>
                                    <th class="p-4 font-bold border-b border-blue-200">Waktu Selesai</th>
                                    <th class="p-4 font-bold border-b border-blue-200 text-center">Status</th>
                                    <th class="p-4 font-bold border-b border-blue-200 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="log in paginatedLogs" :key="log.id" class="hover:bg-blue-50/50 transition-colors">
                                    <td class="p-4 border-b border-gray-100 font-bold text-gray-800">{{ log.student_name }}</td>
                                    <td class="p-4 border-b border-gray-100 text-gray-600 font-medium">{{ log.mission_name }}</td>
                                    <td class="p-4 border-b border-gray-100 text-center">
                                        <span class="inline-flex items-center justify-center bg-blue-100 text-blue-800 font-bold px-3 py-1 rounded-full text-xs border border-blue-200">
                                            Percobaan {{ log.attempt_number }}
                                        </span>
                                    </td>
                                    <td class="p-4 border-b border-gray-100 text-gray-500 text-sm font-medium">{{ log.completed_at }}</td>
                                    <td class="p-4 border-b border-gray-100 text-center">
                                        <span class="inline-flex items-center justify-center bg-green-100 text-green-800 font-bold px-3 py-1 rounded-full text-xs border border-green-200">
                                            Selesai
                                        </span>
                                    </td>
                                    <td class="p-4 border-b border-gray-100 text-center">
                                        <button @click="goToStudentReport(log.student_id)" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-xl text-xs transition-all shadow-sm">
                                            <Eye class="w-3.5 h-3.5" /> Detail
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <Pagination :meta="paginationMeta" @change="handlePageChange" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
