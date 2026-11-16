<?php


namespace App\Http\Controllers;

use App\Models\BlackListCustomer;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Product_Sale;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductBatch;
use App\Models\Delivery;
use App\Models\Payment;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Mail\DeliveryDetails;
use App\Mail\DeliveryChallan;
use Mail;
use App\Models\MailSetting;
use Illuminate\Support\Facades\Cache;
use App\Models\Courier;
use App\Models\Account;

class DeliveryController extends Controller
{
    use \App\Traits\MailInfo;

    public function index()
    {
        if(Auth::user()->hasPermissionTo('delivery')) {
            $isStaff = Auth::user()->roles->contains(function ($role) {
                return $role->id > 2;
            });

             if ($isStaff && config('staff_access') == 'own')
                 $lims_delivery_all = Delivery::with('sale', 'user')->orderBy('id', 'desc')->where('user_id', Auth::id())->get();
             else
                 $lims_delivery_all = Delivery::with('sale', 'user')->orderBy('id', 'desc')->get();

            //dd($lims_delivery_all);

             $lims_courier_list = Courier::where('is_active', true)->get();
             return view('backend.delivery.index', compact('lims_delivery_all', 'lims_courier_list'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function create($id){
        $lims_delivery_data = Delivery::where('sale_id', $id)->first();
        if($lims_delivery_data){
            $customer_sale = DB::table('sales')->join('customers', 'sales.customer_id', '=', 'customers.id')->where('sales.id', $id)->select('sales.reference_no','customers.name')->get();

            $delivery_data[] = $lims_delivery_data->reference_no;
            $delivery_data[] = $customer_sale[0]->reference_no;
            $delivery_data[] = $lims_delivery_data->status;
            $delivery_data[] = $lims_delivery_data->delivered_by;
            $delivery_data[] = $lims_delivery_data->recieved_by;
            $delivery_data[] = $customer_sale[0]->name;
            $delivery_data[] = $lims_delivery_data->address;
            $delivery_data[] = $lims_delivery_data->note;
            $delivery_data[] = $lims_delivery_data->courier_id;
        }
        else{
            $customer_sale = DB::table('sales')->join('customers', 'sales.customer_id', '=', 'customers.id')->where('sales.id', $id)->select('sales.reference_no','customers.name', 'customers.address', 'customers.city', 'customers.country')->get();

            $delivery_data[] = 'dr-' . date("Ymd") . '-'. date("his");
            $delivery_data[] = $customer_sale[0]->reference_no;
            $delivery_data[] = '';
            $delivery_data[] = '';
            $delivery_data[] = '';
            $delivery_data[] = $customer_sale[0]->name;
            $delivery_data[] = $customer_sale[0]->address.' '.$customer_sale[0]->city.' '.$customer_sale[0]->country;
            $delivery_data[] = '';
        }
        return $delivery_data;
    }

    public function store(Request $request)
    {
        $data = $request->except('file');
        $delivery = Delivery::firstOrNew(['reference_no' => $data['reference_no'] ]);
        $document = $request->file;
        if ($document) {
            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = $data['reference_no'] . '.' . $ext;
            $document->move('public/documents/delivery', $documentName);
            $delivery->file = $documentName;
        }
        $delivery->sale_id = $data['sale_id'];
        $delivery->user_id = Auth::id();
        $delivery->courier_id = $data['courier_id'];
        $delivery->address = $data['address'];
        $delivery->delivered_by = $data['delivered_by'];
        $delivery->recieved_by = $data['recieved_by'];
        $delivery->status = $data['status'];
        $delivery->note = $data['note'];
        $delivery->save();
        $lims_sale_data = Sale::find($data['sale_id']);
        $lims_customer_data = Customer::find($lims_sale_data->customer_id);
        $message = 'Delivery created successfully';
        $mail_setting = MailSetting::latest()->first();
        if($lims_customer_data->email && $data['status'] != 1 && $mail_setting) {
            $mail_data['email'] = $lims_customer_data->email;
            $mail_data['customer'] = $lims_customer_data->name;
            $mail_data['sale_reference'] = $lims_sale_data->reference_no;
            $mail_data['delivery_reference'] = $delivery->reference_no;
            $mail_data['status'] = $data['status'];
            $mail_data['address'] = $data['address'];
            $mail_data['delivered_by'] = $data['delivered_by'];
            $this->setMailInfo($mail_setting);
            try{
                Mail::to($mail_data['email'])->send(new DeliveryDetails($mail_data));
            }
            catch(\Exception $e){
                $message = 'Delivery created successfully. Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
            }
        }
        return redirect('delivery')->with('message', $message);
    }

    public function productDeliveryData($id)
    {
        $lims_delivery_data = Delivery::find($id);
        //return 'madarchod';
        $lims_product_sale_data = Product_Sale::where('sale_id', $lims_delivery_data->sale->id)->get();

        foreach ($lims_product_sale_data as $key => $product_sale_data) {
            $product = Product::select('name', 'code')->find($product_sale_data->product_id);
            if($product_sale_data->variant_id) {
                $lims_product_variant_data = ProductVariant::select('item_code')->FindExactProduct($product_sale_data->product_id, $product_sale_data->variant_id)->first();
                $product->code = $lims_product_variant_data->item_code;
            }
            if($product_sale_data->product_batch_id) {
                $product_batch_data = ProductBatch::select('batch_no', 'expired_date')->find($product_sale_data->product_batch_id);
                if($product_batch_data) {
                    $batch_no = $product_batch_data->batch_no;
                    $expired_date = date(config('date_format'), strtotime($product_batch_data->expired_date));
                }
            }
            else {
                $batch_no = 'N/A';
                $expired_date = 'N/A';
            }
            $product_sale[0][$key] = $product->code;
            $product_sale[1][$key] = $product->name;
            $product_sale[2][$key] = $batch_no;
            $product_sale[3][$key] = $expired_date;
            $product_sale[4][$key] = $product_sale_data->qty;
        }
        return $product_sale;
    }

    public function sendMail(Request $request)
    {
        $data = $request->all();
        $lims_delivery_data = Delivery::find($data['delivery_id']);
        $lims_sale_data = Sale::find($lims_delivery_data->sale->id);
        $lims_product_sale_data = Product_Sale::where('sale_id', $lims_delivery_data->sale->id)->get();
        $lims_customer_data = Customer::find($lims_sale_data->customer_id);
        $mail_setting = MailSetting::latest()->first();
        if($lims_customer_data->email && $mail_setting) {
            //collecting male data
            $mail_data['email'] = $lims_customer_data->email;
            $mail_data['date'] = date(config('date_format'), strtotime($lims_delivery_data->created_at->toDateString()));
            $mail_data['delivery_reference_no'] = $lims_delivery_data->reference_no;
            $mail_data['sale_reference_no'] = $lims_sale_data->reference_no;
            $mail_data['status'] = $lims_delivery_data->status;
            $mail_data['customer_name'] = $lims_customer_data->name;
            $mail_data['address'] = $lims_customer_data->address . ', '.$lims_customer_data->city;
            $mail_data['phone_number'] = $lims_customer_data->phone_number;
            $mail_data['note'] = $lims_delivery_data->note;
            $mail_data['prepared_by'] = $lims_delivery_data->user->name;
            if($lims_delivery_data->delivered_by)
                $mail_data['delivered_by'] = $lims_delivery_data->delivered_by;
            else
                $mail_data['delivered_by'] = 'N/A';
            if($lims_delivery_data->recieved_by)
                $mail_data['recieved_by'] = $lims_delivery_data->recieved_by;
            else
                $mail_data['recieved_by'] = 'N/A';
            //return $mail_data;

            foreach ($lims_product_sale_data as $key => $product_sale_data) {
                $lims_product_data = Product::select('code', 'name')->find($product_sale_data->product_id);
                $mail_data['codes'][$key] = $lims_product_data->code;
                $mail_data['name'][$key] = $lims_product_data->name;
                if($product_sale_data->variant_id) {
                    $lims_product_variant_data = ProductVariant::select('item_code')->FindExactProduct($product_sale_data->product_id, $product_sale_data->variant_id)->first();
                    $mail_data['codes'][$key] = $lims_product_variant_data->item_code;
                }
                $mail_data['qty'][$key] = $product_sale_data->qty;
            }
            $this->setMailInfo($mail_setting);
            try{
                Mail::to($mail_data['email'])->send(new DeliveryChallan($mail_data));
                $message = 'Mail sent successfully';
            }
            catch(\Exception $e){
                $message = 'Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
            }
        }
        else
            $message = 'Customer does not have email!';

        return redirect()->back()->with('message', $message);
    }

    public function edit($id)
    {
        $lims_delivery_data = Delivery::find($id);
        $customer_sale = DB::table('sales')->join('customers', 'sales.customer_id', '=', 'customers.id')->where('sales.id', $lims_delivery_data->sale_id)->select('sales.reference_no','customers.name')->get();

        $delivery_data[] = $lims_delivery_data->reference_no;
        $delivery_data[] = $customer_sale[0]->reference_no;
        $delivery_data[] = $lims_delivery_data->status;
        $delivery_data[] = $lims_delivery_data->delivered_by;
        $delivery_data[] = $lims_delivery_data->recieved_by;
        $delivery_data[] = $customer_sale[0]->name;
        $delivery_data[] = $lims_delivery_data->address;
        $delivery_data[] = $lims_delivery_data->note;
        $delivery_data[] = $lims_delivery_data->courier_id;
        return $delivery_data;
    }

    public function update(Request $request)
    {
        $input = $request->except('file');
        //return $input;
        $lims_delivery_data = Delivery::find($input['delivery_id']);
        $document = $request->file;
        if ($document) {
            $this->fileDelete('documents/delivery/', $lims_delivery_data->file);
            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = $input['reference_no'] . '.' . $ext;
            $document->move('public/documents/delivery', $documentName);
            $input['file'] = $documentName;
        }
        $lims_delivery_data->update($input);
        $lims_sale_data = Sale::find($lims_delivery_data->sale_id);
        $lims_customer_data = Customer::find($lims_sale_data->customer_id);
        $message = 'Delivery updated successfully';
        $mail_setting = MailSetting::latest()->first();
        $customer = Customer::where('email', $lims_customer_data->email)->first();
        if ($input['status'] == 0) {
            if (isset($customer->id)) {
                $check = BlackListCustomer::where([
                    ['customer_id', $customer->id],
                    ['delivery_id', $input['delivery_id']]
                ])->first();
                if (!isset($check->id)) {
                    BlackListCustomer::create([
                        'delivery_id' => $input['delivery_id'],
                        "customer_id" => $customer->id,
                        "total_delivery_missed" => 1,
                        "last_delivery_miss_date" => date('Y-m-d')
                    ]);
                }
            }
        } else if($lims_customer_data->email && $input['status'] != 1 && $mail_setting) {
            $mail_data['email'] = $lims_customer_data->email;
            $mail_data['customer'] = $lims_customer_data->name;
            $mail_data['sale_reference'] = $lims_sale_data->reference_no;
            $mail_data['delivery_reference'] = $lims_delivery_data->reference_no;
            $mail_data['status'] = $input['status'];
            $mail_data['address'] = $input['address'];
            $mail_data['delivered_by'] = $input['delivered_by'];
            $this->setMailInfo($mail_setting);
            try{
                Mail::to($mail_data['email'])->send(new DeliveryDetails($mail_data));
            }
            catch(\Exception $e){
                $message = 'Delivery updated successfully. Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
            }
            BlackListCustomer::where([
                ['customer_id', $customer->id],
                ['delivery_id', $input['delivery_id']]
            ])->delete();
        } else {
            BlackListCustomer::where([
                ['customer_id', $customer->id],
                ['delivery_id', $input['delivery_id']]
            ])->delete();
        }
        return redirect('delivery')->with('message', $message);
    }

    public function markDelivered($id)
    {
        $delivery = Delivery::findOrFail($id);

        if ((int) $delivery->status === 0 || (int) $delivery->status === 4) {
            return redirect()->back()->with('not_permitted', 'This order is not ready to be marked delivered.');
        }

        DB::transaction(function () use ($delivery) {
            $delivery->update([
                'status' => 3,
            ]);

            $sale = $delivery->sale;
            $isPayOnDelivery = str_contains((string) $sale?->staff_note, 'Pay on Delivery');
            if ($sale && $isPayOnDelivery && (int) $sale->payment_status !== 4) {
                // COD inventory was reserved at checkout; delivery only records the cash collection.
                $sale->update([
                    'sale_status' => 1,
                    'payment_status' => 4,
                    'paid_amount' => $sale->grand_total,
                ]);

                Payment::create([
                    'sale_id' => $sale->id,
                    'user_id' => auth()->id() ?? $sale->user_id,
                    'account_id' => Account::where('is_default', true)->value('id')
                        ?? Account::where('is_active', true)->value('id'),
                    'payment_reference' => 'cod-' . date('Ymd') . '-' . $sale->id,
                    'amount' => $sale->grand_total,
                    'change' => 0,
                    'paying_method' => 'Pay on Delivery',
                    'payment_note' => 'Cash collected at delivery',
                ]);
            }
        });

        return redirect()->route('delivery.index')
            ->with('message', 'Order marked as delivered.');
    }

    /**
     * Quick status update from the Deliver button on the delivery index page.
     * Allowed statuses: 2 = Delivering (courier dispatched), 3 = Delivered, 0 = Customer Unavailable.
     * When Delivered and the related sale_status is not completed, the response tells the
     * page to open the payment modal so the customer can settle the balance.
     */
    public function updateStatus(Request $request)
    {
        $data = $request->validate([
            'delivery_id'  => 'required|integer|exists:deliveries,id',
            'status'       => 'required|in:0,2,3',
            'delivered_by' => 'nullable|string|max:191',
            'recieved_by'  => 'nullable|string|max:191',
            'note'         => 'nullable|string|max:1000',
        ]);

        $lims_delivery_data = Delivery::findOrFail($data['delivery_id']);
        $lims_delivery_data->status = (int) $data['status'];
        if (isset($data['delivered_by']))
            $lims_delivery_data->delivered_by = $data['delivered_by'];
        if (isset($data['recieved_by']))
            $lims_delivery_data->recieved_by = $data['recieved_by'];
        if (isset($data['note']))
            $lims_delivery_data->note = $data['note'];
        $lims_delivery_data->save();

        $lims_sale_data = Sale::find($lims_delivery_data->sale_id);
        $lims_customer_data = Customer::find($lims_sale_data->customer_id);

        // Blacklist handling for Customer Unavailable, same rule as the full update() method
        $customer = Customer::where('email', $lims_customer_data->email)->first();
        if ((int) $data['status'] === 0) {
            if (isset($customer->id)) {
                $check = BlackListCustomer::where([
                    ['customer_id', $customer->id],
                    ['delivery_id', $data['delivery_id']]
                ])->first();
                if (!isset($check->id)) {
                    BlackListCustomer::create([
                        'delivery_id' => $data['delivery_id'],
                        "customer_id" => $customer->id,
                        "total_delivery_missed" => 1,
                        "last_delivery_miss_date" => date('Y-m-d')
                    ]);
                }
            }
        } else {
            BlackListCustomer::where([
                ['customer_id', $customer->id ?? 0],
                ['delivery_id', $data['delivery_id']]
            ])->delete();
        }

        // Notify the customer exactly like update() does (Packing status is skipped)
        $message = 'Delivery status updated successfully';
        $mail_setting = MailSetting::latest()->first();
        if ((int) $data['status'] !== 0 && $lims_customer_data->email && $mail_setting) {
            $mail_data['email'] = $lims_customer_data->email;
            $mail_data['customer'] = $lims_customer_data->name;
            $mail_data['sale_reference'] = $lims_sale_data->reference_no;
            $mail_data['delivery_reference'] = $lims_delivery_data->reference_no;
            $mail_data['status'] = $data['status'];
            $mail_data['address'] = $lims_delivery_data->address;
            $mail_data['delivered_by'] = $data['delivered_by'] ?? $lims_delivery_data->delivered_by;
            $this->setMailInfo($mail_setting);
            try{
                Mail::to($mail_data['email'])->send(new DeliveryDetails($mail_data));
            }
            catch(\Exception $e){
                $message = 'Delivery status updated successfully. Please setup your <a href="setting/mail_setting">mail setting</a> to send mail.';
            }
        }

        // On Delivered: check sale_status; if not completed the customer must make the payment
        $requires_payment = false;
        $sale_payload = null;
        if ((int) $data['status'] === 3) {
            $returned_amount = DB::table('returns')->where('sale_id', $lims_sale_data->id)->sum('grand_total');
            $balance = round((float) $lims_sale_data->grand_total - (float) $returned_amount - (float) $lims_sale_data->paid_amount, 2);

            if ((int) $lims_sale_data->sale_status !== 1) {
                if ($balance > 0) {
                    $requires_payment = true;
                    $sale_payload = [
                        'id' => $lims_sale_data->id,
                        'reference_no' => $lims_sale_data->reference_no,
                        'customer' => $lims_customer_data->name,
                        'grand_total' => (float) $lims_sale_data->grand_total,
                        'paid_amount' => (float) $lims_sale_data->paid_amount,
                        'balance' => $balance,
                        'grand_total_formatted' => number_format((float) $lims_sale_data->grand_total, config('decimal')),
                        'paid_formatted' => number_format((float) $lims_sale_data->paid_amount, config('decimal')),
                        'balance_formatted' => number_format($balance, config('decimal')),
                    ];
                } else {
                    // Nothing left to collect: complete the sale right away
                    $lims_sale_data->sale_status = 1;
                    $lims_sale_data->payment_status = 4;
                    $lims_sale_data->save();
                    $message .= ' No payment required, sale marked as completed.';
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'requires_payment' => $requires_payment,
            'sale' => $sale_payload
        ]);
    }

    /**
     * Records the payment collected at delivery. Once the sale is fully paid
     * the sale_status is marked completed (1) and payment_status paid (4).
     */
    public function recordDeliveryPayment(Request $request)
    {
        $data = $request->validate([
            'delivery_id'   => 'required|integer|exists:deliveries,id',
            'amount'        => 'required|numeric|min:0.01',
            'paying_method' => 'required|string|in:Cash,Mobile Money,Credit Card,Cheque,Other',
            'payment_note'  => 'nullable|string|max:1000',
        ]);

        $lims_delivery_data = Delivery::findOrFail($data['delivery_id']);
        $lims_sale_data = Sale::findOrFail($lims_delivery_data->sale_id);

        $returned_amount = DB::table('returns')->where('sale_id', $lims_sale_data->id)->sum('grand_total');
        $balance = round((float) $lims_sale_data->grand_total - (float) $returned_amount - (float) $lims_sale_data->paid_amount, 2);

        if ($balance <= 0)
            return response()->json(['success' => false, 'message' => 'This sale has no outstanding balance.'], 422);

        if ((float) $data['amount'] > $balance)
            return response()->json(['success' => false, 'message' => 'The amount exceeds the outstanding balance of ' . number_format($balance, config('decimal')) . '.'], 422);

        DB::transaction(function () use ($data, $lims_sale_data, $returned_amount) {
            Payment::create([
                'sale_id' => $lims_sale_data->id,
                'user_id' => Auth::id() ?? $lims_sale_data->user_id,
                'account_id' => Account::where('is_default', true)->value('id')
                    ?? Account::where('is_active', true)->value('id'),
                'payment_reference' => 'dr-' . date("Ymd") . '-' . date("his"),
                'amount' => $data['amount'],
                'change' => 0,
                'paying_method' => $data['paying_method'],
                'payment_note' => $data['payment_note'] ?? 'Collected at delivery',
            ]);

            $lims_sale_data->paid_amount = (float) $lims_sale_data->paid_amount + (float) $data['amount'];
            $new_balance = round((float) $lims_sale_data->grand_total - (float) $returned_amount - (float) $lims_sale_data->paid_amount, 2);
            if ($new_balance <= 0.009) {
                // Fully collected: clear float drift and complete the sale
                $lims_sale_data->paid_amount = (float) $lims_sale_data->grand_total - (float) $returned_amount;
                $lims_sale_data->payment_status = 4; // Paid
                $lims_sale_data->sale_status = 1;    // Completed
            } else {
                $lims_sale_data->payment_status = 3; // Partial
            }
            $lims_sale_data->save();
        });

        $lims_sale_data->refresh();
        $new_balance = round((float) $lims_sale_data->grand_total - (float) $returned_amount - (float) $lims_sale_data->paid_amount, 2);

        if ((int) $lims_sale_data->sale_status === 1)
            $message = 'Payment recorded successfully. Sale marked as completed.';
        else
            $message = 'Payment recorded successfully. Remaining balance: ' . number_format(max($new_balance, 0), config('decimal'));

        return response()->json([
            'success' => true,
            'message' => $message,
            'sale_status' => (int) $lims_sale_data->sale_status,
            'payment_status' => (int) $lims_sale_data->payment_status,
            'balance' => $new_balance
        ]);
    }




    public function deleteBySelection(Request $request)
    {
        $delivery_id = $request['deliveryIdArray'];
        foreach ($delivery_id as $id) {
            $lims_delivery_data = Delivery::find($id);
            BlackListCustomer::where('delivery_id', $id)->delete();
            $this->fileDelete('documents/delivery/', $lims_delivery_data->file);
            $lims_delivery_data->delete();
        }
        return 'Delivery deleted successfully';
    }

    public function delete($id)
    {
        $lims_delivery_data = Delivery::find($id);
        BlackListCustomer::where('delivery_id', $id)->delete();
        $this->fileDelete('documents/delivery/', $lims_delivery_data->file);
        $lims_delivery_data->delete();

        return redirect('delivery')->with('not_permitted', 'Delivery deleted successfully');
    }




}
