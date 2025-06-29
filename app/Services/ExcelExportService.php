<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\Order;
use App\Models\Review;
use App\Models\Supplier;
use App\Models\User;
use Spatie\SimpleExcel\SimpleExcelWriter;

class ExcelExportService
{
    /**
     * Xuất tất cả dữ liệu hệ thống ra file Excel
     */
    public function exportAll()
    {
        $filename = 'pharmacy_system_data_' . date('Y-m-d_H-i-s') . '.xlsx';
        // Tạo file Excel với mutiple sheets
        $writer = SimpleExcelWriter::streamDownload($filename);
        // Sheet 1: Users
        $this->addUsersSheet($writer);
        // // Sheet 2: Suppliers  
        $this->addSuppliersSheet($writer);
        // // Sheet 3: Medicines
        $this->addMedicinesSheet($writer);
        // // Sheet 4: Orders
        $this->addOrdersSheet($writer);
        // // Sheet 5: Categories
        $this->addCategoriesSheet($writer);
        // Sheet 6: Invoices
        $this->addInvoicesSheet($writer);
        return $writer->toBrowser();
    }

    /**
     * Xuất dữ liệu theo từng module riêng lẻ
     */
    public function exportByModule(string $module)
    {
        $filename = $module . '_export_' . date('Y-m-d_H-i-s') . '.xlsx';
        $tempPath = storage_path('app/temp/' . $filename);
        
        // Tạo thư mục temp nếu chưa có
        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }
        
        $writer = SimpleExcelWriter::create($tempPath);

        switch ($module) {
            case 'users':
                $this->addUsersSheet($writer);
                break;
            case 'suppliers':
                $this->addSuppliersSheet($writer);
                break;
            case 'medicines':
                $this->addMedicinesSheet($writer);
                break;
            case 'orders':
                $this->addOrdersSheet($writer);
                break;
            case 'categories':
                $this->addCategoriesSheet($writer);
                break;
            case 'invoices':
                $this->addInvoicesSheet($writer);
                break;
            default:
                throw new \InvalidArgumentException("Module '{$module}' không được hỗ trợ");
        }

        $writer->close();
        
        // Trả về Laravel response
        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Thêm sheet Users
     */
    private function addUsersSheet(SimpleExcelWriter $writer): void
    {
        $users = User::all();
        $writer->addHeader([
            'ID',
            'Username',
            'Email',
            'Họ',
            'Tên',
            'Số điện thoại',
            'Vai trò',
            'Trạng thái',
            'Email đã xác thực',
            'Ngày tạo'
        ]);
        foreach ($users as $user) {
            $writer->addRow([
                $user->_id,
                $user->username,
                $user->email,
                $user->firstname,
                $user->lastname,
                $user->phone,
                $user->role?->value ?? '',
                $user->status?->value ?? '',
                $user->email_verified_at ? 'Đã xác thực' : 'Chưa xác thực',
                $user->created_at?->format('d/m/Y H:i:s') ?? ''
            ]);
        }
    }

    /**
     * Thêm sheet Suppliers
     */
    private function addSuppliersSheet(SimpleExcelWriter $writer): void
    {
        $suppliers = Supplier::all();
        $writer->addHeader([
            'ID',
            'Tên nhà cung cấp',
            'Địa chỉ',
            'Số điện thoại',
            'Email',
            'Ngày tạo'
        ]);
        foreach ($suppliers as $supplier) {
            $writer->addRow([
                $supplier->_id,
                $supplier->name,
                $supplier->address,
                $supplier->contact_phone,
                $supplier->contact_email,
                $supplier->created_at?->format('d/m/Y H:i:s') ?? ''
            ]);
        }
    }

    /**
     * Thêm sheet Medicines
     */
    private function addMedicinesSheet(SimpleExcelWriter $writer): void
    {
        $medicines = Medicine::with(['category', 'supplier'])->get();
        $writer->addHeader([
            'ID',
            'Tên thuốc',
            'Slug',
            'Danh mục',
            'Nhà cung cấp',
            'Mô tả',
            'Đánh giá trung bình',
            'Số lượt đánh giá',
            'Giá bán',
            'Giá gốc',
            'Giảm giá (%)',
            'Số lượng',
            'Giới hạn số lượng',
            'Trạng thái kho',
            'Nổi bật',
            'Đang hoạt động',
            'Thành phần',
            'Xuất xứ',
            'Đóng gói',
            'Hướng dẫn sử dụng (Người lớn)',
            'Hướng dẫn sử dụng (Trẻ em)',
            'Người tạo',
            'Ngày tạo'
        ]);
        foreach ($medicines as $medicine) {
            // Xử lý variants
            $variants = $medicine->variants ?? [];
            $price = $variants['price'] ?? 0;
            $originalPrice = $variants['original_price'] ?? 0;
            $discountPercent = $variants['discount_percent'] ?? 0;
            $quantity = $variants['quantity'] ?? 0;
            $limitQuantity = $variants['limit_quantity'] ?? 0;
            $stockStatus = $variants['stock_status'] ?? '';
            $isFeatured = isset($variants['is_featured']) ? ($variants['is_featured'] ? 'Có' : 'Không') : 'Không';
            $isActive = isset($variants['is_active']) ? ($variants['is_active'] ? 'Có' : 'Không') : 'Không';

            // Xử lý ratings
            $ratings = $medicine->ratings ?? [];
            $avgRating = $ratings['star'] ?? 0;
            $reviewCount = $ratings['review_count'] ?? 0;

            // Xử lý details
            $details = $medicine->details ?? [];
            $ingredients = $details['ingredients'] ?? '';
            $parameters = $details['paramaters'] ?? [];
            $origin = $parameters['origin'] ?? '';
            $packaging = $parameters['packaging'] ?? '';

            // Xử lý usageguide
            $usageguide = $medicine->usageguide ?? [];
            $dosage = $usageguide['dosage'] ?? [];
            $adultDosage = $dosage['adult'] ?? '';
            $childDosage = $dosage['child'] ?? '';

            $writer->addRow([
                $medicine->id,
                $medicine->name,
                $medicine->slug,
                $medicine->category?->name ?? '',
                $medicine->supplier?->name ?? '',
                $medicine->description,
                $avgRating,
                $reviewCount,
                number_format($price, 0, ',', '.') . ' VNĐ',
                number_format($originalPrice, 0, ',', '.') . ' VNĐ',
                $discountPercent . '%',
                $quantity,
                $limitQuantity,
                $stockStatus,
                $isFeatured,
                $isActive,
                $ingredients,
                $origin,
                $packaging,
                $adultDosage,
                $childDosage,
                $medicine->created_by,
                $medicine->created_at?->format('d/m/Y H:i:s') ?? ''
            ]);
        }
    }

    /**
     * Thêm sheet Orders
     */
    private function addOrdersSheet(SimpleExcelWriter $writer): void
    {
        $orders = Order::with('user')->get();
        $writer->addHeader([
            'ID',
            'Khách hàng',
            'Email khách hàng',
            'Trạng thái',
            'Tổng phụ',
            'Phí vận chuyển',
            'Giảm giá',
            'Tổng tiền',
            'Phương thức thanh toán',
            'Ngày đặt hàng'
        ]);
        foreach ($orders as $order) {
            $writer->addRow([
                $order->_id,
                $order->user?->firstname . ' ' . $order->user?->lastname ?? '',
                $order->user?->email ?? '',
                $order->status,
                number_format($order->sub_total, 0, ',', '.') . ' VNĐ',
                number_format($order->shipping_fee, 0, ',', '.') . ' VNĐ',
                number_format($order->discount, 0, ',', '.') . ' VNĐ',
                number_format($order->total_price, 0, ',', '.') . ' VNĐ',
                $order->payment_method,
                $order->created_at?->format('d/m/Y H:i:s') ?? ''
            ]);
        }
    }

    /**
     * Thêm sheet Categories
     */
    private function addCategoriesSheet(SimpleExcelWriter $writer): void
    {
        $categories = Category::all();
        $writer->addHeader([
                'ID',
                'Tên danh mục',
                'Slug',
                'Mô tả',
                'Ngày tạo'
            ]);
        foreach ($categories as $category) {
            $writer->addRow([
                $category->_id,
                $category->name,
                $category->slug,
                $category->description,
                $category->created_at?->format('d/m/Y H:i:s') ?? ''
            ]);
        }
    }

    /**
     * Thêm sheet Invoices
     */
    private function addInvoicesSheet(SimpleExcelWriter $writer): void
    {
        $invoices = Invoice::with(['user', 'order'])->get();
        $writer->addHeader([
                'ID',
                'Mã hóa đơn',
                'Tên tài khoản',
                'Email tài khoản',
                'Tên khách hàng',
                'Địa chỉ',
                'Số điện thoại',
                'Tổng tiền',
                'Phương thức thanh toán',
                'Trạng thái',
                'Ngày xuất hóa đơn',
                'Ngày tạo'
            ]);
            
        foreach ($invoices as $invoice) {
            // Xử lý issued_at an toàn
            $issuedAt = '';
            if ($invoice->issued_at) {
                if (is_string($invoice->issued_at)) {
                    $issuedAt = $invoice->issued_at;
                } elseif (method_exists($invoice->issued_at, 'format')) {
                    $issuedAt = $invoice->issued_at->format('d/m/Y H:i:s');
                }
            }

            // Xử lý shipping_address an toàn (có thể là array)
            $customerName = 'N/A';
            $customerAddress = 'N/A';
            $customerPhone = 'N/A';
            
            if ($invoice->order && $invoice->order->shipping_address) {
                $shippingAddress = $invoice->order->shipping_address;
                
                // Kiểm tra nếu là array
                if (is_array($shippingAddress)) {
                    $customerName = $shippingAddress['name'] ?? 'N/A';
                    $customerPhone = $shippingAddress['phone'] ?? 'N/A';
                    
                    // Ghép địa chỉ từ array
                    $addressParts = array_filter([
                        $shippingAddress['address_line1'] ?? '',
                        $shippingAddress['address_line2'] ?? '',
                        $shippingAddress['city'] ?? '',
                        $shippingAddress['state'] ?? '',
                        $shippingAddress['country'] ?? '',
                        $shippingAddress['postal_code'] ?? ''
                    ]);
                    $customerAddress = !empty($addressParts) ? implode(', ', $addressParts) : 'N/A';
                } else {
                    // Nếu là object
                    $customerName = $shippingAddress->name ?? 'N/A';
                    $customerPhone = $shippingAddress->phone ?? 'N/A';
                    
                    $addressParts = array_filter([
                        $shippingAddress->address_line1 ?? '',
                        $shippingAddress->address_line2 ?? '',
                        $shippingAddress->city ?? '',
                        $shippingAddress->state ?? '',
                        $shippingAddress->country ?? '',
                        $shippingAddress->postal_code ?? ''
                    ]);
                    $customerAddress = !empty($addressParts) ? implode(', ', $addressParts) : 'N/A';
                }
            }

            $writer->addRow([
                $invoice->_id,
                $invoice->invoice_number ?? '',
                $invoice->user ? $invoice->user->username : 'N/A',
                $invoice->user ? $invoice->user->email : 'N/A',
                $customerName,
                $customerAddress,
                $customerPhone,
                number_format($invoice->total_price ?? 0, 0, ',', '.') . ' VNĐ',
                $invoice->payment_method ?? '',
                $invoice->status,
                $issuedAt,
                $invoice->created_at?->format('d/m/Y H:i:s') ?? ''
            ]);
        }
    }
}
