<template>
  <q-page class="bg-dark text-white flex flex-stretch q-pa-none" style="height: calc(100vh - 64px); min-height: 0;">
    <div class="row full-width full-height no-wrap">
      <!-- LEFT PANEL: USERS LIST (WhatsApp Style) -->
      <div v-if="!selectedUser || !$q.screen.lt.md" class="col-12 col-md-4 col-lg-3 column border-right glass-panel no-border-radius">
        <!-- Search bar -->
        <div class="q-pa-md border-bottom">
          <div class="row items-center justify-between q-mb-sm">
            <span class="text-subtitle1 text-weight-bold text-primary">Chats</span>
            <q-chip outline dark :color="chatsStore.sseStatusColor" size="xs">
              <span class="status-dot q-mr-xs" :class="chatsStore.sseStatusClass"></span>
              Real-time: {{ chatsStore.sseStatus }}
            </q-chip>
          </div>
          <q-input
            v-model="searchQuery"
            placeholder="Search users..."
            dark
            filled
            dense
            color="primary"
            clearable
          >
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>

        <!-- Users List -->
        <q-scroll-area class="col">
          <q-list class="q-py-xs">
            <div v-if="loadingUsers" class="row justify-center q-my-md">
              <q-spinner-dots color="primary" size="30px" />
            </div>
            <div v-else-if="filteredUsers.length === 0" class="text-center q-my-md text-grey-5">
              No users found.
            </div>
            <q-item
              v-for="user in filteredUsers"
              :key="user.id"
              clickable
              :active="selectedUser && selectedUser.id === user.id"
              active-class="active-chat-item"
              class="q-py-md chat-user-item"
              @click="selectUser(user)"
            >
              <q-item-section avatar>
                <q-avatar color="primary" text-color="white" class="shadow-glow">
                  {{ user.username.charAt(0).toUpperCase() }}
                </q-avatar>
              </q-item-section>

              <q-item-section>
                <q-item-label class="text-weight-bold text-white">@{{ user.username }}</q-item-label>
                <q-item-label caption class="text-grey-5 text-weight-medium text-ellipsis">
                  {{ getLastMessage(user.id)?.message || 'No messages yet' }}
                </q-item-label>
              </q-item-section>

              <q-item-section side>
                <q-item-label caption class="text-grey-6" style="font-size: 10px;">
                  {{ getLastMessageTime(user.id) }}
                </q-item-label>
                <q-badge
                  v-if="getUnreadCount(user.id) > 0"
                  color="green"
                  rounded
                  class="q-mt-xs"
                >
                  {{ getUnreadCount(user.id) }}
                </q-badge>
              </q-item-section>
            </q-item>
          </q-list>
        </q-scroll-area>
      </div>

      <!-- RIGHT PANEL: CHAT WINDOW -->
      <div v-if="selectedUser || !$q.screen.lt.md" class="col column bg-chat-window relative-position">
        <template v-if="selectedUser">
          <!-- Chat Window Header -->
          <div class="row items-center q-px-lg q-py-sm border-bottom glass-header-panel">
            <q-btn
              flat
              round
              dense
              icon="arrow_back"
              color="white"
              class="lt-md q-mr-sm"
              @click="selectedUser = null"
            />
            <q-avatar color="primary" text-color="white" size="40px" class="q-mr-md shadow-glow">
              {{ selectedUser.username.charAt(0).toUpperCase() }}
            </q-avatar>
            <div>
              <div class="text-subtitle1 text-weight-bold">@{{ selectedUser.username }}</div>
              <div class="text-caption text-green-4 row items-center">
                <span class="online-dot q-mr-xs"></span>online
              </div>
            </div>
          </div>

          <!-- Messages Scroll Area -->
          <q-scroll-area ref="chatScrollRef" class="col q-pa-lg">
            <div class="column q-gutter-y-md">
              <div
                v-for="msg in currentChatMessages"
                :key="msg.id"
                class="full-width"
              >
                <q-chat-message
                  :name="msg.sender_id === auth.user?.id ? 'Me' : `@${selectedUser.username}`"
                  :avatar="msg.sender_id === auth.user?.id ? undefined : 'https://cdn.quasar.dev/img/avatar2.jpg'"
                  :text="[msg.message]"
                  :sent="msg.sender_id === auth.user?.id"
                  :bg-color="msg.sender_id === auth.user?.id ? 'primary' : 'grey-9'"
                  :text-color="msg.sender_id === auth.user?.id ? 'white' : 'grey-3'"
                  :stamp="formatMessageTime(msg.created_at)"
                />
              </div>
            </div>
          </q-scroll-area>

          <!-- Input Footer -->
          <div class="q-pa-md border-top glass-footer-panel">
            <q-form @submit.prevent="sendMessage" class="row items-center q-gutter-sm">
              <q-input
                v-model="newMessage"
                placeholder="Type a message..."
                dark
                filled
                dense
                class="col"
                bg-color="grey-10"
                autofocus
                @keydown.enter.prevent="sendMessage"
              />
              <q-btn
                type="submit"
                color="primary"
                icon="send"
                round
                dense
                :disable="!newMessage.trim()"
                class="glow-btn"
              />
            </q-form>
          </div>
        </template>

        <template v-else>
          <!-- Placeholder View when no chat selected -->
          <div class="col flex flex-center column text-center q-pa-lg">
            <q-avatar size="100px" color="grey-9" text-color="primary" class="q-mb-md">
              <q-icon name="chat" size="56px" />
            </q-avatar>
            <div class="text-h5 text-weight-bold text-white">Select a Chat</div>
            <p class="text-grey-5 q-mt-sm" style="max-width: 320px;">
              Choose a contact from the list on the left to start a real-time conversation.
            </p>
          </div>
        </template>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { useQuasar } from 'quasar'
import { storeToRefs } from 'pinia'
import { useAuthStore } from 'src/stores/auth'
import { useChatsStore } from 'src/stores/chats'
import { apiFetch } from 'src/lib/api'

const $q = useQuasar()
const auth = useAuthStore()
const chatsStore = useChatsStore()

const { chats } = storeToRefs(chatsStore)

const users = ref([])
const loadingUsers = ref(false)
const searchQuery = ref('')
const selectedUser = ref(null)
const newMessage = ref('')
const chatScrollRef = ref(null)

// Filter users list (exclude self and filter by search query)
const filteredUsers = computed(() => {
  const query = searchQuery.value?.toLowerCase() || ''
  return users.value.filter((u) => {
    const isSelf = u.id === auth.user?.id
    const matchesSearch = u.username.toLowerCase().includes(query)
    return !isSelf && matchesSearch
  })
})

// Current selected chat messages
const currentChatMessages = computed(() => {
  if (!selectedUser.value) return []
  return chats.value.filter((c) => {
    const isSentToSelected = c.sender_id === auth.user?.id && c.receiver_id === selectedUser.value.id
    const isReceivedFromSelected = c.sender_id === selectedUser.value.id && c.receiver_id === auth.user?.id
    return isSentToSelected || isReceivedFromSelected
  })
})

const fetchUsers = async () => {
  loadingUsers.value = true
  try {
    const res = await apiFetch('/users/all')
    users.value = res.item || res || []
  } catch (e) {
    console.error('Failed to fetch users:', e)
  } finally {
    loadingUsers.value = false
  }
}

const selectUser = (user) => {
  selectedUser.value = user
  // Scroll to bottom on selection
  scrollToBottom()
  
  // Mark messages from this user as read
  markAsRead(user.id)
}

const markAsRead = async (userId) => {
  // Find unread messages from this user
  const unread = chats.value.filter(c => c.sender_id === userId && c.receiver_id === auth.user?.id && !c.is_read)
  for (const msg of unread) {
    try {
      await apiFetch(`/chats/${msg.id}`, {
        method: 'PUT',
        body: JSON.stringify({ is_read: 1 })
      })
    } catch (e) {
      console.error(e)
    }
  }
}

const getUnreadCount = (userId) => {
  return chats.value.filter(c => c.sender_id === userId && c.receiver_id === auth.user?.id && !c.is_read).length
}

const getLastMessage = (userId) => {
  const filtered = chats.value.filter((c) => {
    const isSent = c.sender_id === auth.user?.id && c.receiver_id === userId
    const isRecv = c.sender_id === userId && c.receiver_id === auth.user?.id
    return isSent || isRecv
  })
  if (filtered.length === 0) return null
  return filtered[filtered.length - 1]
}

const getLastMessageTime = (userId) => {
  const last = getLastMessage(userId)
  if (!last) return ''
  return formatMessageTime(last.created_at)
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || !selectedUser.value) return
  
  const text = newMessage.value
  newMessage.value = ''
  
  const sent = await chatsStore.sendChatMessage(selectedUser.value.id, text)
  if (sent) {
    scrollToBottom()
  }
}

const scrollToBottom = () => {
  nextTick(() => {
    if (chatScrollRef.value) {
      chatScrollRef.value.setScrollPercentage('vertical', 1.0, 300)
    }
  })
}

// Watch messages count of current chat to auto-scroll on new message
watch(
  () => currentChatMessages.value.length,
  () => {
    scrollToBottom()
  }
)

const formatMessageTime = (dateStr) => {
  if (!dateStr) return ''
  try {
    const d = new Date(dateStr)
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  } catch {
    return dateStr
  }
}

// Lifecycle
onMounted(() => {
  fetchUsers()
  chatsStore.fetchChats()
  chatsStore.connectSSE()
})

onUnmounted(() => {
  chatsStore.closeSSE()
})

// Watch token to automatically connect/disconnect SSE
watch(
  () => auth.token,
  (newToken) => {
    if (newToken) {
      fetchUsers()
      chatsStore.fetchChats()
      chatsStore.connectSSE()
    } else {
      chatsStore.closeSSE()
    }
  }
)
</script>

<style lang="scss" scoped>
.border-right {
  border-right: 1px solid rgba(255, 255, 255, 0.08);
}
.border-bottom {
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.border-top {
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}
.no-border-radius {
  border-radius: 0 !important;
}

.glass-panel {
  background: rgba(255, 255, 255, 0.02);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

.chat-user-item {
  transition: all 0.2s ease;
  border-left: 3px solid transparent;
  &:hover {
    background: rgba(255, 255, 255, 0.04);
  }
}

.active-chat-item {
  background: rgba(0, 180, 219, 0.08) !important;
  border-left: 3px solid #00b4db;
  .text-white {
    color: #00b4db !important;
  }
}

.bg-chat-window {
  background-color: #0c0f14;
  background-image: radial-gradient(rgba(0, 180, 219, 0.03) 1px, transparent 0);
  background-size: 24px 24px;
}

.glass-header-panel {
  background: rgba(15, 20, 30, 0.7);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

.glass-footer-panel {
  background: rgba(15, 20, 30, 0.85);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

.text-ellipsis {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.shadow-glow {
  box-shadow: 0 0 12px rgba(0, 180, 219, 0.35);
}

.glow-btn {
  box-shadow: 0 0 12px rgba(0, 180, 219, 0.4);
  transition: all 0.3s ease;
  &:hover {
    box-shadow: 0 0 20px rgba(0, 180, 219, 0.7);
    transform: translateY(-1px);
  }
}

.online-dot {
  display: inline-block;
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background-color: #4caf50;
  box-shadow: 0 0 6px #4caf50;
}

.status-dot {
  display: inline-block;
  width: 6px;
  height: 6px;
  border-radius: 50%;
  &.status-connected {
    background-color: #4caf50;
    box-shadow: 0 0 6px #4caf50;
  }
  &.status-connecting {
    background-color: #ffeb3b;
    box-shadow: 0 0 6px #ffeb3b;
  }
  &.status-disconnected {
    background-color: #f44336;
    box-shadow: 0 0 6px #f44336;
  }
}
</style>
