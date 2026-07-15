<template>
  <q-layout view="lHh Lpr lFf" class="bg-dark text-white">
    <!-- NAVBAR (HEADER) -->
    <q-header class="glass-header text-white" height-hint="64">
      <q-toolbar class="q-px-lg" style="height: 64px">
        <q-btn
          v-if="auth.token"
          flat
          dense
          round
          icon="menu"
          aria-label="Menu"
          @click="toggleLeftDrawer"
          class="q-mr-sm hover-glow-btn"
        />

        <!-- App Logo & Title -->
        <q-avatar size="32px" class="shadow-glow q-mr-sm">
          <img src="icons/favicon-128x128.png">
        </q-avatar>
        <q-toolbar-title class="text-weight-bold text-h6 tracking-wide bg-gradient-text-logo">
          PADI Real-time
        </q-toolbar-title>

        <q-space />

        <div class="row items-center q-gutter-md">
          <!-- Language Switcher -->
          <q-btn-dropdown
            flat
            dense
            no-caps
            color="white"
            :label="currentLocaleLabel"
            icon="language"
            class="hover-glow-btn"
          >
            <q-list class="glass-menu text-white" style="min-width: 120px">
              <q-item clickable v-close-popup @click="changeLocale('en-US')">
                <q-item-section>English</q-item-section>
              </q-item>
              <q-item clickable v-close-popup @click="changeLocale('id-ID')">
                <q-item-section>Bahasa Indonesia</q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>

          <!-- Login Link -->
          <q-btn
            v-if="!auth.token"
            flat
            dense
            no-caps
            color="white"
            :label="t('nav.signIn')"
            icon="login"
            to="/login"
            class="hover-glow-btn"
          />

          <!-- User Information & Profile Dropdown -->
          <div
            v-if="auth.token"
            class="row items-center q-gutter-sm cursor-pointer profile-btn q-pa-xs rounded-borders"
          >
            <q-avatar size="32px" color="primary" text-color="white" class="shadow-glow">
              {{ auth.user?.username?.charAt(0).toUpperCase() || 'U' }}
            </q-avatar>
            <div class="gt-xs text-left q-ml-xs">
              <div class="text-caption text-weight-bold leading-none">
                @{{ auth.user?.username || 'User' }}
              </div>
              <div class="text-grey-5" style="font-size: 10px; line-height: 1">
                {{ auth.user?.role || 'Guest' }}
              </div>
            </div>
            <q-icon name="arrow_drop_down" size="20px" class="text-grey-4 gt-xs" />

            <q-menu
              auto-close
              class="glass-menu text-white"
              transition-show="jump-down"
              transition-hide="jump-up"
            >
              <q-list style="min-width: 150px">
                <q-item clickable class="hover-menu-item" @click="handleLogout">
                  <q-item-section avatar>
                    <q-icon name="logout" color="red-5" />
                  </q-item-section>
                  <q-item-section class="text-red-4">Sign Out</q-item-section>
                </q-item>
              </q-list>
            </q-menu>
          </div>
        </div>
      </q-toolbar>
      <q-separator dark style="opacity: 0.1" />
    </q-header>

    <!-- SIDEBAR (DRAWER) -->
    <q-drawer
      v-if="auth.token"
      v-model="leftDrawerOpen"
      show-if-above
      bordered
      class="glass-drawer"
      :width="260"
    >
      <div class="column justify-between h-full py-md">
        <!-- Sidebar Navigation -->
        <div>
          <!-- Sidebar Header / Category -->
          <div
            class="q-px-lg q-pt-md q-pb-sm text-grey-5 text-weight-bold text-uppercase tracking-wider"
            style="font-size: 10px"
          >
            Main Navigation
          </div>

          <q-list class="q-px-sm q-gutter-y-xs">
            <q-item
              v-for="link in linksList"
              :key="link.title"
              clickable
              :to="link.link"
              exact
              class="sidebar-item rounded-borders q-mx-sm"
              active-class="sidebar-item-active"
            >
              <q-item-section avatar class="min-w-0">
                <q-icon :name="link.icon" size="20px" class="sidebar-icon" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-weight-bold sidebar-label">{{ link.title }}</q-item-label>
                <q-item-label caption class="text-grey-5 sidebar-caption">{{
                  link.caption
                }}</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </div>

        <!-- Sidebar Footer -->
        <div class="q-pa-md text-center">
          <q-separator dark class="q-mb-md" style="opacity: 0.05" />
          <div class="text-caption text-grey-6" style="font-size: 10px">
            PADI Realtime Example App v1.0.0
          </div>
        </div>
      </div>
    </q-drawer>

    <!-- MAIN PAGE CONTAINER -->
    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from 'src/stores/auth'
import { useI18n } from 'vue-i18n'

const router = useRouter()
const auth = useAuthStore()
const { locale, t } = useI18n({ useScope: 'global' })

const leftDrawerOpen = ref(false)

const currentLocaleLabel = computed(() => {
  return locale.value === 'id-ID' ? 'ID' : 'EN'
})

const changeLocale = (newLocale) => {
  locale.value = newLocale
}

const linksList = computed(() => [
  {
    title: t('nav.home'),
    caption: 'Landing Page',
    icon: 'home',
    link: '/',
  },
  {
    title: t('nav.posts'),
    caption: 'Real-time CRUD Posts',
    icon: 'dashboard',
    link: '/posts',
  },
  {
    title: 'Chats',
    caption: 'WhatsApp Style Live Chat',
    icon: 'chat',
    link: '/chats',
  },
])

function toggleLeftDrawer() {
  leftDrawerOpen.value = !leftDrawerOpen.value
}

const handleLogout = () => {
  auth.logout()
  router.push('/login')
}
</script>

<style lang="scss">
// Custom Header styling
.glass-header {
  background: rgba(20, 20, 20, 0.7) !important;
  backdrop-filter: blur(12px) !important;
  -webkit-backdrop-filter: blur(12px) !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.bg-gradient-text-logo {
  background: linear-gradient(135deg, #00b4db 0%, #0083b0 100%);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}

.hover-glow-btn {
  transition: all 0.3s ease;
  &:hover {
    color: #00b4db !important;
    background: rgba(255, 255, 255, 0.05);
  }
}

// Profile button style
.profile-btn {
  transition: all 0.3s ease;
  &:hover {
    background: rgba(255, 255, 255, 0.06);
  }
}

// Custom Glass Drawer styling
.glass-drawer {
  background: rgba(15, 15, 15, 0.95) !important;
  border-right: 1px solid rgba(255, 255, 255, 0.06) !important;
}

// Sidebar links design
.sidebar-item {
  margin: 4px 8px;
  padding: 8px 12px;
  transition: all 0.3s ease;
  color: #b0bec5;

  .sidebar-icon {
    color: #b0bec5;
    transition: all 0.3s ease;
  }
  .sidebar-label {
    font-size: 13px;
  }
  .sidebar-caption {
    font-size: 10px;
    opacity: 0.8;
  }

  &:hover {
    background: rgba(255, 255, 255, 0.04) !important;
    color: #ffffff;
    .sidebar-icon {
      color: #00b4db;
    }
  }
}

.sidebar-item-active {
  background: linear-gradient(
    135deg,
    rgba(0, 180, 219, 0.15) 0%,
    rgba(0, 131, 176, 0.05) 100%
  ) !important;
  color: #00b4db !important;
  border-left: 3px solid #00b4db;
  border-radius: 0 8px 8px 0;

  .sidebar-icon {
    color: #00b4db !important;
  }
}

// Glass Dropdown Menu
.glass-menu {
  background: rgba(20, 20, 20, 0.9) !important;
  backdrop-filter: blur(12px) !important;
  -webkit-backdrop-filter: blur(12px) !important;
  border: 1px solid rgba(255, 255, 255, 0.08) !important;
  box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.4) !important;
  border-radius: 12px !important;

  .hover-menu-item {
    transition: all 0.3s ease;
    font-size: 13px;
    &:hover {
      background: rgba(255, 255, 255, 0.05);
    }
  }
}

.leading-none {
  line-height: 1;
}
.min-w-0 {
  min-width: 40px !important;
}
.h-full {
  height: 100%;
}
.py-md {
  padding-top: 16px;
  padding-bottom: 16px;
}
</style>
