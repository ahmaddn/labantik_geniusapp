<script setup>
import { ref, computed } from "vue";

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
        // Format: [{ name: 'edit', icon: 'pi-pencil', class: 'bg-yellow-400 border-yellow-500' }, ...]
    },
    emptyMessage: {
        type: String,
        default: "Belum ada data.",
    },
    showPerPageControl: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(["action"]);

const currentPage = ref(1);
const perPage = ref(props.initialPerPage);
const sortField = ref(null);
const sortDirection = ref("asc");

const sort = (field) => {
    if (!field) return;

    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
    } else {
        sortField.value = field;
        sortDirection.value = "asc";
    }
};

const sortedData = computed(() => {
    if (!sortField.value) return props.data;

    return [...props.data].sort((a, b) => {
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

const handleAction = (actionName, row) => {
    emit("action", { action: actionName, data: row });
};
</script>

<template>
    <!-- Combined Toolbar and Table -->
    <div
        class="bg-white rounded-3xl border-4 border-blue-200 shadow-playful overflow-hidden"
    >
        <!-- Toolbar Per Page Control -->
        <div
            v-if="showPerPageControl"
            class="flex justify-end p-4 border-b-4 border-blue-200"
        >
            <div
                class="flex items-center gap-2 bg-blue-50 px-4 py-2 rounded-2xl border-2 border-blue-200"
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
                            <i
                                v-if="
                                    column.sortable && sortField === column.key
                                "
                                :class="
                                    sortDirection === 'asc'
                                        ? 'pi pi-sort-up'
                                        : 'pi pi-sort-down'
                                "
                                class="ml-2 text-blue-600"
                            ></i>
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
                                {{ row[column.key] }}
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
                                    <i
                                        v-if="action.icon"
                                        :class="['pi', action.icon]"
                                    ></i>
                                    <span v-if="action.label && !action.icon">{{
                                        action.label
                                    }}</span>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="paginatedData.length === 0">
                        <td
                            :colspan="
                                columns.length + (actions.length > 0 ? 1 : 0)
                            "
                            class="p-6 text-center text-gray-500 font-medium"
                        >
                            {{ emptyMessage }}
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
            </div>

            <!-- Pagination Buttons -->
            <div class="flex items-center gap-2">
                <button
                    @click="changePage(currentPage - 1)"
                    :disabled="currentPage === 1"
                    class="px-4 py-2 rounded-xl border-4 border-blue-300 font-bold hover:bg-blue-100 transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <i class="pi pi-chevron-left"></i>
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
                    <i class="pi pi-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom Scrollbar Styling */
.custom-scrollbar::-webkit-scrollbar {
    width: 12px;
    height: 12px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #dbeafe; /* bg-blue-100 */
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #3b82f6; /* bg-blue-500 */
    border-radius: 10px;
    border: 2px solid #dbeafe; /* border with track color */
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #2563eb; /* bg-blue-600 */
}

.custom-scrollbar::-webkit-scrollbar-corner {
    background: #dbeafe;
}

/* Firefox */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #3b82f6 #dbeafe;
}
</style>
