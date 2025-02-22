<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use MongoDB\BSON\ObjectId;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Prefix("v2/invoices")]
#[Middleware("jwt.auth")]
class InvoicesController extends Controller
{
    #[Get("/all", "invoice.all")]
    public function getAllInvoices()
    {
        $invoices = Invoice::all();
        return $this->json($invoices, 'Get all invoices successful');
    }

    #[Get("/user-invoices", "invoice.user-invoices")]
    public function getUserInvoices(Request $request)
    {
        $userId = new ObjectId($request->user()->id);
        $invoices = Invoice::where('user_id', $userId)->with('user:name')->get()->map(function ($invoice) {
            $invoice->username = $invoice->user->name;
            return $invoice;
        });

        return $this->json($invoices, 'Get user invoices successful');
    }
}
