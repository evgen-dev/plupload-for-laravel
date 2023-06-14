<?php

    namespace EvgenDev\LaravelPlupload\Filters;

    class Extensions{

        protected $extensions = [];

        public function __construct(mixed $extensions){
            $this->set($extensions);
        }

        public function set(mixed $extensions){
            if(is_string($extensions)){
                $this->extensions = array_filter(array_map([$this, 'prepare'], explode(',', $extensions)));
            }elseif(is_array($extensions)){
                $this->extensions = array_filter(array_map([$this, 'prepare'], $extensions));
            }else{
                throw new \InvalidArgumentException('Extensions must be array or string comma separated');
            }
        }

        public function get(){
            return $this->extensions;
        }

        public function add(string $extension){

            $extension = $this->prepare($extension);

            if($extension && !in_array($extension, $this->extensions)){
                $this->extensions[] = $extension;
            }

            return $this;
        }

        public function remove(string $extension){
            $extension = $this->prepare($extension);

            if($extension){
                $key = array_search($extension, $this->extensions);
                if($key !== false){
                    unset($this->extensions[$key]);
                }
            }

            return $this;
        }

        private function prepare($value){
            return mb_strtolower(trim($value));
        }
    }
