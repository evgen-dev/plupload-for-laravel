<?php

namespace EvgenDev\LaravelPlupload\Filters;

class Filesize{

    const FILE_SIZE_UNITS_B = 'B';

    const FILE_SIZE_UNITS_KB = 'KB';

    const FILE_SIZE_UNITS_MB = 'MB';

    const FILE_SIZE_UNITS_GB = 'GB';

    const BYTES_SYSTEM_DECIMAL = 'decimal';

    const BYTES_SYSTEM_BINARY = 'binary';

    const BYTES_IN_ONE_KB_DECIMAL = 1000;

    const BYTES_IN_ONE_KB_BINARY = 1024;


    /**
     * @var int Original filesize value
     */
    protected $filesize = 0;

    /**
     * @var int Filesize in bytes
     */
    protected $filesizeInBytes = 0;

    /**
     * @var string Original filesize units
     */
    protected $units = self::FILE_SIZE_UNITS_MB;

    /**
     * @var string Current bytes standard
     */
    protected $system = self::BYTES_SYSTEM_BINARY;


    /**
     * @param $filesize
     * @param string $units
     * @param string $system
     */
    public function __construct($filesize, string $units = self::FILE_SIZE_UNITS_MB, string $system = self::BYTES_SYSTEM_BINARY){
        $this->setUnits($units)
            ->setFilesize($filesize)
            ->setSystem($system);
    }

    /**
     * @param int $filesize
     * @return $this
     */
    public function setFilesize(int $filesize){
        if(!$filesize){
            throw \InvalidArgumentException('Filesize can be greater than zero');
        }

        $this->filesize = $filesize;

        $this->convertToBytes();

        return $this;
    }

    /**
     * @param string $units
     * @return $this
     */
    public function setUnits(string $units){

        if(!in_array($units, [self::FILE_SIZE_UNITS_B, self::FILE_SIZE_UNITS_KB,
            self::FILE_SIZE_UNITS_MB, self::FILE_SIZE_UNITS_GB])){

            throw \InvalidArgumentException('Unknown filesize units passed');
        }

        $this->units = $units;

        return $this;
    }

    /**
     * @param string $system
     * @return $this
     */
    public function setSystem(string $system){

        if(!in_array($system, [self::BYTES_SYSTEM_DECIMAL, self::BYTES_SYSTEM_BINARY])){
            throw \InvalidArgumentException('Unknown bytes system passed');
        }

        $this->system = $system;

        return $this;
    }

    /**
     * @param string $inUnits
     * @return float|int|void
     */
    public function getFilesize(string $inUnits = self::FILE_SIZE_UNITS_B){

        if(!in_array($inUnits, [self::FILE_SIZE_UNITS_B, self::FILE_SIZE_UNITS_KB,
            self::FILE_SIZE_UNITS_MB, self::FILE_SIZE_UNITS_GB])){

            throw \InvalidArgumentException('Unknown filesize units');
        }

        $bytesInKB = $this->getBytesInKilobyte();

        switch ($inUnits){
            case self::FILE_SIZE_UNITS_B:
                return $this->filesizeInBytes;
            case self::FILE_SIZE_UNITS_KB:
                return $this->filesizeInBytes / $bytesInKB;
            case self::FILE_SIZE_UNITS_MB:
                return $this->filesizeInBytes / pow($bytesInKB, 2);
            case self::FILE_SIZE_UNITS_GB:
                return $this->filesizeInBytes / pow($bytesInKB, 3);
        }
    }

    public function getBytesInKilobyte(){
        return $this->system === self::BYTES_SYSTEM_BINARY ?
            self::BYTES_IN_ONE_KB_BINARY : self::BYTES_IN_ONE_KB_DECIMAL;
    }

    protected function convertToBytes(){
        $bytesInKB = $this->getBytesInKilobyte();

        switch ($this->units){
            case self::FILE_SIZE_UNITS_KB:
                $this->filesizeInBytes =  $this->formatFloat($this->filesize * $bytesInKB, 0);
                break;

            case self::FILE_SIZE_UNITS_MB:
                $this->filesizeInBytes = $this->formatFloat($this->filesize * pow($bytesInKB, 2), 0, '');
                break;

            case self::FILE_SIZE_UNITS_GB:
                $this->filesizeInBytes =  $this->formatFloat($this->filesize * pow($bytesInKB, 3), 0);
                break;

            default: $this->filesizeInBytes = $this->filesize;
        }
        return $this;
    }

    protected function formatFloat($value, $decimals = 2): string{
        return number_format($value, $decimals, '.', '');
    }
}
