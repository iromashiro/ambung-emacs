<?php

namespace App\Repositories\Eloquent;

use App\Models\Cart;
use App\Repositories\Interfaces\CartRepositoryInterface;

class CartRepository implements CartRepositoryInterface
{
    protected $model;

    public function __construct(Cart $model)
    {
        $this->model = $model;
    }

    public function findByUserId($userId)
    {
        return $this->model->where('user_id', $userId)
            ->with('product.store')
            ->get();
    }

    public function findBySessionId($sessionId)
    {
        return $this->model->where('session_id', $sessionId)
            ->with('product.store')
            ->get();
    }

    public function findByUserOrSession($userId, $sessionId)
    {
        if ($userId) {
            return $this->findByUserId($userId);
        }

        return $this->findBySessionId($sessionId);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $cart = $this->model->findOrFail($id);
        $cart->update($data);
        return $cart;
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }
}
