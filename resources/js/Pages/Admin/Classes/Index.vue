<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch, onMounted, onUnmounted, computed } from "vue";
import { usePage, useForm, router } from "@inertiajs/vue3";
import Modal from "@/Components/UI/Modal.vue";
import ConfirmDialog from "@/Components/UI/ConfirmDialog.vue";
import Toast from "@/Components/UI/Toast.vue";
import InputField from "@/Components/UI/Forms/InputField.vue";
import TextareaField from "@/Components/UI/Forms/TextAreaField.vue";
import Button from "@/Components/UI/Button.vue";
import Card from "@/Components/UI/Card.vue";
import Pagination from "@/Components/UI/Pagination.vue";
import {
    BookOpen,
    Star,
    Plus,
    Pencil,
    Trash2,
    Save,
    UserKey,
    PackageOpen,
    Loader2,
} from "lucide-vue-next";
import SelectField from "@/Components/UI/Forms/SelectField.vue";

const props = defineProps({
    /**
     * Laravel paginator: { data, current_page, last_page, per_page, total, from, to }
     * Controller: Class::paginate(12)
     */
    classes: Object,
    teachers: Array,
});

const page = usePage();

const showDialog = ref(false);
const showDeleteDialog = ref(false);
const isEdit = ref(false);
const selectedId = ref(null);
const successMessage = ref("");
const showSuccess = ref(false);
const cardVariant = ref("playful");
const toastType = ref("success");
const editingClass = ref(null);
const editId = ref(null);
const form = useForm({
    name: "",
    description: "",
    teacher_id: "",
});

const showToast = (message, type = "success") => {
    successMessage.value = message;
    toastType.value = type;
    showSuccess.value = true;
    setTimeout(() => (showSuccess.value = false), 2500);
};

const openCreate = () => {
    isEdit.value = false;
    editingClass.value = null;
    form.reset();
    showDialog.value = true;
};

const openEdit = (classItem) => {
    isEdit.value = true;
    editId.value = classItem.id;
    editingClass.value = classItem;
    form.name = classItem.name;
    form.description = classItem.description || "";
    form.teacher_id = classItem.teacher_id || "";
    showDialog.value = true;
};

const confirmDelete = (id) => {
    selectedId.value = id;
    showDeleteDialog.value = true;
};

const getFirstError = (errors) => {
    return Object.values(errors)[0] ?? "Terjadi kesalahan.";
};

const saveClass = () => {
    if (!form.name.trim() || !form.teacher_id || !form.description) {
        showToast("Semua kolom wajib diisi.", "error");
        return;
    }
    const options = {
        onError: (errors) => showToast(getFirstError(errors), "error"),
        onSuccess: () => {
            showDialog.value = false;
            form.reset();
        },
    };

    if (isEdit.value) {
        form.put(route("admin.classes.update", editId.value), options);
    } else {
        form.post(route("admin.classes.store"), options);
    }
};

const deleteClass = () => {
    form.delete(route("admin.classes.destroy", selectedId.value), {
        onError: (errors) => showToast(getFirstError(errors), "error"),
        onSuccess: () => {
            showDeleteDialog.value = false;
        },
    });
};

const availableTeachers = computed(() => {
    if (!isEdit.value || !editingClass.value?.teacher) {
        return props.teachers;
    }

    const currentTeacher = editingClass.value.teacher;
    const alreadyInList = props.teachers.some(
        (t) => t.id === currentTeacher.id,
    );
    if (!alreadyInList) {
        return [currentTeacher, ...props.teachers];
    }

    return props.teachers;
});

const toggleCardVariant = () => {
    cardVariant.value = cardVariant.value === "playful" ? "normal" : "playful";
};

// ── Pagination ────────────────────────────────────────────────────────────────
const handlePageChange = (pageNumber) => {
    router.get(
        route("admin.classes.index"),
        { page: pageNumber },
        { preserveScroll: true, preserveState: true },
    );
};

watch(showDialog, (val) => lockScroll(val));
watch(showDeleteDialog, (val) => lockScroll(val));
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) showToast(flash.success, "success");
        if (flash?.error) showToast(flash.error, "error");
        if (flash?.message) showToast(flash.message, "info");
    },
);

const lockScroll = (state) => {
    document.body.style.overflow = state ? "hidden" : "";
};

const handleEsc = (e) => {
    if (e.key === "Escape") {
        showDialog.value = false;
        showDeleteDialog.value = false;
    }
};

onMounted(() => {
    window.addEventListener("keydown", handleEsc);
});

onUnmounted(() => {
    window.removeEventListener("keydown", handleEsc);
});
</script>

<template>
    <AppLayout>
        <div class="p-5">
            <!-- Toolbar Header -->
            <div
                class="bg-white rounded-3xl border-4 border-blue-200 shadow-playful p-5 mb-8"
            >
                <div
                    class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
                >
                    <!-- Kiri -->
                    <div class="flex items-center gap-4">
                        <div
                            class="bg-blue-100 p-3 rounded-2xl border-2 border-blue-300"
                        >
                            <BookOpen class="text-blue-600 w-6 h-6" />
                        </div>

                        <div>
                            <h1
                                class="text-2xl md:text-3xl font-heading font-bold text-gray-800"
                            >
                                Daftar Kelas
                            </h1>
                            <p class="text-sm text-gray-500">
                                Kelola dan atur data kelas dengan mudah
                            </p>
                        </div>
                    </div>

                    <!-- Kanan -->
                    <div class="flex items-center gap-3">
                        <Button
                            :variant="
                                cardVariant === 'playful' ? 'warning' : 'light'
                            "
                            size="md"
                            :icon="Star"
                            @click="toggleCardVariant"
                        >
                            {{
                                cardVariant === "playful" ? "Playful" : "Normal"
                            }}
                        </Button>

                        <Button
                            variant="primary"
                            size="lg"
                            :icon="Plus"
                            @click="openCreate"
                        >
                            Tambah Kelas
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Grid menggunakan Card -->
            <TransitionGroup
                v-if="props.classes?.data?.length > 0"
                name="card"
                tag="div"
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"
            >
                <Card
                    v-for="classItem in props.classes.data"
                    :key="classItem.id"
                    :variant="cardVariant"
                    :title="classItem.name"
                    :subtitle="classItem.description"
                    :icon="BookOpen"
                    icon-color="blue"
                    border-color="blue"
                >
                    <template #default>
                        <div class="flex items-center gap-1.5 mt-2">
                            <UserKey
                                class="w-3.5 h-3.5 text-gray-400 flex-shrink-0"
                            />
                            <span class="text-xs text-gray-400 truncate">
                                {{
                                    classItem.teacher?.name ??
                                    "Belum ada Wali Kelas"
                                }}
                            </span>
                        </div>
                    </template>
                    <template #footer>
                        <div class="flex justify-end gap-2 mt-4">
                            <Button
                                variant="warning"
                                size="md"
                                :icon="Pencil"
                                @click="openEdit(classItem)"
                            />

                            <Button
                                variant="danger"
                                size="md"
                                :icon="Trash2"
                                @click="confirmDelete(classItem.id)"
                            />
                        </div>
                    </template>
                </Card>
            </TransitionGroup>

            <!-- Empty State -->
            <div
                v-else
                class="flex flex-col items-center justify-center py-20 text-center"
            >
                <div
                    class="bg-blue-50 p-6 rounded-3xl border-4 border-blue-100 mb-5"
                >
                    <PackageOpen class="w-14 h-14 text-blue-300 mx-auto" />
                </div>
                <h3 class="font-heading font-bold text-gray-600 text-lg mb-1">
                    Belum ada kelas
                </h3>
                <p class="text-sm text-gray-400 mb-5">
                    Mulai dengan menambahkan kelas pertama untuk siswa.
                </p>
                <Button
                    variant="primary"
                    size="md"
                    :icon="Plus"
                    @click="openCreate"
                >
                    Tambah Kelas Pertama
                </Button>
            </div>

            <!-- Pagination -->
            <Pagination
                :meta="{
                    current_page: props.classes.current_page,
                    last_page: props.classes.last_page,
                    per_page: props.classes.per_page,
                    total: props.classes.total,
                    from: props.classes.from,
                    to: props.classes.to,
                }"
                color="blue"
                @change="handlePageChange"
            />
        </div>

        <!-- Create / Edit Modal -->
        <Modal
            :show="showDialog"
            :title="isEdit ? 'Ubah Data Kelas' : 'Tambah Kelas Baru'"
            @close="showDialog = false"
        >
            <div class="space-y-4">
                <SelectField
                    v-model="form.teacher_id"
                    label="Wali Kelas"
                    placeholder="Pilih Wali Kelas"
                    :icon="UserRound"
                    :options="availableTeachers"
                    option-value="id"
                    option-label="name"
                    border-color="blue"
                    required
                />
                <InputField
                    v-model="form.name"
                    type="text"
                    label="Nama Kelas"
                    placeholder="Nama Kelas (Contoh: 2A)"
                    :icon="BookOpen"
                    required
                    border-color="blue"
                />
                <p v-if="form.errors.name" class="text-red-500 text-sm">
                    {{ form.errors.name }}
                </p>

                <TextareaField
                    v-model="form.description"
                    label="Deskripsi"
                    placeholder="Penjelasan singkat tentang kelas ini"
                    :rows="3"
                    border-color="blue"
                />
            </div>

            <template #footer>
                <div class="flex justify-end gap-3">
                    <Button
                        variant="light"
                        size="md"
                        @click="showDialog = false"
                    >
                        Batal
                    </Button>

                    <Button
                        variant="primary"
                        size="md"
                        :icon="Save"
                        :disabled="form.processing"
                        @click="saveClass"
                    >
                        <span
                            v-if="form.processing"
                            class="flex items-center gap-2"
                        >
                            <Loader2 class="w-4 h-4 animate-spin" />
                            Menyimpan...
                        </span>
                        <span v-else>
                            <span class="flex items-center gap-2">
                                Simpan
                            </span>
                        </span>
                    </Button>
                </div>
            </template>
        </Modal>

        <!-- Delete Confirmation Dialog -->
        <ConfirmDialog
            :show="showDeleteDialog"
            title="Apakah kamu yakin ingin menghapus kelas ini?"
            message="Data yang sudah dihapus tidak dapat dikembalikan lagi."
            @confirm="deleteClass"
            @cancel="showDeleteDialog = false"
        />

        <!-- Toast Notification -->
        <Toast
            :show="showSuccess"
            :message="successMessage"
            :type="toastType"
            @close="showSuccess = false"
        />
    </AppLayout>
</template>
