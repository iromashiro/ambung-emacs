<?php

namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    protected $errorCode;
    
    public function __construct(string $message = "", int $errorCode = 0, \Throwable $previous = null)
    {
        $this->errorCode = $errorCode;
        parent::__construct($message, 0, $previous);
    }
    
    public function getErrorCode()
    {
        return $this->errorCode;
    }
    
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'business_exception',
                'error_code' => $this->getErrorCode(),
                'message' => $this->getMessage()
            ], 422);
        }
        
        return back()->withErrors(['error' => $this->getMessage()]);
    }
}