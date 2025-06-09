<?php

namespace App\Repositories\Interfaces;

interface CartRepositoryInterface
{
    public function findByUserId($userId);
    public function findBySessionId($sessionId);
    public function findByUserOrSession($userId, $sessionId);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}
