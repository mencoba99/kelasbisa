<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transactions;
use Yajra\DataTables\DataTables;
use DB;
use App\LogClasses;

class TransactionsController extends Controller
{
    /**
     * Halaman index
     * 
     * @return view
     */
    public function index()
    {
        $listStatus = Transactions::where('deleted_at', '=', null)->distinct()->get('status');
        $pendingTransactions = Transactions::where('deleted_at', '=', null)->where('status', '=', 'pending')->count();
        $doneTransactions = Transactions::where('deleted_at', '=', null)->where('status', '=', 'Done')->count();
        $totalTransactions = Transactions::where('deleted_at', '=', null)->count();
        $totalPrices = Transactions::where('deleted_at', '=', null)->where('status', '=', 'Done')->sum('total_prices');
        return view('pages.admin.transaction.transaction_view', compact('listStatus', 'pendingTransactions', 'doneTransactions', 'totalTransactions', 'totalPrices'));
    }

    /**
     * List data transaksi
     * 
     * @param $request menerima data
     * 
     * @return Datatable
     */
    public function getListTransaction(Request $request)
    {
        if ($request->ajax()) {
            if ($request->status == 'all') {
                $transactions = Transactions::where('deleted_at', '=', null)->with('user')->with('class')->orderBy('id', 'desc')->get();
            } else {
                $transactions = Transactions::where('deleted_at', '=', null)->where('status', '=', $request->status)->with('user')->with('class')->orderBy('id', 'desc')->get();
            }
            return DataTables::of($transactions)
                ->addColumn(
                    'action',
                    function ($transactions) {
                        if ($transactions->status == 'pending') {
                            return '<div class="btn-group">
                                <button class="btn btn-sm btn-primary btn-asign"
                                data-id="' . $transactions->id . '"
                                data-username="' . $transactions->user->name . '"
                                data-transaction="' . $transactions->total_prices . '"
                                data-classname="' . $transactions->class->name . '">Asign</button>
                            </div>';
                        } else {
                            return '<div class="btn-group">
                                <button class="btn btn-sm btn-danger btn-unasign"
                                data-id="' . $transactions->id . '"
                                data-code="' . $transactions->transaction_code . '"
                                >Unasign</button>
                            </div>';
                        }
                    }
                )
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
        }
    }

    /**
     * Asign user
     * 
     * @param $request menerima data
     * 
     * @return mixed
     */
    public function asignUser(Request $request)
    {
        $dataTransaction = Transactions::where('id', '=', $request->id);

        try {
            DB::beginTransaction();
            Transactions::where('id', '=', $request->id)->update(
                [
                    'status' => 'done',
                ]
            );

            $newLog = LogClasses::create(
                [
                    'transaction_id' => $request->id,
                    'user_id' => $dataTransaction->first()->user_id,
                    'class_id' => $dataTransaction->first()->class_id,
                    'transaction_code' => $dataTransaction->first()->transaction_code,
                ]
            );
            DB::commit();
            $response = [
                'status' => true,
                'message' => 'Berhasil asign user',
                'notes' => ''
            ];
        } catch (\Exception $e) {
            throw $e;
            DB::rollback();
            $response = [
                'status' => false,
                'message' => 'Gagal asign user',
                'notes' => ''
            ];
        }

        return response()->json($response);
    }

    /**
     * Unasign user
     * 
     * @param $request menerima data
     * 
     * @return mixed
     */
    public function unasignUser(Request $request)
    {
        $transactions_id = $request->id;
        $transactions_code = $request->code;

        try {
            DB::beginTransaction();
            LogClasses::where('transaction_id', '=', $transactions_id)->where('transaction_code', '=', $transactions_code)->delete();
            Transactions::where('id', '=', $transactions_id)->where('transaction_code', '=', $transactions_code)->delete();
            DB::commit();
            $response = [
                'status' => true,
                'message' => 'User berhasil di unasign'
            ];
        } catch (\Exception $e) {
            DB::rollback();
            $response = [
                'status' => false,
                'message' => 'User gagal di unasign'
            ];
        }

        return response()->json($response);
    }

    /**
     * Batalkan transaksi
     * 
     * @param $request id transaksi
     * 
     * @return json
     */
    public function deleteTransaction(Request $request)
    {
        $delete = Transactions::where('id', '=', $request->id)->where('user_id', '=', Auth()->user()->id)->delete();
        if ($delete) {
            $response = [
                'status' => true,
                'message' => 'Berhasil membatalkan transaksi',
                'notes' => ''
            ];
        } else {
            $response = [
                'status' => true,
                'message' => 'Berhasil membatalkan transaksi',
                'notes' => ''
            ];
        }
        return response()->json($response);
    }
}
