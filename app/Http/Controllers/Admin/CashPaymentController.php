<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashPayment;
use Illuminate\Http\Request;

class CashPaymentController extends Controller
{
    public function index(){
        $cashPayments = '';
        return view('admin.cashPayments.index', compact('cashPayments'));
    }

    public function create(){
        $serial_no = '654412364';
        return view('admin.cashPayments.create',compact('serial_no'));
    }

    public function store(Request $request)
    {


        return redirect()->route('admin.cashPayments.index')->with('success', 'Cash Payment Voucher Created Successfully.');
    }

    public function show()
    {
        $cashPayments='';
        return view('admin.cashPayments.show', compact('cashPayments'));
    }

    public function destroy()
    {
        
        return redirect()->route('admin.cashPayments.index')->with('delete_msg', 'Cash Payment Voucher Deleted Successfully.');
    }
}
