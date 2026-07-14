<template>
  <div class="login-page">
    <!-- Animated mesh gradient background -->
    <div class="login-bg">
      <div class="bg-orb orb-1"></div>
      <div class="bg-orb orb-2"></div>
      <div class="bg-orb orb-3"></div>
    </div>

    <!-- Grid overlay -->
    <div class="grid-overlay"></div>

    <div class="login-wrap">
      <!-- Left panel -->
      <div class="login-left">
        <div class="brand-badge">
          <span class="badge-icon">⚡</span>
          <span>Smart-Hub</span>
        </div>
        <h1 class="login-tagline">Sistem Manajemen<br><span class="gradient-text">Peralatan Modern</span></h1>
        <p class="login-desc">
          Platform terintegrasi untuk pengelolaan aset, inventaris peralatan,
          dan transaksi peminjaman secara efisien dan real-time.
        </p>
        <div class="feature-list">
          <div class="feature-item" v-for="f in features" :key="f.text">
            <div class="feature-icon">{{ f.icon }}</div>
            <span>{{ f.text }}</span>
          </div>
        </div>
      </div>

      <!-- Right panel: Login Form -->
      <div class="login-card">
        <div class="card-inner">
          <div class="login-header">
            <div class="login-logo">⚡</div>
            <h2>Selamat Datang</h2>
            <p>Masuk ke akun administrator Anda</p>
          </div>

          <!-- Error Alert -->
          <div v-if="errors.login" class="alert alert-danger">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px">
              <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            {{ errors.login }}
          </div>

          <form @submit.prevent="submit" novalidate>
            <div class="form-group">
              <label for="email" class="form-label">Alamat Email</label>
              <div class="input-icon-wrap">
                <svg class="input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                  <polyline points="22,6 12,13 2,6"/>
                </svg>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  class="form-control has-icon"
                  :class="{ 'is-invalid': errors.email }"
                  placeholder="admin@smarthub.com"
                  autocomplete="email"
                  required
                />
              </div>
              <p v-if="errors.email" class="form-error">{{ errors.email }}</p>
            </div>

            <div class="form-group">
              <label for="password" class="form-label">Password</label>
              <div class="input-icon-wrap">
                <svg class="input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input
                  id="password"
                  v-model="form.password"
                  :type="showPwd ? 'text' : 'password'"
                  class="form-control has-icon has-toggle"
                  :class="{ 'is-invalid': errors.password }"
                  placeholder="••••••••"
                  autocomplete="current-password"
                  required
                />
                <button type="button" class="toggle-pwd" @click="showPwd = !showPwd" :title="showPwd ? 'Sembunyikan' : 'Tampilkan'">
                  <svg v-if="!showPwd" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                  </svg>
                  <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>
                  </svg>
                </button>
              </div>
              <p v-if="errors.password" class="form-error">{{ errors.password }}</p>
            </div>

            <button
              id="btn-login"
              type="submit"
              class="btn btn-primary btn-full"
              :disabled="form.processing"
            >
              <span v-if="form.processing" class="spinner"></span>
              <span v-else>Masuk ke Dashboard</span>
            </button>
          </form>

          <p class="login-footer">© {{ year }} Smart-Hub Management System</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

defineProps({
  errors: { type: Object, default: () => ({}) },
});

const showPwd = ref(false);
const year    = new Date().getFullYear();

const form = useForm({ email: '', password: '' });

const submit = () => {
  form.post('/login', { onFinish: () => form.reset('password') });
};

const features = [
  { icon: '📦', text: 'Manajemen Inventaris Peralatan' },
  { icon: '🔄', text: 'Tracking Peminjaman Real-time' },
  { icon: '📊', text: 'Dashboard Statistik Interaktif' },
  { icon: '🛡️', text: 'Sistem Autentikasi Aman' },
];
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: stretch;
  background: var(--bg);
  position: relative;
  overflow: hidden;
}

/* Background orbs */
.login-bg {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.bg-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(100px);
  opacity: 0.18;
  animation: float 10s ease-in-out infinite;
}

.orb-1 {
  width: 500px; height: 500px;
  background: radial-gradient(circle, #6366f1, transparent);
  top: -150px; left: -100px;
}
.orb-2 {
  width: 400px; height: 400px;
  background: radial-gradient(circle, #06b6d4, transparent);
  bottom: -100px; right: -100px;
  animation-delay: -5s;
}
.orb-3 {
  width: 300px; height: 300px;
  background: radial-gradient(circle, #8b5cf6, transparent);
  top: 40%; left: 40%;
  animation-delay: -3s;
}

@keyframes float {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33%       { transform: translate(20px, -20px) scale(1.05); }
  66%       { transform: translate(-15px, 15px) scale(0.97); }
}

/* Grid overlay */
.grid-overlay {
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
  background-size: 50px 50px;
  pointer-events: none;
}

.login-wrap {
  position: relative;
  z-index: 1;
  display: flex;
  width: 100%;
  min-height: 100vh;
}

/* Left Panel */
.login-left {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 4rem 5%;
  max-width: 52%;
}

.brand-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(99,102,241,0.12);
  border: 1px solid rgba(99,102,241,0.25);
  color: var(--primary-light);
  padding: 0.4rem 1rem;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 700;
  width: fit-content;
  margin-bottom: 2rem;
  letter-spacing: 0.05em;
}

.badge-icon { font-size: 1rem; }

.login-tagline {
  font-size: clamp(2rem, 4vw, 3.2rem);
  font-weight: 800;
  line-height: 1.15;
  margin-bottom: 1.25rem;
  letter-spacing: -0.03em;
}

.gradient-text {
  background: linear-gradient(135deg, var(--primary-light) 0%, var(--accent) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.login-desc {
  color: var(--text-2);
  font-size: 1rem;
  line-height: 1.7;
  margin-bottom: 2.5rem;
  max-width: 460px;
}

.feature-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  color: var(--text-2);
  font-size: 0.9rem;
  font-weight: 500;
}

.feature-icon {
  width: 36px;
  height: 36px;
  background: var(--surface-2);
  border: 1px solid var(--border);
  border-radius: var(--r-md);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  flex-shrink: 0;
}

/* Right Panel: Card */
.login-card {
  width: 480px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: rgba(17, 24, 39, 0.6);
  border-left: 1px solid var(--border);
  backdrop-filter: blur(20px);
}

.card-inner {
  width: 100%;
  max-width: 400px;
  animation: slideUp 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(24px); }
  to   { opacity: 1; transform: translateY(0); }
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.login-logo {
  width: 56px; height: 56px;
  background: linear-gradient(135deg, var(--primary), var(--accent));
  border-radius: var(--r-xl);
  font-size: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.25rem;
  box-shadow: 0 8px 24px rgba(99,102,241,0.4);
}

.login-header h2 {
  font-size: 1.5rem;
  font-weight: 800;
  margin-bottom: 0.3rem;
  letter-spacing: -0.02em;
}

.login-header p {
  color: var(--text-3);
  font-size: 0.875rem;
}

/* Input with icon */
.input-icon-wrap {
  position: relative;
}

.input-icon {
  position: absolute;
  left: 0.875rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-3);
  pointer-events: none;
}

.form-control.has-icon {
  padding-left: 2.5rem;
}

.form-control.has-toggle {
  padding-right: 2.75rem;
}

.toggle-pwd {
  position: absolute;
  right: 0.875rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: var(--text-3);
  display: flex;
  align-items: center;
  padding: 0.2rem;
  transition: var(--ease);
}
.toggle-pwd:hover { color: var(--text-1); }

.btn-full {
  width: 100%;
  padding: 0.8rem;
  font-size: 0.9rem;
  margin-top: 0.25rem;
  border-radius: var(--r-lg);
}

.login-footer {
  text-align: center;
  margin-top: 2rem;
  font-size: 0.72rem;
  color: var(--text-3);
}

/* Responsive: mobile stacks vertically */
@media (max-width: 900px) {
  .login-wrap {
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
  }

  .login-left {
    display: none;
  }

  .login-card {
    width: 100%;
    max-width: 480px;
    border-left: none;
    background: transparent;
    backdrop-filter: none;
    padding: 2rem 1.5rem;
  }
}
</style>
