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
          <h1 class="text-h4 text-weight-bold q-my-none bg-gradient-text">Real-Time Posts</h1>
          <p class="text-grey-4 q-mt-sm q-mb-none">Created and updated records will sync across all browsers instantly.</p>
        </div>
        <q-btn
          color="primary"
          icon="add"
          label="Create New Post"
          class="glow-btn"
          @click="openCreateDialog"
        />
      </div>

      <div class="row q-mb-md">
        <q-input
          v-model="searchQuery"
          label="Search posts..."
          dark
          filled
          color="primary"
          class="full-width"
          clearable
          @update:model-value="fetchPosts"
        >
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
      </div>

      <!-- POSTS GRID -->
      <div v-if="fetching" class="row justify-center q-my-xl">
        <q-spinner-dots color="primary" size="40px" />
      </div>
      <div v-else-if="posts.length === 0" class="text-center q-my-xl text-grey-5">
        <q-icon name="folder_open" size="64px" class="q-mb-md" />
        <div class="text-h6">No posts found</div>
        <div>Create a post to see it here in real-time.</div>
      </div>
      <div v-else class="row q-col-gutter-lg">
        <div v-for="post in posts" :key="post.id" class="col-12 col-md-4">
          <q-card class="post-card glass-panel text-white column justify-between" style="height: 100%;">
            <q-card-section>
              <div class="row justify-between items-start q-mb-sm">
                <span class="text-caption text-grey-5">#{{ post.id }} | @{{ post.user?.username || 'Unknown' }}</span>
                <q-badge :color="post.status === 'published' ? 'green' : 'amber'" outline>
                  {{ post.status || 'draft' }}
                </q-badge>
              </div>
              <h3
                class="text-h6 text-weight-bold q-mt-none q-mb-sm text-primary cursor-pointer"
                @click="router.push(`/posts/${post.id}`)"
              >
                {{ post.title }}
              </h3>
              <p class="text-body2 text-grey-4 text-justify line-clamp-3">
                {{ post.excerpt || (post.content ? post.content.replace(/<[^>]*>/g, '') : 'No description provided.') }}
              </p>
            </q-card-section>

            <q-card-section class="q-pt-none">
              <div class="row justify-between items-center text-caption text-grey-5 q-mb-md">
                <span>Slug: <code>{{ post.slug }}</code></span>
                <span>Views: {{ post.views || 0 }}</span>
              </div>
              <q-separator dark class="q-mb-md" />
              <div class="row justify-end q-gutter-sm">
                <q-btn
                  flat
                  round
                  color="primary"
                  icon="visibility"
                  size="sm"
                  @click="router.push(`/posts/${post.id}`)"
                >
                  <q-tooltip>View Details</q-tooltip>
                </q-btn>
                <q-btn
                  flat
                  round
                  color="amber"
                  icon="edit"
                  size="sm"
                  @click="openEditDialog(post)"
                >
                  <q-tooltip>Edit Post</q-tooltip>
                </q-btn>
                <q-btn
                  flat
                  round
                  color="red"
                  icon="delete"
                  size="sm"
                  @click="confirmDelete(post)"
                >
                  <q-tooltip>Delete Post</q-tooltip>
                </q-btn>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <!-- CREATE/EDIT DIALOG -->
    <q-dialog v-model="postDialog" persistent>
      <q-card class="glass-panel text-white dialog-card" style="width: 800px; max-width: 90vw;">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6 text-weight-bold text-primary">
            {{ isEditing ? 'Edit Post' : 'Create New Post' }}
          </div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section class="scroll" style="max-height: 75vh;">
          <q-form @submit="savePost" class="q-gutter-md">
            <q-input
              v-model="postForm.title"
              label="Title *"
              dark
              filled
              required
              @update:model-value="onTitleChange"
            />
            <q-input
              v-model="postForm.slug"
              label="Slug *"
              dark
              filled
              required
              hint="Unique identifier, generated automatically from title"
            />
            <q-input
              v-model="postForm.excerpt"
              label="Excerpt"
              dark
              filled
              type="textarea"
              rows="2"
            />
            <div class="text-subtitle2 text-grey-4 q-mb-xs">Content</div>
            <q-editor
              v-model="postForm.content"
              min-height="8rem"
              dark
              flat
              bordered
              class="bg-transparent text-white"
            />
            <q-select
              v-model="postForm.status"
              :options="['draft', 'published']"
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
          Are you sure you want to delete post <strong>"{{ selectedPost?.title }}"</strong>? This action cannot be undone.
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
import { usePostsStore } from 'src/stores/posts'

const router = useRouter()
const auth = useAuthStore()
const postsStore = usePostsStore()

// Deconstruct reactive states from stores
const { posts, fetching, saving, searchQuery, sseStatus, sseStatusColor, sseStatusClass } = storeToRefs(postsStore)

const fetchPosts = () => postsStore.fetchPosts()

// Dialog state
const postDialog = ref(false)
const deleteDialog = ref(false)
const isEditing = ref(false)
const selectedPost = ref(null)

const postForm = ref({
  title: '',
  slug: '',
  excerpt: '',
  content: '',
  status: 'draft',
  views: 0
})

// Lifecycle
onMounted(() => {
  postsStore.fetchPosts()
  postsStore.connectSSE()
})

onUnmounted(() => {
  postsStore.closeSSE()
})

// Watch token to automatically connect/disconnect SSE (e.g. if logged out from another source)
watch(
  () => auth.token,
  (newToken) => {
    if (newToken) {
      postsStore.fetchPosts()
      postsStore.connectSSE()
    } else {
      postsStore.closeSSE()
    }
  }
)

const handleLogout = () => {
  auth.logout()
  router.push('/login')
}

const openCreateDialog = () => {
  isEditing.value = false
  postForm.value = {
    title: '',
    slug: '',
    excerpt: '',
    content: '',
    status: 'draft',
    views: 0
  }
  postDialog.value = true
}

const openEditDialog = (post) => {
  isEditing.value = true
  selectedPost.value = post
  postForm.value = {
    title: post.title,
    slug: post.slug,
    excerpt: post.excerpt,
    content: post.content,
    status: post.status || 'draft',
    views: post.views || 0
  }
  postDialog.value = true
}

const confirmDelete = (post) => {
  selectedPost.value = post
  deleteDialog.value = true
}

const onTitleChange = (val) => {
  if (!isEditing.value) {
    postForm.value.slug = val
      .toLowerCase()
      .replace(/[^a-z0-9 -]/g, '') // remove invalid chars
      .replace(/\s+/g, '-') // collapse whitespace and replace by -
      .replace(/-+/g, '-') // collapse dashes
      .trim()
  }
}

const savePost = async () => {
  let success = false
  if (isEditing.value) {
    success = await postsStore.updatePost(selectedPost.value.id, postForm.value)
  } else {
    success = await postsStore.createPost(postForm.value)
  }
  if (success) {
    postDialog.value = false
  }
}

const handleDelete = async () => {
  const success = await postsStore.deletePost(selectedPost.value.id)
  if (success) {
    deleteDialog.value = false
  }
}
</script>

<style lang="scss">
// Premium dark-mode variables and classes
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

.post-card {
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
