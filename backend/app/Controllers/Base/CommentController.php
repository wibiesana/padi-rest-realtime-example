<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Comment;
use App\Resources\CommentResource;

class CommentController extends Controller
{
    protected Comment $model;
    /** @var array Relations for eager loading */
    protected array $withRelations = [
        'post:id,title',
        'user:id,username',
        'parent:id,id',
        'createdBy:id,username',
        'updatedBy:id,username'
    ];

    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Comment();
    }
    
    /**
     * Get all comments with pagination
     * GET /comments
     */
    public function index()
    {
        $page = max(1, (int)$this->request->query('page', 1));
        $perPage = min(100, max(1, (int)$this->request->query('per-page', 25)));
        $search = $this->request->query('search');
        
        $sortBy = $this->request->query('sort_by');
        $order = $this->request->query('order', 'asc');
        $orderBy = $sortBy ? ($sortBy . ' ' . (strtolower($order) === 'desc' ? 'DESC' : 'ASC')) : null;

        $query = $search ? Comment::search(substr($search, 0, 255)) : Comment::find();
        
        $result = $query->with(...$this->withRelations)
            ->orderBy($orderBy ?? 'id DESC')
            ->paginate($perPage, $page);

        return CommentResource::collection($result);
    }
    
    /**
     * Get all comments without pagination
     * GET /comments/all
     */
    public function all()
    {
        $search = $this->request->query('search');
        $limit = min(5000, max(1, (int)$this->request->query('limit', 1000)));
        $sortBy = $this->request->query('sort_by');
        $order = $this->request->query('order', 'asc');
        $orderBy = $sortBy ? ($sortBy . ' ' . (strtolower($order) === 'desc' ? 'DESC' : 'ASC')) : null;

        $query = $search ? Comment::search(substr($search, 0, 255)) : Comment::find();
        
        $results = $query->with(...$this->withRelations)
            ->orderBy($orderBy ?? 'id DESC')
            ->limit($limit)
            ->all();

        return CommentResource::collection($results);
    }
    
    /**
     * Get single comment
     * GET /comments/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        $comment = Comment::find()->with(...$this->withRelations)->findOrFailByPk($id);
        
        return CommentResource::make($comment);
    }
    
    /**
     * Create new comment
     * POST /comments
     */
    public function store()
    {
        $validated = $this->validate([
            'post_id' => 'required|integer',
            'user_id' => 'required|integer',
            'parent_id' => 'nullable|integer',
            'content' => 'required|string',
            'status' => 'nullable|string|max:20'
        ]);
        
        try {
            $id = $this->model->create($validated);
            $comment = Comment::find()->with(...$this->withRelations)->findOrFailByPk($id);
            return $this->created(CommentResource::make($comment));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create comment', $e);
        }
    }
    
    /**
     * Update comment
     * PUT /comments/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        Comment::findOrFail($id);
        
        $validated = $this->validate([
            'post_id' => 'sometimes|integer',
            'user_id' => 'sometimes|integer',
            'parent_id' => 'sometimes|nullable|integer',
            'content' => 'sometimes|string',
            'status' => 'sometimes|nullable|string|max:20'
        ]);
        
        try {
            $this->model->update($id, $validated);
            $comment = Comment::find()->with(...$this->withRelations)->findOrFailByPk($id);
            return CommentResource::make($comment);
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update comment', $e);
        }
    }
    
    /**
     * Delete comment
     * DELETE /comments/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        Comment::findOrFail($id);
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete comment', $e);
        }
    }
}