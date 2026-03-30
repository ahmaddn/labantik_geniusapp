<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { router, usePage } from "@inertiajs/vue3";
import { onMounted, ref, computed } from "vue";
import {
    BookOpen,
    Users,
    ClipboardList,
    TrendingUp,
    Flag,
    FileText,
    LayoutTemplate,
    GraduationCap,
    Plus,
    ChevronRight,
    CheckCircle,
    XCircle,
    Star,
    AlertTriangle,
    UserPlus,
    Pencil,
    Layers,
    Sparkles,
    Hand,
    PartyPopper,
    Smile,
} from "lucide-vue-next";
import {
    Chart,
    DoughnutController,
    ArcElement,
    Tooltip,
    Legend,
} from "chart.js";

Chart.register(DoughnutController, ArcElement, Tooltip, Legend);

const page = usePage();

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    recent_modules: { type: Array, default: () => [] },
    recent_students: { type: Array, default: () => [] },
    recent_attempts: { type: Array, default: () => [] },
    score_distribution: {
        type: Object,
        default: () => ({ low: 0, medium: 0, high: 0 }),
    },
});

const s = computed(() => ({
    total_modules: props.stats?.total_modules ?? 0,
    total_missions: props.stats?.total_missions ?? 0,
    total_quizzes: props.stats?.total_quizzes ?? 0,
    total_materials: props.stats?.total_materials ?? 0,
    total_templates: props.stats?.total_templates ?? 0,
    total_classes: props.stats?.total_classes ?? 0,
    total_students: props.stats?.total_students ?? 0,
    total_teachers: props.stats?.total_teachers ?? 0,
    total_attempts: props.stats?.total_attempts ?? 0,
    global_avg_score: props.stats?.global_avg_score ?? null,
    active_modules: props.stats?.active_modules ?? 0,
    inactive_modules: props.stats?.inactive_modules ?? 0,
}));

const userName = computed(() => page.props?.auth?.user?.name ?? "Admin");

const go = (routeName, params) => {
    try {
        router.visit(route(routeName, params));
    } catch (e) {
        console.warn("Route not found:", routeName);
    }
};

const scoreColor = (v) => {
    if (v === null || v === undefined) return "text-gray-400";
    return v >= 80
        ? "text-blue-600"
        : v >= 60
          ? "text-amber-600"
          : "text-red-500";
};
const scoreBg = (v) => {
    if (v === null || v === undefined) return "bg-gray-100";
    return v >= 80 ? "bg-blue-100" : v >= 60 ? "bg-amber-100" : "bg-red-100";
};
const scoreLabel = (v) => {
    if (v === null || v === undefined) return "Belum ada data";
    return v >= 80 ? "Baik" : v >= 60 ? "Cukup" : "Perlu ditingkatkan";
};
const scoreLabelIcon = (v) => {
    if (v === null || v === undefined) return null;
    return v >= 80 ? "party" : null;
};
const scoreText = (v) => {
    if (v === null || v === undefined) return "-";
    return v >= 80 ? "Baik" : v >= 60 ? "Cukup" : "Kurang";
};

const donutRef = ref(null);
onMounted(() => {
    if (!donutRef.value) return;
    const { low = 0, medium = 0, high = 0 } = props.score_distribution ?? {};
    if (low + medium + high === 0) return;
    new Chart(donutRef.value, {
        type: "doughnut",
        data: {
            labels: ["Perlu Perhatian", "Cukup", "Baik"],
            datasets: [
                {
                    data: [low, medium, high],
                    backgroundColor: [
                        "rgba(239,68,68,0.9)",
                        "rgba(245,158,11,0.9)",
                        "rgba(59,130,246,0.9)",
                    ],
                    borderColor: ["#ef4444", "#f59e0b", "#3b82f6"],
                    borderWidth: 2,
                    hoverOffset: 4,
                },
            ],
        },
        options: {
            responsive: true,
            cutout: "74%",
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: { label: (ctx) => ` ${ctx.label}: ${ctx.raw}` },
                },
            },
        },
    });
});

const hour = new Date().getHours();
const greeting =
    hour < 11
        ? "Selamat Pagi"
        : hour < 15
          ? "Selamat Siang"
          : hour < 18
            ? "Selamat Sore"
            : "Selamat Malam";
</script>

<template>
    <AppLayout>
        <div class="p-5 max-w-7xl mx-auto space-y-5">
            <!-- ── WELCOME BANNER ─────────────────────────────────── -->
            <div
                class="relative bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-700 rounded-2xl p-6 md:p-7 overflow-hidden"
            >
                <div
                    class="absolute -top-8 -right-8 w-40 h-40 bg-white/10 rounded-full pointer-events-none"
                />
                <div
                    class="absolute bottom-0 right-24 w-24 h-24 bg-white/10 rounded-full pointer-events-none"
                />
                <div
                    class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                >
                    <div>
                        <div class="flex items-center gap-1.5 mb-1">
                            <Sparkles class="w-3.5 h-3.5 text-yellow-300" />
                            <span
                                class="text-blue-100 text-xs font-medium tracking-wide"
                                >{{ greeting }}</span
                            >
                        </div>
                        <h1
                            class="text-xl md:text-2xl font-bold text-white flex items-center gap-2"
                        >
                            {{ userName }}
                            <Smile class="w-6 h-6 text-yellow-300" />
                        </h1>
                        <p class="text-blue-100/80 text-sm mt-0.5">
                            Platform belajar Anda berjalan dengan baik.
                        </p>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <button
                            @click="go('admin.modules.index')"
                            class="bg-white text-blue-600 px-4 py-2 rounded-lg font-semibold text-sm hover:bg-blue-50 transition-colors flex items-center gap-1.5 shadow-sm whitespace-nowrap"
                        >
                            <Plus class="w-4 h-4" /> Buat Modul
                        </button>
                        <button
                            @click="go('admin.users.index')"
                            class="bg-white/15 border border-white/30 text-white px-4 py-2 rounded-lg font-semibold text-sm hover:bg-white/25 transition-colors flex items-center gap-1.5 whitespace-nowrap"
                        >
                            <UserPlus class="w-4 h-4" /> Tambah Siswa
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── STAT CARDS ─────────────────────────────────────── -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Modul -->
                <div
                    @click="go('admin.modules.index')"
                    class="group bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-sm p-5 cursor-pointer transition-all flex flex-col gap-0 min-h-[160px]"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div
                            class="bg-blue-100 group-hover:bg-blue-200 w-11 h-11 rounded-xl flex items-center justify-center transition-colors shrink-0"
                        >
                            <BookOpen class="w-5 h-5 text-blue-600" />
                        </div>
                        <ChevronRight
                            class="w-4 h-4 text-gray-300 group-hover:text-blue-400 mt-0.5 transition-colors"
                        />
                    </div>
                    <p
                        class="text-3xl font-bold text-gray-800 tabular-nums leading-none"
                    >
                        {{ s.total_modules }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1.5 mb-0">Total Modul</p>
                    <div class="flex flex-wrap gap-1.5 mt-auto pt-4">
                        <span
                            class="inline-flex items-center gap-1 text-xs bg-green-50 text-green-700 border border-green-200 px-2 py-1 rounded-full font-medium leading-none"
                        >
                            <CheckCircle class="w-3 h-3" />{{
                                s.active_modules
                            }}
                            aktif
                        </span>
                        <span
                            class="inline-flex items-center gap-1 text-xs bg-gray-100 text-gray-500 border border-gray-200 px-2 py-1 rounded-full font-medium leading-none"
                        >
                            <XCircle class="w-3 h-3" />{{ s.inactive_modules }}
                            draft
                        </span>
                    </div>
                </div>

                <!-- Siswa -->
                <div
                    @click="go('admin.users.index')"
                    class="group bg-white rounded-xl border border-gray-200 hover:border-indigo-300 hover:shadow-sm p-5 cursor-pointer transition-all flex flex-col min-h-[160px]"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div
                            class="bg-indigo-100 group-hover:bg-indigo-200 w-11 h-11 rounded-xl flex items-center justify-center transition-colors shrink-0"
                        >
                            <Users class="w-5 h-5 text-indigo-600" />
                        </div>
                        <ChevronRight
                            class="w-4 h-4 text-gray-300 group-hover:text-indigo-400 mt-0.5 transition-colors"
                        />
                    </div>
                    <p
                        class="text-3xl font-bold text-gray-800 tabular-nums leading-none"
                    >
                        {{ s.total_students }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1.5">Total Siswa</p>
                    <div class="flex flex-wrap gap-1.5 mt-auto pt-4">
                        <span
                            class="inline-flex items-center gap-1 text-xs bg-indigo-50 text-indigo-700 border border-indigo-200 px-2 py-1 rounded-full font-medium leading-none"
                        >
                            <GraduationCap class="w-3 h-3" />{{
                                s.total_teachers
                            }}
                            guru
                        </span>
                        <span
                            class="inline-flex items-center gap-1 text-xs bg-purple-50 text-purple-700 border border-purple-200 px-2 py-1 rounded-full font-medium leading-none"
                        >
                            <Layers class="w-3 h-3" />{{ s.total_classes }}
                            kelas
                        </span>
                    </div>
                </div>

                <!-- Pengerjaan -->
                <div
                    @click="go('admin.reports.index')"
                    class="group bg-white rounded-xl border border-gray-200 hover:border-green-300 hover:shadow-sm p-5 cursor-pointer transition-all flex flex-col min-h-[160px]"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div
                            class="bg-green-100 group-hover:bg-green-200 w-11 h-11 rounded-xl flex items-center justify-center transition-colors shrink-0"
                        >
                            <ClipboardList class="w-5 h-5 text-green-600" />
                        </div>
                        <ChevronRight
                            class="w-4 h-4 text-gray-300 group-hover:text-green-400 mt-0.5 transition-colors"
                        />
                    </div>
                    <p
                        class="text-3xl font-bold text-gray-800 tabular-nums leading-none"
                    >
                        {{ s.total_attempts }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1.5">
                        Quiz Diselesaikan
                    </p>
                    <p class="text-xs text-gray-400 mt-auto pt-4 leading-tight">
                        Total pengerjaan oleh semua siswa
                    </p>
                </div>

                <!-- Rata-rata Nilai -->
                <div
                    @click="go('admin.reports.index')"
                    class="group bg-white rounded-xl border border-gray-200 hover:border-amber-300 hover:shadow-sm p-5 cursor-pointer transition-all flex flex-col min-h-[160px]"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div
                            class="bg-amber-100 group-hover:bg-amber-200 w-11 h-11 rounded-xl flex items-center justify-center transition-colors shrink-0"
                        >
                            <TrendingUp class="w-5 h-5 text-amber-600" />
                        </div>
                        <ChevronRight
                            class="w-4 h-4 text-gray-300 group-hover:text-amber-400 mt-0.5 transition-colors"
                        />
                    </div>
                    <p
                        class="text-3xl font-bold tabular-nums leading-none"
                        :class="scoreColor(s.global_avg_score)"
                    >
                        {{ s.global_avg_score ?? "—" }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1.5">Rata-rata Nilai</p>
                    <p
                        class="text-xs font-semibold mt-auto pt-4 leading-tight flex items-center gap-1"
                        :class="scoreColor(s.global_avg_score)"
                    >
                        {{ scoreLabel(s.global_avg_score) }}
                        <PartyPopper
                            v-if="
                                scoreLabelIcon(s.global_avg_score) === 'party'
                            "
                            class="w-3.5 h-3.5"
                        />
                    </p>
                </div>
            </div>

            <!-- ── SHORTCUT PILLS ─────────────────────────────────── -->
            <div class="grid grid-cols-4 gap-3">
                <button
                    @click="go('admin.modules.index')"
                    class="bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-sm p-4 flex items-center gap-3 transition-all text-left"
                >
                    <div
                        class="bg-blue-100 w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                    >
                        <Flag class="w-4 h-4 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 leading-none mb-1">
                            Misi
                        </p>
                        <p
                            class="text-xl font-bold text-gray-800 tabular-nums leading-none"
                        >
                            {{ s.total_missions }}
                        </p>
                    </div>
                </button>
                <button
                    @click="go('admin.modules.index')"
                    class="bg-white rounded-xl border border-gray-200 hover:border-orange-300 hover:shadow-sm p-4 flex items-center gap-3 transition-all text-left"
                >
                    <div
                        class="bg-orange-100 w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                    >
                        <ClipboardList class="w-4 h-4 text-orange-600" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 leading-none mb-1">
                            Quiz
                        </p>
                        <p
                            class="text-xl font-bold text-gray-800 tabular-nums leading-none"
                        >
                            {{ s.total_quizzes }}
                        </p>
                    </div>
                </button>
                <button
                    @click="go('admin.modules.index')"
                    class="bg-white rounded-xl border border-gray-200 hover:border-teal-300 hover:shadow-sm p-4 flex items-center gap-3 transition-all text-left"
                >
                    <div
                        class="bg-teal-100 w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                    >
                        <FileText class="w-4 h-4 text-teal-600" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 leading-none mb-1">
                            Materi
                        </p>
                        <p
                            class="text-xl font-bold text-gray-800 tabular-nums leading-none"
                        >
                            {{ s.total_materials }}
                        </p>
                    </div>
                </button>
                <button
                    @click="go('admin.templates.index')"
                    class="bg-white rounded-xl border border-gray-200 hover:border-pink-300 hover:shadow-sm p-4 flex items-center gap-3 transition-all text-left"
                >
                    <div
                        class="bg-pink-100 w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                    >
                        <LayoutTemplate class="w-4 h-4 text-pink-600" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 leading-none mb-1">
                            Template
                        </p>
                        <p
                            class="text-xl font-bold text-gray-800 tabular-nums leading-none"
                        >
                            {{ s.total_templates }}
                        </p>
                    </div>
                </button>
            </div>

            <!-- ── MAIN GRID ───────────────────────────────────────── -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
                <!-- Modul Terbaru — 2/3 -->
                <div class="lg:col-span-2 flex flex-col gap-2.5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-gray-700">
                            Modul Terbaru
                        </h2>
                        <button
                            @click="go('admin.modules.index')"
                            class="text-xs text-blue-500 font-medium flex items-center gap-0.5 hover:text-blue-700"
                        >
                            Lihat semua <ChevronRight class="w-3.5 h-3.5" />
                        </button>
                    </div>

                    <template v-if="recent_modules.length > 0">
                        <div
                            v-for="m in recent_modules"
                            :key="m.id"
                            @click="go('admin.modules.show', m.id)"
                            class="group bg-white rounded-xl border border-gray-200 hover:border-blue-200 hover:shadow-sm p-3.5 cursor-pointer transition-all flex items-center gap-3"
                        >
                            <div
                                class="bg-blue-50 w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
                            >
                                <BookOpen class="w-4 h-4 text-blue-500" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p
                                    class="font-semibold text-gray-800 text-sm truncate leading-tight"
                                >
                                    {{ m.name }}
                                </p>
                                <div
                                    class="flex items-center gap-2 mt-0.5 flex-wrap"
                                >
                                    <span
                                        class="text-xs text-gray-400 flex items-center gap-0.5"
                                    >
                                        <Flag class="w-2.5 h-2.5" />{{
                                            m.missions_count
                                        }}
                                        misi
                                    </span>
                                    <span class="text-gray-300">·</span>
                                    <span
                                        class="text-xs text-gray-400 flex items-center gap-0.5"
                                    >
                                        <Users class="w-2.5 h-2.5" />{{
                                            m.students_done
                                        }}
                                        siswa
                                    </span>
                                    <span class="text-gray-300">·</span>
                                    <span class="text-xs text-gray-400">{{
                                        m.created_at
                                    }}</span>
                                </div>
                            </div>
                            <div class="shrink-0 flex items-center gap-1.5">
                                <span
                                    class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                    :class="
                                        m.is_active
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-gray-100 text-gray-500'
                                    "
                                >
                                    {{ m.is_active ? "Aktif" : "Draft" }}
                                </span>
                                <button
                                    @click.stop="go('admin.modules.index')"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 border border-amber-200 opacity-0 group-hover:opacity-100 transition-all"
                                >
                                    <Pencil class="w-3 h-3" />
                                </button>
                            </div>
                        </div>
                    </template>

                    <div
                        v-else
                        class="bg-white rounded-xl border border-dashed border-gray-300 p-8 text-center"
                    >
                        <BookOpen class="w-8 h-8 text-gray-200 mx-auto mb-2" />
                        <p class="text-gray-400 text-sm mb-3">
                            Belum ada modul. Yuk buat yang pertama!
                        </p>
                        <button
                            @click="go('admin.modules.index')"
                            class="bg-blue-500 text-white px-4 py-1.5 rounded-lg text-sm font-semibold hover:bg-blue-600 transition-colors"
                        >
                            + Buat Modul
                        </button>
                    </div>
                </div>

                <!-- Kolom kanan — 1/3 -->
                <div class="flex flex-col gap-4">
                    <!-- Distribusi Nilai -->
                    <div class="bg-white rounded-xl border border-gray-200 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h2 class="text-sm font-bold text-gray-700">
                                    Distribusi Nilai
                                </h2>
                                <p class="text-xs text-gray-400">
                                    Semua pengerjaan
                                </p>
                            </div>
                        </div>

                        <template
                            v-if="
                                (score_distribution?.low ?? 0) +
                                    (score_distribution?.medium ?? 0) +
                                    (score_distribution?.high ?? 0) >
                                0
                            "
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="shrink-0"
                                    style="width: 80px; height: 80px"
                                >
                                    <canvas ref="donutRef" />
                                </div>
                                <div class="flex-1 space-y-1.5">
                                    <div
                                        v-for="item in [
                                            {
                                                label: 'Baik',
                                                key: 'high',
                                                dot: 'bg-blue-500',
                                                val: 'text-blue-600',
                                            },
                                            {
                                                label: 'Cukup',
                                                key: 'medium',
                                                dot: 'bg-amber-400',
                                                val: 'text-amber-600',
                                            },
                                            {
                                                label: 'Kurang',
                                                key: 'low',
                                                dot: 'bg-red-400',
                                                val: 'text-red-500',
                                            },
                                        ]"
                                        :key="item.key"
                                        class="flex items-center justify-between"
                                    >
                                        <div class="flex items-center gap-1.5">
                                            <div
                                                :class="item.dot"
                                                class="w-2 h-2 rounded-full shrink-0"
                                            />
                                            <span
                                                class="text-xs text-gray-600"
                                                >{{ item.label }}</span
                                            >
                                        </div>
                                        <span
                                            class="text-xs font-bold tabular-nums"
                                            :class="item.val"
                                        >
                                            {{
                                                score_distribution?.[
                                                    item.key
                                                ] ?? 0
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div
                            v-else
                            class="flex flex-col items-center py-5 gap-1.5"
                        >
                            <ClipboardList class="w-7 h-7 text-gray-200" />
                            <p class="text-xs text-gray-400">Belum ada data</p>
                        </div>
                    </div>

                    <!-- Siswa Terbaru -->
                    <div class="bg-white rounded-xl border border-gray-200 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-sm font-bold text-gray-700">
                                Siswa Terbaru
                            </h2>
                            <button
                                @click="go('admin.users.index')"
                                class="text-xs text-blue-500 font-medium flex items-center gap-0.5 hover:text-blue-700"
                            >
                                Semua <ChevronRight class="w-3.5 h-3.5" />
                            </button>
                        </div>
                        <div
                            v-if="recent_students.length > 0"
                            class="space-y-2.5"
                        >
                            <div
                                v-for="st in recent_students"
                                :key="st.id"
                                class="flex items-center gap-2.5"
                            >
                                <div
                                    class="bg-indigo-100 w-8 h-8 rounded-lg flex items-center justify-center shrink-0 font-bold text-indigo-600 text-xs leading-none"
                                >
                                    {{
                                        st.name?.charAt(0)?.toUpperCase() ?? "?"
                                    }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p
                                        class="text-sm font-semibold text-gray-800 truncate leading-tight"
                                    >
                                        {{ st.name }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        Kelas {{ st.class }} ·
                                        {{ st.created_at }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="flex flex-col items-center py-5 gap-1.5"
                        >
                            <Users class="w-7 h-7 text-gray-200" />
                            <p class="text-xs text-gray-400">
                                Belum ada siswa terdaftar
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── RECENT ATTEMPTS ────────────────────────────────── -->
            <div>
                <div class="flex items-center justify-between mb-2.5">
                    <h2 class="text-sm font-bold text-gray-700">
                        Quiz yang Baru Diselesaikan
                    </h2>
                    <button
                        @click="go('admin.reports.index')"
                        class="text-xs text-blue-500 font-medium flex items-center gap-0.5 hover:text-blue-700"
                    >
                        Lihat laporan <ChevronRight class="w-3.5 h-3.5" />
                    </button>
                </div>

                <div
                    class="bg-white rounded-xl border border-gray-200 overflow-hidden"
                >
                    <template v-if="recent_attempts.length > 0">
                        <div class="divide-y divide-gray-100">
                            <div
                                v-for="(a, i) in recent_attempts"
                                :key="i"
                                class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors"
                            >
                                <div
                                    class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                                    :class="scoreBg(a.score)"
                                >
                                    <Star
                                        v-if="a.score >= 80"
                                        class="w-4 h-4 text-blue-600"
                                    />
                                    <CheckCircle
                                        v-else-if="a.score >= 60"
                                        class="w-4 h-4 text-amber-600"
                                    />
                                    <AlertTriangle
                                        v-else
                                        class="w-4 h-4 text-red-500"
                                    />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p
                                        class="text-sm font-semibold text-gray-800 truncate leading-tight"
                                    >
                                        {{ a.student_name }}
                                    </p>
                                    <p
                                        class="text-xs text-gray-400 truncate leading-tight"
                                    >
                                        {{ a.quiz_title }} · {{ a.finished_at }}
                                    </p>
                                </div>
                                <div
                                    class="shrink-0 flex items-center gap-2 justify-end"
                                >
                                    <span
                                        class="text-sm font-bold tabular-nums"
                                        :class="scoreColor(a.score)"
                                        >{{ a.score }}</span
                                    >
                                    <span
                                        class="text-xs font-semibold px-2.5 py-0.5 rounded-full whitespace-nowrap"
                                        :class="[
                                            scoreBg(a.score),
                                            scoreColor(a.score),
                                        ]"
                                    >
                                        {{ scoreText(a.score) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div v-else class="flex flex-col items-center py-10 gap-2">
                        <ClipboardList class="w-9 h-9 text-gray-200" />
                        <p class="font-semibold text-gray-500 text-sm">
                            Belum ada pengerjaan quiz
                        </p>
                        <p class="text-xs text-gray-400">
                            Quiz yang diselesaikan siswa akan muncul di sini
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
