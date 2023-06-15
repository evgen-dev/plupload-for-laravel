<?php

namespace EvgenDev\LaravelPlupload;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use EvgenDev\LaravelPlupload\Filters\Extensions;
use EvgenDev\LaravelPlupload\Filters\Filesize;

class Receiver
{
    /**
     * @var int Delete temporary file older than this value (seconds)
     */
    private $maxFileAge = 7200; //7200 seconds (2 hours)

    protected $request;

    /**
     * @var Extensions Allowed extensions for upload, leave blank if unlimited
     */
    protected Extensions $extensions;

    /**
     * @var Filesize Maximum size of uploading file in bytes
     */
    protected Filesize $filesize;

    public function __construct(Request $request, ?Filesize $filesize, ?Extensions $extensions)
    {
        $this->request = $request;

        if($extensions !== null)
            $this->extensions = $extensions;

        if($filesize !== null)
            $this->filesize = $filesize;
    }

    /**
     * @return void
     * @throws PluploadException
     */
    public function validateExtensions(): void{
        $fileName = $this->request->input('name');
        $extension = mb_strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if(isset($this->extensions) && !in_array($extension, $this->extensions->get())){
            throw new PluploadException(__('validation.invalid_file_extension', ['extension' => $extension]), 415);
        }
    }

    /**
     * @param $inputFieldName
     * @return void
     * @throws PluploadInvalidFilesizeException
     */
    public function validateFilesizeAfterChunkUploaded($inputFieldName): void{
        if(!isset($this->filesize)){
            return;
        }

        $fileName = $this->getPath().DIRECTORY_SEPARATOR.$this->request->input('name');
        $fileName .= $this->hasChunks() ? '.part' : '';

        if(!file_exists($fileName)){
            return;
        }

        if(filesize($fileName) > $this->filesize->getFilesize(Filesize::FILE_SIZE_UNITS_B)){
            throw new PluploadInvalidFilesizeException(__('validation.max.file', [
                'attribute' => $inputFieldName,
                'max' => $this->filesize->getFilesize($this->filesize->getUnits()),
                'units' => $this->filesize->getUnits()
            ]), 413);
        }
    }

    public function getPath()
    {
        $path = storage_path().'/plupload';

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        return $path;
    }

    public function receiveSingle($name, Closure $handler)
    {
        if ($this->request->file($name)) {
            return $handler($this->request->file($name));
        }

        return false;
    }

    private function appendData($filePathPartial, UploadedFile $file)
    {
        if (!$out = @fopen($filePathPartial, 'ab')) {
            throw new PluploadException('Failed to open output stream.', 102);
        }

        if (!$in = @fopen($file->getPathname(), 'rb')) {
            throw new PluploadException('Failed to open input stream', 101);
        }
        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }

        @fclose($out);
        @fclose($in);
    }

    public function receiveChunks($name, Closure $handler)
    {
        $result = false;

        if ($this->request->file($name)) {
            $file = $this->request->file($name);
            $chunk = (int) $this->request->input('chunk', false);
            $chunks = (int) $this->request->input('chunks', false);
            $fileName = $this->request->input('name');

            $filePath = $this->getPath().'/'.$fileName.'.part';

            if($chunk > 0 && !file_exists($filePath)){
                return $result;
            }

            $this->appendData($filePath, $file);

            if ($chunk == $chunks - 1) {
                $file = new UploadedFile($filePath, $fileName, 'blob', UPLOAD_ERR_OK, true);

                $result = $handler($file);

                @unlink($filePath);
            }
        }

        return $result;
    }

    public function removeOldFiles()
    {
        $targetDir = $this->getPath();
        if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
            while (($file = readdir($dir)) !== false) {
                $tmpFilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                // Remove temp file if it is older than the max age and is not the current file
                if ((filemtime($tmpFilePath) < time() - $this->maxFileAge)) {
                    @unlink($tmpFilePath);
                }
            }
            closedir($dir);
        }

    }

    public function hasChunks()
    {
        return (bool) $this->request->get('chunks', false);
    }

    public function receive($name, Closure $handler)
    {
        $response = [];
        $response['jsonrpc'] = '2.0';

        $this->removeOldFiles();

        try {
            $this->validateExtensions();

            if ($this->hasChunks()) {
                $result = $this->receiveChunks($name, $handler);
            } else {
                $result = $this->receiveSingle($name, $handler);
            }

            $this->validateFilesizeAfterChunkUploaded($name);

            $response['result'] = $result;

        }catch (PluploadException $exception){
            $response['error'] = [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ];

        }catch (PluploadInvalidFilesizeException $exception){
            $filePath = $this->getPath().'/'.$this->request->input('name');
            @unlink($filePath);
            @unlink($filePath.'.part');

            $response['error'] = [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ];

            http_response_code($exception->getCode());

        }catch (\Exception $exception){
            $response['error'] = [
                'code' => '500',
                'message' => 'Unexpected Error'.(env('APP_DEBUG') ? ': '.$exception->getMessage() : '')
            ];
        }

        return $response;
    }
}
