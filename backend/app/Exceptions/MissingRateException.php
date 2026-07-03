<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Seçilen sefer için personelin pozisyonuna ücret tanımlı olmadığında fırlatılır.
 * Kullanıcıya hangi sefer/pozisyon için tanım eksik olduğunu açıkça söyler.
 */
class MissingRateException extends Exception
{
    public function __construct(string $tripName, string $positionName)
    {
        parent::__construct(
            "\"{$tripName}\" seferi için \"{$positionName}\" pozisyonuna tanımlı bir mesai ücreti bulunamadı. " .
            'Lütfen önce Sefer Yönetimi ekranından bu pozisyon için ücret tanımlayınız.'
        );
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => ['entries' => [$this->getMessage()]],
        ], 422);
    }
}
