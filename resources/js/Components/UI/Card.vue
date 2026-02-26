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
        required: true,
    },
    subtitle: {
        type: String,
        default: "",
    },
    icon: {
        type: String,
        default: "",
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
        // Format: [{ label, icon, variant, onClick }]
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
    if (props.variant === "playful") {
        return `bg-${props.iconColor}-100 p-3 rounded-2xl border-2 border-${props.iconColor}-300`;
    }
    return `bg-${props.iconColor}-50 p-2 rounded-lg`;
});

const badgeClasses = computed(() => {
    const colorMap = {
        green: "bg-green-100 text-green-700 border-green-300",
        blue: "bg-blue-100 text-blue-700 border-blue-300",
        yellow: "bg-yellow-100 text-yellow-700 border-yellow-300",
        red: "bg-red-100 text-red-700 border-red-300",
        purple: "bg-purple-100 text-purple-700 border-purple-300",
    };

    if (props.variant === "playful") {
        return `inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border-2 ${colorMap[props.badgeColor] || colorMap.green}`;
    }
    return `inline-flex items-center px-2 py-1 rounded-md text-xs font-medium ${colorMap[props.badgeColor] || colorMap.green}`;
});

const handleClick = (e) => {
    if (!e.target.closest("button")) {
        emit("click");
    }
};

const handleAction = (action, index) => {
    if (action.onClick) {
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
                :alt="title"
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
                <i
                    :class="[
                        `pi ${icon} text-${iconColor}-600`,
                        variant === 'playful' ? 'text-2xl' : 'text-xl',
                    ]"
                ></i>
            </div>

            <div class="flex-1 min-w-0">
                <!-- Badge -->
                <div v-if="badge" class="mb-2">
                    <span :class="badgeClasses">{{ badge }}</span>
                </div>

                <!-- Title -->
                <h3
                    :class="[
                        'font-bold text-gray-800 truncate',
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
            v-if="actions.length > 0"
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
                @click="handleAction(action, index)"
            >
                {{ action.label }}
            </Button>
        </div>

        <!-- Footer Slot -->
        <div v-if="$slots.footer">
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
