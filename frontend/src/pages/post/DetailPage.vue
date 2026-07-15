<template>
  <q-page class="q-pa-md bg-dark text-white flex flex-center" style="min-height: 100vh">
    <div class="dashboard-wrapper full-width" style="max-width: 900px">
      <!-- TOP NAVIGATION BAR -->
      <div class="row justify-between items-center q-mb-xl glass-panel q-pa-md rounded-borders">
        <div class="row items-center q-gutter-md">
          <q-btn flat round icon="arrow_back" color="white" to="/posts">
            <q-tooltip>Back to Posts</q-tooltip>
          </q-btn>
          <div class="text-subtitle1 text-weight-bold">Post Details</div>
        </div>

        <div class="row items-center q-gutter-md">
          <!-- SSE STATUS CHIPS -->
          <q-chip outline dark :color="postsStore.sseStatusColor" class="q-px-md sse-status-chip">
            <span class="status-dot q-mr-sm" :class="postsStore.sseStatusClass"></span>
            Posts Sync: {{ postsStore.sseStatus }}
          </q-chip>
          <q-chip
            outline
            dark
            :color="commentsStore.sseStatusColor"
            class="q-px-md sse-status-chip"
          >
            <span class="status-dot q-mr-sm" :class="commentsStore.sseStatusClass"></span>
            Comments Sync: {{ commentsStore.sseStatus }}
          </q-chip>
        </div>
      </div>

      <!-- POST DETAIL CARD -->
      <div v-if="loading" class="row justify-center q-my-xl">
        <q-spinner-dots color="primary" size="40px" />
      </div>
      <div v-else-if="!post" class="text-center q-my-xl text-grey-5">
        <q-icon name="error_outline" size="64px" class="q-mb-md" />
        <div class="text-h6">Post not found</div>
      </div>
      <div v-else>
        <!-- POST HEADER -->
        <div class="q-mb-lg">
          <div class="row justify-between items-center q-mb-sm">
            <span class="text-caption text-grey-5">
              By @{{ post.user?.username || 'Unknown' }} | Published
              {{ formatDate(post.created_at) }}
            </span>
            <q-badge :color="post.status === 'published' ? 'green' : 'amber'" outline>
              {{ post.status || 'draft' }}
            </q-badge>
          </div>
          <h1 class="text-h3 text-weight-bold q-my-none text-primary">{{ post.title }}</h1>
          <div class="row items-center q-mt-sm text-grey-5 text-caption q-gutter-md">
            <span>Slug: <code>{{ post.slug }}</code></span>
            <span><q-icon name="visibility" class="q-mr-xs" />Views: {{ post.views || 0 }}</span>
          </div>
          
          <!-- Post Tags Chips -->
          <div class="row q-gutter-xs q-mt-md">
            <q-chip
              v-for="tag in getPostTags()"
              :key="tag.id"
              size="sm"
              color="secondary"
              text-color="white"
              outline
            >
              <q-icon name="local_offer" size="14px" class="q-mr-xs" />
              {{ tag.name }}
            </q-chip>
          </div>
        </div>

        <!-- EXCERPT -->
        <blockquote
          v-if="post.excerpt"
          class="q-pa-md q-my-md text-italic text-grey-4 glass-panel excerpt-box"
        >
          {{ post.excerpt }}
        </blockquote>

        <!-- CONTENT -->
        <div class="q-my-xl text-body1 text-grey-3 post-content" v-html="post.content"></div>

        <q-separator dark class="q-my-xl" />

        <!-- COMMENTS SECTION -->
        <div>
          <h2 class="text-h5 text-weight-bold q-mb-md text-secondary">
            Comments ({{ postComments.length }})
          </h2>

          <!-- ADD NEW COMMENT FORM -->
          <div class="q-mb-xl glass-panel q-pa-md">
            <div class="text-subtitle2 q-mb-sm text-weight-bold text-grey-4">Add a Comment</div>
            <q-form @submit="submitComment" class="q-gutter-md">
              <q-editor
                v-model="newCommentText"
                min-height="6rem"
                dark
                flat
                bordered
                class="bg-transparent text-white"
                placeholder="Write your comment here..."
                required
              />
              <div class="row justify-between items-center">
                <q-select
                  v-model="newCommentStatus"
                  :options="['pending', 'approved', 'spam']"
                  label="Status"
                  dark
                  filled
                  dense
                  style="width: 150px"
                />
                <q-btn
                  type="submit"
                  color="secondary"
                  label="Submit Comment"
                  icon="send"
                  :loading="commentsStore.saving"
                  class="glow-btn"
                />
              </div>
            </q-form>
          </div>

          <!-- COMMENTS LIST -->
          <div v-if="postComments.length === 0" class="text-center q-my-lg text-grey-5">
            <q-icon name="chat_bubble_outline" size="48px" class="q-mb-sm" />
            <div>No comments on this post yet. Be the first to comment!</div>
          </div>
          <div v-else class="q-gutter-md">
            <div
              v-for="comment in postComments"
              :key="comment.id"
              class="comment-item glass-panel q-pa-md"
            >
              <div class="row justify-between items-center q-mb-xs">
                <div class="row items-center q-gutter-xs">
                  <q-avatar
                    size="24px"
                    color="secondary"
                    text-color="white"
                    class="text-weight-bold"
                  >
                    {{ comment.user?.username?.charAt(0).toUpperCase() || 'U' }}
                  </q-avatar>
                  <span class="text-subtitle2 text-weight-bold"
                    >@{{ comment.user?.username || 'Unknown' }}</span
                  >
                  <span class="text-caption text-grey-5"
                    >• {{ formatDate(comment.created_at) }}</span
                  >
                </div>
                <div class="row items-center q-gutter-sm">
                  <q-badge :color="getCommentStatusColor(comment.status)" outline size="sm">
                    {{ comment.status }}
                  </q-badge>
                  <q-btn
                    v-if="comment.user_id === auth.user?.id"
                    flat
                    round
                    color="red"
                    icon="delete"
                    size="xs"
                    @click="deleteComment(comment.id)"
                  >
                    <q-tooltip>Delete Comment</q-tooltip>
                  </q-btn>
                </div>
              </div>
              <div
                class="text-body2 text-grey-3 q-mt-sm q-pl-md border-left-highlight"
                v-html="comment.content"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useQuasar } from 'quasar'
import { storeToRefs } from 'pinia'
import { useAuthStore } from 'src/stores/auth'
import { usePostsStore } from 'src/stores/posts'
import { useCommentsStore } from 'src/stores/comments'
import { useTagsStore } from 'src/stores/tags'
import { apiFetch } from 'src/lib/api'

const route = useRoute()
const $q = useQuasar()
const auth = useAuthStore()
const postsStore = usePostsStore()
const commentsStore = useCommentsStore()
const tagsStore = useTagsStore()

const { comments } = storeToRefs(commentsStore)
const { tags } = storeToRefs(tagsStore)

const post = ref(null)
const loading = ref(true)

// Post Tags local states
const postTags = ref([])

const fetchPostTags = async () => {
  try {
    const res = await apiFetch('/post-tags/all')
    postTags.value = res.item || res || []
  } catch (e) {
    console.error('Failed to fetch post tags:', e)
  }
}

let posttagsEventSource = null
const connectPostTagsSSE = () => {
  if (posttagsEventSource) {
    posttagsEventSource.close()
  }
  const hubUrl = import.meta.env.VITE_MERCURE_URL || 'http://localhost:8085/.well-known/mercure'
  const topic = encodeURIComponent('posttags')
  posttagsEventSource = new EventSource(`${hubUrl}?topic=${topic}`)
  posttagsEventSource.onmessage = (event) => {
    try {
      const payload = JSON.parse(event.data)
      const { event: ev, data, id } = payload
      if (ev === 'posttag_created') {
        if (!postTags.value.some(pt => pt.id === data.id)) {
          postTags.value.push(data)
        }
      } else if (ev === 'posttag_deleted') {
        const rawId = id || data
        const deletedId = parseInt(typeof rawId === 'object' && rawId !== null ? (rawId.id || rawId) : rawId)
        const idx = postTags.value.findIndex(pt => pt.id === deletedId)
        if (idx !== -1) {
          postTags.value.splice(idx, 1)
        }
      }
    } catch (e) {
      console.error('Failed to parse posttag SSE message:', e)
    }
  }
}

const closePostTagsSSE = () => {
  if (posttagsEventSource) {
    posttagsEventSource.close()
    posttagsEventSource = null
  }
}

const getPostTags = () => {
  if (!post.value) return []
  const associations = postTags.value.filter((pt) => pt.post_id === post.value.id)
  return associations.map((pt) => tags.value.find((t) => t.id === pt.tag_id)).filter((t) => !!t)
}

// Add comment state
const newCommentText = ref('')
const newCommentStatus = ref('approved')

// Filter comments belonging to this post
const postComments = computed(() => {
  return comments.value.filter((c) => c.post_id === parseInt(route.params.id))
})

const fetchPostDetail = async () => {
  loading.value = true
  try {
    const res = await apiFetch(`/posts/${route.params.id}`)
    post.value = res.item || res
  } catch {
    $q.notify({
      type: 'negative',
      message: 'Failed to load post details',
      position: 'top-right',
    })
  } finally {
    loading.value = false
  }
}

// Lifecycle
onMounted(() => {
  fetchPostDetail()
  commentsStore.fetchComments()
  commentsStore.connectSSE()
  postsStore.connectSSE()
  tagsStore.fetchTags()
  tagsStore.connectSSE()
  fetchPostTags()
  connectPostTagsSSE()
})

onUnmounted(() => {
  commentsStore.closeSSE()
  postsStore.closeSSE()
  tagsStore.closeSSE()
  closePostTagsSSE()
})

// Listen to posts real-time updates to update page content if updated remotely
watch(
  () => postsStore.posts,
  () => {
    const updatedPost = postsStore.posts.find((p) => p.id === parseInt(route.params.id))
    if (updatedPost) {
      post.value = updatedPost
    }
  },
  { deep: true },
)

const submitComment = async () => {
  if (!newCommentText.value.trim()) return

  const success = await commentsStore.createComment({
    post_id: parseInt(route.params.id),
    content: newCommentText.value,
    status: newCommentStatus.value,
  })

  if (success) {
    newCommentText.value = ''
  }
}

const deleteComment = async (commentId) => {
  $q.dialog({
    title: 'Confirm Delete',
    message: 'Are you sure you want to delete this comment?',
    cancel: true,
    persistent: true,
    ok: {
      color: 'red',
      label: 'Delete',
    },
  }).onOk(async () => {
    await commentsStore.deleteComment(commentId)
  })
}

const getCommentStatusColor = (status) => {
  if (status === 'approved') return 'green'
  if (status === 'spam') return 'red'
  return 'amber'
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  try {
    const d = new Date(dateStr)
    return (
      d.toLocaleDateString() +
      ' ' +
      d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    )
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

.excerpt-box {
  border-left: 4px solid #00b4db;
}

.post-content {
  line-height: 1.8;
  word-break: break-word;
  ::v-deep(p) {
    margin-bottom: 1.2rem;
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
  box-shadow: 0 0 15px rgba(0, 180, 219, 0.3);
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

.border-left-highlight {
  border-left: 2px solid rgba(0, 180, 219, 0.4);
  padding-left: 10px;
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
</style>
