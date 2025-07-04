<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientInvoice;
use Illuminate\Http\Request;

class ClientInvoiceController extends Controller
{
    public function index(){
        $clientInvoices = '';
        return view('admin.clientInvoices.index', compact('clientInvoices'));
    }

    public function create(){
        $serial_no = '654412364';
        return view('admin.clientInvoices.create',compact('serial_no'));
    }

    public function store(Request $request)
    {


        return redirect()->route('admin.clientInvoices.index')->with('success', 'Client Invoice Created Successfully.');
    }

    public function show()
    {
        $clientInvoices='';
        return view('admin.clientInvoices.show', compact('clientInvoices'));
    }

    public function destroy()
    {
        
        return redirect()->route('admin.clientInvoices.index')->with('delete_msg', 'Client Invoice Deleted Successfully.');
    }
}
