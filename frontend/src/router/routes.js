const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { 
        path: '', 
        component: () => import('pages/IndexPage.vue')
      },
      { 
        path: 'posts', 
        component: () => import('pages/post/IndexPage.vue'),
        meta: { requiresAuth: true }
      },
      { 
        path: 'login', 
        component: () => import('pages/auth/LoginPage.vue'),
        meta: { guestOnly: true }
      },
      {
        path: 'posts/:id',
        component: () => import('pages/post/DetailPage.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'chats',
        component: () => import('pages/chat/IndexPage.vue'),
        meta: { requiresAuth: true }
      }
    ],
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue'),
  },
]

export default routes
