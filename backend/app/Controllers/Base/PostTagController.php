<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\PostTag;
use App\Resources\PostTagResource;

class PostTagController extends Controller
{
    protected PostTag $model;
    /** @var array Relations for eager loading */
    protected array $withRelations = [
        'post:id,title',
        'tag:id,name',
        'createdBy:id,username'
    ];

    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new PostTag();
    }
    
    /**
     * Get all posttags with pagination
     * GET /posttags
     */
    public function index()
    {
        $page = max(1, (int)$this->request->query('page', 1));
        $perPage = min(100, max(1, (int)$this->request->query('per-page', 25)));
        $search = $this->request->query('search');
        
        $sortBy = $this->request->query('sort_by');
        $order = $this->request->query('order', 'asc');
        $orderBy = $sortBy ? ($sortBy . ' ' . (strtolower($order) === 'desc' ? 'DESC' : 'ASC')) : null;

        $query = $search ? PostTag::search(substr($search, 0, 255)) : PostTag::find();
        
        $result = $query->with(...$this->withRelations)
            ->orderBy($orderBy ?? 'id DESC')
            ->paginate($perPage, $page);

        return PostTagResource::collection($result);
    }
    
    /**
     * Get all posttags without pagination
     * GET /posttags/all
     */
    public function all()
    {
        $search = $this->request->query('search');
        $limit = min(5000, max(1, (int)$this->request->query('limit', 1000)));
        $sortBy = $this->request->query('sort_by');
        $order = $this->request->query('order', 'asc');
        $orderBy = $sortBy ? ($sortBy . ' ' . (strtolower($order) === 'desc' ? 'DESC' : 'ASC')) : null;

        $query = $search ? PostTag::search(substr($search, 0, 255)) : PostTag::find();
        
        $results = $query->with(...$this->withRelations)
            ->orderBy($orderBy ?? 'id DESC')
            ->limit($limit)
            ->all();

        return PostTagResource::collection($results);
    }
    
    /**
     * Get single posttag
     * GET /posttags/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        $posttag = PostTag::find()->with(...$this->withRelations)->findOrFailByPk($id);
        
        return PostTagResource::make($posttag);
    }
    
    /**
     * Create new posttag
     * POST /posttags
     */
    public function store()
    {
        $validated = $this->validate([
            'post_id' => 'required|integer',
            'tag_id' => 'required|integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            $posttag = PostTag::find()->with(...$this->withRelations)->findOrFailByPk($id);
            return $this->created(PostTagResource::make($posttag));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create posttag', $e);
        }
    }
    
    /**
     * Update posttag
     * PUT /posttags/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        PostTag::findOrFail($id);
        
        $validated = $this->validate([
            'post_id' => 'sometimes|integer',
            'tag_id' => 'sometimes|integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
            $posttag = PostTag::find()->with(...$this->withRelations)->findOrFailByPk($id);
            return PostTagResource::make($posttag);
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update posttag', $e);
        }
    }
    
    /**
     * Delete posttag
     * DELETE /posttags/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        PostTag::findOrFail($id);
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete posttag', $e);
        }
    }
}