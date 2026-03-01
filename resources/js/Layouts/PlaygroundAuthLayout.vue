<template>
  <div class="playground-shell">
    <!-- Animated sky background -->
    <div class="sky-bg" aria-hidden="true">
      <div class="cloud cloud-1">☁</div>
      <div class="cloud cloud-2">☁</div>
      <div class="cloud cloud-3">☁</div>
    </div>

    <!-- Top Navigation -->
    <header class="top-bar">
      <div class="top-bar-inner">
        <!-- Brand -->
        <Link :href="route('student.index')" class="brand">
          <span class="brand-icon">💧</span>
          <span class="brand-name">EduAir</span>
        </Link>

        <!-- Nav -->
        <nav class="nav-links">
          <Link
            :href="route('student.index')"
            class="nav-item"
            :class="{ active: $page.url === '/playground' || $page.url.startsWith('/playground/module') }"
          >
            🏠 Beranda
          </Link>
          <!-- <Link
            :href="route('student.dashboard')"
            class="nav-item"
            :class="{ active: $page.url.startsWith('/dashboard') }"
          >
            📊 Progress
          </Link> -->
        </nav>

        <!-- User info -->
        <div class="user-info">
          <div class="xp-badge" title="Total XP">
            ⚡ {{ $page.props.auth.user.xp ?? 0 }} XP
          </div>
          <div class="avatar-wrap">
            <div class="avatar">{{ userInitial }}</div>
            <span class="user-name">{{ firstName }}</span>
          </div>
          <Link :href="route('profile.edit')" class="icon-btn" title="Profil">
            ⚙️
          </Link>
        </div>
      </div>
    </header>

    <!-- Slot for page content -->
    <main class="playground-main">
      <slot />
    </main>

    <!-- Grass footer -->
    <footer class="grass-footer" aria-hidden="true">
      <div class="grass-strip"></div>
      <div class="footer-bar">
        🌿 Belajar sambil bermain, menjaga alam bersama · EduAir
      </div>
    </footer>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()

const userInitial = computed(() =>
  (page.props.auth.user.name ?? 'S').charAt(0).toUpperCase()
)

const firstName = computed(() =>
  (page.props.auth.user.name ?? '').split(' ')[0]
)
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Baloo+2:wght@700;800&display=swap');

/* ── Reset ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ── Shell ── */
.playground-shell {
  min-height: 100vh;
  font-family: 'Nunito', sans-serif;
  background: linear-gradient(180deg, #b8e4f9 0%, #d4eeff 45%, #e8f7d0 100%);
  display: flex;
  flex-direction: column;
  overflow-x: hidden;
  position: relative;
}

/* ── Sky ── */
.sky-bg {
  position: fixed;
  inset: 0;
  pointer-events: none;
  z-index: 0;
}
.cloud {
  position: absolute;
  color: #fff;
  opacity: 0.55;
  filter: drop-shadow(0 2px 10px rgba(255,255,255,0.7));
  animation: floatCloud linear infinite;
}
.cloud-1 { top: 5%;  font-size: 2.8rem; animation-duration: 28s; }
.cloud-2 { top: 14%; font-size: 2rem;   animation-duration: 40s; animation-delay: -12s; }
.cloud-3 { top: 3%;  font-size: 2.4rem; animation-duration: 52s; animation-delay: -26s; }
@keyframes floatCloud {
  from { transform: translateX(-150px); }
  to   { transform: translateX(110vw); }
}

/* ── Top bar ── */
.top-bar {
  position: sticky;
  top: 0;
  z-index: 100;
  background: rgba(255, 255, 255, 0.82);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  border-bottom: 3px solid #a8d8f0;
  box-shadow: 0 4px 20px rgba(100, 180, 240, 0.18);
}
.top-bar-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0.65rem 2rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

/* Brand */
.brand {
  display: flex;
  align-items: center;
  gap: 0.45rem;
  text-decoration: none;
  font-family: 'Baloo 2', cursive;
  font-size: 1.45rem;
  font-weight: 800;
  color: #1a6fa3;
  flex-shrink: 0;
}
.brand-icon { font-size: 1.7rem; }

/* Nav links */
.nav-links {
  display: flex;
  gap: 0.4rem;
  flex: 1;
}
.nav-item {
  padding: 0.4rem 1rem;
  border-radius: 99px;
  font-weight: 700;
  font-size: 0.88rem;
  color: #4a7c9e;
  text-decoration: none;
  transition: background 0.18s, color 0.18s;
}
.nav-item:hover { background: rgba(30, 120, 190, 0.1); color: #1a6fa3; }
.nav-item.active { background: #1a6fa3; color: #fff; }

/* User info */
.user-info {
  display: flex;
  align-items: center;
  gap: 0.7rem;
  margin-left: auto;
}
.xp-badge {
  background: linear-gradient(135deg, #ffe066, #ffc300);
  color: #7a4e00;
  font-weight: 800;
  font-size: 0.78rem;
  padding: 0.28rem 0.85rem;
  border-radius: 99px;
  box-shadow: 0 2px 8px rgba(255,180,0,0.3);
  white-space: nowrap;
}
.avatar-wrap {
  display: flex;
  align-items: center;
  gap: 0.45rem;
}
.avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: linear-gradient(135deg, #4ec9ff, #1a6fa3);
  color: #fff;
  font-weight: 800;
  font-size: 0.95rem;
  display: grid;
  place-items: center;
  box-shadow: 0 2px 8px rgba(30,120,190,0.35);
  flex-shrink: 0;
}
.user-name {
  font-weight: 700;
  color: #1a6fa3;
  font-size: 0.9rem;
}
.icon-btn {
  font-size: 1.1rem;
  text-decoration: none;
  opacity: 0.6;
  transition: opacity 0.2s;
}
.icon-btn:hover { opacity: 1; }

/* ── Main content ── */
.playground-main {
  flex: 1;
  position: relative;
  z-index: 1;
}

/* ── Footer ── */
.grass-footer {
  position: relative;
  z-index: 1;
  margin-top: 2rem;
}
.grass-strip {
  height: 22px;
  background: repeating-linear-gradient(
    90deg,
    #5cb85c 0px,   #5cb85c 14px,
    #45a145 14px,  #45a145 28px,
    #6dcc6d 28px,  #6dcc6d 42px
  );
  clip-path: polygon(
    0% 100%, 1.5% 15%, 3% 100%, 4.5% 5%,  6% 100%, 7.5% 25%,
    9% 100%, 10.5% 10%, 12% 100%, 13.5% 20%, 15% 100%,
    16.5% 8%, 18% 100%, 19.5% 18%, 21% 100%, 22.5% 12%,
    24% 100%, 25.5% 22%, 27% 100%, 28.5% 6%, 30% 100%,
    31.5% 18%, 33% 100%, 34.5% 14%, 36% 100%, 37.5% 24%,
    39% 100%, 40.5% 10%, 42% 100%, 43.5% 18%, 45% 100%,
    46.5% 6%, 48% 100%, 49.5% 20%, 51% 100%, 52.5% 14%,
    54% 100%, 55.5% 22%, 57% 100%, 58.5% 8%, 60% 100%,
    61.5% 18%, 63% 100%, 64.5% 12%, 66% 100%, 67.5% 25%,
    69% 100%, 70.5% 10%, 72% 100%, 73.5% 20%, 75% 100%,
    76.5% 6%, 78% 100%, 79.5% 18%, 81% 100%, 82.5% 14%,
    84% 100%, 85.5% 22%, 87% 100%, 88.5% 8%, 90% 100%,
    91.5% 18%, 93% 100%, 94.5% 12%, 96% 100%, 97.5% 20%, 100% 100%
  );
}
.footer-bar {
  background: #3a9a3a;
  color: #d4ffd4;
  text-align: center;
  padding: 0.55rem 1rem;
  font-size: 0.78rem;
  font-weight: 600;
}

/* ── Responsive ── */
@media (max-width: 768px) {
  .top-bar-inner { padding: 0.6rem 1rem; gap: 0.8rem; }
  .user-name { display: none; }
  .xp-badge { display: none; }
  .brand-name { font-size: 1.2rem; }
}
@media (max-width: 480px) {
  .nav-links .nav-item:not(.active) { display: none; }
}
</style>
