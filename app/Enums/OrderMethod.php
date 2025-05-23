<?php

namespace App\Enums;

enum OrderMethod: string
{
  case COD = "COD";
  case CREDIT_CARD = "CREDIT-CARD";
  case BANK_TRANSFER = "BANK-TRANSFER";

  public function label(): string
  {
    return match ($this) {
      self::COD => "Tiền mặt",
      self::CREDIT_CARD => "Thẻ tín dụng",
      self::BANK_TRANSFER => "Chuyển khoản",
    };
  }
}
