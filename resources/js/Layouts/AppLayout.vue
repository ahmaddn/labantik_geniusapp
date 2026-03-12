<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import Sidebar from "@/Components/Sidebar.vue";
import Topbar from "@/Components/Topbar.vue";

const sidebarOpen = ref(true);
const isMobile = ref(false);

const checkMobile = () => {
    isMobile.value = window.innerWidth < 1024;
};

const initializeSidebar = () => {
    checkMobile();
    sidebarOpen.value = !isMobile.value;
};

const handleResize = () => {
    const wasMobile = isMobile.value;
    checkMobile();
    if (isMobile.value && !wasMobile) {
        sidebarOpen.value = false;
    }
};

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const closeSidebar = () => {
    sidebarOpen.value = false;
};

onMounted(() => {
    initializeSidebar();
    window.addEventListener("resize", handleResize);
});

onUnmounted(() => {
    window.removeEventListener("resize", handleResize);
});
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Sidebar -->
        <Sidebar
            :open="sidebarOpen"
            :isMobile="isMobile"
            @close="closeSidebar"
        />

        <!-- Main Content -->
        <div
            class="transition-all duration-300"
            :class="[
                !isMobile && sidebarOpen ? 'lg:ml-[280px]' : '',
                !isMobile && !sidebarOpen ? 'lg:ml-[72px]' : '',
            ]"
        >
            <Topbar
                :sidebarOpen="sidebarOpen"
                @toggle-sidebar="toggleSidebar"
            />

            <main class="p-2 md:p-4 lg:p-6">
                <slot />
            </main>
        </div>

        <!-- Mobile Overlay -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sidebarOpen && isMobile"
                @click="closeSidebar"
                class="fixed inset-0 bg-black/30 z-30 lg:hidden"
            ></div>
        </Transition>
    </div>
</template>
