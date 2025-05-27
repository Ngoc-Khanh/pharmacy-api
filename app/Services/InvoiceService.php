<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Order;

class InvoiceService
{
  /**
   * Tạo hóa đơn từ đơn hàng
   * 
   * @param Order $order Đơn hàng cần tạo hóa đơn
   * @return Invoice Hóa đơn được tạo
   */
  public function createFromOrder(Order $order)
  {
    $today = date('Ymd');
    $lastInvoice = Invoice::where('invoice_number', 'like', "INV-{$today}-%")
      ->orderBy('invoice_number', 'desc')
      ->first();
    $sequenceNumber = 1;
    if ($lastInvoice) {
      $parts = explode('-', $lastInvoice->invoice_number);
      $sequenceNumber = (int)end($parts) + 1;
    }
    $invoiceNumber = sprintf("INV-%s-%03d", $today, $sequenceNumber);
    return Invoice::create([
      'order_id' => $order->_id,
      'user_id' => $order->user_id,
      'invoice_number' => $invoiceNumber,
      'items' => $order->items,
      'total_price' => $order->total_price,
      'payment_method' => $order->payment_method,
      'issued_at' => now(),
      'status' => InvoiceStatus::PENDING->value,
      'created_at' => now(),
    ]);
  }
}
