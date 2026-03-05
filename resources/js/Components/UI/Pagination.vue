<script setup>
import { computed } from "vue";
import { ChevronLeft, ChevronRight } from "lucide-vue-next";

const props = defineProps({
    /**
     * Object pagination dari Laravel (misal: props.templates.links / meta)
     * Bisa juga terima { current_page, last_page, per_page, total } langsung
     */
    meta: {
        type: Object,
        required: true,
        // { current_page, last_page, per_page, total, from, to }
    },
    /**
     * Warna aksen: 'blue' | 'green' | 'yellow' | 'purple' | 'pink'
     */
    color: {
        type: String,
        default: "blue",
    },
});

const emit = defineEmits(["change"]);

// ── Colour map ────────────────────────────────────────────────────────────────
const colorMap = {
    blue: {
        active: "bg-blue-500 border-blue-600 text-white shadow-[0_4px_0_#1d4ed8]",
        hover: "hover:bg-blue-50 hover:border-blue-300 hover:text-blue-600",
        nav: "bg-blue-100 border-blue-300 text-blue-600 hover:bg-blue-200 shadow-[0_4px_0_#93c5fd]",
        navDisabled:
            "bg-gray-100 border-gray-200 text-gray-300 cursor-not-allowed shadow-none",
        info: "text-blue-500",
    },
    green: {
        active: "bg-green-500 border-green-600 text-white shadow-[0_4px_0_#15803d]",
        hover: "hover:bg-green-50 hover:border-green-300 hover:text-green-600",
        nav: "bg-green-100 border-green-300 text-green-600 hover:bg-green-200 shadow-[0_4px_0_#86efac]",
        navDisabled:
            "bg-gray-100 border-gray-200 text-gray-300 cursor-not-allowed shadow-none",
        info: "text-green-500",
    },
    yellow: {
        active: "bg-yellow-400 border-yellow-500 text-white shadow-[0_4px_0_#ca8a04]",
        hover: "hover:bg-yellow-50 hover:border-yellow-300 hover:text-yellow-600",
        nav: "bg-yellow-100 border-yellow-300 text-yellow-600 hover:bg-yellow-200 shadow-[0_4px_0_#fde68a]",
        navDisabled:
            "bg-gray-100 border-gray-200 text-gray-300 cursor-not-allowed shadow-none",
        info: "text-yellow-500",
    },
    purple: {
        active: "bg-purple-500 border-purple-600 text-white shadow-[0_4px_0_#6d28d9]",
        hover: "hover:bg-purple-50 hover:border-purple-300 hover:text-purple-600",
        nav: "bg-purple-100 border-purple-300 text-purple-600 hover:bg-purple-200 shadow-[0_4px_0_#c4b5fd]",
        navDisabled:
            "bg-gray-100 border-gray-200 text-gray-300 cursor-not-allowed shadow-none",
        info: "text-purple-500",
    },
    pink: {
        active: "bg-pink-500 border-pink-600 text-white shadow-[0_4px_0_#be185d]",
        hover: "hover:bg-pink-50 hover:border-pink-300 hover:text-pink-600",
        nav: "bg-pink-100 border-pink-300 text-pink-600 hover:bg-pink-200 shadow-[0_4px_0_#f9a8d4]",
        navDisabled:
            "bg-gray-100 border-gray-200 text-gray-300 cursor-not-allowed shadow-none",
        info: "text-pink-500",
    },
};

const c = computed(() => colorMap[props.color] ?? colorMap.blue);

// ── Page list dengan ellipsis ─────────────────────────────────────────────────
const pages = computed(() => {
    const current = props.meta.current_page;
    const last = props.meta.last_page;
    if (last <= 1) return [];

    const range = [];
    const delta = 2; // jumlah halaman di kiri/kanan current

    for (let i = 1; i <= last; i++) {
        if (
            i === 1 ||
            i === last ||
            (i >= current - delta && i <= current + delta)
        ) {
            range.push(i);
        }
    }

    // Sisipkan ellipsis
    const withGaps = [];
    let prev = null;
    for (const page of range) {
        if (prev !== null && page - prev > 1) {
            withGaps.push("...");
        }
        withGaps.push(page);
        prev = page;
    }
    return withGaps;
});

const goTo = (page) => {
    if (page === "..." || page === props.meta.current_page) return;
    emit("change", page);
};

const prev = () => {
    if (props.meta.current_page > 1)
        emit("change", props.meta.current_page - 1);
};

const next = () => {
    if (props.meta.current_page < props.meta.last_page)
        emit("change", props.meta.current_page + 1);
};
</script>

<template>
    <div
        v-if="meta.last_page > 1"
        class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-8 select-none"
    >
        <!-- Info -->
        <p class="text-sm text-gray-500 font-medium">
            Menampilkan
            <span :class="['font-bold', c.info]">{{ meta.from ?? 0 }}</span>
            –
            <span :class="['font-bold', c.info]">{{ meta.to ?? 0 }}</span>
            dari
            <span :class="['font-bold', c.info]">{{ meta.total }}</span>
            data
        </p>

        <!-- Tombol -->
        <div class="flex items-center gap-2">
            <!-- Prev -->
            <button
                :class="[
                    'flex items-center justify-center w-10 h-10 rounded-2xl border-2 font-bold transition-all active:translate-y-1 active:shadow-none',
                    meta.current_page === 1 ? c.navDisabled : c.nav,
                ]"
                :disabled="meta.current_page === 1"
                @click="prev"
                aria-label="Halaman sebelumnya"
            >
                <ChevronLeft class="w-4 h-4" />
            </button>

            <!-- Nomor halaman -->
            <template v-for="(p, i) in pages" :key="i">
                <!-- Ellipsis -->
                <span
                    v-if="p === '...'"
                    class="w-10 h-10 flex items-center justify-center text-gray-400 font-bold text-lg"
                >
                    ···
                </span>

                <!-- Nomor -->
                <button
                    v-else
                    :class="[
                        'w-10 h-10 rounded-2xl border-2 font-bold text-sm transition-all active:translate-y-1 active:shadow-none',
                        p === meta.current_page
                            ? c.active
                            : [
                                  'bg-white border-gray-200 text-gray-600',
                                  c.hover,
                              ],
                    ]"
                    @click="goTo(p)"
                    :aria-current="p === meta.current_page ? 'page' : undefined"
                >
                    {{ p }}
                </button>
            </template>

            <!-- Next -->
            <button
                :class="[
                    'flex items-center justify-center w-10 h-10 rounded-2xl border-2 font-bold transition-all active:translate-y-1 active:shadow-none',
                    meta.current_page === meta.last_page
                        ? c.navDisabled
                        : c.nav,
                ]"
                :disabled="meta.current_page === meta.last_page"
                @click="next"
                aria-label="Halaman berikutnya"
            >
                <ChevronRight class="w-4 h-4" />
            </button>
        </div>
    </div>
</template>
