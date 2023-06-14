<?php

namespace EvgenDev\LaravelPlupload;

use Closure;
use EvgenDev\LaravelPlupload\Filters\Extensions;
use EvgenDev\LaravelPlupload\Filters\Filesize;
use Illuminate\Http\Request;

class Manager
{
    protected $request;
    protected $receiver;
    protected $filesize = null;
    protected $extensions = null;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function receive($name, $handler){
        $this->receiver = new Receiver($this->request, $this->filesize, $this->extensions);
        return $this->receiver->receive($name, $handler);
    }

    public function sizelimit($filesize,
                             string $units = Filesize::FILE_SIZE_MB,
                             string $system = Filesize::BYTES_SYSTEM_BINARY){

        $this->filesizeLimit =  new Filesize($filesize, $units, $system);
        return $this;
    }

    public function extensions(mixed $extensions){
        $this->extensions =  new Extensions($extensions);
        return $this;
    }
}
