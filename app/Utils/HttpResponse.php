<?php

namespace App\Utils;

/**
 * @OA\Schema(
 *     schema="HttpResponse",
 *     title="HTTP Response",
 *     description="Standard API response format for Pharmacity Store",
 *     @OA\Property(property="data", type="object", nullable=true, description="Response data"),
 *     @OA\Property(property="message", type="string", description="Response message"),
 *     @OA\Property(property="status", type="integer", description="HTTP status code"),
 *     @OA\Property(property="locale", type="string", description="Response locale"),
 *     @OA\Property(property="error", type="object", nullable=true, description="Error details if any")
 * )
 */
class HttpResponse
{
  /**
   * Format response data to a standardized JSON structure
   *
   * @param mixed $data The response data
   * @param string $message The response message (or translation key)
   * @param int $status HTTP status code
   * @param string $locale The locale for translation
   * @param mixed $errors Error details if any
   * @return array Standardized response array
   *
   * @OA\Response(
   *     response="SuccessResponse",
   *     description="Standard success response",
   *     @OA\JsonContent(ref="#/components/schemas/HttpResponse")
   * )
   */
  public static function toJson(
    mixed $data = null,
    string $message = "success",
    int $status = 200,
    string $locale = "en_US",
    mixed $errors = null,
  ) {
    $message = is_array($message) ?
      __(key: $message[0], replace: $message[1], locale: $locale) :
      __($message, locale: $locale);
    return [
      "data" => $data,
      "message" => $message,
      "status" => $status,
      "locale" => $locale,
      "error" => $errors,
    ];
  }
}
