export default {
  failed: 'Action failed',
  success: 'Action was successful',
  nav: {
    home: 'Home',
    posts: 'Posts Dashboard',
    comments: 'Comments Dashboard',
    tags: 'Tags Dashboard',
    signOut: 'Sign Out',
    signIn: 'Sign In',
    register: 'Register'
  },
  home: {
    subtitle: 'A high-performance, real-time reactive application demo.',
    syncTitle: 'Real-Time Sync',
    syncDesc: 'Experience instant client-to-client synchronization powered by SSE (Server-Sent Events) and Mercure.',
    authTitle: 'Secure Auth',
    authDesc: 'Complete registration, confirmation validation, and secure JWT token-based authentication flow.',
    btnDashboard: 'Go to Dashboard',
    btnGetStarted: 'Get Started',
    architectureTitle: 'Real-time Broadcast Architecture',
    noteTitle: 'Active Mode Note:',
    noteDesc: 'This demo application is currently configured using the Direct Method (No Queue) to ensure 100% instant sync locally. For high-load production environments (busy sites), you are highly recommended to use the Queue system.',
    runQueueTitle: 'How to run the Queue Worker (If Queue Mode is Enabled):',
    runQueueDesc: 'Open a new terminal window in the project root directory and execute the following command:',
    directTitle: 'Direct Method (No Queue)',
    directPros: 'Instant data transmission (0-10ms), no background process needed in development.',
    directCons: 'Adds synchronous workload to the web server, blocks the main request if Mercure is slow/busy, potentially reduces throughput under high traffic.',
    queueTitle: 'Queue Method (Background Worker)',
    queuePros: 'Extremely lightweight main requests (<1ms), high fault-tolerance (auto-retry if Mercure is down).',
    queueCons: 'Requires an active background worker process, introduces local processing delay on database queue (unless using Redis).',
    prosLabel: 'Pros:',
    consLabel: 'Cons:'
  }
}
