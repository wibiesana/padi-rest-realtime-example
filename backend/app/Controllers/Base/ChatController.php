<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Chat;
use App\Resources\ChatResource;

class ChatController extends Controller
{
    protected Chat $model;
    /** @var array Relations for eager loading */
    protected array $withRelations = [
        'sender:id,username',
        'receiver:id,username',
        'createdBy:id,username',
        'updatedBy:id,username'
    ];

    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Chat();
    }
    
    /**
     * Get all chats with pagination
     * GET /chats
     */
    public function index()
    {
        $page = max(1, (int)$this->request->query('page', 1));
        $perPage = min(100, max(1, (int)$this->request->query('per-page', 25)));
        $search = $this->request->query('search');
        
        $sortBy = $this->request->query('sort_by');
        $order = $this->request->query('order', 'asc');
        $orderBy = $sortBy ? ($sortBy . ' ' . (strtolower($order) === 'desc' ? 'DESC' : 'ASC')) : null;

        $query = $search ? Chat::search(substr($search, 0, 255)) : Chat::find();
        
        $result = $query->with(...$this->withRelations)
            ->orderBy($orderBy ?? 'id DESC')
            ->paginate($perPage, $page);

        return ChatResource::collection($result);
    }
    
    /**
     * Get all chats without pagination
     * GET /chats/all
     */
    public function all()
    {
        $search = $this->request->query('search');
        $limit = min(5000, max(1, (int)$this->request->query('limit', 1000)));
        $sortBy = $this->request->query('sort_by');
        $order = $this->request->query('order', 'asc');
        $orderBy = $sortBy ? ($sortBy . ' ' . (strtolower($order) === 'desc' ? 'DESC' : 'ASC')) : null;

        $query = $search ? Chat::search(substr($search, 0, 255)) : Chat::find();
        
        $results = $query->with(...$this->withRelations)
            ->orderBy($orderBy ?? 'id DESC')
            ->limit($limit)
            ->all();

        return ChatResource::collection($results);
    }
    
    /**
     * Get single chat
     * GET /chats/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        $chat = Chat::find()->with(...$this->withRelations)->findOrFailByPk($id);
        
        return ChatResource::make($chat);
    }
    
    /**
     * Create new chat
     * POST /chats
     */
    public function store()
    {
        $validated = $this->validate([
            'sender_id' => 'required|integer',
            'receiver_id' => 'required|integer',
            'message' => 'required|string',
            'is_read' => 'nullable|integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            $chat = Chat::find()->with(...$this->withRelations)->findOrFailByPk($id);
            return $this->created(ChatResource::make($chat));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create chat', $e);
        }
    }
    
    /**
     * Update chat
     * PUT /chats/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        Chat::findOrFail($id);
        
        $validated = $this->validate([
            'sender_id' => 'sometimes|integer',
            'receiver_id' => 'sometimes|integer',
            'message' => 'sometimes|string',
            'is_read' => 'sometimes|nullable|integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
            $chat = Chat::find()->with(...$this->withRelations)->findOrFailByPk($id);
            return ChatResource::make($chat);
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update chat', $e);
        }
    }
    
    /**
     * Delete chat
     * DELETE /chats/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        Chat::findOrFail($id);
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete chat', $e);
        }
    }
}