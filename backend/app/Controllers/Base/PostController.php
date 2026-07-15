<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Post;
use App\Resources\PostResource;

class PostController extends Controller
{
    protected Post $model;
    /** @var array Relations for eager loading */
    protected array $withRelations = [
        'user:id,username',
        'createdBy:id,username',
        'updatedBy:id,username'
    ];

    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Post();
    }
    
    /**
     * Get all posts with pagination
     * GET /posts
     */
    public function index()
    {
        $page = max(1, (int)$this->request->query('page', 1));
        $perPage = min(100, max(1, (int)$this->request->query('per-page', 25)));
        $search = $this->request->query('search');
        
        $sortBy = $this->request->query('sort_by');
        $order = $this->request->query('order', 'asc');
        $orderBy = $sortBy ? ($sortBy . ' ' . (strtolower($order) === 'desc' ? 'DESC' : 'ASC')) : null;

        $query = $search ? Post::search(substr($search, 0, 255)) : Post::find();
        
        $result = $query->with(...$this->withRelations)
            ->orderBy($orderBy ?? 'id DESC')
            ->paginate($perPage, $page);

        return PostResource::collection($result);
    }
    
    /**
     * Get all posts without pagination
     * GET /posts/all
     */
    public function all()
    {
        $search = $this->request->query('search');
        $limit = min(5000, max(1, (int)$this->request->query('limit', 1000)));
        $sortBy = $this->request->query('sort_by');
        $order = $this->request->query('order', 'asc');
        $orderBy = $sortBy ? ($sortBy . ' ' . (strtolower($order) === 'desc' ? 'DESC' : 'ASC')) : null;

        $query = $search ? Post::search(substr($search, 0, 255)) : Post::find();
        
        $results = $query->with(...$this->withRelations)
            ->orderBy($orderBy ?? 'id DESC')
            ->limit($limit)
            ->all();

        return PostResource::collection($results);
    }
    
    /**
     * Get single post
     * GET /posts/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        $post = Post::find()->with(...$this->withRelations)->findOrFailByPk($id);
        
        return PostResource::make($post);
    }
    
    /**
     * Create new post
     * POST /posts
     */
    public function store()
    {
        $validated = $this->validate([
            'user_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:posts,slug',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'status' => 'nullable|string|max:20',
            'published_at' => 'nullable|date_format:Y-m-d H:i:s',
            'views' => 'nullable|integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            $post = Post::find()->with(...$this->withRelations)->findOrFailByPk($id);
            return $this->created(PostResource::make($post));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create post', $e);
        }
    }
    
    /**
     * Update post
     * PUT /posts/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        Post::findOrFail($id);
        
        $validated = $this->validate([
            'user_id' => 'sometimes|integer',
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:posts,slug,' . $id,
            'content' => 'sometimes|nullable|string',
            'excerpt' => 'sometimes|nullable|string',
            'featured_image' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|string|max:20',
            'published_at' => 'sometimes|nullable|date_format:Y-m-d H:i:s',
            'views' => 'sometimes|nullable|integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
            $post = Post::find()->with(...$this->withRelations)->findOrFailByPk($id);
            return PostResource::make($post);
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update post', $e);
        }
    }
    
    /**
     * Delete post
     * DELETE /posts/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        Post::findOrFail($id);
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete post', $e);
        }
    }
}