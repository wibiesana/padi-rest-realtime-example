<template>
  <q-page class="bg-dark text-white flex flex-center q-pa-md" style="min-height: 100vh">
    <div class="landing-container text-center">
      <!-- HERO HEADER -->
      <div class="q-mb-xl">
        <div class="logo-wrapper q-mb-md">
          <q-avatar size="80px" color="primary" text-color="white" class="shadow-glow">
            <q-icon name="bolt" size="48px" />
          </q-avatar>
        </div>
        <h1 class="text-h2 text-weight-bold bg-gradient-text q-my-none">PADI Framework</h1>
        <p class="text-h6 text-grey-4 q-mt-md">
          {{ $t('home.subtitle') }}
        </p>
      </div>

      <!-- FEATURES GRID -->
      <div
        class="row q-col-gutter-lg justify-center q-mb-xl"
        style="max-width: 1000px; margin: 0 auto"
      >
        <div class="col-12 col-md-6">
          <q-card class="feature-card glass-panel text-white text-left q-pa-md">
            <q-card-section>
              <q-avatar icon="sync" color="primary" text-color="white" class="q-mb-md" />
              <h3 class="text-h6 text-weight-bold q-my-none">{{ $t('home.syncTitle') }}</h3>
              <p class="text-body2 text-grey-5 q-mt-sm q-mb-none">
                {{ $t('home.syncDesc') }}
              </p>
            </q-card-section>
          </q-card>
        </div>

        <div class="col-12 col-md-6">
          <q-card class="feature-card glass-panel text-white text-left q-pa-md">
            <q-card-section>
              <q-avatar icon="security" color="accent" text-color="white" class="q-mb-md" />
              <h3 class="text-h6 text-weight-bold q-my-none">{{ $t('home.authTitle') }}</h3>
              <p class="text-body2 text-grey-5 q-mt-sm q-mb-none">
                {{ $t('home.authDesc') }}
              </p>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- QUEUE GUIDE SECTION -->
      <div class="q-my-xl text-left glass-panel q-pa-lg" style="max-width: 850px; margin: 2rem auto;">
        <div class="row items-center q-gutter-md q-mb-md">
          <q-avatar icon="lan" color="warning" text-color="white" />
          <h2 class="text-h5 text-weight-bold q-my-none text-warning">{{ $t('home.architectureTitle') }}</h2>
        </div>

        <q-banner rounded class="bg-grey-9 text-white q-mb-lg" style="border: 1px solid rgba(255, 255, 255, 0.05)">
          <template v-slot:avatar>
            <q-icon name="info" color="warning" />
          </template>
          <div>
            <strong>{{ $t('home.noteTitle') }}</strong> {{ $t('home.noteDesc') }}
          </div>
        </q-banner>

        <!-- PROS AND CONS -->
        <div class="row q-col-gutter-md q-mb-lg">
          <!-- DIRECT METHOD -->
          <div class="col-12 col-md-6">
            <div class="text-subtitle1 text-weight-bold text-primary q-mb-sm">{{ $t('home.directTitle') }}</div>
            <div class="text-caption text-grey-4">
              <div class="q-mb-xs"><q-icon name="add_circle" color="green" class="q-mr-xs"/><strong>{{ $t('home.prosLabel') }}</strong> {{ $t('home.directPros') }}</div>
              <div><q-icon name="remove_circle" color="red" class="q-mr-xs"/><strong>{{ $t('home.consLabel') }}</strong> {{ $t('home.directCons') }}</div>
            </div>
          </div>
          
          <!-- QUEUE METHOD -->
          <div class="col-12 col-md-6">
            <div class="text-subtitle1 text-weight-bold text-warning q-mb-sm">{{ $t('home.queueTitle') }}</div>
            <div class="text-caption text-grey-4">
              <div class="q-mb-xs"><q-icon name="add_circle" color="green" class="q-mr-xs"/><strong>{{ $t('home.prosLabel') }}</strong> {{ $t('home.queuePros') }}</div>
              <div><q-icon name="remove_circle" color="red" class="q-mr-xs"/><strong>{{ $t('home.consLabel') }}</strong> {{ $t('home.queueCons') }}</div>
            </div>
          </div>
        </div>

        <q-separator dark class="q-my-md" style="opacity: 0.1;" />

        <div>
          <div class="text-subtitle2 text-weight-bold text-white q-mb-xs">{{ $t('home.runQueueTitle') }}</div>
          <p class="text-caption text-grey-5 q-mb-sm">
            {{ $t('home.runQueueDesc') }}
          </p>
          <div class="bg-black q-pa-md rounded-borders text-mono text-weight-bold" style="font-family: monospace; border: 1px solid #333; letter-spacing: 0.5px;">
            <span class="text-green-4">php</span> scripts/queue-worker.php
          </div>
        </div>
      </div>

      <!-- CTA ACTION BUTTONS -->
      <div class="row justify-center q-gutter-md q-mt-md">
        <template v-if="auth.token">
          <q-btn
            color="primary"
            :label="$t('home.btnDashboard')"
            icon="dashboard"
            class="glow-btn q-px-xl q-py-sm"
            to="/posts"
          />
          <q-btn
            outline
            color="white"
            :label="$t('nav.signOut')"
            icon="logout"
            class="q-px-xl q-py-sm"
            @click="handleLogout"
          />
        </template>
        <template v-else>
          <q-btn
            color="primary"
            :label="$t('home.btnGetStarted')"
            icon="login"
            class="glow-btn q-px-xl q-py-sm"
            to="/login"
          />
        </template>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { useAuthStore } from 'src/stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

const handleLogout = () => {
  auth.logout()
  router.push('/login')
}
</script>

<style lang="scss" scoped>
.landing-container {
  width: 100%;
  max-width: 1200px;
}

.logo-wrapper {
  animation: pulseLogo 3s infinite ease-in-out;
}

.bg-gradient-text {
  background: linear-gradient(135deg, #00b4db 0%, #0083b0 100%);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}

.glass-panel {
  background: rgba(255, 255, 255, 0.02);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 16px;
  box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
}

.feature-card {
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  height: 100%;
  &:hover {
    transform: translateY(-5px);
    border-color: rgba(0, 180, 219, 0.3);
    box-shadow: 0 12px 35px 0 rgba(0, 180, 219, 0.15);
  }
}

.glow-btn {
  box-shadow: 0 0 15px rgba(0, 180, 219, 0.4);
  transition: all 0.3s ease;
  &:hover {
    box-shadow: 0 0 25px rgba(0, 180, 219, 0.7);
    transform: translateY(-2px);
  }
}

.shadow-glow {
  box-shadow: 0 0 20px rgba(0, 180, 219, 0.4);
}

@keyframes pulseLogo {
  0%,
  100% {
    transform: scale(1);
    filter: drop-shadow(0 0 5px rgba(0, 180, 219, 0.2));
  }
  50% {
    transform: scale(1.05);
    filter: drop-shadow(0 0 15px rgba(0, 180, 219, 0.6));
  }
}
</style>
