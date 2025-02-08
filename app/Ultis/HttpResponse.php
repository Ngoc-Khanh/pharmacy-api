<?php

namespace App\Ultis;

class HttpResponse
{
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
