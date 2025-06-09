<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    protected $message = 'Insufficient stock for the requested quantity';
    
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'insufficient_stock',
                'message' => $this->getMessage()
            ], 422);
        }
        
        return back()->withErrors(['stock' => $this->getMessage()]);
    }
}