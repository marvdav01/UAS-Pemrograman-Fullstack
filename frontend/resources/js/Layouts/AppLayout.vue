<template>
  <div class="app-container">
    <!-- Mobile Hamburger -->
    <button class="hamburger" @click="sidebarOpen = !sidebarOpen" aria-label="Toggle menu">
      <span :class="{ open: sidebarOpen }"></span>
      <span :class="{ open: sidebarOpen }"></span>
      <span :class="{ open: sidebarOpen }"></span>
    </button>

    <!-- Overlay for mobile -->
    <div v-if="sidebarOpen" class="sidebar-overlay" @click="sidebarOpen = false"></div>

    <!-- Sidebar -->
    <aside class="sidebar" :class="{ 'sidebar-open': sidebarOpen }">
      <div class="sidebar-logo">
        <div class="logo-icon">🏢</div>
        <div>
          <div class="logo-title">Smart-Hub</div>
          <div class="logo-sub">Management System</div>
        </div>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-section-label">Dashboard</div>
        <Link href="/dashboard" class="nav-item" :class="{ active: isActive('/dashboard') }">
          <span class="nav-icon">📊</span>
          <span>Dashboard</span>
        </Link>

        <div class="nav-section-label">Master Data</div>
        <Link href="/items" class="nav-item" :class="{ active: isActive('/items') }">
          <span class="nav-icon">📦</span>
          <span>Data Peralatan</span>
        </Link>
        <Link href="/categories" class="nav-item" :class="{ active: isActive('/categories') }">
          <span class="nav-icon">🗂️</span>
          <span>Kategori</span>
        </Link>

        <div class="nav-section-label">Transaksi</div>
        <Link href="/transactions" class="nav-item" :class="{ active: isActive('/transactions') }">
          <span class="nav-icon">🔄</span>
          <span>Transaksi</span>
        </Link>
      </nav>

      <div class="sidebar-footer">
        <div class="user-info">
          <div class="user-avatar">{{ userInitial }}</div>
          <div>
            <div class="user-name">{{ userName }}</div>
            <div class="user-role">Administrator</div>
          </div>
        </div>
        <Link href="/logout" method="post" as="button" class="btn-logout">
          <span>⏏</span> Logout
        </Link>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="content-area">
      <header class="top-bar">
        <div class="top-bar-left">
          <h1 class="page-title">{{ title }}</h1>
        </div>
        <div class="top-bar-right">
          <div class="time-display">{{ currentTime }}</div>
        </div>
      </header>
      <main class="main-content">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
  title: {
    type: String,
    default: 'Dashboard',
  },
});

const page = usePage();
const sidebarOpen = ref(false);
const currentTime = ref('');

const userName = computed(() => page.props.auth?.user?.name || 'Admin');
const userInitial = computed(() => userName.value.charAt(0).toUpperCase());

const isActive = (path) => window.location.pathname.startsWith(path);

let timer;
const updateTime = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

onMounted(() => {
  updateTime();
  timer = setInterval(updateTime, 60000);
});

onUnmounted(() => clearInterval(timer));
</script>

<style scoped>
.hamburger {
  display: none;
  position: fixed;
  top: 1rem;
  left: 1rem;
  z-index: 100;
  background: var(--surface-color);
  border: 1px solid var(--surface-border);
  border-radius: var(--radius-md);
  padding: 0.5rem;
  cursor: pointer;
  flex-direction: column;
  gap: 4px;
}

.hamburger span {
  display: block;
  width: 22px;
  height: 2px;
  background: var(--text-primary);
  border-radius: 2px;
  transition: var(--transition);
}

.sidebar-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 40;
}

.sidebar {
  width: 260px;
  min-height: 100vh;
  background-color: var(--surface-color);
  border-right: 1px solid var(--surface-border);
  padding: 1.5rem 1rem;
  display: flex;
  flex-direction: column;
  position: sticky;
  top: 0;
  height: 100vh;
  overflow-y: auto;
  z-index: 50;
}

.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem;
  margin-bottom: 2rem;
}

.logo-icon {
  font-size: 1.75rem;
  background: linear-gradient(135deg, var(--primary-color), #8b5cf6);
  border-radius: var(--radius-md);
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--text-primary);
}

.logo-sub {
  font-size: 0.7rem;
  color: var(--text-secondary);
}

.sidebar-nav {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.nav-section-label {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--text-secondary);
  padding: 0.75rem 0.75rem 0.25rem;
  margin-top: 0.5rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.65rem 0.75rem;
  border-radius: var(--radius-md);
  color: var(--text-secondary);
  font-size: 0.9rem;
  transition: var(--transition);
  text-decoration: none;
}

.nav-item:hover {
  background-color: rgba(59, 130, 246, 0.1);
  color: var(--text-primary);
}

.nav-item.active {
  background-color: rgba(59, 130, 246, 0.15);
  color: var(--primary-color);
  font-weight: 600;
}

.nav-icon {
  font-size: 1.1rem;
}

.sidebar-footer {
  border-top: 1px solid var(--surface-border);
  padding-top: 1rem;
  margin-top: 1rem;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem;
  margin-bottom: 0.75rem;
}

.user-avatar {
  width: 36px;
  height: 36px;
  background: linear-gradient(135deg, var(--primary-color), #8b5cf6);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1rem;
  color: white;
}

.user-name {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--text-primary);
}

.user-role {
  font-size: 0.75rem;
  color: var(--text-secondary);
}

.btn-logout {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.5rem;
  border-radius: var(--radius-md);
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger-color);
  border: 1px solid rgba(239, 68, 68, 0.3);
  cursor: pointer;
  transition: var(--transition);
  font-size: 0.9rem;
}

.btn-logout:hover {
  background: rgba(239, 68, 68, 0.2);
}

.content-area {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.top-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 2rem;
  border-bottom: 1px solid var(--surface-border);
  background: var(--surface-color);
  position: sticky;
  top: 0;
  z-index: 30;
}

.page-title {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0;
}

.time-display {
  font-size: 0.9rem;
  color: var(--text-secondary);
}

.main-content {
  padding: 2rem;
  flex: 1;
}

@media (max-width: 768px) {
  .hamburger {
    display: flex;
  }

  .sidebar-overlay {
    display: block;
  }

  .sidebar {
    position: fixed;
    left: 0;
    top: 0;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    width: 280px;
  }

  .sidebar.sidebar-open {
    transform: translateX(0);
  }

  .content-area {
    width: 100%;
  }

  .top-bar {
    padding: 1rem 1rem 1rem 4rem;
  }

  .main-content {
    padding: 1rem;
  }
}
</style>
