<?php

namespace App\Exceptions;

use Exception;

class InvalidStatusTransitionException extends Exception
{
    protected $message = 'Invalid order status transition';
    
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'invalid_status_transition',
                'message' => $this->getMessage()
            ], 422);
        }
        
        return back()->withErrors(['status' => $this->getMessage()]);
    }
}