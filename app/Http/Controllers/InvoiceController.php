<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Monolog\Handler\FirePHPHandler;

class InvoiceController extends Controller
{
    public function getInvoices()
    {
        $invoices = Invoice::with('customer')->orderBy('id', 'DESC')->get();

        return response()->json([
            'invoices' => $invoices
        ], 200);
    }

    public function searchInvoice(Request $request)
    {
        $search = $request->get('s');

        if ($search != null) {

            $invoices = Invoice::with('customer')
                ->where('id', 'LIKE', "%$search%")
                ->orWhere('number', 'LIKE', "%$search%")
                ->get();

            return response()->json([
                'invoices' => $invoices
            ], 200);
        } {
            return $this->getInvoices();
        }
    }

    public function createInvoice(Request $request)
    {

        if (!empty($request->number)) {

            $invoice = new Invoice;
            $invoice->number = (int)$request->number;
            $invoice->customer_id = $request->customer_id;
            $invoice->date = $request->date;
            $invoice->due_date = $request->due_date;
            $invoice->reference = $request->reference;
            $invoice->discount =  $request->discount ? $request->discount : 0;
            $invoice->terms_and_conditions =  $request->terms;
            $invoice->sub_total = $request->sub_total;
            $invoice->total =  $request->total;
            $invoice->save();

            if (!empty($request->items)) {

                foreach ($request->items as $item) {
                    $product = new Product();

                    $product->item_code = "IC-" . rand(10, 1000);
                    $product->description = $item['description'];
                    $product->unit_price = $item['unitPrice'];
                    $product->save();


                    $invoice_item = new InvoiceItem();
                    $invoice_item->invoice_id = $invoice->id;
                    $invoice_item->product_id = $product->id;
                    $invoice_item->unit_price = $item['unitPrice'];
                    $invoice_item->quantity = $item['quantity'];

                    $invoice_item->save();
                }
            }



            return response()->json($invoice);
        } else {
            return false;
        }
    }
}
