<template>
  <div class="app-wrapper">
    <!-- Backdrop mobile -->
    <div
      class="sidebar-backdrop"
      :class="{ 'is-open': sidebarOpen }"
      @click="sidebarOpen = false"
    ></div>

    <!-- Sidebar -->
    <aside class="sidebar" :class="{ 'is-open': sidebarOpen }">
      <!-- Brand -->
      <div class="sidebar-brand">
        <div class="brand-icon">⚡</div>
        <div class="brand-text">
          <div class="brand-name">Smart-Hub</div>
          <div class="brand-sub">Management System</div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="sidebar-nav">
        <div class="nav-group">
          <div class="nav-label">Overview</div>
          <Link href="/dashboard" class="nav-link" :class="{ active: isActive('/dashboard') }" @click="sidebarOpen = false">
            <span class="nav-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
              </svg>
            </span>
            Dashboard
          </Link>
        </div>

        <div class="nav-group">
          <div class="nav-label">Master Data</div>
          <Link href="/items" class="nav-link" :class="{ active: isActive('/items') }" @click="sidebarOpen = false">
            <span class="nav-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
              </svg>
            </span>
            Data Peralatan
          </Link>
          <Link href="/categories" class="nav-link" :class="{ active: isActive('/categories') }" @click="sidebarOpen = false">
            <span class="nav-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2z"/>
              </svg>
            </span>
            Kategori
          </Link>
        </div>

        <div class="nav-group">
          <div class="nav-label">Transaksi</div>
          <Link href="/transactions" class="nav-link" :class="{ active: isActive('/transactions') }" @click="sidebarOpen = false">
            <span class="nav-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/>
                <polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/>
              </svg>
            </span>
            Peminjaman
          </Link>
        </div>
      </nav>

      <!-- Footer user -->
      <div class="sidebar-footer">
        <div class="user-card">
          <div class="user-avatar">{{ userInitial }}</div>
          <div class="user-info">
            <div class="user-name">{{ userName }}</div>
            <div class="user-role">Administrator</div>
          </div>
        </div>
        <Link href="/logout" method="post" as="button" class="btn-logout">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
          Keluar
        </Link>
      </div>
    </aside>

    <!-- Main -->
    <div class="main-area">
      <!-- Topbar -->
      <header class="topbar">
        <div class="topbar-left">
          <button class="hamburger" @click="sidebarOpen = !sidebarOpen" aria-label="Toggle menu">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
          </button>
          <h1 class="topbar-title">{{ title }}</h1>
        </div>
        <div class="topbar-right">
          <div class="topbar-time">{{ currentTime }}</div>
        </div>
      </header>

      <!-- Slot konten halaman -->
      <main class="page-content">
        <!-- Flash Messages -->
        <div v-if="flash.success" class="alert alert-success" style="margin-bottom:1.25rem">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:2px">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          {{ flash.success }}
        </div>
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

const page         = usePage();
const sidebarOpen  = ref(false);
const currentTime  = ref('');

const flash    = computed(() => page.props.flash ?? {});
const userName = computed(() => page.props.auth?.user?.name ?? 'Admin');
const userInitial = computed(() => userName.value.charAt(0).toUpperCase());

const isActive = (path) => {
  if (typeof window === 'undefined') return false;
  return window.location.pathname.startsWith(path);
};

let timer;
const updateTime = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('id-ID', {
    weekday: 'short', day: '2-digit', month: 'short',
    hour: '2-digit', minute: '2-digit',
  });
};

onMounted(() => {
  updateTime();
  timer = setInterval(updateTime, 30000);
});

onUnmounted(() => clearInterval(timer));
</script>
