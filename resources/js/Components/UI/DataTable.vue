<script setup>
import { ref, computed } from "vue";
import {
    ChevronUp,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    Search,
} from "lucide-vue-next";

const props = defineProps({
    columns: {
        type: Array,
        required: true,
        // Format: [{ key: 'name', label: 'Nama', sortable: true }, ...]
    },
    data: {
        type: Array,
        required: true,
    },
    perPageOptions: {
        type: Array,
        default: () => [5, 10, 15, 20],
    },
    initialPerPage: {
        type: Number,
        default: 5,
    },
    actions: {
        type: Array,
        default: () => [],
        // Format: [{ name: 'edit', icon: IconComponent, class: 'bg-yellow-400 border-yellow-500' }, ...]
    },
    emptyMessage: {
        type: String,
        default: "Belum ada data.",
    },
    showPerPageControl: {
        type: Boolean,
        default: true,
    },
    searchPlaceholder: {
        type: String,
        default: "Cari data...",
    },
});

const emit = defineEmits(["action"]);

const currentPage = ref(1);
const perPage = ref(props.initialPerPage);
const sortField = ref(null);
const sortDirection = ref("asc");
const searchQuery = ref("");

const sort = (field) => {
    if (!field) return;
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
    } else {
        sortField.value = field;
        sortDirection.value = "asc";
    }
};

// Filter data berdasarkan search query — cari di semua kolom
const filteredData = computed(() => {
    if (!searchQuery.value.trim()) return props.data;

    const query = searchQuery.value.toLowerCase();
    return props.data.filter((row) =>
        props.columns.some((col) => {
            const val = row[col.key];
            return val != null && String(val).toLowerCase().includes(query);
        }),
    );
});

const sortedData = computed(() => {
    if (!sortField.value) return filteredData.value;

    return [...filteredData.value].sort((a, b) => {
        const aVal = a[sortField.value];
        const bVal = b[sortField.value];
        if (aVal < bVal) return sortDirection.value === "asc" ? -1 : 1;
        if (aVal > bVal) return sortDirection.value === "asc" ? 1 : -1;
        return 0;
    });
});

const totalPages = computed(() =>
    Math.ceil(sortedData.value.length / perPage.value),
);

const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    return sortedData.value.slice(start, start + perPage.value);
});

const changePage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const changePerPage = (value) => {
    perPage.value = value;
    currentPage.value = 1;
};

// Reset ke halaman 1 setiap kali search berubah
const handleSearch = () => {
    currentPage.value = 1;
};

const handleAction = (actionName, row) => {
    emit("action", { action: actionName, data: row });
};
</script>

<template>
    <!-- Combined Toolbar and Table -->
    <div
        class="bg-white rounded-3xl border-4 border-blue-200 shadow-playful overflow-hidden"
    >
        <!-- Toolbar -->
        <div
            v-if="showPerPageControl"
            class="flex flex-col sm:flex-row justify-between items-center gap-3 p-4 border-b-4 border-blue-200"
        >
            <!-- Search -->
            <div class="relative w-full sm:w-auto sm:min-w-[260px]">
                <Search
                    :size="16"
                    class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
                />
                <input
                    v-model="searchQuery"
                    @input="handleSearch"
                    type="text"
                    :placeholder="searchPlaceholder"
                    class="w-full pl-10 pr-4 py-2 rounded-2xl border-4 border-blue-200 focus:border-blue-400 outline-none font-medium text-gray-700 bg-blue-50 placeholder-gray-400 transition-colors"
                />
            </div>

            <!-- Per Page -->
            <div
                class="flex items-center gap-2 bg-blue-50 px-4 py-2 rounded-2xl border-2 border-blue-200 shrink-0"
            >
                <label
                    for="perPage"
                    class="text-sm font-bold text-gray-700 whitespace-nowrap"
                >
                    Tampilkan:
                </label>
                <select
                    id="perPage"
                    v-model="perPage"
                    @change="changePerPage(perPage)"
                    class="px-3 py-1.5 rounded-lg border-2 border-blue-300 focus:border-blue-500 outline-none font-bold text-gray-700 bg-white cursor-pointer min-w-[70px]"
                >
                    <option
                        v-for="option in perPageOptions"
                        :key="option"
                        :value="option"
                    >
                        {{ option }}
                    </option>
                </select>
                <span class="text-sm font-medium text-gray-600">data</span>
            </div>
        </div>

        <!-- Table with Scroll -->
        <div class="overflow-auto max-h-[600px] custom-scrollbar">
            <table class="w-full text-left">
                <thead class="bg-blue-100 sticky top-0 z-10">
                    <tr>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            :class="[
                                'p-4 font-bold text-gray-800 transition',
                                column.sortable
                                    ? 'cursor-pointer hover:bg-blue-200'
                                    : '',
                                column.align === 'center' ? 'text-center' : '',
                            ]"
                            @click="column.sortable ? sort(column.key) : null"
                        >
                            {{ column.label }}
                            <component
                                v-if="
                                    column.sortable && sortField === column.key
                                "
                                :is="
                                    sortDirection === 'asc'
                                        ? ChevronUp
                                        : ChevronDown
                                "
                                :size="16"
                                class="ml-2 text-blue-600 inline"
                            />
                        </th>
                        <th
                            v-if="actions.length > 0"
                            class="p-4 text-center font-bold text-gray-800"
                        >
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="row in paginatedData"
                        :key="row.id"
                        class="border-t border-gray-200 hover:bg-blue-50 transition"
                    >
                        <td
                            v-for="column in columns"
                            :key="column.key"
                            :class="[
                                'p-4 text-gray-700',
                                column.key === columns[0].key
                                    ? 'font-semibold'
                                    : '',
                                column.align === 'center' ? 'text-center' : '',
                            ]"
                        >
                            <slot :name="`cell-${column.key}`" :row="row">
                                <!-- Kalau ada formatter, gunakan formatter, kalau tidak tampilkan biasa -->
                                {{
                                    column.formatter
                                        ? column.formatter(row[column.key], row)
                                        : row[column.key]
                                }}
                            </slot>
                        </td>

                        <td v-if="actions.length > 0" class="p-4 text-center">
                            <div class="flex justify-center gap-2">
                                <button
                                    v-for="action in actions"
                                    :key="action.name"
                                    @click="handleAction(action.name, row)"
                                    :class="[
                                        'text-white px-4 py-2 rounded-xl border-4 font-bold hover:scale-105 transition-transform',
                                        action.class,
                                    ]"
                                    :title="action.label"
                                >
                                    <component
                                        v-if="action.icon"
                                        :is="action.icon"
                                        :size="16"
                                    />
                                    <span v-if="action.label && !action.icon">{{
                                        action.label
                                    }}</span>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Empty state: tidak ada data sama sekali -->
                    <tr v-if="paginatedData.length === 0 && !searchQuery">
                        <td
                            :colspan="
                                columns.length + (actions.length > 0 ? 1 : 0)
                            "
                            class="p-6 text-center text-gray-500 font-medium"
                        >
                            {{ emptyMessage }}
                        </td>
                    </tr>

                    <!-- Empty state: hasil pencarian kosong -->
                    <tr v-if="paginatedData.length === 0 && searchQuery">
                        <td
                            :colspan="
                                columns.length + (actions.length > 0 ? 1 : 0)
                            "
                            class="p-8 text-center"
                        >
                            <div class="flex flex-col items-center gap-2">
                                <Search :size="32" class="text-gray-300" />
                                <p class="font-bold text-gray-500">
                                    Tidak ada hasil
                                </p>
                                <p class="text-sm text-gray-400">
                                    Tidak ditemukan data untuk
                                    <span class="font-semibold text-blue-500"
                                        >"{{ searchQuery }}"</span
                                    >
                                </p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div
            v-if="totalPages > 0 && paginatedData.length > 0"
            class="flex flex-col sm:flex-row justify-between items-center gap-4 py-4 px-4 border-t-4 border-blue-200"
        >
            <!-- Info -->
            <div class="text-sm text-gray-600 font-medium">
                Menampilkan
                <span class="font-bold text-gray-800">{{
                    (currentPage - 1) * perPage + 1
                }}</span>
                sampai
                <span class="font-bold text-gray-800">{{
                    Math.min(currentPage * perPage, sortedData.length)
                }}</span>
                dari
                <span class="font-bold text-gray-800">{{
                    sortedData.length
                }}</span>
                data
                <span v-if="searchQuery" class="text-blue-500">
                    (difilter dari {{ data.length }} total)
                </span>
            </div>

            <!-- Pagination Buttons -->
            <div class="flex items-center gap-2">
                <button
                    @click="changePage(currentPage - 1)"
                    :disabled="currentPage === 1"
                    class="px-4 py-2 rounded-xl border-4 border-blue-300 font-bold hover:bg-blue-100 transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <ChevronLeft :size="16" />
                </button>

                <button
                    v-for="page in totalPages"
                    :key="page"
                    @click="changePage(page)"
                    :class="[
                        'px-4 py-2 rounded-xl border-4 font-bold transition',
                        currentPage === page
                            ? 'bg-blue-500 text-white border-blue-600'
                            : 'border-blue-300 hover:bg-blue-100',
                    ]"
                >
                    {{ page }}
                </button>

                <button
                    @click="changePage(currentPage + 1)"
                    :disabled="currentPage === totalPages"
                    class="px-4 py-2 rounded-xl border-4 border-blue-300 font-bold hover:bg-blue-100 transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <ChevronRight :size="16" />
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 12px;
    height: 12px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #dbeafe;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #3b82f6;
    border-radius: 10px;
    border: 2px solid #dbeafe;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #2563eb;
}

.custom-scrollbar::-webkit-scrollbar-corner {
    background: #dbeafe;
}

.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #3b82f6 #dbeafe;
}
</style>
