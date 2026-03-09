<script setup>
import { computed } from "vue";
import Button from "@/Components/UI/Button.vue";

const props = defineProps({
    variant: {
        type: String,
        default: "playful", // 'playful' or 'normal'
    },
    title: {
        type: String,
        default: "",
    },
    subtitle: {
        type: String,
        default: "",
    },
    icon: {
        type: [String, Object, Function],
        default: null,
    },
    iconColor: {
        type: String,
        default: "blue",
    },
    borderColor: {
        type: String,
        default: "blue",
    },
    badge: {
        type: String,
        default: "",
    },
    badgeColor: {
        type: String,
        default: "green",
    },
    image: {
        type: String,
        default: "",
    },
    hoverable: {
        type: Boolean,
        default: true,
    },
    actions: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["click", "action"]);

const cardClasses = computed(() => {
    if (props.variant === "playful") {
        return [
            "bg-white rounded-3xl shadow-playful p-6",
            `border-4 border-${props.borderColor}-200`,
            props.hoverable
                ? "hover:scale-105 hover:shadow-playful-lg transition-all duration-300 cursor-pointer"
                : "",
        ];
    }
    return [
        "bg-white rounded-xl shadow-md p-6",
        "border border-gray-200",
        props.hoverable
            ? "hover:shadow-lg transition-shadow duration-200 cursor-pointer"
            : "",
    ];
});

const iconBgClasses = computed(() => {
    const map = {
        blue: "bg-blue-100 border-blue-300",
        green: "bg-green-50 border-green-200",
        yellow: "bg-amber-50 border-amber-200",
        red: "bg-red-50 border-red-200",
        purple: "bg-indigo-50 border-indigo-200",
    };
    const cls = map[props.iconColor] || map.blue;
    if (props.variant === "playful") {
        return `${cls} p-3 rounded-2xl border-2`;
    }
    return `${cls.split(" ")[0]} p-2 rounded-lg`;
});

const iconTextClass = computed(() => {
    const map = {
        blue: "text-blue-600",
        green: "text-green-500",
        yellow: "text-amber-500",
        red: "text-red-500",
        purple: "text-indigo-500",
    };
    return map[props.iconColor] || map.blue;
});

const badgeClasses = computed(() => {
    const colorMap = {
        green: "bg-blue-50 text-blue-500 border-blue-200",
        blue: "bg-green-100 text-green-700 border-green-300",
        yellow: "bg-amber-50 text-amber-600 border-amber-200",
        red: "bg-red-50 text-red-500 border-red-200",
        purple: "bg-indigo-50 text-indigo-600 border-indigo-200",
    };
    const cls = colorMap[props.badgeColor] || colorMap.blue;
    if (props.variant === "playful") {
        return `inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border-2 ${cls}`;
    }
    return `inline-flex items-center px-2 py-1 rounded-md text-xs font-medium ${cls}`;
});

const handleClick = (e) => {
    if (!e.target.closest("button")) {
        emit("click");
    }
};

const handleAction = (action, index) => {
    if (action && action.onClick) {
        action.onClick();
    }
    emit("action", { action, index });
};
</script>

<template>
    <div :class="cardClasses" @click="handleClick">
        <!-- Image (if provided) -->
        <div v-if="image" class="mb-4">
            <img
                :src="image"
                :alt="title || 'Card image'"
                :class="
                    variant === 'playful'
                        ? 'rounded-2xl border-2 border-gray-200'
                        : 'rounded-lg'
                "
                class="w-full h-40 object-cover"
            />
        </div>

        <!-- Header with Icon -->
        <div class="flex items-start gap-3 mb-4">
            <div v-if="icon" :class="iconBgClasses">
                <component
                    :is="icon"
                    :class="[
                        iconTextClass,
                        variant === 'playful' ? 'w-6 h-6' : 'w-5 h-5',
                    ]"
                />
            </div>

            <div class="flex-1 min-w-0">
                <!-- Badge -->
                <div v-if="badge" class="mb-2">
                    <span :class="badgeClasses">{{ badge }}</span>
                </div>

                <!-- Title -->
                <h3
                    v-if="title"
                    :class="[
                        'font-bold text-gray-800',
                        variant === 'playful'
                            ? 'text-xl font-heading'
                            : 'text-lg',
                    ]"
                >
                    {{ title }}
                </h3>

                <!-- Subtitle -->
                <p
                    v-if="subtitle"
                    :class="[
                        'text-gray-500 mt-1',
                        variant === 'playful' ? 'text-sm' : 'text-xs',
                    ]"
                >
                    {{ subtitle }}
                </p>
            </div>
        </div>

        <!-- Content Slot -->
        <div v-if="$slots.default" class="mb-4">
            <slot></slot>
        </div>

        <!-- Actions -->
        <div
            v-if="actions && actions.length > 0"
            class="flex items-center gap-2 pt-4 border-t"
            :class="
                variant === 'playful' ? 'border-gray-200' : 'border-gray-100'
            "
        >
            <Button
                v-for="(action, index) in actions"
                :key="index"
                :variant="action.variant || 'primary'"
                :size="action.size || 'sm'"
                :icon="action.icon"
                @click.stop="handleAction(action, index)"
            >
                {{ action.label }}
            </Button>
        </div>

        <!-- Footer Slot -->
        <div v-if="$slots.footer" class="mt-4">
            <slot name="footer"></slot>
        </div>
    </div>
</template>

<style scoped>
.shadow-playful {
    box-shadow:
        0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06),
        0 8px 16px -4px rgba(59, 130, 246, 0.1);
}

.shadow-playful-lg {
    box-shadow:
        0 10px 15px -3px rgba(0, 0, 0, 0.1),
        0 4px 6px -2px rgba(0, 0, 0, 0.05),
        0 16px 32px -8px rgba(59, 130, 246, 0.15);
}
</style>
