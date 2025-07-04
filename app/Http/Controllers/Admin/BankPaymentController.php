<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankPayment;
use Illuminate\Http\Request;

class BankPaymentController extends Controller
{
    public function index(){
        $bankPayments = '';
        return view('admin.bankPayments.index', compact('bankPayments'));
    }

    public function create(){
        $serial_no = '654412364';
        return view('admin.bankPayments.create',compact('serial_no'));
    }

    public function store(Request $request)
    {


        return redirect()->route('admin.bankPayments.index')->with('success', 'Bank Pyament Voucher created successfully.');
    }

    public function show()
    {
        $bankPayments='';
        return view('admin.bankPayments.show', compact('bankPayments'));
    }

    public function destroy()
    {
        
        return redirect()->route('admin.bankPayments.index')->with('delete_msg', 'Bank Pyament Voucher deleted successfully.');
    }
}
