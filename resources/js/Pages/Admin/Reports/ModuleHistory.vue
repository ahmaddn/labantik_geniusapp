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
    AlertTriangle,
} from "lucide-vue-next";
import { h } from "vue";

const props = defineProps({
    module: Object,
    students: Array,
    module_summary: Object,
});

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
                    class="flex flex-col sm:flex-row items-start sm:items-center gap-4"
                >
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
            </div>

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
    </AppLayout>
</template>
