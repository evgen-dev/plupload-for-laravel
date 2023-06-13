<?php

namespace EvgenDev\LaravelPlupload;

use Closure;
use Illuminate\Http\Request;

class Manager
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function receive($name, $extensions, $sizeLimit, Closure $handler)
    {
        $receiver = new Receiver($this->request, $extensions, $sizeLimit);

        return $receiver->receive($name, $handler);
    }
}
