<script setup>
import { computed, ref, onMounted, onUnmounted, nextTick } from "vue";
import { ChevronDown, Check } from "lucide-vue-next";

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: "",
    },
    options: {
        type: Array,
        required: true,
        default: () => [],
    },
    label: {
        type: String,
        default: "",
    },
    placeholder: {
        type: String,
        default: "Pilih opsi",
    },
    error: {
        type: String,
        default: "",
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },
    icon: {
        type: [String, Object, Function],
        default: "",
    },
    borderColor: {
        type: String,
        default: "blue",
        validator: (value) =>
            ["blue", "green", "yellow", "red", "purple", "gray"].includes(
                value,
            ),
    },
    optionValue: {
        type: String,
        default: "value",
    },
    optionLabel: {
        type: String,
        default: "label",
    },
});

const emit = defineEmits(["update:modelValue", "change"]);

const isOpen = ref(false);
const containerRef = ref(null);
const triggerRef = ref(null);
const dropdownStyle = ref({});

const isEmpty = computed(() => !props.options || props.options.length === 0);

const selectedLabel = computed(() => {
    if (!props.modelValue) return null;
    const found = props.options.find((opt) => {
        const val = typeof opt === "object" ? opt[props.optionValue] : opt;
        return val == props.modelValue;
    });
    if (!found) return null;
    return typeof found === "object" ? found[props.optionLabel] : found;
});

const selectOption = (option) => {
    const value =
        typeof option === "object" ? option[props.optionValue] : option;
    emit("update:modelValue", value);
    emit("change", value);
    isOpen.value = false;
};

const calcPosition = () => {
    const rect = triggerRef.value?.getBoundingClientRect();
    if (!rect) return;

    const dropdownMaxHeight = 220;
    const spaceBelow = window.innerHeight - rect.bottom;
    const spaceAbove = rect.top;

    if (spaceBelow >= dropdownMaxHeight || spaceBelow >= spaceAbove) {
        dropdownStyle.value = {
            position: "fixed",
            top: `${rect.bottom + 8}px`,
            left: `${rect.left}px`,
            width: `${rect.width}px`,
            zIndex: 9999,
        };
    } else {
        dropdownStyle.value = {
            position: "fixed",
            bottom: `${window.innerHeight - rect.top + 8}px`,
            left: `${rect.left}px`,
            width: `${rect.width}px`,
            zIndex: 9999,
        };
    }
};

const toggleOpen = async () => {
    if (props.disabled || isEmpty.value) return;
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        await nextTick();
        calcPosition();
    }
};

const handleClickOutside = (e) => {
    const dropdown = document.getElementById("select-dropdown-portal");
    if (
        containerRef.value &&
        !containerRef.value.contains(e.target) &&
        (!dropdown || !dropdown.contains(e.target))
    ) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener("mousedown", handleClickOutside);
    window.addEventListener("scroll", calcPosition, true);
    window.addEventListener("resize", calcPosition);
});

onUnmounted(() => {
    document.removeEventListener("mousedown", handleClickOutside);
    window.removeEventListener("scroll", calcPosition, true);
    window.removeEventListener("resize", calcPosition);
});

const borderClasses = computed(() => {
    const colors = {
        blue: "border-blue-200 focus-within:border-blue-400",
        green: "border-green-200 focus-within:border-green-400",
        yellow: "border-yellow-200 focus-within:border-yellow-400",
        red: "border-red-200 focus-within:border-red-400",
        purple: "border-purple-200 focus-within:border-purple-400",
        gray: "border-gray-200 focus-within:border-gray-400",
    };
    return colors[props.borderColor];
});

const activeColorClasses = computed(() => {
    const colors = {
        blue: "bg-blue-50 text-blue-600",
        green: "bg-green-50 text-green-600",
        yellow: "bg-yellow-50 text-yellow-600",
        red: "bg-red-50 text-red-600",
        purple: "bg-purple-50 text-purple-600",
        gray: "bg-gray-50 text-gray-600",
    };
    return colors[props.borderColor];
});

const checkColorClass = computed(() => {
    const colors = {
        blue: "text-blue-500",
        green: "text-green-500",
        yellow: "text-yellow-500",
        red: "text-red-500",
        purple: "text-purple-500",
        gray: "text-gray-500",
    };
    return colors[props.borderColor];
});
</script>

<template>
    <div class="w-full" ref="containerRef">
        <!-- Label -->
        <label v-if="label" class="block text-sm font-bold text-gray-700 mb-2">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <!-- Trigger Button -->
        <div class="relative">
            <button
                ref="triggerRef"
                type="button"
                @click="toggleOpen"
                :class="[
                    'w-full px-4 py-3 rounded-2xl border-4 outline-none font-medium transition-colors text-left flex items-center',
                    icon ? 'pl-12' : '',
                    'pr-12',
                    borderClasses,
                    disabled || isEmpty
                        ? 'bg-gray-100 cursor-not-allowed'
                        : 'bg-white cursor-pointer',
                    error ? 'border-red-300' : '',
                ]"
            >
                <!-- Icon -->
                <component
                    v-if="icon"
                    :is="icon"
                    :size="16"
                    class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"
                />

                <!-- Value / Placeholder -->
                <span
                    :class="selectedLabel ? 'text-gray-800' : 'text-gray-400'"
                >
                    {{
                        isEmpty
                            ? "Tidak ada data tersedia"
                            : (selectedLabel ?? placeholder)
                    }}
                </span>

                <!-- Arrow -->
                <ChevronDown
                    v-if="!isEmpty"
                    :size="16"
                    :class="[
                        'absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 transition-transform duration-200',
                        isOpen ? 'rotate-180' : '',
                    ]"
                />
            </button>
        </div>

        <!-- Error -->
        <p v-if="error" class="mt-2 text-sm text-red-500 font-medium">
            {{ error }}
        </p>
    </div>

    <!--
        Teleport ke body supaya dropdown tidak terpotong oleh overflow modal.
        Posisinya dihitung secara fixed menggunakan getBoundingClientRect()
        sehingga selalu tepat di bawah (atau atas) trigger button.
    -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 translate-y-[-8px]"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-[-8px]"
        >
            <div
                v-if="isOpen && !isEmpty"
                id="select-dropdown-portal"
                :style="dropdownStyle"
                class="bg-white rounded-2xl border-4 border-gray-100 shadow-xl overflow-hidden"
            >
                <!-- Placeholder option -->
                <button
                    type="button"
                    @click="
                        () => {
                            emit('update:modelValue', '');
                            emit('change', '');
                            isOpen = false;
                        }
                    "
                    :class="[
                        'w-full text-left px-4 py-3 text-sm font-medium transition-colors text-gray-400 hover:bg-gray-50',
                        !modelValue ? activeColorClasses : '',
                    ]"
                >
                    {{ placeholder }}
                </button>

                <div class="border-t-2 border-gray-100" />

                <!-- Options list -->
                <div class="max-h-52 overflow-y-auto">
                    <button
                        v-for="(option, index) in options"
                        :key="index"
                        type="button"
                        @click="selectOption(option)"
                        :class="[
                            'w-full text-left px-4 py-3 text-sm font-medium transition-colors flex items-center justify-between gap-2',
                            (typeof option === 'object'
                                ? option[optionValue]
                                : option) == modelValue
                                ? activeColorClasses
                                : 'text-gray-700 hover:bg-gray-50',
                        ]"
                    >
                        <span>
                            {{
                                typeof option === "object"
                                    ? option[optionLabel]
                                    : option
                            }}
                        </span>
                        <Check
                            v-if="
                                (typeof option === 'object'
                                    ? option[optionValue]
                                    : option) == modelValue
                            "
                            :size="14"
                            :class="checkColorClass"
                            class="flex-shrink-0"
                        />
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
