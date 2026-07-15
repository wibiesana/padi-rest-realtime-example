<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Tag;
use App\Resources\TagResource;

class TagController extends Controller
{
    protected Tag $model;
    /** @var array Relations for eager loading */
    protected array $withRelations = [
        'createdBy:id,username',
        'updatedBy:id,username'
    ];

    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Tag();
    }
    
    /**
     * Get all tags with pagination
     * GET /tags
     */
    public function index()
    {
        $page = max(1, (int)$this->request->query('page', 1));
        $perPage = min(100, max(1, (int)$this->request->query('per-page', 25)));
        $search = $this->request->query('search');
        
        $sortBy = $this->request->query('sort_by');
        $order = $this->request->query('order', 'asc');
        $orderBy = $sortBy ? ($sortBy . ' ' . (strtolower($order) === 'desc' ? 'DESC' : 'ASC')) : null;

        $query = $search ? Tag::search(substr($search, 0, 255)) : Tag::find();
        
        $result = $query->with(...$this->withRelations)
            ->orderBy($orderBy ?? 'id DESC')
            ->paginate($perPage, $page);

        return TagResource::collection($result);
    }
    
    /**
     * Get all tags without pagination
     * GET /tags/all
     */
    public function all()
    {
        $search = $this->request->query('search');
        $limit = min(5000, max(1, (int)$this->request->query('limit', 1000)));
        $sortBy = $this->request->query('sort_by');
        $order = $this->request->query('order', 'asc');
        $orderBy = $sortBy ? ($sortBy . ' ' . (strtolower($order) === 'desc' ? 'DESC' : 'ASC')) : null;

        $query = $search ? Tag::search(substr($search, 0, 255)) : Tag::find();
        
        $results = $query->with(...$this->withRelations)
            ->orderBy($orderBy ?? 'id DESC')
            ->limit($limit)
            ->all();

        return TagResource::collection($results);
    }
    
    /**
     * Get single tag
     * GET /tags/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        $tag = Tag::find()->with(...$this->withRelations)->findOrFailByPk($id);
        
        return TagResource::make($tag);
    }
    
    /**
     * Create new tag
     * POST /tags
     */
    public function store()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:100|unique:tags,name',
            'slug' => 'required|string|max:100|unique:tags,slug',
            'description' => 'nullable|string'
        ]);
        
        try {
            $id = $this->model->create($validated);
            $tag = Tag::find()->with(...$this->withRelations)->findOrFailByPk($id);
            return $this->created(TagResource::make($tag));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create tag', $e);
        }
    }
    
    /**
     * Update tag
     * PUT /tags/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        Tag::findOrFail($id);
        
        $validated = $this->validate([
            'name' => 'sometimes|string|max:100|unique:tags,name,' . $id,
            'slug' => 'sometimes|string|max:100|unique:tags,slug,' . $id,
            'description' => 'sometimes|nullable|string'
        ]);
        
        try {
            $this->model->update($id, $validated);
            $tag = Tag::find()->with(...$this->withRelations)->findOrFailByPk($id);
            return TagResource::make($tag);
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update tag', $e);
        }
    }
    
    /**
     * Delete tag
     * DELETE /tags/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        Tag::findOrFail($id);
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete tag', $e);
        }
    }
}