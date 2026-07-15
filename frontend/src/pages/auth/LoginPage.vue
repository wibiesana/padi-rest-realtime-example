<template>
  <q-page class="q-pa-md bg-dark text-white flex flex-center" style="min-height: 100vh;">
    <!-- AUTHENTICATION CONTAINER -->
    <div class="auth-card-wrapper q-pa-lg">
      <div class="text-center q-mb-xl">
        <h2 class="text-h3 text-weight-bold bg-gradient-text q-my-none">PADI Framework</h2>
        <p class="text-subtitle1 text-grey-5">Real-time CRUD Demo Application</p>
      </div>

      <q-card class="auth-card glass-panel text-white">
        <q-tabs
          v-model="tab"
          dense
          class="text-grey-4"
          active-color="primary"
          indicator-color="primary"
          align="justify"
          narrow-indicator
        >
          <q-tab name="login" label="Login" />
          <q-tab name="register" label="Register" />
        </q-tabs>

        <q-separator dark />

        <q-tab-panels v-model="tab" animated class="bg-transparent text-white">
          <!-- LOGIN PANEL -->
          <q-tab-panel name="login">
            <q-form @submit="handleLogin" class="q-gutter-md">
              <q-input
                v-model="loginForm.username"
                label="Username or Email"
                dark
                filled
                required
                color="primary"
              />
              <q-input
                v-model="loginForm.password"
                label="Password"
                type="password"
                dark
                filled
                required
                color="primary"
              />
              <div class="row justify-between items-center q-mt-sm">
                <q-checkbox
                  v-model="rememberMe"
                  label="Remember Me"
                  dark
                  color="primary"
                />
              </div>
              <div class="row justify-end q-mt-md">
                <q-btn
                  label="Login"
                  type="submit"
                  color="primary"
                  :loading="loading"
                  class="full-width glow-btn"
                />
              </div>
            </q-form>
          </q-tab-panel>

          <!-- REGISTER PANEL -->
          <q-tab-panel name="register">
            <q-form @submit="handleRegister" class="q-gutter-md">
              <q-input
                v-model="registerForm.username"
                label="Username"
                dark
                filled
                required
                color="primary"
              />
              <q-input
                v-model="registerForm.email"
                label="Email"
                type="email"
                dark
                filled
                required
                color="primary"
              />
              <q-input
                v-model="registerForm.password"
                label="Password (min 8 chars, 1 uppercase, 1 special)"
                type="password"
                dark
                filled
                required
                color="primary"
              />
              <q-input
                v-model="registerForm.password_confirmation"
                label="Confirm Password"
                type="password"
                dark
                filled
                required
                color="primary"
              />
              <div class="row justify-end q-mt-md">
                <q-btn
                  label="Register"
                  type="submit"
                  color="primary"
                  :loading="loading"
                  class="full-width glow-btn"
                />
              </div>
            </q-form>
          </q-tab-panel>
        </q-tab-panels>
      </q-card>
    </div>
  </q-page>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'src/stores/auth'

const $q = useQuasar()
const router = useRouter()
const auth = useAuthStore()
// Auth Tabs and Forms
const tab = ref('login')
const loading = ref(false)
const rememberMe = ref(false)
const loginForm = ref({ username: '', password: '' })
const registerForm = ref({ username: '', email: '', password: '', password_confirmation: '' })

onMounted(() => {
  const savedUsername = localStorage.getItem('remembered_username')
  if (savedUsername) {
    loginForm.value.username = savedUsername
    rememberMe.value = true
  }
})

// Authentication
const handleLogin = async () => {
  loading.value = true
  try {
    const result = await auth.login(loginForm.value.username, loginForm.value.password)
    if (result.success) {
      if (rememberMe.value) {
        localStorage.setItem('remembered_username', loginForm.value.username)
      } else {
        localStorage.removeItem('remembered_username')
      }
      $q.notify({
        type: 'positive',
        message: 'Logged in successfully',
        position: 'top-right'
      })
      router.push('/')
    } else {
      $q.notify({
        type: 'negative',
        message: result.message,
        position: 'top-right'
      })
    }
  } catch (e) {
    $q.notify({
      type: 'negative',
      message: e.message || 'Login failed',
      position: 'top-right'
    })
  } finally {
    loading.value = false
  }
}

const handleRegister = async () => {
  loading.value = true
  try {
    const result = await auth.register(
      registerForm.value.username,
      registerForm.value.email,
      registerForm.value.password,
      registerForm.value.password_confirmation
    )
    if (result.success) {
      $q.notify({
        type: 'positive',
        message: 'Account registered & logged in successfully',
        position: 'top-right'
      })
      router.push('/')
    } else {
      let errMsg = result.message
      if (result.errors) {
        // Validation errors
        errMsg = Object.values(result.errors).flat().join(', ')
      }
      $q.notify({
        type: 'negative',
        message: errMsg,
        position: 'top-right'
      })
    }
  } catch (e) {
    $q.notify({
      type: 'negative',
      message: e.message || 'Registration failed',
      position: 'top-right'
    })
  } finally {
    loading.value = false
  }
}
</script>

<style lang="scss">
.bg-gradient-text {
  background: linear-gradient(135deg, #00b4db 0%, #0083b0 100%);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}

.auth-card-wrapper {
  width: 100%;
  max-width: 450px;
}

.glass-panel {
  background: rgba(255, 255, 255, 0.03);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
}

.auth-card {
  padding: 10px;
}

.glow-btn {
  box-shadow: 0 0 15px rgba(0, 180, 219, 0.4);
  transition: all 0.3s ease;
  &:hover {
    box-shadow: 0 0 25px rgba(0, 180, 219, 0.7);
    transform: translateY(-2px);
  }
}
</style>
