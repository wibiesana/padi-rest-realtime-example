<template>
  <q-page class="q-pa-md bg-dark text-white flex flex-center" style="min-height: 100vh;">
    <!-- MAIN CRUD DASHBOARD CONTAINER -->
    <div class="dashboard-wrapper full-width" style="max-width: 1200px;">
      <!-- TOP NAVIGATION BAR -->
      <div class="row justify-between items-center q-mb-xl glass-panel q-pa-md rounded-borders">
        <div class="row items-center q-gutter-md">
          <q-avatar size="42px" color="primary" text-color="white" class="shadow-glow">
            {{ auth.user?.username?.charAt(0).toUpperCase() || 'U' }}
          </q-avatar>
          <div>
            <div class="text-subtitle1 text-weight-bold">Hi, {{ auth.user?.username || 'User' }}</div>
            <div class="text-caption text-grey-5">Role: {{ auth.user?.role || 'Guest' }}</div>
          </div>
        </div>

        <div class="row items-center q-gutter-md">
          <!-- SSE CONNECTION STATUS INDICATOR -->
          <q-chip outline dark :color="sseStatusColor" class="q-px-md sse-status-chip">
            <span class="status-dot q-mr-sm" :class="sseStatusClass"></span>
            Real-time: {{ sseStatus }}
          </q-chip>
          <q-btn outline color="red-5" label="Logout" @click="handleLogout" icon="logout" />
        </div>
      </div>

      <!-- MAIN HEADER & SEARCH -->
      <div class="row justify-between items-center q-mb-lg">
        <div>
          <h1 class="text-h4 text-weight-bold q-my-none bg-gradient-text">Real-Time Comments</h1>
          <p class="text-grey-4 q-mt-sm q-mb-none">Created and updated comments sync instantly using Mercure SSE.</p>
        </div>
        <q-btn
          color="primary"
          icon="add"
          label="Add New Comment"
          class="glow-btn"
          @click="openCreateDialog"
        />
      </div>

      <div class="row q-mb-md">
        <q-input
          v-model="searchQuery"
          label="Search comments..."
          dark
          filled
          color="primary"
          class="full-width"
          clearable
          @update:model-value="fetchComments"
        >
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
      </div>

      <!-- COMMENTS GRID -->
      <div v-if="fetching" class="row justify-center q-my-xl">
        <q-spinner-dots color="primary" size="40px" />
      </div>
      <div v-else-if="comments.length === 0" class="text-center q-my-xl text-grey-5">
        <q-icon name="forum" size="64px" class="q-mb-md" />
        <div class="text-h6">No comments found</div>
        <div>Create a comment to see it here in real-time.</div>
      </div>
      <div v-else class="row q-col-gutter-lg">
        <div v-for="comment in comments" :key="comment.id" class="col-12 col-md-4">
          <q-card class="comment-card glass-panel text-white column justify-between" style="height: 100%;">
            <q-card-section>
              <div class="row justify-between items-start q-mb-sm">
                <span class="text-caption text-grey-5">#{{ comment.id }} | @{{ comment.user?.username || 'Unknown' }}</span>
                <q-badge :color="getStatusColor(comment.status)" outline>
                  {{ comment.status || 'pending' }}
                </q-badge>
              </div>
              <div class="text-caption text-primary q-mb-md text-weight-bold">
                Post: "{{ comment.post?.title || 'Unknown Post' }}"
              </div>
              <p class="text-body2 text-grey-4 text-justify line-clamp-3" v-html="comment.content || 'No content provided.'"></p>
            </q-card-section>

            <q-card-section class="q-pt-none">
              <q-separator dark class="q-mb-md" />
              <div class="row justify-between items-center text-caption text-grey-5">
                <span>{{ formatDate(comment.created_at) }}</span>
                <div class="row q-gutter-sm">
                  <q-btn
                    flat
                    round
                    color="amber"
                    icon="edit"
                    size="sm"
                    @click="openEditDialog(comment)"
                  >
                    <q-tooltip>Edit Comment</q-tooltip>
                  </q-btn>
                  <q-btn
                    flat
                    round
                    color="red"
                    icon="delete"
                    size="sm"
                    @click="confirmDelete(comment)"
                  >
                    <q-tooltip>Delete Comment</q-tooltip>
                  </q-btn>
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <!-- CREATE/EDIT DIALOG -->
    <q-dialog v-model="commentDialog" persistent>
      <q-card class="glass-panel text-white dialog-card" style="width: 800px; max-width: 90vw;">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6 text-weight-bold text-primary">
            {{ isEditing ? 'Edit Comment' : 'Create New Comment' }}
          </div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section class="scroll" style="max-height: 75vh;">
          <q-form @submit="saveComment" class="q-gutter-md">
            <q-select
              v-model="commentForm.post_id"
              :options="postOptions"
              label="Select Post *"
              dark
              filled
              emit-value
              map-options
              required
              :loading="loadingPosts"
            />
            <div class="text-subtitle2 text-grey-4 q-mb-xs">Comment Content</div>
            <q-editor
              v-model="commentForm.content"
              min-height="6rem"
              dark
              flat
              bordered
              class="bg-transparent text-white"
            />
            <q-select
              v-model="commentForm.status"
              :options="['pending', 'approved', 'spam']"
              label="Status"
              dark
              filled
            />

            <div class="row justify-end q-gutter-sm q-mt-md">
              <q-btn label="Cancel" flat color="grey-5" v-close-popup />
              <q-btn label="Save" type="submit" color="primary" :loading="saving" class="glow-btn" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- DELETE CONFIRM DIALOG -->
    <q-dialog v-model="deleteDialog" persistent>
      <q-card class="glass-panel text-white dialog-card">
        <q-card-section class="row items-center">
          <q-avatar icon="warning" color="red" text-color="white" />
          <span class="q-ml-sm text-h6">Delete Confirmation</span>
        </q-card-section>

        <q-card-section class="q-pt-none">
          Are you sure you want to delete this comment? This action cannot be undone.
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="grey-5" v-close-popup />
          <q-btn label="Delete" color="red" @click="handleDelete" :loading="saving" class="glow-btn" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useAuthStore } from 'src/stores/auth'
import { useCommentsStore } from 'src/stores/comments'
import { apiFetch } from 'src/lib/api'

const router = useRouter()
const auth = useAuthStore()
const commentsStore = useCommentsStore()

const { comments, fetching, saving, searchQuery, sseStatus, sseStatusColor, sseStatusClass } = storeToRefs(commentsStore)

// Dialog state
const commentDialog = ref(false)
const deleteDialog = ref(false)
const isEditing = ref(false)
const selectedComment = ref(null)

const commentForm = ref({
  post_id: null,
  content: '',
  status: 'pending'
})

// Posts option list
const postOptions = ref([])
const loadingPosts = ref(false)

const fetchPostOptions = async () => {
  loadingPosts.value = true
  try {
    const res = await apiFetch('/posts/all')
    const list = res.item || res || []
    postOptions.value = list.map(post => ({
      label: post.title,
      value: post.id
    }))
  } catch (e) {
    console.error('Failed to fetch posts options:', e)
  } finally {
    loadingPosts.value = false
  }
}

// Lifecycle
onMounted(() => {
  commentsStore.fetchComments()
  commentsStore.connectSSE()
  fetchPostOptions()
})

onUnmounted(() => {
  commentsStore.closeSSE()
})

// Watch token to reconnect SSE
watch(
  () => auth.token,
  (newToken) => {
    if (newToken) {
      commentsStore.fetchComments()
      commentsStore.connectSSE()
    } else {
      commentsStore.closeSSE()
    }
  }
)

const handleLogout = () => {
  auth.logout()
  router.push('/login')
}

const openCreateDialog = () => {
  isEditing.value = false
  commentForm.value = {
    post_id: postOptions.value.length > 0 ? postOptions.value[0].value : null,
    content: '',
    status: 'pending'
  }
  commentDialog.value = true
}

const openEditDialog = (comment) => {
  isEditing.value = true
  selectedComment.value = comment
  commentForm.value = {
    post_id: comment.post_id,
    content: comment.content,
    status: comment.status || 'pending'
  }
  commentDialog.value = true
}

const confirmDelete = (comment) => {
  selectedComment.value = comment
  deleteDialog.value = true
}

const saveComment = async () => {
  let success = false
  if (isEditing.value) {
    success = await commentsStore.updateComment(selectedComment.value.id, commentForm.value)
  } else {
    success = await commentsStore.createComment(commentForm.value)
  }
  if (success) {
    commentDialog.value = false
  }
}

const handleDelete = async () => {
  const success = await commentsStore.deleteComment(selectedComment.value.id)
  if (success) {
    deleteDialog.value = false
  }
}

const getStatusColor = (status) => {
  if (status === 'approved') return 'green'
  if (status === 'spam') return 'red'
  return 'amber'
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  try {
    const d = new Date(dateStr)
    return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  } catch {
    return dateStr
  }
}
</script>

<style lang="scss" scoped>
.bg-gradient-text {
  background: linear-gradient(135deg, #00b4db 0%, #0083b0 100%);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}

.glass-panel {
  background: rgba(255, 255, 255, 0.03);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
}

.dialog-card {
  border-radius: 16px;
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
  box-shadow: 0 0 15px rgba(0, 180, 219, 0.3);
}

.comment-card {
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  cursor: default;
  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 35px 0 rgba(0, 180, 219, 0.2);
    border-color: rgba(0, 180, 219, 0.3);
  }
}

.sse-status-chip {
  font-weight: 500;
}

.status-dot {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  &.status-connected {
    background-color: #4caf50;
    box-shadow: 0 0 8px #4caf50;
    animation: pulse 1.5s infinite;
  }
  &.status-connecting {
    background-color: #ffeb3b;
    box-shadow: 0 0 8px #ffeb3b;
    animation: pulse 1.5s infinite;
  }
  &.status-disconnected {
    background-color: #f44336;
    box-shadow: 0 0 8px #f44336;
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 0.5;
    box-shadow: 0 0 4px currentColor;
  }
  50% {
    opacity: 1;
    box-shadow: 0 0 12px currentColor;
  }
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
