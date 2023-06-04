<?php

namespace App\Http\Controllers\API;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\API\BaseController as BaseController;

class ProductController extends BaseController
{
    public function index()
    {
        $payments = Payment::get();
        $data = [];
        $data['items'] = $payments;
        $data['name'] = 'Pagos';

        return $this->sendResponse($data, 'Pagos listados correctamente.');
    }

    public function index_public()
    {
        $payments = Payment::inRandomOrder()->get();
        return $this->sendResponse($payments, 'Pagos listados correctamente.');
    }

    public function index_three()
    {
        $payments = Payment::inRandomOrder()->take(3)->get();
        return $this->sendResponse($payments, 'Pagos listados correctamente.');
    }

    public function show_public($uuid){
        $payment = Payment::where('uuid', $uuid)->first();
        if($payment){
            return $this->sendResponse($payment, 'Pago encontrado.');
        }else{
            return $this->sendError('Pago no encontrado.');
        }
    }

    public function show($id){
        $payment = Payment::find($id);
        if($payment){
            return $this->sendResponse($payment, 'Pago encontrado.');
        }else{
            return $this->sendError('Pago no encontrado.');
        }
    }

    public function store(Request $request)
    {
        $payment = Payment::create([
            'uuid' => Str::uuid(),
            'detail' => $request->get('detail'),
            'amount' => $request->get('amount'),
            'voucher' => $request->get('voucher'),
            'user_id' => $request->get('user_id')
        ]);

        $payment = $payment->fresh();

        return $this->sendResponse($payment, 'Pago creado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        $payment->update($request->all());

        return $this->sendResponse($payment, 'Pago actualizado correctamente.');
    }

    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();
        return $this->sendResponse([], 'Pago eliminado correctamente');
    }
}
