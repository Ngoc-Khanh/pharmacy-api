<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('app.ai_api_url', env('AI_API_URL', 'https://api-ai.ngockhanh.me/api'));
    }

    /**
     * Gọi API để embedding medicine theo ID
     * 
     * @param string $medicineId ID của medicine cần embedding
     * @return array Response từ API embedding
     */
    public function embedMedicine(string $medicineId): array
    {
        try {
            $url = "{$this->baseUrl}/v1/embed/{$medicineId}/embed-medicine";
            Log::info("Calling embedding API", [
                'url' => $url,
                'medicine_id' => $medicineId,
            ]);
            $response = Http::timeout(30)->retry(3, 1000)->post($url);
            if ($response->successful()) {
                $data = $response->json();
                Log::info('Embedding API success', [
                    'medicine_id' => $medicineId,
                    'response' => $data,
                ]);
                return [
                    'success' => true,
                    'data' => $data,
                    'message' => 'Embedding completed successfully',
                ];
            } else {
                Log::error('Embedding API failed', [
                    'medicine_id' => $medicineId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Embedding API failed: ' . $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Embedding API exception', [
                'medicine_id' => $medicineId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'Failed to embed medicine',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Gọi API để xóa embedding medicine theo ID
     * 
     * @param string $medicineId ID của medicine cần xóa embedding
     * @return array Response từ API xóa embedding
     */
    public function deleteMedicineEmbedding(string $medicineId): array
    {
        try {
            $url = "{$this->baseUrl}/v1/embed/{$medicineId}/delete-medicine";
            Log::info('Calling delete embedding API', [
                'url' => $url,
                'medicine_id' => $medicineId,
            ]);
            $response = Http::timeout(30)->retry(3, 1000)->delete($url);
            if ($response->successful()) {
                $data = $response->json();
                Log::info('Delete embedding API success', [
                    'medicine_id' => $medicineId,
                    'response' => $data,
                ]);
                return [
                    'success' => true,
                    'data' => $data,
                    'message' => 'Embedding deleted successfully',
                ];
            } else {
                Log::error('Delete embedding API failed', [
                    'medicine_id' => $medicineId,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Delete embedding API failed: ' . $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Delete embedding API exception', [
                'medicine_id' => $medicineId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'data' => null,
                'message' => 'Delete embedding API error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Gọi API embedding bất đồng bộ (không chờ kết quả)
     * 
     * @param string $medicineId ID của medicine cần embedding
     * @return void
     */
    public function embedMedicineAsync(string $medicineId): void
    {
        try {
            $url = "{$this->baseUrl}/v1/embed/{$medicineId}/embed-medicine";
            Log::info('Calling embedding API async', [
                'url' => $url,
                'medicine_id' => $medicineId
            ]);
            // Gọi API không đồng bộ với timeout ngắn
            Http::timeout(5)->async()->post($url);
        } catch (\Exception $e) {
            Log::error('Async embedding API exception', [
                'medicine_id' => $medicineId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Gọi API xóa embedding bất đồng bộ (không chờ kết quả)
     * 
     * @param string $medicineId ID của medicine cần xóa embedding
     * @return void
     */
    public function deleteMedicineEmbeddingAsync(string $medicineId): void
    {
        try {
            $url = "{$this->baseUrl}/v1/embed/{$medicineId}/delete-medicine";
            Log::info('Calling delete embedding API async', [
                'url' => $url,
                'medicine_id' => $medicineId
            ]);
            // Gọi API không đồng bộ với timeout ngắn
            Http::timeout(5)
                ->async()
                ->delete($url);
        } catch (\Exception $e) {
            Log::error('Async delete embedding API exception', [
                'medicine_id' => $medicineId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
