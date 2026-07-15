<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Job;
use App\Resources\JobResource;

class JobController extends Controller
{
    protected Job $model;
    /** @var array Relations for eager loading */
    protected array $withRelations = [];

    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Job();
    }
    
    /**
     * Get all jobs with pagination
     * GET /jobs
     */
    public function index()
    {
        $page = max(1, (int)$this->request->query('page', 1));
        $perPage = min(100, max(1, (int)$this->request->query('per-page', 25)));
        $search = $this->request->query('search');
        
        $sortBy = $this->request->query('sort_by');
        $order = $this->request->query('order', 'asc');
        $orderBy = $sortBy ? ($sortBy . ' ' . (strtolower($order) === 'desc' ? 'DESC' : 'ASC')) : null;

        $query = $search ? Job::search(substr($search, 0, 255)) : Job::find();
        
        $result = $query->with(...$this->withRelations)
            ->orderBy($orderBy ?? 'id DESC')
            ->paginate($perPage, $page);

        return JobResource::collection($result);
    }
    
    /**
     * Get all jobs without pagination
     * GET /jobs/all
     */
    public function all()
    {
        $search = $this->request->query('search');
        $limit = min(5000, max(1, (int)$this->request->query('limit', 1000)));
        $sortBy = $this->request->query('sort_by');
        $order = $this->request->query('order', 'asc');
        $orderBy = $sortBy ? ($sortBy . ' ' . (strtolower($order) === 'desc' ? 'DESC' : 'ASC')) : null;

        $query = $search ? Job::search(substr($search, 0, 255)) : Job::find();
        
        $results = $query->with(...$this->withRelations)
            ->orderBy($orderBy ?? 'id DESC')
            ->limit($limit)
            ->all();

        return JobResource::collection($results);
    }
    
    /**
     * Get single job
     * GET /jobs/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        $job = Job::find()->with(...$this->withRelations)->findOrFailByPk($id);
        
        return JobResource::make($job);
    }
    
    /**
     * Create new job
     * POST /jobs
     */
    public function store()
    {
        $validated = $this->validate([
            'queue' => 'required|string|max:255',
            'payload' => 'required|string',
            'attempts' => 'nullable|integer',
            'reserved_at' => 'nullable|integer',
            'available_at' => 'required|integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            $job = Job::find()->with(...$this->withRelations)->findOrFailByPk($id);
            return $this->created(JobResource::make($job));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create job', $e);
        }
    }
    
    /**
     * Update job
     * PUT /jobs/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        Job::findOrFail($id);
        
        $validated = $this->validate([
            'queue' => 'sometimes|string|max:255',
            'payload' => 'sometimes|string',
            'attempts' => 'sometimes|nullable|integer',
            'reserved_at' => 'sometimes|nullable|integer',
            'available_at' => 'sometimes|integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
            $job = Job::find()->with(...$this->withRelations)->findOrFailByPk($id);
            return JobResource::make($job);
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update job', $e);
        }
    }
    
    /**
     * Delete job
     * DELETE /jobs/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        Job::findOrFail($id);
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete job', $e);
        }
    }
}